<?php
include 'config.php';
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user']['id'];

if (isset($_POST['action']) && isset($_POST['requester_id'])) {
    $requester_id = intval($_POST['requester_id']);
    $action = $_POST['action'];
    
    header('Content-Type: application/json');
    
    if ($action === 'accept') {
        $stmt = $conn->prepare("UPDATE friends SET status='accepted' WHERE user_id=? AND friend_id=?");
        $stmt->bind_param("ii", $requester_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Friend request accepted!', 'action' => 'accept']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error accepting request']);
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM friends WHERE user_id=? AND friend_id=?");
        $stmt->bind_param("ii", $requester_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Friend request rejected!', 'action' => 'reject']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error rejecting request']);
        }
    } elseif ($action === 'block') {
        $stmt = $conn->prepare("DELETE FROM friends WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?)");
        $stmt->bind_param("iiii", $requester_id, $user_id, $user_id, $requester_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'blocked')");
        $stmt->bind_param("ii", $user_id, $requester_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User blocked!', 'action' => 'block']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error blocking user']);
        }
    } elseif ($action === 'follow_back') {
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $requester_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Follow request sent!', 'action' => 'follow_back']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error sending follow request']);
        }
    }
    exit();
}

if (isset($_GET['accept'])) {
    $requester_id = intval($_GET['accept']);
    $stmt = $conn->prepare("UPDATE friends SET status='accepted' WHERE user_id=? AND friend_id=?");
    $stmt->bind_param("ii", $requester_id, $user_id);
    if ($stmt->execute()) {
        echo "<p style='color:green;text-align:center;'>Friend request accepted!</p>";
    } else {
        echo "<p style='color:red;text-align:center;'>Error accepting request!</p>";
    }
}

if (isset($_GET['reject'])) {
    $requester_id = intval($_GET['reject']);
    $stmt = $conn->prepare("DELETE FROM friends WHERE user_id=? AND friend_id=?");
    $stmt->bind_param("ii", $requester_id, $user_id);
    if ($stmt->execute()) {
        echo "<p style='color:red;text-align:center;'>Friend request rejected!</p>";
    } else {
        echo "<p style='color:red;text-align:center;'>Error rejecting request!</p>";
    }
}

$stmt = $conn->prepare("
    SELECT users.id, users.name 
    FROM friends
    JOIN users ON friends.user_id = users.id
    WHERE friends.friend_id = ? AND friends.status = 'pending'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Friend Requests</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f0f2f5;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .request {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: opacity 0.3s ease;
        }
        .request span {
            font-size: 16px;
            font-weight: bold;
        }
        .actions button {
            padding: 6px 10px;
            margin-left: 5px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .accept {
            background-color: #28a745;
        }
        .accept:hover {
            background-color: #218838;
        }
        .reject {
            background-color: #dc3545;
        }
        .reject:hover {
            background-color: #c82333;
        }
        .block {
            background-color: #6c757d;
        }
        .block:hover {
            background-color: #5a6268;
        }
        .follow-back {
            background-color: #007bff;
        }
        .follow-back:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .hidden {
            display: none;
        }
        .fade-out {
            opacity: 0.3;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Pending Friend Requests</h2>
        <div id="message-container"></div>
        <div id="requests-container">
            <?php
            if ($result->num_rows == 0) {
                echo "<p id='no-requests' style='text-align:center;'>No pending requests.</p>";
            } else {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='request' data-user-id='{$row['id']}'>";
                    echo "<span>" . htmlspecialchars($row['name']) . "</span>";
                    echo "<div class='actions'>
                        <button class='accept' onclick='handleRequest({$row['id']}, \"accept\")'>Accept</button>
                        <button class='reject' onclick='handleRequest({$row['id']}, \"reject\")'>Reject</button>
                        <button class='block' onclick='handleRequest({$row['id']}, \"block\")'>Block</button>
                    </div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>

    <script>
        function showMessage(message, type = 'success') {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `<div class="message ${type}">${message}</div>`;
            
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 3000);
        }

        function handleRequest(userId, action) {
            const requestElement = document.querySelector(`[data-user-id="${userId}"]`);
            
            requestElement.classList.add('fade-out');
            
            const formData = new FormData();
            formData.append('requester_id', userId);
            formData.append('action', action);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    if (data.action === 'accept') {
                        const actionsDiv = requestElement.querySelector('.actions');
                        actionsDiv.innerHTML = `
                            <button class='follow-back' onclick='handleRequest(${userId}, "follow_back")'>Follow Back</button>
                            <span style='color: #28a745; font-size: 12px; margin-left: 10px;'>✓ Accepted</span>
                        `;
                        requestElement.classList.remove('fade-out');
                    } else if (data.action === 'follow_back') {
                        showMessage('Follow request sent successfully!', 'success');
                        setTimeout(() => {
                            requestElement.style.display = 'none';
                            checkIfNoRequests();
                        }, 1000);
                    } else {
                        setTimeout(() => {
                            requestElement.style.display = 'none';
                            checkIfNoRequests();
                        }, 500);
                    }
                } else {
                    showMessage(data.message || 'Something went wrong. Please try again.', 'error');
                    requestElement.classList.remove('fade-out');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
                requestElement.classList.remove('fade-out');
            });
        }
        
        function checkIfNoRequests() {
            const requestsContainer = document.getElementById('requests-container');
            const visibleRequests = requestsContainer.querySelectorAll('.request:not([style*="display: none"])');
            
            if (visibleRequests.length === 0) {
                requestsContainer.innerHTML = "<p id='no-requests' style='text-align:center;'>No pending requests.</p>";
            }
        }
    </script>
</body>
</html>