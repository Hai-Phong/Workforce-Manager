<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
include 'connection.php';

$success = '';
$error = '';
$admin_username = $_SESSION['admin_username'] ?? 'root';

// Lấy thông tin admin
$stmt = $conn->prepare("SELECT admin_username FROM admin_info WHERE admin_username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Lấy mật khẩu hiện tại
    $stmt = $conn->prepare("SELECT admin_password FROM admin_info WHERE admin_username = ?");
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $error = "Account not found.";
    } else {
        $db_pass = $row['admin_password'];
        // Nếu mật khẩu đã hash thì dùng password_verify, nếu chưa thì so sánh trực tiếp
        if ((strpos($db_pass, '$2y$') === 0 && !password_verify($old_pass, $db_pass)) ||
            (strpos($db_pass, '$2y$') !== 0 && $old_pass !== $db_pass)) {
            $error = "Old password is incorrect.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "New passwords do not match.";
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_info SET admin_password = ? WHERE admin_username = ?");
            $stmt->bind_param("ss", $hash, $admin_username);
            if ($stmt->execute()) {
                $success = "Password updated successfully.";
            } else {
                $error = "Failed to update password.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 400px; margin: 0 auto; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; }
        input[type="password"], input[type="text"] {
            width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;
        }
        .btn-container { display: flex; gap: 10px; }
        .success-message { color: #2a7; margin-bottom: 10px; }
        .error-message { color: #c00; margin-bottom: 10px; }
        .profile-table { width: 100%; margin-bottom: 32px; border-collapse: collapse; }
        .profile-table th, .profile-table td { text-align: left; padding: 6px 10px; }
        .profile-table th { width: 120px; color: #555; }
        .profile-table tr { border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Settings</h1>
        </header>
        <div class="form-container">
            <!-- Thông tin cá nhân admin -->
            <h3>Your Profile</h3>
            <table class="profile-table">
                <tr>
                    <th>Username</th>
                    <td><?php echo htmlspecialchars($admin['admin_username']); ?></td>
                </tr>
            </table>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="old_password">Old Password</label>
                    <input type="password" name="old_password" id="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-secondary">Change Password</button>
            </form>
            <div style="margin-top:20px;">
                <a href="starting.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>