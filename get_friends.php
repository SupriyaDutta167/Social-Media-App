<?php
include 'config.php';

if (!isset($_SESSION['user'])) {
    echo "Please login first.";
    exit();
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT u.id, u.name 
    FROM users u
    JOIN friends f ON (f.friend_id = u.id AND f.user_id = ?) OR (f.user_id = u.id AND f.friend_id = ?)
    WHERE f.status = 'accepted' AND u.id != ?
    ORDER BY u.name ASC
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p>No friends found. <a href='search_friend.php' style='color: #3498db; text-decoration: none;'>Search for friends</a></p>";
} else {
    echo "<p><strong>Your Friends (" . $result->num_rows . "):</strong></p>";
    while ($row = $result->fetch_assoc()) {
        echo "<button class='friend-btn' onclick='startChat({$row['id']}, \"" . h($row['name']) . "\")'>" . h($row['name']) . "</button>";
    }
}
?>