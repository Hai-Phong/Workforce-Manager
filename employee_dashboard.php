<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['employee_logged_in']) || !isset($_SESSION['emp_no'])) {
    header("Location: employee_login.php");
    exit();
}

require_once 'connection.php';

$emp_no = $_SESSION['emp_no'];
$name = $_SESSION['employee_name'] ?? '';

// --- Real-time check-in/check-out ---
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Vietnam timezone
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// Check today's attendance
$stmt = $conn->prepare("SELECT * FROM attendance WHERE emp_no = ? AND date = ?");
$stmt->bind_param("is", $emp_no, $today);
$stmt->execute();
$attendance_today = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_checked_in = $attendance_today && $attendance_today['check_in'] && !$attendance_today['check_out'];
$is_checked_out = $attendance_today && $attendance_today['check_in'] && $attendance_today['check_out'];

// Handle check-in
if (isset($_POST['check_in']) && !$is_checked_in && !$is_checked_out) {
    $stmt = $conn->prepare("INSERT INTO attendance (emp_no, date, check_in) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $emp_no, $today, $now);
    $stmt->execute();
    $stmt->close();
    header("Location: employee_dashboard.php");
    exit();
}

// Handle check-out
if (isset($_POST['check_out']) && $is_checked_in && !$is_checked_out) {
    $stmt = $conn->prepare("UPDATE attendance SET check_out = ? WHERE emp_no = ? AND date = ?");
    $stmt->bind_param("sis", $now, $emp_no, $today);
    $stmt->execute();
    $stmt->close();
    header("Location: employee_dashboard.php");
    exit();
}

// --- Salary viewing (latest salary) ---
$stmt = $conn->prepare("SELECT salary FROM salaries WHERE emp_no = ? ORDER BY from_date DESC LIMIT 1");
$stmt->bind_param("i", $emp_no);
$stmt->execute();
$res_salary = $stmt->get_result();
$salary = $res_salary->fetch_assoc()['salary'] ?? 'N/A';
$stmt->close();

// --- Attendance for current month/year ---
$month = date('m');
$year = date('Y');
$stmt = $conn->prepare("SELECT COUNT(*) AS present_days FROM attendance WHERE emp_no = ? AND MONTH(date) = ? AND YEAR(date) = ? AND check_in IS NOT NULL");
$stmt->bind_param("iss", $emp_no, $month, $year);
$stmt->execute();
$present_days = $stmt->get_result()->fetch_assoc()['present_days'] ?? 0;
$stmt->close();

$total_days = date('t'); // Total days in current month

// --- Attendance details for the month ---
$stmt = $conn->prepare("SELECT date, check_in, check_out FROM attendance WHERE emp_no = ? AND MONTH(date) = ? AND YEAR(date) = ?");
$stmt->bind_param("iss", $emp_no, $month, $year);
$stmt->execute();
$res_attendance = $stmt->get_result();
$attendance_map = [];
while ($row = $res_attendance->fetch_assoc()) {
    $attendance_map[$row['date']] = $row;
}
$stmt->close();

// --- Calculate working days (Mon-Fri) from Monday this week up to today ---
$week_start = date('Y-m-d', strtotime('monday this week'));
$today_date = date('Y-m-d');
$workdays = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime($week_start . " +$i days"));
    $weekday = date('N', strtotime($date)); // 1 (Mon) - 7 (Sun)
    if ($weekday <= 5 && $date <= $today_date) {
        $workdays[] = $date;
    }
}

