<?php
include 'config.php';

if (!isset($_SESSION['user'])) {
    echo "Please login first.";
    exit();
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND seen = FALSE ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='notification-item'><p>No new notifications.</p></div>";
} else {
    while ($row = $result->fetch_assoc()) {
        $time = date('M d, H:i', strtotime($row['created_at']));
        echo "<div class='notification-item'>";
        echo "<strong>" . h($row['message']) . "</strong>";
        echo "<br><small style='color:#666;'>$time</small>";
        echo "</div>";
    }
    
    $update = $conn->prepare("UPDATE notifications SET seen = TRUE WHERE user_id = ? AND seen = FALSE");
    $update->bind_param("i", $user_id);
    $update->execute();
}
?>