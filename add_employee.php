<?php
include 'connection.php';

if (isset($_POST['save'])) {
    // Collect and sanitize form data
    $full_name  = trim($_POST['name']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $gender     = $_POST['gender'];
    $birth_date = $_POST['dob'];
    $address    = mysqli_real_escape_string($conn, $_POST['address']);
    $hire_date  = date('Y-m-d');
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    // Đặt mật khẩu mặc định là 123456
    $password   = password_hash('123456', PASSWORD_DEFAULT);

    // Tách họ tên (giản đơn: lấy từ cuối là last_name, còn lại là first_name)
    $name_parts = explode(' ', $full_name);
    $last_name = array_pop($name_parts);
    $first_name = implode(' ', $name_parts);

    // Chuyển gender về đúng giá trị DB
    if ($gender == 'Male') $gender_db = 'M';
    elseif ($gender == 'Female') $gender_db = 'F';
    else $gender_db = 'O';

    // Insert vào bảng employees
    $stmt = $conn->prepare("INSERT INTO employees (birth_date, first_name, last_name, gender, hire_date, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $birth_date, $first_name, $last_name, $gender_db, $hire_date, $password);
    if ($stmt->execute()) {
        $emp_no = $stmt->insert_id;

        // Insert vào bảng dept_emp
        $dept_stmt = $conn->prepare("SELECT dept_no FROM departments WHERE dept_name = ?");
        $dept_stmt->bind_param("s", $department);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        $dept_row = $dept_result->fetch_assoc();
        $dept_no = $dept_row['dept_no'] ?? null;
        $dept_stmt->close();

        if ($dept_no) {
            $stmt2 = $conn->prepare("INSERT INTO dept_emp (emp_no, dept_no, from_date, to_date) VALUES (?, ?, ?, '9999-01-01')");
            $stmt2->bind_param("iss", $emp_no, $dept_no, $hire_date);
            $stmt2->execute();
            $stmt2->close();
        }

        echo "<script>alert('Employee added successfully! Default password is 123456');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

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
            <form action="" method="POST">
                <label for="id">Employee ID</label>
                <input type="text" name="id" class="textfield" placeholder="ID will be automatically updated" readonly>
                
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
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                
                <label for="dob">Date of Birth</label>
                <div class="dob-field">
                    <input type="date" id="dob" name="dob" class="textfield" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <label for="address">Address</label>
                <textarea name="address" class="textfield" placeholder="Address" required></textarea>

                <!-- Trường nhập mật khẩu đã bị loại bỏ -->

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