// Count present days in workdays
$present_week = 0;
foreach ($workdays as $date) {
    if (isset($attendance_map[$date]) && $attendance_map[$date]['check_in']) {
        $present_week++;
    }
}
$total_workdays = count($workdays);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard { display: flex; flex-wrap: wrap; gap: 24px; }
        .card, .dashboard-card { background: #fff; border-radius: 8px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px #eee; flex: 1 1 300px; }
        .dashboard-actions { margin-top: 16px; }
        .salary { font-size: 2em; color: #2a7; }
        .attendance-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .attendance-table th, .attendance-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .attendance-table th { background: #f5f5f5; }
        .quick-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; background: #007bff; color: #fff; cursor: pointer; text-decoration: none; }
        .btn-secondary { background: #6c757d; }
        .stat-value { font-size: 2em; font-weight: bold; }
        .stat-label { color: #888; }
        .logout-btn { float: right; }
    </style>
    <script>
    // If not logged in, redirect to login (for cached pages)
    if (!<?php echo json_encode(isset($_SESSION['employee_logged_in']) && $_SESSION['employee_logged_in'] === true); ?>) {
        window.location.replace("employee_login.php");
    }
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
            <h1>Employee Management System</h1>
            <p>Welcome, <?php echo htmlspecialchars($name); ?> (Employee #<?php echo htmlspecialchars($emp_no); ?>)</p>
        </header>
        <div class="dashboard">
            <div class="card">
                <h3>Real-time Attendance</h3>
                <form method="post" class="dashboard-actions">
                    <?php if (!$attendance_today): ?>
                        <button type="submit" name="check_in" class="btn">Check In</button>
                        <span style="margin-left:10px;">Not checked in yet</span>
                    <?php elseif ($is_checked_in): ?>
                        <button type="submit" name="check_out" class="btn btn-secondary">Check Out</button>
                        <span style="margin-left:10px;">Checked in at: <?php echo date('H:i', strtotime($attendance_today['check_in'])); ?></span>
                    <?php elseif ($is_checked_out): ?>
                        <span>Checked in at: <?php echo date('H:i', strtotime($attendance_today['check_in'])); ?></span>
                        <span style="margin-left:10px;">Checked out at: <?php echo date('H:i', strtotime($attendance_today['check_out'])); ?></span>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card">
                <h3>Salary Information</h3>
                <div class="salary"><?php echo is_numeric($salary) ? number_format($salary, 0, ',', '.') . ' VNĐ' : 'N/A'; ?></div>
                <small>(Latest salary record)</small>
            </div>
            <div class="card">
                <h3>Attendance This Week (<?php
                    $monday = date('d/m/Y', strtotime('monday this week'));
                    $sunday = date('d/m/Y', strtotime('sunday this week'));
                    echo $monday . " - " . $sunday;
                ?>)</h3>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php echo $present_week; ?>
                        </div>
                        <div class="stat-label">Days Present</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_workdays; ?></div>
                        <div class="stat-label">Total Workdays</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php
                            echo $total_workdays > 0 ? round(($present_week / $total_workdays) * 100, 2) : 0;
                            ?>%
                        </div>
                        <div class="stat-label">Attendance %</div>
                    </div>
                </div>
                <table class="attendance-table">
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                    </tr>
                    <?php
                    for ($i = 0; $i < 7; $i++) {
                        $date = date('Y-m-d', strtotime($week_start . " +$i days"));
                        $weekday = date('N', strtotime($date));
                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($date)) . "</td>";
                        if ($weekday <= 5 && $date <= $today_date) { // Only show status for workdays up to today
                            $row = $attendance_map[$date] ?? null;
                            if ($row) {
                                echo "<td>" . ($row['check_in'] ? date('H:i', strtotime($row['check_in'])) : '-') . "</td>";
                                echo "<td>" . ($row['check_out'] ? date('H:i', strtotime($row['check_out'])) : '-') . "</td>";
                                echo "<td style='color:green;'>Present</td>";
                            } else {
                                echo "<td>-</td><td>-</td><td style='color:red;'>Absent</td>";
                            }
                        } elseif ($weekday <= 5) { // Future workday
                            echo "<td>-</td><td>-</td><td>-</td>";
                        } else { // Weekend
                            echo "<td colspan='3' style='color:#888;'>Weekend</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </table>
                <a href="attendance_history.php" class="btn btn-secondary" style="margin-top:10px;">View Full Attendance</a>
            </div>
        </div>
        <div class="card">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="employee_setting.php" class="btn">Settings</a>
                <a href="search_employee.php" class="btn">Search Employee</a>
                <a href="employee_logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>