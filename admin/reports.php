<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Reports</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .report-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }
        .report-header {
            border-bottom: 2px solid #0A76D8;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .download-btn {
            background: #0A76D8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .download-btn:hover {
            background: #0056b3;
        }
        .print-btn {
            background:rgb(48, 177, 78);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .print-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <?php

    

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    include("../connection.php");
    
    
    if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
        $report_id = $_GET['id'];
        $sql = "SELECT * FROM reports WHERE report_id = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            $report_type = ucfirst($report['report_type']);
            $generated_date = $report['generated_date'];
            $report_data = $report['report_data'];
            
            echo '
            <div class="container">
                <div style="padding: 20px;">
                    <a href="reports.php" class="non-style-link"><button class="login-btn btn-primary-soft btn" style="margin-bottom: 20px;">← Back to Reports</button></a>
                    <div class="report-content">
                        <div class="report-header">
                            <h2>'.$report_type.' Report</h2>
                            <p>Generated on: '.date('F j, Y, g:i a', strtotime($generated_date)).'</p>
                        </div>
                        <div>
                            <p><strong>Report Content:</strong></p>
                            <p>'.nl2br(htmlspecialchars($report_data)).'</p>
                        </div>
                        <a href="reports.php?action=print&id='.$report_id.'" class="print-btn" target="_blank">Print Report</a>
                    </div>
                </div>
            </div>';
            exit();
        }
    }
    
    
    if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['id'])) {
        $report_id = $_GET['id'];
        $sql = "SELECT * FROM reports WHERE report_id = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            $report_type = ucfirst($report['report_type']);
            $generated_date = date('Y-m-d_H-i-s', strtotime($report['generated_date']));
            $filename = $report_type.'_Report_'.$generated_date.'.txt';
            $content = "=== ".$report_type." Report ===\n";
            $content .= "Generated on: ".date('F j, Y, g:i a', strtotime($report['generated_date']))."\n\n";
            $content .= $report['report_data'];
            
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit();
        }
    }
    
    
    if (isset($_GET['action']) && $_GET['action'] == 'print' && isset($_GET['id'])) {
        $report_id = $_GET['id'];
        $sql = "SELECT * FROM reports WHERE report_id = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            $report_type = ucfirst($report['report_type']);
            $generated_date = $report['generated_date'];
            $report_data = $report['report_data'];
            
            // Process the report data to display in a table format
            $report_lines = explode("\n", $report_data);
            
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Print Report - '.$report_type.'</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
                    th, td { border: 1px solid #333; padding: 12px 8px; text-align: left; vertical-align: top; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    tr:nth-child(even) { background-color: #f9f9f9; }
                    .report-section { background-color: #e9ecef; }
                    .section-header { font-weight: bold; font-size: 16px; padding: 10px 8px !important; }
                    .field-name { font-weight: bold; width: 30%; background-color: #f8f9fa; }
                    .field-value { width: 70%; }
                    .print-controls { text-align: center; margin: 20px 0; }
                    .print-btn { padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 0 10px; }
                    .back-btn { padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; text-decoration: none; display: inline-block; margin: 0 10px; }
                    @media print {
                        .print-controls { display: none; }
                        body { margin: 0; padding: 10px; }
                        table { font-size: 12px; }
                        th, td { padding: 8px 4px; }
                    }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <h1>'.$report_type.' Report</h1>
                    <p>Generated on: '.date('F j, Y, g:i a', strtotime($generated_date)).'</p>
                </div>
                
                <div class="print-controls">
                    <button class="print-btn" onclick="window.print()">Print Report</button>
                    <a href="reports.php" class="back-btn">Back to Reports</a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            // Try to parse the report data into structured format
            $structured_data = [];
            foreach($report_lines as $line) {
                $line = trim($line);
                if($line !== '') {
                    // Check if this is a section header (ends with colon)
                    if(substr(rtrim($line), -1) === ':') {
                        $structured_data[] = array('key' => rtrim($line), 'value' => '', 'type' => 'header');
                    }
                    // Check if this is a key-value pair (contains colon in reasonable position)
                    elseif(strpos($line, ':') !== false && strpos($line, ':') < 50) {
                        $parts = explode(':', $line, 2);
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        // Skip if key is empty (likely just a value continuation)
                        if(!empty($key)) {
                            $structured_data[] = array('key' => $key, 'value' => $value, 'type' => 'data');
                        } else {
                            // Just add as a general line
                            $structured_data[] = array('key' => 'Data', 'value' => htmlspecialchars($line), 'type' => 'data');
                        }
                    } else {
                        // Just add as a general line
                        $structured_data[] = array('key' => 'Info', 'value' => htmlspecialchars($line), 'type' => 'data');
                    }
                }
            }
            
            // Output the structured data
            foreach($structured_data as $item) {
                if($item['type'] === 'header') {
                    echo '<tr class="report-section">
                        <td colspan="2" class="section-header">'.htmlspecialchars($item['key']).'</td>
                    </tr>';
                } else {
                    echo '<tr>
                        <td class="field-name">'.htmlspecialchars($item['key']).'</td>
                        <td class="field-value">'.htmlspecialchars($item['value']).'</td>
                    </tr>';
                }
            }
            
            echo '</tbody>
                </table>';
                
            echo '<div class="print-controls" style="margin-top: 20px;">';
            echo '<button class="print-btn" onclick="window.print()">Print Report</button>';
            echo '<a href="reports.php" class="back-btn">Back to Reports</a>';
            echo '</div>';
                
            echo '</body>';
            echo '</html>';
            exit();
        }
    }
    
    ?>
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
                                    <p class="profile-subtitle">admin@HealthCarePlus.com</p>
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
                    <td class="menu-btn menu-icon-report menu-active menu-icon-report-active">
                        <a href="reports.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Reports</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-statistics">
                        <a href="statistics.php" class="non-style-link-menu"><div><p class="menu-text">Statistics</p></a></div>
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
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="index.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Reports Management</p>
                                           
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                        date_default_timezone_set('Asia/Kolkata');
                        $today = date('Y-m-d');
                        echo $today;
                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
               
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Generate New Report</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;">
                        <center>
                        <form method="post" action="">
                            <table width="90%" class="sub-table scrolldown add-doc-form-container" border="0">
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="report_type" class="form-label">Report Type: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <select name="report_type" class="box" required>
                                            <option value="" disabled selected hidden>Choose Report Type</option>
                                            <option value="appointments">Appointments Report</option>
                                            <option value="payments">Payments Report</option>
                                            <option value="doctors">Doctors Report</option>
                                            <option value="patients">Patients Report</option>
                                            <option value="medical">Patient Documents</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="date_from" class="form-label">Date Range (From): </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="date" name="date_from" class="input-text" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="date_to" class="form-label">Date Range (To): </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="date" name="date_to" class="input-text" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="submit" name="generate_report" value="Generate Report" class="login-btn btn-primary btn">
                                    </td>
                                </tr>
                            </table>
                        </form>
                        </center>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4" style="padding-top:30px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Generated Reports</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;">
                        <center>
                        <form method="GET" action="" style="margin: 10px;">
                            <select name="filter_type" class="box" style="width: 200px; display: inline-block;">
                                <option value="">All Report Types</option>
                                <option value="appointments" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type']=='appointments')?'selected':''; ?>>Appointments</option>
                                <option value="payments" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type']=='payments')?'selected':''; ?>>Payments</option>
                                <option value="doctors" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type']=='doctors')?'selected':''; ?>>Doctors</option>
                                <option value="patients" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type']=='patients')?'selected':''; ?>>Patients</option>
                                <option value="medical" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type']=='medical')?'selected':''; ?>>Patient Documents</option>
                            </select>
                            &nbsp;
                            <input type="submit" value="Filter" class="login-btn btn-primary-soft btn" style="display: inline-block;">
                            &nbsp;
                            <a href="reports.php" class="login-btn btn-primary-soft btn" style="display: inline-block;">Clear</a>
                        </form>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    Report Type
                                </th>
                                <th class="table-headin">
                                    Patient Name
                                </th>
                                <th class="table-headin">
                                    Generated Date
                                </th>
                                <th class="table-headin">
                                    Date Range
                                </th>
                                <th class="table-headin">
                                    Actions
                                </th>
                        </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                            
                            if (isset($_POST['generate_report'])) {
                                $report_type = $_POST['report_type'];
                                $date_from = $_POST['date_from'];
                                $date_to = $_POST['date_to'];
                                $admin_id = 1; 
                                
                                
                                $report_data = "";
                                switch($report_type) {
                                    case 'appointments':
                                        $stmt = $database->prepare("
                                            SELECT a.apponum, p.pname, d.docname, s.title, s.scheduledate, s.scheduletime, a.appodate 
                                            FROM appointment a 
                                            INNER JOIN schedule s ON a.scheduleid = s.scheduleid 
                                            INNER JOIN patient p ON a.pid = p.pid 
                                            INNER JOIN doctor d ON s.docid = d.docid 
                                            WHERE a.appodate BETWEEN ? AND ? 
                                            ORDER BY a.appodate DESC, a.apponum ASC
                                        ");
                                        $stmt->bind_param("ss", $date_from, $date_to);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        
                                        $report_data = "Appointments Report ($date_from to $date_to)\n\n";
                                        $report_data .= "Total appointments in period: " . $result->num_rows . "\n\n";
                                        $report_data .= "Appointment Details:\n";
                                        $report_data .= str_repeat("-", 80) . "\n";
                                        
                                        while ($row = $result->fetch_assoc()) {
                                            $report_data .= "Appointment #: " . $row['apponum'] . "\n";
                                            $report_data .= "  Patient: " . $row['pname'] . "\n";
                                            $report_data .= "  Doctor: " . $row['docname'] . "\n";
                                            $report_data .= "  Session: " . $row['title'] . "\n";
                                            $report_data .= "  Session Date: " . $row['scheduledate'] . " at " . $row['scheduletime'] . "\n";
                                            $report_data .= "  Booking Date: " . $row['appodate'] . "\n";
                                            $report_data .= str_repeat("-", 80) . "\n";
                                        }
                                        $stmt->close();
                                        break;
                                    case 'payments':
                                        $report_data = "Payments Report ($date_from to $date_to)\n\n";
                                        $report_data .= "Payment data would be listed here in a full implementation.";
                                        break;
                                    case 'doctors':
                                        $stmt = $database->prepare("SELECT COUNT(*) as count FROM doctor");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $count = $result->fetch_assoc()['count'];
                                        $report_data = "Doctors Report ($date_from to $date_to)\n\n";
                                        $report_data .= "Total doctors: $count\n\n";
                                        $report_data .= "Detailed doctor data would be listed here in a full implementation.";
                                        break;
                                    case 'patients':
                                        $stmt = $database->prepare("SELECT COUNT(*) as count FROM patient");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $count = $result->fetch_assoc()['count'];
                                        $report_data = "Patients Report ($date_from to $date_to)\n\n";
                                        $report_data .= "Total patients: $count\n\n";
                                        $report_data .= "Detailed patient data would be listed here in a full implementation.";
                                        break;
                                    case 'medical':
                                        $report_data = "Patient Documents Summary ($date_from to $date_to)\n\n";
                                        $report_data .= "This report includes patient documents generated by doctors.\n\n";
                                        $report_data .= "Detailed patient document data would be listed here in a full implementation.";
                                        break;
                                    default:
                                        $report_data = "Report data not available.";
                                }
                                
                                
                                $sql = "INSERT INTO reports (report_type, generated_by, report_data) VALUES (?, ?, ?)";
                                $stmt = $database->prepare($sql);
                                $stmt->bind_param("sis", $report_type, $admin_id, $report_data);
                                $stmt->execute();
                                $stmt->close();
                                
                                echo '<tr><td colspan="4"><center><p style="color: green; font-size: 18px;">Report generated successfully!</p></center></td></tr>';
                            }
                            
                            
                            // Check if patient_id column exists before using it in ORDER BY
                            $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
                            
                            $check_column = "SHOW COLUMNS FROM `reports` LIKE 'patient_id'";
                            $column_result = $database->query($check_column);
                            
                            if ($column_result->num_rows > 0) {
                                $sqlmain = "SELECT * FROM reports";
                            } else {
                                $sqlmain = "SELECT * FROM reports";
                            }
                            
                            if (!empty($filter_type)) {
                                $sqlmain .= " WHERE report_type = '" . $database->real_escape_string($filter_type) . "'";
                            }
                            $sqlmain .= " ORDER BY generated_date DESC";
                            $result = $database->query($sqlmain);

                            if($result->num_rows==0){
                                echo '<tr>
                                <td colspan="4">
                                <br><br><br><br>
                                <center>
                                <img src="../img/notfound.svg" width="25%">
                                
                                <br>
                                <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No reports found!</p>
                                </center>
                                <br><br><br><br>
                                </td>
                                </tr>';
                                
                            }
                            else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $report_id=$row["report_id"];
                                    $report_type=ucfirst($row["report_type"]);
                                    $generated_date=$row["generated_date"];
                                    
                                    // Get patient name if patient_id exists
                                    $patient_name = "N/A";
                                    if (isset($row["patient_id"]) && !empty($row["patient_id"])) {
                                        $patient_sql = "SELECT pname FROM patient WHERE pid = ?";
                                        $patient_stmt = $database->prepare($patient_sql);
                                        $patient_stmt->bind_param("i", $row["patient_id"]);
                                        $patient_stmt->execute();
                                        $patient_result = $patient_stmt->get_result();
                                        if ($patient_result->num_rows > 0) {
                                            $patient_name = $patient_result->fetch_assoc()["pname"];
                                        }
                                    }
                                    
                                    echo '<tr>
                                        <td>
                                        '.$report_type.'
                                        </td>
                                        <td>
                                        '.$patient_name.'
                                        </td>
                                        <td>
                                        '.substr($generated_date,0,16).'
                                        </td>
                                        <td>
                                        N/A
                                        </td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                            <a href="?action=view&id='.$report_id.'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-view">View</button></a>
                                            &nbsp;&nbsp;&nbsp;
                                            <a href="?action=print&id='.$report_id.'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-print">Print</button></a>
                                        </div>
                                        </td>
                                    </tr>';
                                }
                            }
                                 
                            ?>

                            </tbody>

                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
            </table>
        </div>
    </div>
</body>
</html>