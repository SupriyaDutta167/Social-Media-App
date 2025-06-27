<?php
include 'config.php';

if (!isset($_SESSION['user'])) {
    echo "Please login first.";
    exit();
}

$sender_id = $_SESSION['user']['id'];
$receiver_id = intval($_POST['receiver_id'] ?? 0);

if ($receiver_id <= 0) {
    echo "Invalid receiver ID.";
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
    echo "You can only view messages with friends.";
    exit();
}

$stmt = $conn->prepare("
    SELECT sender_id, message, timestamp FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY timestamp ASC
    LIMIT 50
");
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p style='text-align: center; color: #7f8c8d; font-style: italic;'>No messages yet. Start the conversation!</p>";
} else {
    while ($row = $result->fetch_assoc()) {
        $isSender = $row['sender_id'] == $sender_id;
        $class = $isSender ? 'sent' : 'received';
        $time = date('H:i', strtotime($row['timestamp']));
        echo "<div class='message $class'>" . h($row['message']) . " <small class='message-time'>($time)</small></div>";
    }
}
?>