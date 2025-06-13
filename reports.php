<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
include 'connection.php';

// Get employee count by department (IT has 10, Marketing has 20, etc.)
$departments = [];
$counts = [];
$sql = "SELECT d.dept_name, COUNT(de.emp_no) as count
        FROM departments d
        LEFT JOIN dept_emp de ON d.dept_no = de.dept_no
        GROUP BY d.dept_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['dept_name'];
        $counts[] = $row['count'];
    }
}

// --- Salary distribution overall ---
$salary_ranges = ['<10M', '10M-20M', '20M-30M', '30M-40M', '>=40M'];
$salary_bins = [0, 0, 0, 0, 0];
$sql3 = "SELECT salary FROM salaries WHERE to_date IS NULL OR to_date = '9999-01-01'";
$result3 = $conn->query($sql3);
if ($result3 && $result3->num_rows > 0) {
    while ($row = $result3->fetch_assoc()) {
        $salary = (int)$row['salary'];
        if ($salary < 10000000) $salary_bins[0]++;
        elseif ($salary < 20000000) $salary_bins[1]++;
        elseif ($salary < 30000000) $salary_bins[2]++;
        elseif ($salary < 40000000) $salary_bins[3]++;
        else $salary_bins[4]++;
    }
}

// --- Salary distribution by department ---
$dept_salary_dist = [];
$sql4 = "SELECT d.dept_name, s.salary
         FROM departments d
         JOIN dept_emp de ON d.dept_no = de.dept_no
         JOIN salaries s ON de.emp_no = s.emp_no
         WHERE s.to_date IS NULL OR s.to_date = '9999-01-01'";
$result4 = $conn->query($sql4);
if ($result4 && $result4->num_rows > 0) {
    while ($row = $result4->fetch_assoc()) {
        $dept = $row['dept_name'];
        $salary = (int)$row['salary'];
        if (!isset($dept_salary_dist[$dept])) $dept_salary_dist[$dept] = [0,0,0,0,0];
        if ($salary < 10000000) $dept_salary_dist[$dept][0]++;
        elseif ($salary < 20000000) $dept_salary_dist[$dept][1]++;
        elseif ($salary < 30000000) $dept_salary_dist[$dept][2]++;
        elseif ($salary < 40000000) $dept_salary_dist[$dept][3]++;
        else $dept_salary_dist[$dept][4]++;
    }
}

// --- Attendance distribution overall (this month) ---
$attendance_ranges = ['<5', '5-10', '11-15', '16-20', '>=21'];
$attendance_bins = [0,0,0,0,0];
$month = date('m');
$year = date('Y');
$sql5 = "SELECT e.emp_no, COUNT(a.id) as days_present
         FROM employees e
         LEFT JOIN attendance a ON e.emp_no = a.emp_no AND MONTH(a.date) = '$month' AND YEAR(a.date) = '$year' AND a.check_in IS NOT NULL
         GROUP BY e.emp_no";
$result5 = $conn->query($sql5);
if ($result5 && $result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $days = (int)$row['days_present'];
        if ($days < 5) $attendance_bins[0]++;
        elseif ($days < 11) $attendance_bins[1]++;
        elseif ($days < 16) $attendance_bins[2]++;
        elseif ($days < 21) $attendance_bins[3]++;
        else $attendance_bins[4]++;
    }
}

// --- Attendance distribution by department (this month) ---
$dept_attendance_dist = [];
$sql6 = "SELECT d.dept_name, e.emp_no, COUNT(a.id) as days_present
         FROM departments d
         JOIN dept_emp de ON d.dept_no = de.dept_no
         JOIN employees e ON de.emp_no = e.emp_no
         LEFT JOIN attendance a ON e.emp_no = a.emp_no AND MONTH(a.date) = '$month' AND YEAR(a.date) = '$year' AND a.check_in IS NOT NULL
         GROUP BY d.dept_name, e.emp_no";
