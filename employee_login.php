<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION['employee_logged_in']) && $_SESSION['employee_logged_in'] === true) {
    header("Location: employee_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'connection.php';

    $emp_no = trim($_POST['emp_no']);
    $password = trim($_POST['password']);

    if (empty($emp_no) || empty($password)) {
        $error = "Please enter both employee number and password";
    } else {
        $sql = "SELECT emp_no, first_name, last_name, password FROM employees WHERE emp_no = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // So sánh đúng chuẩn với hash
            if (password_verify($password, $user['password'])) {
                $_SESSION['employee_logged_in'] = true;
                $_SESSION['emp_no'] = $user['emp_no'];
                $_SESSION['employee_name'] = $user['first_name'] . ' ' . $user['last_name'];

                header("Location: employee_dashboard.php");
                exit();
            } else {
                $error = "Invalid employee number or password";
            }
        } else {
            $error = "Invalid employee number or password";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login | Employee Management System</title>
    <link rel="stylesheet" href="style.css">
    <script>
    // Force reload on browser back/forward navigation
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Employee Portal</h1>
            <p>Login to access your information</p>
        </header>
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="emp_no">Employee Number</label>
                    <input type="text" id="emp_no" name="emp_no" class="textfield" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="textfield" required autocomplete="off">
                </div>
                <div class="btn-container">
                    <button type="submit" class="btn">Login</button>
                    <a href="forgot_password.php" class="btn btn-secondary">Forgot Password?</a>
                </div>
            </form>
            <div class="login-footer">
                <p>Administrator? <a href="admin_login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>