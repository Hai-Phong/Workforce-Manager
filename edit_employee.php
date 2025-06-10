<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
include 'connection.php';

// Fetch departments for dropdown
$departments = [];
$dept_res = $conn->query("SELECT dept_no, dept_name FROM departments");
while ($row = $dept_res->fetch_assoc()) {
    $departments[$row['dept_no']] = $row['dept_name'];
}

$success = '';
$error = '';
$employee = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_no = intval($_POST['emp_no']);

    // If searching for employee
    if (isset($_POST['search'])) {
        // Get employee info
        $stmt = $conn->prepare("SELECT e.emp_no, e.first_name, e.last_name, e.gender, e.birth_date, e.hire_date,
            de.dept_no, t.title, s.salary
            FROM employees e
            LEFT JOIN dept_emp de ON e.emp_no = de.emp_no AND (de.to_date IS NULL OR de.to_date = '9999-01-01')
            LEFT JOIN titles t ON e.emp_no = t.emp_no AND (t.to_date IS NULL OR t.to_date = '9999-01-01')
            LEFT JOIN salaries s ON e.emp_no = s.emp_no AND (s.to_date IS NULL OR s.to_date = '9999-01-01')
            WHERE e.emp_no = ?");
        $stmt->bind_param("i", $emp_no);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $employee = $result->fetch_assoc();
        } else {
            $error = "Employee not found.";
        }
        $stmt->close();
    }
    // If updating employee info (only department, title, salary)
    elseif (isset($_POST['update'])) {
        $dept_no = $_POST['department'];
        $title = trim($_POST['title']);
        $salary = intval($_POST['salary']);

        // Update dept_emp (set old to_date, insert new)
        $conn->query("UPDATE dept_emp SET to_date=CURDATE() WHERE emp_no=$emp_no AND (to_date IS NULL OR to_date='9999-01-01')");
        $stmt = $conn->prepare("INSERT INTO dept_emp (emp_no, dept_no, from_date, to_date) VALUES (?, ?, CURDATE(), '9999-01-01')");
        $stmt->bind_param("is", $emp_no, $dept_no);
        $stmt->execute();
        $stmt->close();

        // Update titles (set old to_date, insert new)
        $conn->query("UPDATE titles SET to_date=CURDATE() WHERE emp_no=$emp_no AND (to_date IS NULL OR to_date='9999-01-01')");
        $stmt = $conn->prepare("INSERT INTO titles (emp_no, title, from_date, to_date) VALUES (?, ?, CURDATE(), '9999-01-01')");
        $stmt->bind_param("is", $emp_no, $title);
        $stmt->execute();
        $stmt->close();

        // Update salaries (set old to_date, insert new)
        $conn->query("UPDATE salaries SET to_date=CURDATE() WHERE emp_no=$emp_no AND (to_date IS NULL OR to_date='9999-01-01')");
        $stmt = $conn->prepare("INSERT INTO salaries (emp_no, salary, from_date, to_date) VALUES (?, ?, CURDATE(), '9999-01-01')");
        $stmt->bind_param("ii", $emp_no, $salary);
        $stmt->execute();
        $stmt->close();

        $success = "Employee department, title, and salary updated successfully.";
        // Reload employee info for display
        $_POST['search'] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; }
        input[type="text"], input[type="number"], input[type="date"], select {
            width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;
        }
        .btn-container { display: flex; gap: 10px; }
        .success-message { color: #2a7; margin-bottom: 10px; }
        .error-message { color: #c00; margin-bottom: 10px; }
        .readonly-field { background: #f5f5f5; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Employee</h1>
        </header>
        <div class="form-container">
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" style="margin-bottom: 24px;">
                <label for="emp_no">Employee Number</label>
                <input type="number" name="emp_no" id="emp_no" value="<?php echo isset($_POST['emp_no']) ? htmlspecialchars($_POST['emp_no']) : ''; ?>" required>
                <button type="submit" name="search" class="btn">Search</button>
            </form>

            <?php if ($employee): ?>
            <form method="post">
                <input type="hidden" name="emp_no" value="<?php echo htmlspecialchars($employee['emp_no']); ?>">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($employee['first_name']); ?>" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($employee['last_name']); ?>" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <input type="text" value="<?php echo htmlspecialchars($employee['gender']); ?>" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="date" value="<?php echo htmlspecialchars($employee['birth_date']); ?>" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label>Hire Date</label>
                    <input type="date" value="<?php echo htmlspecialchars($employee['hire_date']); ?>" readonly class="readonly-field">
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select name="department" id="department" required>
                        <?php foreach ($departments as $dept_no => $dept_name): ?>
                            <option value="<?php echo $dept_no; ?>" <?php if ($employee['dept_no'] == $dept_no) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($dept_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($employee['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="salary">Salary (VNĐ)</label>
                    <input type="number" name="salary" id="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>" required>
                </div>
                <div class="btn-container">
                    <button type="submit" name="update" class="btn">Update Employee</button>
                    <a href="starting.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>