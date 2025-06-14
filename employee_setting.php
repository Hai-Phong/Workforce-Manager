<?php
session_start();
if (!isset($_SESSION['employee_logged_in']) || !isset($_SESSION['emp_no'])) {
    header("Location: employee_login.php");
    exit();
}
include 'connection.php';

$emp_no = $_SESSION['emp_no'];
$success = '';
$error = '';

// Lấy thông tin cá nhân
$stmt = $conn->prepare("SELECT emp_no, first_name, last_name, birth_date, gender, hire_date FROM employees WHERE emp_no = ?");
$stmt->bind_param("i", $emp_no);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$current_gender = $employee['gender'] ?? '';
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Change gender
    if (isset($_POST['update_gender'])) {
        $gender = $_POST['gender'];
        $stmt = $conn->prepare("UPDATE employees SET gender = ? WHERE emp_no = ?");
        $stmt->bind_param("si", $gender, $emp_no);
        if ($stmt->execute()) {
            $success = "Gender updated successfully.";
            $current_gender = $gender;
            $employee['gender'] = $gender;
        } else {
            $error = "Failed to update gender.";
        }
        $stmt->close();
    }
    // Change password
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        // Fetch current password
        $stmt = $conn->prepare("SELECT password FROM employees WHERE emp_no = ?");
        $stmt->bind_param("i", $emp_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $error = "Account not found.";
        } else {
            $db_pass = $row['password'];
            if ((strpos($db_pass, '$2y$') === 0 && !password_verify($old_pass, $db_pass)) ||
                (strpos($db_pass, '$2y$') !== 0 && $old_pass !== $db_pass)) {
                $error = "Old password is incorrect.";
            } elseif ($new_pass !== $confirm_pass) {
                $error = "New passwords do not match.";
            } else {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE employees SET password = ? WHERE emp_no = ?");
                $stmt->bind_param("si", $hash, $emp_no);
                if ($stmt->execute()) {
                    $success = "Password updated successfully.";
                } else {
                    $error = "Failed to update password.";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 400px; margin: 0 auto; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; }
        input[type="password"], select {
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
            <h1>Employee Settings</h1>
        </header>
        <div class="form-container">
            <!-- Thông tin cá nhân -->
            <h3>Your Profile</h3>
            <table class="profile-table">
                <tr>
                    <th>Employee No</th>
                    <td><?php echo htmlspecialchars($employee['emp_no']); ?></td>
                </tr>
                <tr>
                    <th>First Name</th>
                    <td><?php echo htmlspecialchars($employee['first_name']); ?></td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td><?php echo htmlspecialchars($employee['last_name']); ?></td>
                </tr>
                <tr>
                    <th>Birth Date</th>
                    <td><?php echo htmlspecialchars($employee['birth_date']); ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td>
                        <?php
                        if ($employee['gender'] == 'M') echo 'Male';
                        elseif ($employee['gender'] == 'F') echo 'Female';
                        else echo 'Other';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Hire Date</th>
                    <td><?php echo htmlspecialchars($employee['hire_date']); ?></td>
                </tr>
            </table>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" style="margin-bottom: 32px;">
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" required>
                        <option value="M" <?php if ($current_gender == 'M') echo 'selected'; ?>>Male</option>
                        <option value="F" <?php if ($current_gender == 'F') echo 'selected'; ?>>Female</option>
                        <option value="O" <?php if ($current_gender == 'O') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
                <button type="submit" name="update_gender" class="btn">Update Gender</button>
            </form>

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
                <a href="employee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>