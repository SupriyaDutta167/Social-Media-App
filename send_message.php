<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    echo "Please login first.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

if (!isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    echo "Missing required parameters.";
    exit();
}

$sender_id = $_SESSION['user']['id'];
$receiver_id = intval($_POST['receiver_id']);
$message = trim($_POST['message']);

if ($receiver_id <= 0) {
    echo "Invalid receiver ID.";
    exit();
}

if (empty($message)) {
    echo "Message cannot be empty.";
    exit();
}

$friendship_check = $conn->prepare("
    SELECT * FROM friends 
    WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)) 
    AND status = 'accepted'
");
$friendship_check->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$friendship_check->execute();
$friendship = $friendship_check->get_result()->fetch_assoc();

if (!$friendship) {
    echo "You can only send messages to friends.";
    exit();
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    $sender_name = $_SESSION['user']['name'];
    $notification_message = $sender_name . " sent you a message: " . substr($message, 0, 30) . (strlen($message) > 30 ? "..." : "");
    
    $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, seen) VALUES (?, ?, FALSE)");
    $notification_stmt->bind_param("is", $receiver_id, $notification_message);
    $notification_stmt->execute();
    
    echo "Message sent successfully.";
} else {
    echo "Error sending message: " . $conn->error;
}
?>