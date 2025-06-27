<?php 
include 'config.php'; 
if (!isset($_SESSION['user'])){
    header('Location: login.php');
    exit();
} 
$user_id = $_SESSION['user']['id'];

if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $friend_id = intval($_GET['add']);
    
    $check_stmt = $conn->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $check_stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if (!$existing) {
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $friend_id);
        if ($stmt->execute()) {
            echo "<div class='success-message'>Friend request sent successfully!</div>";
        }
    } else {
        echo "<div class='success-message'>Friend request already exists!</div>";
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Friends</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .header h3 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .search-section {
            padding: 30px;
            background: white;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e5e5e5;
            border-radius: 50px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .search-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .results-section {
            padding: 0 30px 30px;
        }
        
        .user-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            margin: 15px 0;
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: #4f46e5;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .add-friend-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .add-friend-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }
        
        .add-friend-btn.disabled {
            background: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 15px 25px;
            margin: 20px 30px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }
        
        .no-results::before {
            content: "🔍";
            display: block;
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .back-btn {
            display: inline-block;
            margin: 20px 30px;
            padding: 10px 20px;
            background: #666;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #555;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .user-card {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>🔍 Search Friends</h3>
        </div>
        
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="search-section">
            <form class="search-form" method="GET">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Enter name to search for friends..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                    required
                >
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>
        
        <div class="results-section">
            <?php
            if (!empty($search)) {
                $search_param = '%' . $search . '%';
                $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? AND id != ?");
                $stmt->bind_param("si", $search_param, $user_id);
                $stmt->execute();
                $res = $stmt->get_result();
                
                if ($res->num_rows == 0) {
                    echo "<div class='no-results'>No users found matching '<strong>" . htmlspecialchars($search) . "</strong>'</div>";
                } else {
                    while ($row = $res->fetch_assoc()) {
                        $fid = $row['id'];
                        
                        $check_stmt = $conn->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
                        $check_stmt->bind_param("iiii", $user_id, $fid, $fid, $user_id);
                        $check_stmt->execute();
                        $friendship = $check_stmt->get_result()->fetch_assoc();
                        
                        echo "<div class='user-card'>";
                        echo "<div class='user-info'>";
                        echo "<div class='user-avatar'>" . strtoupper(substr($row['name'], 0, 1)) . "</div>";
                        echo "<div class='user-name'>" . htmlspecialchars($row['name']) . "</div>";
                        echo "</div>";
                        
                        if (!$friendship) {
                            echo "<a class='add-friend-btn' href='?search=" . urlencode($search) . "&add=" . $row['id'] . "'>+ Add Friend</a>";
                        } else {
                            if ($friendship['status'] == 'pending') {
                                echo "<span class='add-friend-btn disabled'>Request Sent</span>";
                            } elseif ($friendship['status'] == 'accepted') {
                                echo "<span class='add-friend-btn disabled'>Already Friends</span>";
                            } elseif ($friendship['status'] == 'blocked') {
                                echo "<span class='add-friend-btn disabled'>Blocked</span>";
                            }
                        }
                        
                        echo "</div>";
                    }
                }
            }
            ?>
        </div>
    </div>
</body>
</html>