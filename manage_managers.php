<?php
<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
include 'connection.php';

$success = '';
$error = '';

// Lấy danh sách phòng ban
$departments = [];
$res = $conn->query("SELECT dept_no, dept_name FROM departments");
while ($row = $res->fetch_assoc()) {
    $departments[$row['dept_no']] = $row['dept_name'];
}

// Lấy danh sách nhân viên
$employees = [];
$res = $conn->query("SELECT emp_no, first_name, last_name FROM employees ORDER BY first_name, last_name");
while ($row = $res->fetch_assoc()) {
    $employees[$row['emp_no']] = $row['first_name'] . ' ' . $row['last_name'];
}

// Bổ nhiệm trưởng phòng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dept_no'], $_POST['emp_no'])) {
    $dept_no = $_POST['dept_no'];
    $emp_no = $_POST['emp_no'];

    // Đặt to_date của trưởng phòng cũ về hôm nay
    $conn->query("UPDATE dept_manager SET to_date = CURDATE() WHERE dept_no = '$dept_no' AND (to_date IS NULL OR to_date = '9999-01-01')");

    // Thêm trưởng phòng mới
    $stmt = $conn->prepare("INSERT INTO dept_manager (dept_no, emp_no, from_date, to_date) VALUES (?, ?, CURDATE(), '9999-01-01')");
    $stmt->bind_param("si", $dept_no, $emp_no);
    if ($stmt->execute()) {
        $success = "Manager assigned successfully!";
    } else {
        $error = "Failed to assign manager.";
    }
    $stmt->close();
}

// Lấy trưởng phòng hiện tại cho từng phòng ban
$managers = [];
$res = $conn->query("SELECT dm.dept_no, e.first_name, e.last_name, dm.emp_no
    FROM dept_manager dm
    JOIN employees e ON dm.emp_no = e.emp_no
    WHERE (dm.to_date IS NULL OR dm.to_date = '9999-01-01')");
while ($row = $res->fetch_assoc()) {
    $managers[$row['dept_no']] = [
        'emp_no' => $row['emp_no'],
        'name' => $row['first_name'] . ' ' . $row['last_name']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Managers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .form-table th, .form-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .form-table th { background: #f5f5f5; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Manage Department Managers</h1>
    </header>
    <div class="form-container">
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>Department</th>
                    <th>Current Manager</th>
                    <th>Assign New Manager</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($departments as $dept_no => $dept_name): ?>
                <tr>
                    <td><?php echo htmlspecialchars($dept_name); ?></td>
                    <td>
                        <?php
                        if (isset($managers[$dept_no])) {
                            echo htmlspecialchars($managers[$dept_no]['name']) . " (ID: " . $managers[$dept_no]['emp_no'] . ")";
                        } else {
                            echo '<span style="color:#888;">(No manager)</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <select name="emp_no" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp_id => $emp_name): ?>
                                <option value="<?php echo $emp_id; ?>"><?php echo htmlspecialchars($emp_name) . " (ID: $emp_id)"; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="dept_no" value="<?php echo htmlspecialchars($dept_no); ?>">
                    </td>
                    <td>
                        <button type="submit" class="btn btn-secondary">Assign</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </form>
        <a href="departments.php" class="btn btn-secondary">Back to Departments</a>
    </div>
</div>
</body>
</html>