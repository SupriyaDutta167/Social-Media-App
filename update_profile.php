<?php 
include 'config.php'; 

if (!isset($_SESSION['user'])){
    header('Location: login.php');
    exit();
} 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    header('Location: dashboard.php?error=upload_failed');
    exit();
}

$target_dir = "assets/uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$file = $_FILES['profile_pic'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_error = $file['error'];

$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    header('Location: dashboard.php?error=invalid_file_type');
    exit();
}

if ($file_size > 5 * 1024 * 1024) {
    header('Location: dashboard.php?error=file_too_large');
    exit();
}

$new_filename = time() . "_" . uniqid() . "." . $file_extension;
$target_path = $target_dir . $new_filename;

if (move_uploaded_file($file_tmp, $target_path)) {
    $old_profile_pic = $_SESSION['user']['profile_pic'];
    if ($old_profile_pic && $old_profile_pic !== 'default.png' && file_exists($target_dir . $old_profile_pic)) {
        unlink($target_dir . $old_profile_pic);
    }
    
    $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    $stmt->bind_param("si", $new_filename, $_SESSION['user']['id']);
    
    if ($stmt->execute()) {
        $_SESSION['user']['profile_pic'] = $new_filename;
        header('Location: dashboard.php?success=profile_updated');
    } else {
        unlink($target_path);
        header('Location: dashboard.php?error=database_error');
    }
} else {
    header('Location: dashboard.php?error=file_move_failed');
}

exit();
?>