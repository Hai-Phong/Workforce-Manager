<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: starting.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once 'connection.php';
    

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        /* Prepare and execute query */
        $sql = "SELECT admin_username, admin_password FROM admin_info WHERE admin_username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        /* Check if user exists */
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            /* Verify password (plain text comparison - not recommended for production) */
            if ($password === $user['admin_password']) {
                /* Set session variables */
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['admin_username'];
                
                /* Redirect to starting page */
                header("Location: starting.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
        
        /* Close statement */
        $stmt->close();
    }
    
    /* Close connection */
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Admin Login | Employee Management System</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Prevent any navigation using browser buttons
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, document.URL);
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Administrator Portal</h1>
            <p>Login to manage the employee system</p>
        </header>
        
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                

                <div class="form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" class="textfield" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="textfield" required autocomplete="off">
                </div>
                
                <div class="btn-container">
                    <button type="submit" class="btn">Login</button>
                    <a href="forgot_password.php" class="btn btn-secondary">Recover Access</a>
                </div>
            </form>
            
            <div class="login-footer">
                <p>Employee? <a href="employee_login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>