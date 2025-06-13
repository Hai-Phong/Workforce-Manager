
<?php
session_start();
if (!isset($_SESSION['employee_logged_in']) || !isset($_SESSION['emp_no'])) {
    header("Location: employee_login.php");
    exit();
}
include 'connection.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_by = isset($_GET['search_by']) ? $_GET['search_by'] : 'name';

// Build WHERE clause for search
$where = '';
$params = [];
if ($search !== '') {
    if ($search_by === 'id') {
        $where = "WHERE e.emp_no = ?";
        $params[] = $search;
    } else { // name
        $where = "WHERE CONCAT(e.first_name, ' ', e.last_name) LIKE ?";
        $params[] = '%' . $search . '%';
    }
}

// Prepare SQL with search
$sql = "SELECT e.emp_no, e.first_name, e.last_name, e.gender, e.birth_date, e.hire_date, d.dept_name
        FROM employees e
        LEFT JOIN dept_emp de ON e.emp_no = de.emp_no AND (de.to_date IS NULL OR de.to_date = '9999-01-01')
        LEFT JOIN departments d ON de.dept_no = d.dept_no
        $where
        ORDER BY e.emp_no ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Employee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f9f9f9;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background-color: white;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        h1 {
            color: #333;
        }
        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #2980b9;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form select, .search-form input[type="text"] {
            padding: 7px;
            margin-right: 5px;
        }
        .search-form button {
            padding: 7px 15px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 3px;
        }
        .search-form button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <h1>Search Employee</h1>
    <form class="search-form" method="get" action="">
        <select name="search_by">
            <option value="name" <?php if($search_by=='name') echo 'selected'; ?>>Name</option>
            <option value="id" <?php if($search_by=='id') echo 'selected'; ?>>ID</option>
        </select>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
        <button type="submit">Search</button>
        <a href="search_employee.php" style="margin-left:10px;">Reset</a>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Gender</th>
            <th>Birth Date</th>
            <th>Hire Date</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo $row['emp_no']; ?></td>
            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
            <td><?php echo htmlspecialchars($row['dept_name'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['gender']); ?></td>
            <td><?php echo htmlspecialchars($row['birth_date']); ?></td>
            <td><?php echo htmlspecialchars($row['hire_date']); ?></td>
        </tr>
        <?php
            endwhile;
        else:
        ?>
        <tr>
            <td colspan="6">No employees found.</td>
        </tr>
        <?php endif; ?>
    </table>
    <div style="margin-top: 20px;">
        <a class="back-btn" href="employee_dashboard.php">Back</a>
    </div>
</body>
</html>