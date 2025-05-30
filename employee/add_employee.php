<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Registration</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Employee Registration</h1>
        </header>
        
        <div class="form-container">
            <form action="#" method="POST">
                <label for="id">Employee ID</label>
                <input type="text" name="id" class="textfield" placeholder="ID" required>
                
                <label for="name">Full Name</label>
                <input type="text" name="name" class="textfield" placeholder="Employee Name" required>
                
                <label for="department">Department</label>
                <select class="textfield" name="department" required>
                    <option value="">Select Department</option>
                    <option>Executive</option>
                    <option>Human Resources</option>
                    <option>Accounting</option>
                    <option>R&D</option>
                    <option>IT</option>
                    <option>Sales</option>
                    <option>Marketing</option>
                    <option>Customer Support</option>
                </select>
                
                <label for="email">Email</label>
                <input type="email" name="email" class="textfield" placeholder="Email" required>
                
                <label for="gender">Gender</label>
                <select class="textfield" name="gender" required>
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
                
                <label for="dob">Date of Birth</label>
                <div class="dob-field">
                    <input type="date" id="dob" name="dob" class="textfield" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <label for="address">Address</label>
                <textarea name="address" class="textfield" placeholder="Address" required></textarea>
                
                <div class="btn-container">
                    <input type="submit" name="save" value="SAVE" class="btn">
                    <button type="button" class="btn2" onclick="window.location.href='starting.php'">
                    CANCEL
                	</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
