<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
include 'connection.php';

// Lấy danh sách phòng ban và trưởng phòng hiện tại
$sql = "SELECT d.dept_no, d.dept_name, e.first_name, e.last_name
        FROM departments d
        LEFT JOIN dept_manager dm ON d.dept_no = dm.dept_no AND (dm.to_date IS NULL OR dm.to_date = '9999-01-01')
        LEFT JOIN employees e ON dm.emp_no = e.emp_no";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Departments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Departments</h1>
    </header>
    <div class="card">
        <table>
            <tr>
                <th>Dept No</th>
                <th>Dept Name</th>
                <th>Manager</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['dept_no']); ?></td>
                <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                <td>
                    <?php
                    if ($row['first_name']) {
                        echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                    } else {
                        echo '<span style="color:#888;">(No manager)</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <a href="manage_managers.php" class="btn btn-secondary" style="margin-top:20px;">Manage Managers</a>
        <a href="starting.php" class="btn btn-secondary" style="margin-top:20px;">Back to Dashboard</a>
    </div>
</div>
</body>
</html>