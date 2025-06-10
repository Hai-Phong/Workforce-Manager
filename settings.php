<?php
<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Settings</h1>
    </header>
    <div class="card">
        <p>Settings and configuration options will be available here.</p>
        <a href="starting.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>