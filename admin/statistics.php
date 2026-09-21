<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
}

include("../connection.php");

$doctor_count = $database->query("SELECT COUNT(*) as count FROM doctor")->fetch_assoc()['count'];
$patient_count = $database->query("SELECT COUNT(*) as count FROM patient")->fetch_assoc()['count'];
$appointment_count = $database->query("SELECT COUNT(*) as count FROM appointment")->fetch_assoc()['count'];
$schedule_count = $database->query("SELECT COUNT(*) as count FROM schedule")->fetch_assoc()['count'];

$specialties_result = $database->query("SELECT s.sname, COUNT(d.docid) as doctor_count FROM specialties s LEFT JOIN doctor d ON s.id = d.specialties GROUP BY s.id, s.sname ORDER BY doctor_count DESC");

$specialties_data = [];
while($row = $specialties_result->fetch_assoc()) {
    $specialties_data[] = $row;
}

$appointment_trend_result = $database->query("
    SELECT DATE(appodate) as date, COUNT(*) as count 
    FROM appointment 
    WHERE appodate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(appodate)
    ORDER BY date ASC
");

$appointment_trend_data = [];
while($row = $appointment_trend_result->fetch_assoc()) {
    $appointment_trend_data[] = $row;
}

$top_doctors_result = $database->query("
    SELECT d.docname, COUNT(a.appoid) as appointment_count
    FROM doctor d
    LEFT JOIN schedule s ON d.docid = s.docid
    LEFT JOIN appointment a ON s.scheduleid = a.scheduleid
    GROUP BY d.docid, d.docname
    ORDER BY appointment_count DESC
    LIMIT 5
");

$top_doctors_data = [];
while($row = $top_doctors_result->fetch_assoc()) {
    $top_doctors_data[] = $row;
}

// Calculate additional statistics
$recent_appointments_result = $database->query(
    "SELECT a.*, p.pname, d.docname, s.title, s.scheduledate, s.scheduletime 
    FROM appointment a 
    LEFT JOIN patient p ON a.pid = p.pid 
    LEFT JOIN schedule s ON a.scheduleid = s.scheduleid 
    LEFT JOIN doctor d ON s.docid = d.docid 
    ORDER BY a.appodate DESC 
    LIMIT 5"
);
$recent_appointments = [];
while($row = $recent_appointments_result->fetch_assoc()) {
    $recent_appointments[] = $row;
}

// Calculate percentage changes compared to previous period
$current_period_appointments = $database->query(
    "SELECT COUNT(*) as count FROM appointment WHERE appodate >= CURDATE() - INTERVAL 7 DAY"
)->fetch_assoc()['count'];
$previous_period_appointments = $database->query(
    "SELECT COUNT(*) as count FROM appointment WHERE appodate BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE() - INTERVAL 7 DAY"
)->fetch_assoc()['count'];

$appointment_change = 0;
if ($previous_period_appointments > 0) {
    $appointment_change = (($current_period_appointments - $previous_period_appointments) / $previous_period_appointments) * 100;
}


// Calculate revenue if payments table exists
$revenue = 0;
$payments_table_exists = $database->query("SHOW TABLES LIKE 'payments'");
if ($payments_table_exists->num_rows > 0) {
    $revenue_result = $database->query(
        "SELECT SUM(amount) as total_revenue FROM payments WHERE payment_status = 'completed'"
    )->fetch_assoc();
    $revenue = $revenue_result['total_revenue'] ?: 0;
}

// Calculate today's appointments
$todays_appointments = $database->query(
    "SELECT COUNT(*) as count FROM appointment WHERE appodate = CURDATE()"
)->fetch_assoc()['count'];

// Calculate upcoming schedules
$upcoming_schedules = $database->query(
    "SELECT COUNT(*) as count FROM schedule WHERE scheduledate >= CURDATE()"
)->fetch_assoc()['count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    
    <title>Statistics</title>
    <style>
        .dashboard-section {
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .stat-card.doctors::before {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
        }
        
        .stat-card.patients::before {
            background: linear-gradient(90deg, #10b981, #34d399);
        }
        
        .stat-card.appointments::before {
            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
        }
        
        .stat-card.schedules::before {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }
        
        .stat-card.revenue::before {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }
        
        .stat-card.today::before {
            background: linear-gradient(90deg, #ec4899, #f472b6);
        }
        
        .stat-card.trends::before {
            background: linear-gradient(90deg, #06b6d4, #0ea5e9);
        }
        
        .stat-card.upcoming::before {
            background: linear-gradient(90deg, #8b5cf6, #d8b4fe);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
            line-height: 1.2;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .stat-change {
            font-size: 0.9rem;
            padding: 3px 8px;
            border-radius: 4px;
            margin-top: 5px;
            display: inline-block;
        }
        
        .positive {
            background-color: #dcfce7;
            color: #22c55e;
        }
        
        .negative {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-wrapper {
            height: 300px;
            position: relative;
        }
        
        .bar-chart {
            display: flex;
            align-items: end;
            height: 250px;
            gap: 20px;
            padding: 20px;
            border-left: 2px solid #eee;
            border-bottom: 2px solid #eee;
        }
        
        .bar {
            flex: 1;
            background: linear-gradient(to top, #3b82f6, #60a5fa);
            border-radius: 5px 5px 0 0;
            position: relative;
            min-width: 40px;
        }
        
        .bar-label {
            position: absolute;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8rem;
        }
        
        .bar-value {
            position: absolute;
            top: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .pie-chart {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: conic-gradient(
                #3b82f6 0% <?php echo ($doctor_count / ($doctor_count + $patient_count)) * 100; ?>%, 
                #10b981 <?php echo ($doctor_count / ($doctor_count + $patient_count)) * 100; ?>% 100%
            );
            margin: 0 auto;
            position: relative;
        }
        
        .pie-chart-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@edoc.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-feedback">
                        <a href="feedback.php" class="non-style-link-menu"><div><p class="menu-text">Feedback</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report">
                        <a href="reports.php" class="non-style-link-menu"><div><p class="menu-text">Reports</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings menu-active menu-icon-settings-active">
                        <a href="statistics.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Statistics</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-payment">
                        <a href="payment_reports.php" class="non-style-link-menu"><div><p class="menu-text">Payment Reports</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-heart">
                        <a href="manage-health-tips.php" class="non-style-link-menu"><div><p class="menu-text">Health Tips</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="dashboard-body">
            <div class="dashboard-section">
                <h1 style="font-size: 1.8rem; margin-bottom: 20px;">System Statistics Dashboard</h1>

                <div class="stats-grid">
                    <div class="stat-card doctors">
                        <div class="stat-number" style="color: #3b82f6;"><?php echo $doctor_count; ?></div>
                        <div class="stat-label">Total Doctors</div>
                        <div class="stat-change positive">+<?php echo round(($doctor_count / max($patient_count, 1)) * 100, 1); ?>% DR/P ratio</div>
                    </div>
                    
                    <div class="stat-card patients">
                        <div class="stat-number" style="color: #10b981;"><?php echo $patient_count; ?></div>
                        <div class="stat-label">Total Patients</div>
                        <div class="stat-change <?php echo $patient_count > 0 ? 'positive' : 'negative'; ?>">Active</div>
                    </div>
                    
                    <div class="stat-card appointments">
                        <div class="stat-number" style="color: #8b5cf6;"><?php echo $appointment_count; ?></div>
                        <div class="stat-label">Total Appointments</div>
                        <div class="stat-change <?php echo $appointment_change >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $appointment_change >= 0 ? '+' : ''; ?><?php echo round($appointment_change, 1); ?>% from last period
                        </div>
                    </div>
                    
                    <div class="stat-card schedules">
                        <div class="stat-number" style="color: #f59e0b;"><?php echo $schedule_count; ?></div>
                        <div class="stat-label">Total Schedules</div>
                        <div class="stat-change <?php echo $upcoming_schedules > 0 ? 'positive' : 'negative'; ?>"><?php echo $upcoming_schedules; ?> upcoming</div>
                    </div>
                    
                    <div class="stat-card revenue">
                        <div class="stat-number" style="color: #ef4444;">LKR <?php echo number_format($revenue, 2); ?></div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-change positive">Completed</div>
                    </div>
                    
                    <div class="stat-card today">
                        <div class="stat-number" style="color: #ec4899;"><?php echo $todays_appointments; ?></div>
                        <div class="stat-label">Today's Appointments</div>
                        <div class="stat-change positive"><?php echo $todays_appointments > 0 ? 'Active' : 'None'; ?></div>
                    </div>
                    
                    <div class="stat-card trends">
                        <div class="stat-number" style="color: #06b6d4;"><?php echo count($appointment_trend_data); ?>/7</div>
                        <div class="stat-label">Recent Activity</div>
                        <div class="stat-change positive">Days tracked</div>
                    </div>
                    
                    <div class="stat-card upcoming">
                        <div class="stat-number" style="color: #8b5cf6;"><?php echo $upcoming_schedules; ?></div>
                        <div class="stat-label">Upcoming Schedules</div>
                        <div class="stat-change positive">Future bookings</div>
                    </div>
                </div>

                <div class="two-column-layout">
                    <div class="chart-container">
                        <h2 class="chart-title">Doctor-Patient Ratio</h2>
                        <div class="chart-wrapper">
                            <div class="pie-chart">
                                <div class="pie-chart-center">
                                    1:<?php echo $patient_count > 0 ? round($patient_count / $doctor_count, 1) : 0; ?>
                                </div>
                            </div>
                            <div class="chart-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #3b82f6;"></div>
                                    <div>Doctors (<?php echo $doctor_count; ?>)</div>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #10b981;"></div>
                                    <div>Patients (<?php echo $patient_count; ?>)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <h2 class="chart-title">Top Doctors by Appointments</h2>
                        <div class="chart-wrapper">
                            <div class="bar-chart">
                                <?php foreach($top_doctors_data as $doctor): ?>
                                    <?php if($doctor['appointment_count'] > 0): ?>
                                        <div class="bar" style="height: <?php echo ($doctor['appointment_count'] / max(array_column($top_doctors_data, 'appointment_count'), 1)) * 200; ?>px;">
                                            <div class="bar-value"><?php echo $doctor['appointment_count']; ?></div>
                                            <div class="bar-label"><?php echo substr($doctor['docname'], 0, 8); ?><?php echo strlen($doctor['docname']) > 8 ? '...' : ''; ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chart-container">
                    <h2 class="chart-title">Appointment Trends (Last 7 Days)</h2>
                    <div class="chart-wrapper">
                        <div class="bar-chart">
                            <?php 
                            // Create an array with all 7 days
                            $days = [];
                            for ($i = 6; $i >= 0; $i--) {
                                $date = date('Y-m-d', strtotime("-$i days"));
                                $found = false;
                                
                                foreach($appointment_trend_data as $trend) {
                                    if ($trend['date'] == $date) {
                                        $days[] = $trend;
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if (!$found) {
                                    $days[] = ['date' => $date, 'count' => 0];
                                }
                            }
                            
                            foreach($days as $day): 
                            ?>
                                <div class="bar" style="height: <?php echo (max(array_column($appointment_trend_data, 'count')) > 0 ? ($day['count'] / max(array_column($appointment_trend_data, 'count'))) * 200 : 0); ?>px;">
                                    <div class="bar-value"><?php echo $day['count']; ?></div>
                                    <div class="bar-label"><?php echo date('M d', strtotime($day['date'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="two-column-layout">
                    <div class="table-container">
                        <h2 class="table-title">Doctors by Specialty</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Specialty</th>
                                    <th>Doctor Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($specialties_data, 0, 8) as $specialty): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($specialty['sname']); ?></td>
                                        <td><?php echo $specialty['doctor_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-container">
                        <h2 class="table-title">Recent Appointments</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_appointments as $apt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($apt['pname'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($apt['docname'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($apt['scheduledate'] ?? 'N/A')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($recent_appointments)): ?>
                                    <tr>
                                        <td colspan="3">No recent appointments</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>