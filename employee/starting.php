<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Employee Management System</h1>
            <p>Welcome back, Administrator</p>
        </header>
        
        <div class="dashboard">
            <div class="card">
                <h3>Employee Records</h3>
                <p>View and manage all employee information in your organization.</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">142</div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">8</div>
                        <div class="stat-label">Departments</div>
                    </div>
                </div>
                <a href="employees.php" class="btn">View Employees</a>
            </div>
            
            <div class="card">
                <h3>Add New Employee</h3>
                <p>Register new employees and add them to your database.</p>
                <a href="add_employee.php" class="btn">Add Employee</a>
            </div>
            
            <div class="card">
                <h3>Reports & Analytics</h3>
                <p>Generate reports and view analytics about your workforce.</p>
                <a href="reports.php" class="btn">View Reports</a>
            </div>
        </div>
        
        <div class="card">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="attendance.php" class="btn">Attendance</a>
                <a href="payroll.php" class="btn">Payroll</a>
                <a href="departments.php" class="btn">Departments</a>
                <a href="settings.php" class="btn btn-secondary">Settings</a>
            </div>
        </div>
    </div>
</body>
</html>