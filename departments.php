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
    <title>Departments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Departments</h1>
    </header>
    <div class="card">
        <p>Manage departments here. (Feature coming soon!)</p>
        <a href="starting.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>