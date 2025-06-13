<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login | Employee Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Employee Portal</h1>
            <p>Login to access your information</p>
        </header>
        
        <div class="form-container">
            <form action="employee_dashboard.php" method="post">
                <div class="form-group">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id" class="textfield" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="textfield" required>
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