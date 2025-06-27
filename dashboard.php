<?php
include 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

$user = $_SESSION['user'];
$userid = $user['id'];

$message = '';
$message_type = '';
if (isset($_GET['success']) && $_GET['success'] === 'profile_updated') {
    $message = 'Profile picture updated successfully!';
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'upload_failed':
            $message = 'Failed to upload file. Please try again.';
            break;
        case 'invalid_file_type':
            $message = 'Invalid file type. Please upload JPG, PNG, or GIF.';
            break;
        case 'file_too_large':
            $message = 'File too large. Maximum size is 5MB.';
            break;
        default:
            $message = 'An error occurred. Please try again.';
    }
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            position: fixed;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidebar img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: block;
            margin: 0 auto 15px;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.2);
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 12px 0;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
            min-height: 100vh;
        }

        h2 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-section {
            margin-bottom: 30px;
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .form-section h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        textarea {
            resize: vertical;
            height: 100px;
            font-family: inherit;
        }

        button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .post {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .post-author {
            color: #3498db;
            font-weight: 600;
            font-size: 16px;
        }

        .post-time {
            color: #95a5a6;
            font-size: 12px;
        }

        .post-content {
            line-height: 1.6;
            color: #2c3e50;
        }

        .chat-section {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        #chatBox {
            border: 2px solid #e1e8ed;
            padding: 20px;
            margin-top: 15px;
            border-radius: 12px;
            background-color: #f8f9fa;
        }

        #messages {
            height: 300px;
            overflow-y: auto;
            border: 2px solid #e1e8ed;
            padding: 15px;
            margin-bottom: 15px;
            background-color: white;
            border-radius: 8px;
        }

        .message {
            margin-bottom: 12px;
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .message.sent {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .message.received {
            background-color: #ecf0f1;
            color: #2c3e50;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 5px;
        }

        .friend-btn {
            display: inline-block;
            margin: 8px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .friend-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .notification-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .pagination button {
            padding: 8px 16px;
            background: #ecf0f1;
            color: #2c3e50;
            border: 1px solid #bdc3c7;
        }

        .pagination button.active {
            background: #3498db;
            color: white;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }

        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .success-message {
            background: #27ae60;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar a {
                display: inline-block;
                margin: 5px;
                padding: 8px 12px;
                font-size: 14px;
            }

            .form-section {
                padding: 15px;
            }

            .post {
                padding: 15px;
            }

            .chat-section {
                padding: 15px;
            }

            #messages {
                height: 200px;
            }

            .message {
                max-width: 85%;
            }
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <img src="assets/uploads/<?php echo h($user['profile_pic'] ?: 'default.png'); ?>" alt="Profile Picture" onerror="this.src='assets/uploads/default.png'">
    <a href="dashboard.php">🏠 Home</a>
    <a href="search_friend.php">👫 Find Friends</a>
    <a href="Friend_Request.php">📨 Friend Requests</a>
    <a href="notification.php">🔔 Notifications</a>
    <a href="post_status.php">📝 Post</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main-content">
    <h2>Welcome, <?php echo h($user['name']); ?> 👋</h2>

    <?php if ($message): ?>
        <div class="<?php echo $message_type; ?>-message"><?php echo h($message); ?></div>
    <?php endif; ?>

    <form action="update_profile.php" method="post" enctype="multipart/form-data">
        <label><strong>Change Profile Picture:</strong></label>
        <input type="file" name="profile_pic" accept="image/*" required>
        <button type="submit">Update</button>
    </form>

    <div class="form-section">
        <h3>🔍 Search Friends</h3>
        <form action="search_friend.php" method="get">
            <input type="text" name="search" placeholder="Enter name to search..." required>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="form-section">
        <h3>📝 Create a Post</h3>
        <textarea id="postContent" rows="4" placeholder="What's on your mind?"></textarea>
        <button onclick="submitPost()" id="postBtn">Post</button>
        <div id="postStatus"></div>
    </div>

    <div class="form-section">
        <h3>🔔 Recent Notifications</h3>
        <div id="notifications" class="loading">Loading notifications...</div>
    </div>

    <div class="chat-section">
        <h3>💬 Chat with Friends</h3>
        <div id="friendList" class="loading">Loading friends...</div>

        <div id="chatBox" style="display:none;">
            <h4 id="chatWith">Chatting with: </h4>
            <div id="messages"></div>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="msgInput" placeholder="Type message..." style="flex: 1;">
                <button onclick="sendMessage()" id="sendBtn">Send</button>
            </div>
        </div>
    </div>

    <?php 
    function getUserName($id, $conn) {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['name'];
        }
        return "Unknown User";
    }

    function getFriendIDs($userid, $conn) {
        $friendIDs = [$userid];
        $stmt = $conn->prepare("SELECT user_id, friend_id FROM friends WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'");
        $stmt->bind_param("ii", $userid, $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $friendID = ($row['user_id'] == $userid) ? $row['friend_id'] : $row['user_id'];
            if (!in_array($friendID, $friendIDs)) {
                $friendIDs[] = $friendID;
            }
        }
        return $friendIDs;
    }

    function displayPosts($start, $limit, $userid, $conn) {
        $friendIDs = getFriendIDs($userid, $conn);
        if (count($friendIDs) == 1) {
            echo "<div class='post'><p>No posts to show. Add some friends to see their posts!</p></div>";
            return;
        }
        
        $placeholders = str_repeat('?,', count($friendIDs) - 1) . '?';
        
        $sql = "SELECT * FROM posts WHERE user_id IN ($placeholders) ORDER BY id DESC LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        
        $types = str_repeat('i', count($friendIDs)) . 'ii';
        $params = array_merge($friendIDs, [$start, $limit]);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<h3>📰 Recent Posts</h3>";
        
        if ($result->num_rows == 0) {
            echo "<div class='post'><p>No posts found.</p></div>";
            return;
        }
        
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="post">
                <div class="post-header">
                    <span class="post-author"><?php echo h(getUserName($row['user_id'], $conn)); ?></span>
                    <span class="post-time"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></span>
                </div>
                <div class="post-content">
                    <?php echo nl2br(h($row['content'])); ?>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <div class="form-section">
        <h3>📄 Posts Navigation</h3>
        <div class="pagination">
            <button type="button" onclick="loadPage(1)" class="<?php echo (!isset($_GET['pagi']) || $_GET['pagi'] == 1) ? 'active' : ''; ?>">Page 1</button>
            <button type="button" onclick="loadPage(2)" class="<?php echo (isset($_GET['pagi']) && $_GET['pagi'] == 2) ? 'active' : ''; ?>">Page 2</button>
            <button type="button" onclick="loadPage(3)" class="<?php echo (isset($_GET['pagi']) && $_GET['pagi'] == 3) ? 'active' : ''; ?>">Page 3</button>
            <button type="button" onclick="loadPage(4)" class="<?php echo (isset($_GET['pagi']) && $_GET['pagi'] == 4) ? 'active' : ''; ?>">Page 4</button>
        </div>
    </div>

    <?php
    $page = isset($_GET['pagi']) ? intval($_GET['pagi']) : 1;
    $limit = 5;
    $start = ($page - 1) * $limit;
    displayPosts($start, $limit, $userid, $conn);
    ?>

</div>

<script>
let currentFriendId = null;
let messageInterval = null;

function loadFriends() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        document.getElementById("friendList").innerHTML = this.responseText;
    };
    xhttp.onerror = function() {
        document.getElementById("friendList").innerHTML = "<p>Error loading friends. Please refresh the page.</p>";
    };
    xhttp.open("GET", "get_friends.php", true);
    xhttp.send();
}

function startChat(friendId, friendName) {
    currentFriendId = friendId;
    document.getElementById("chatBox").style.display = "block";
    document.getElementById("chatWith").innerText = "Chatting with: " + friendName;
    
    if (messageInterval) {
        clearInterval(messageInterval);
    }
    
    fetchMessages();
    messageInterval = setInterval(fetchMessages, 3000);
    
    document.getElementById("msgInput").focus();
}

function fetchMessages() {
    if (!currentFriendId) return;
    
    const xhttp = new XMLHttpRequest();
    xhttp.open("POST", "fetch_messages.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.onload = function() {
        document.getElementById("messages").innerHTML = this.responseText;
        document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
    };
    xhttp.onerror = function() {
        console.error("Error fetching messages");
    };
    xhttp.send("receiver_id=" + currentFriendId);
}

function sendMessage() {
    const messageInput = document.getElementById("msgInput");
    const sendBtn = document.getElementById("sendBtn");
    const message = messageInput.value.trim();
    
    if (!message || !currentFriendId) return;
    
    messageInput.disabled = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner"></span>Sending...';
    
    const xhttp = new XMLHttpRequest();
    xhttp.open("POST", "send_message.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.onload = function() {
        if (this.responseText.includes("successfully")) {
            messageInput.value = "";
            fetchMessages();
        } else {
            alert("Error: " + this.responseText);
        }
        
        messageInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = 'Send';
        messageInput.focus();
    };
    xhttp.onerror = function() {
        alert("Network error. Please try again.");
        messageInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = 'Send';
    };
    xhttp.send("receiver_id=" + currentFriendId + "&message=" + encodeURIComponent(message));
}

function submitPost() {
    const content = document.getElementById("postContent").value.trim();
    const postBtn = document.getElementById("postBtn");
    const postStatus = document.getElementById("postStatus");
    
    if (!content) return;
    
    postBtn.disabled = true;
    postBtn.innerHTML = '<span class="spinner"></span>Posting...';
    postStatus.innerHTML = '';
    
    const xhttp = new XMLHttpRequest();
    xhttp.open("POST", "post_status.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.onload = function() {
        if (this.responseText.includes("successfully")) {
            postStatus.innerHTML = '<div class="success-message">Post created successfully!</div>';
            document.getElementById("postContent").value = "";
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            postStatus.innerHTML = '<div class="error-message">Error: ' + this.responseText + '</div>';
        }
        
        postBtn.disabled = false;
        postBtn.innerHTML = 'Post';
    };
    xhttp.onerror = function() {
        postStatus.innerHTML = '<div class="error-message">Network error. Please try again.</div>';
        postBtn.disabled = false;
        postBtn.innerHTML = 'Post';
    };
    xhttp.send("content=" + encodeURIComponent(content));
}

function loadNotifications() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        document.getElementById("notifications").innerHTML = this.responseText;
    };
    xhttp.onerror = function() {
        document.getElementById("notifications").innerHTML = "<p>Error loading notifications.</p>";
    };
    xhttp.open("GET", "notification.php", true); 
    xhttp.send();
}

function loadPage(page) {
    window.location.href = 'dashboard.php?pagi=' + page;
}

document.addEventListener('DOMContentLoaded', function() {
    const msgInput = document.getElementById("msgInput");
    if (msgInput) {
        msgInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
    
    const postContent = document.getElementById("postContent");
    if (postContent) {
        postContent.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                submitPost();
            }
        });
    }
});

setInterval(loadNotifications, 30000);

window.onload = function() {
    loadFriends();
    loadNotifications();
};
</script>

</body>
</html>