$result6 = $conn->query($sql6);
if ($result6 && $result6->num_rows > 0) {
    while ($row = $result6->fetch_assoc()) {
        $dept = $row['dept_name'];
        $days = (int)$row['days_present'];
        if (!isset($dept_attendance_dist[$dept])) $dept_attendance_dist[$dept] = [0,0,0,0,0];
        if ($days < 5) $dept_attendance_dist[$dept][0]++;
        elseif ($days < 11) $dept_attendance_dist[$dept][1]++;
        elseif ($days < 16) $dept_attendance_dist[$dept][2]++;
        elseif ($days < 21) $dept_attendance_dist[$dept][3]++;
        else $dept_attendance_dist[$dept][4]++;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports & Analytics</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto 40px auto;
            height: 350px;
        }
        @media (max-width: 700px) {
            .chart-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Reports & Analytics</h1>
    </header>
    <div class="card">
        <h3>Employee Count by Department</h3>
        <div class="chart-container">
            <canvas id="deptChart"></canvas>
        </div>
        <h3 style="margin-top:40px;">Attendance Distribution (This Month)</h3>
        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>
        <h3 style="margin-top:40px;">Attendance Distribution by Department (This Month)</h3>
        <div class="chart-container">
            <canvas id="attendanceDeptChart"></canvas>
        </div>
        <a href="starting.php" class="btn btn-secondary" style="margin-top:30px;">Back to Dashboard</a>
    </div>
</div>
<script>
    // Employee Count by Department - Horizontal Bar
    const deptCtx = document.getElementById('deptChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($departments); ?>,
            datasets: [{
                label: 'Employees',
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(41, 128, 185, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });

    // Attendance Distribution Chart - Horizontal Bar
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($attendance_ranges); ?>,
            datasets: [{
                label: 'Employees',
                data: <?php echo json_encode($attendance_bins); ?>,
                backgroundColor: [
                    'rgba(52, 152, 219, 0.7)',
                    'rgba(231, 76, 60, 0.7)',
                    'rgba(46, 204, 113, 0.7)',
                    'rgba(241, 196, 15, 0.7)',
                    'rgba(155, 89, 182, 0.7)'
                ],
                borderColor: [
                    'rgba(41, 128, 185, 1)',
                    'rgba(192, 57, 43, 1)',
                    'rgba(39, 174, 96, 1)',
                    'rgba(243, 156, 18, 1)',
                    'rgba(142, 68, 173, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });

    // Attendance Distribution by Department Chart - Horizontal Stacked Bar
    const attendanceDeptCtx = document.getElementById('attendanceDeptChart').getContext('2d');
    const attendanceDeptLabels = <?php echo json_encode(array_keys($dept_attendance_dist)); ?>;
    const attendanceDeptData = <?php echo json_encode(array_values($dept_attendance_dist)); ?>;
    const attendanceDeptRanges = <?php echo json_encode($attendance_ranges); ?>;
    const attendanceDeptDatasets = [];
    for (let i = 0; i < attendanceDeptRanges.length; i++) {
        attendanceDeptDatasets.push({
            label: attendanceDeptRanges[i],
            data: attendanceDeptData.map(arr => arr[i]),
            backgroundColor: [
                'rgba(52, 152, 219, 0.7)',
                'rgba(231, 76, 60, 0.7)',
                'rgba(46, 204, 113, 0.7)',
                'rgba(241, 196, 15, 0.7)',
                'rgba(155, 89, 182, 0.7)'
            ][i],
            stack: 'Stack 0'
        });
    }
    new Chart(attendanceDeptCtx, {
        type: 'bar',
        data: {
            labels: attendanceDeptLabels,
            datasets: attendanceDeptDatasets
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {},
            scales: {
                x: { stacked: true, beginAtZero: true },
                y: { stacked: true }
            }
        }
    });
</script>
</body>
</html>