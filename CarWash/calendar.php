<?php
require_once 'includes/init.php';
$auth->requireStaff();

$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

$firstDayOfMonth = mktime(0,0,0,$month,1,$year);
$daysInMonth = intval(date('t', $firstDayOfMonth));
$startWeekday = intval(date('N', $firstDayOfMonth)); // 1 (Mon) - 7 (Sun)

// Collect bookings for the month
$bookingsByDay = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $dateStr = date('Y-m-d', mktime(0,0,0,$month,$day,$year));
    $list = $booking->getAll($dateStr, null, null) ?: [];
    if (!empty($list)) {
        $bookingsByDay[$day] = $list;
    }
}

function monthName($m){ return date('F', mktime(0,0,0,$m,1,2000)); }

// Prev/Next month calculations
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Car Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .day { background: #fff; border: 1px solid #e9ecef; border-radius: 8px; min-height: 120px; padding: 8px; }
        .day-num { font-weight: bold; }
        .booking { font-size: 12px; margin-top: 4px; padding: 4px; background: #f8f9fa; border-left: 3px solid #0d6efd; border-radius: 4px; }
        .weekday-header { font-weight: 600; text-align: center; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 bg-dark text-white p-3 min-vh-100">
            <h4>🚗 Car Wash</h4>
            <nav class="nav flex-column">
                <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
                <a class="nav-link text-white" href="customers.php">Customers</a>
                <a class="nav-link text-white" href="bookings.php">Bookings</a>
                <a class="nav-link text-white" href="services.php">Services</a>
                <a class="nav-link text-white" href="payments.php">Payments</a>
                <?php if ($auth->isAdmin()): ?>
                <a class="nav-link text-white" href="users.php">Users</a>
                <a class="nav-link text-white" href="reports.php">Reports</a>
                <?php endif; ?>
                <a class="nav-link text-white" href="logout.php">Logout</a>
            </nav>
        </div>
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><?php echo monthName($month) . ' ' . $year; ?> Calendar</h2>
                <div>
                    <a class="btn btn-outline-secondary" href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">Prev</a>
                    <a class="btn btn-outline-secondary" href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Next</a>
                    <a class="btn btn-primary" href="bookings.php">Back to List</a>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <div class="calendar">
                        <div class="weekday-header">Mon</div>
                        <div class="weekday-header">Tue</div>
                        <div class="weekday-header">Wed</div>
                        <div class="weekday-header">Thu</div>
                        <div class="weekday-header">Fri</div>
                        <div class="weekday-header">Sat</div>
                        <div class="weekday-header">Sun</div>
                        <?php for ($i=1; $i<$startWeekday; $i++): ?>
                            <div class="day"></div>
                        <?php endfor; ?>
                        <?php for ($d=1; $d<=$daysInMonth; $d++): ?>
                            <div class="day">
                                <div class="day-num"><?php echo $d; ?></div>
                                <?php if (!empty($bookingsByDay[$d])): ?>
                                    <?php foreach ($bookingsByDay[$d] as $b): ?>
                                        <div class="booking">
                                            <div><strong><?php echo htmlspecialchars($b['service_name']); ?></strong></div>
                                            <div><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                            <div><small><?php echo htmlspecialchars($b['booking_time']); ?> - <?php echo ucfirst($b['status']); ?></small></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
