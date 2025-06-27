<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    echo "Please login first.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user']['id'];
    $content = trim($_POST['content']);

    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        
        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;

            $stmt2 = $conn->prepare("
                SELECT DISTINCT 
                    CASE 
                        WHEN user_id = ? THEN friend_id 
                        ELSE user_id 
                    END as friend_id
                FROM friends 
                WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'
            ");
            $stmt2->bind_param("iii", $user_id, $user_id, $user_id);
            $stmt2->execute();
            $result = $stmt2->get_result();

            $message = "Your friend posted: " . substr($content, 0, 50) . (strlen($content) > 50 ? "..." : "");
            $stmt3 = $conn->prepare("INSERT INTO notifications (user_id, post_id, message) VALUES (?, ?, ?)");
            
            while ($friend = $result->fetch_assoc()) {
                $stmt3->bind_param("iis", $friend['friend_id'], $post_id, $message);
                $stmt3->execute();
            }

            echo "Post created successfully!";
        } else {
            echo "Error creating post: " . $stmt->error;
        }
    } else {
        echo "Post content cannot be empty.";
    }
}
?>