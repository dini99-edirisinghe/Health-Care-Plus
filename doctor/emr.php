<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }

}else{
    header("location: ../login.php");
}

include("../connection.php");
include("../includes/delete_handler.php");

$sql_check = "SHOW TABLES LIKE 'medical_records'";
$result_check = $database->query($sql_check);

if ($result_check->num_rows == 0) {
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '    <meta charset="UTF-8">';
    echo '    <meta http-equiv="X-UA-Compatible" content="IE=edge">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '    <link rel="stylesheet" href="../css/animations.css">';
    echo '    <link rel="stylesheet" href="../css/main.css">';
    echo '    <link rel="stylesheet" href="../css/admin.css">';
    echo '    <link rel="stylesheet" href="../css/emr.css">';
    echo '    <title>Electronic Medical Records</title>';
    echo '    <style>';
    echo '        .container { max-width: 800px; margin: 50px auto; padding: 20px; }';
    echo '        .alert { padding: 20px; border-radius: 8px; margin: 20px 0; }';
    echo '        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }';
    echo '        .btn { display: inline-block; padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; }';
    echo '        .btn:hover { background: #1976D2; }';
    echo '    </style>';
    echo '</head>';
    echo '<body>';
    echo '    <div class="container">';
    echo '        <div class="alert alert-warning">';
    echo '            <h2>⚠ Database Setup Required</h2>';
    echo '            <p>The Electronic Medical Records (EMR) feature requires database tables that haven\'t been created yet.</p>';
    echo '            <p>Please run the database setup script to enable this feature:</p>';
    echo '            <ol>';
    echo '                <li>Go to: <a href="../INSTALL_EMR.html">Install EMR Tables</a></li>';
    echo '                <li>Click "Install EMR Tables Now"</li>';
    echo '                <li>Or re-import <code>SQL_Database_edoc.sql</code> to your database</li>';
    echo '            </ol>';
    echo '            <p>Or manually import the <code>SQL_Database_edoc.sql</code> file into your database.</p>';
    echo '            <a href="index.php" class="btn">← Back to Dashboard</a>';
    echo '        </div>';
    echo '    </div>';
    echo '</body>';
    echo '</html>';
    exit();
}


$sqlmain= "select * from doctor where docemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();
$doctor_id=$userfetch["docid"];
$username=$userfetch["docname"];

// Get unread notification count for doctor
$unreadNotificationsQuery = $database->query("SELECT COUNT(*) AS unread_count FROM session_notifications WHERE docid = $doctor_id AND status = 'pending'");
$unreadNotificationsCount = 0;
if ($unreadNotificationsQuery && $unreadNotificationsQuery->num_rows > 0) {
    $unreadNotificationRow = $unreadNotificationsQuery->fetch_assoc();
    $unreadNotificationsCount = (int)$unreadNotificationRow['unread_count'];
}

if ($_POST) {
    if (isset($_POST["add_record"])) {
        $patient_id = $_POST["patient_id"];
        $visit_date = $_POST["visit_date"];
        $chief_complaint = $_POST["chief_complaint"];
        $diagnosis = $_POST["diagnosis"];
        $treatment_plan = $_POST["treatment_plan"];
        $notes = $_POST["notes"];
        
        $sql = "INSERT INTO medical_records (patient_id, doctor_id, visit_date, chief_complaint, diagnosis, treatment_plan, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iisssssi", $patient_id, $doctor_id, $visit_date, $chief_complaint, $diagnosis, $treatment_plan, $notes, $doctor_id);
        $stmt->execute();
        $record_id = $database->insert_id;
        
        if (!empty($_POST["medications"])) {
            foreach ($_POST["medications"] as $med) {
                if (!empty($med["name"])) {
                    $med_name = $med["name"];
                    $dosage = $med["dosage"];
                    $frequency = $med["frequency"];
                    $duration = $med["duration"];
                    $instructions = $med["instructions"];
                    
                    $sql_med = "INSERT INTO medications (record_id, medicine_name, dosage, frequency, duration, instructions, prescribed_by) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_med = $database->prepare($sql_med);
                    $stmt_med->bind_param("isssssi", $record_id, $med_name, $dosage, $frequency, $duration, $instructions, $doctor_id);
                    $stmt_med->execute();
                }
            }
        }
        
        $message = "Medical record added successfully!";
    }
    
    if (isset($_POST["add_vitals"])) {
        $record_id = $_POST["record_id"];
        $bp_systolic = $_POST["bp_systolic"];
        $bp_diastolic = $_POST["bp_diastolic"];
        $heart_rate = $_POST["heart_rate"];
        $temperature = $_POST["temperature"];
        $respiratory_rate = $_POST["respiratory_rate"];
        $oxygen_saturation = $_POST["oxygen_saturation"];
        $weight = $_POST["weight"];
        $height = $_POST["height"];
        
        $bmi = null;
        if ($weight && $height) {
            $height_m = $height / 100;
            $bmi = round($weight / ($height_m * $height_m), 2);
        }
        
        $sql = "INSERT INTO vital_signs (record_id, bp_systolic, bp_diastolic, heart_rate, temperature, respiratory_rate, oxygen_saturation, weight, height, bmi, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iiiddiddddi", $record_id, $bp_systolic, $bp_diastolic, $heart_rate, $temperature, $respiratory_rate, $oxygen_saturation, $weight, $height, $bmi, $doctor_id);
        $stmt->execute();
        
        $message = "Vital signs recorded successfully!";
    }
}

$sql_patients = "SELECT DISTINCT p.pid, p.pname, p.pemail 
                 FROM patient p 
                 INNER JOIN appointment a ON p.pid = a.pid 
                 INNER JOIN schedule s ON a.scheduleid = s.scheduleid 
                 WHERE s.docid = ? 
                 ORDER BY p.pname";
$stmt_patients = $database->prepare($sql_patients);
$stmt_patients->bind_param("i", $doctor_id);
$stmt_patients->execute();
$patients_result = $stmt_patients->get_result();

$selected_patient = isset($_GET["patient_id"]) ? $_GET["patient_id"] : "";
$records_result = null;
if ($selected_patient) {
    $sql_records = "SELECT mr.*, p.pname as patient_name 
                    FROM medical_records mr 
                    INNER JOIN patient p ON mr.patient_id = p.pid 
                    WHERE mr.patient_id = ? AND mr.doctor_id = ? 
                    ORDER BY mr.visit_date DESC";
    $stmt_records = $database->prepare($sql_records);
    $stmt_records->bind_param("ii", $selected_patient, $doctor_id);
    $stmt_records->execute();
    $records_result = $stmt_records->get_result();
}

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
    <link rel="stylesheet" href="../css/emr.css">
    <title>Patient Records</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .table-headin{
            background: #2196F3;
            color: white;
        }
        .table-headin:hover{
            background: #1976D2;
        }
        .menu-icon-medical{
            background: #E3F2FD;
        }
        .menu-icon-medical:hover{
            background: #BBDEFB;
        }
        .menu-text.menu-icon-medical{
            color: #1976D2;
        }
        .dashboard-icons{
            background: #2196F3;
        }
        .btn-icon-back{
            background: #2196F3;
        }
        .sub-table tr:hover{
            background: #E3F2FD;
        }
        .filter-container{
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-element{
            margin-bottom: 15px;
        }
        .form-label{
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-input{
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-submit{
            background: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-submit:hover{
            background: #1976D2;
        }
        .record-card{
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .record-header{
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .record-title{
            font-size: 18px;
            font-weight: bold;
            color: #2196F3;
        }
        .record-date{
            color: #666;
            font-size: 14px;
        }
        .section-title{
            font-weight: bold;
            margin: 15px 0 10px 0;
            color: #1976D2;
        }
        .medication-item{
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .tabs{
            display: flex;
            margin-bottom: 20px;
        }
        .tab{
            padding: 10px 20px;
            background: #f5f5f5;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
        }
        .tab.active{
            background: #2196F3;
            color: white;
        }
        .tab-content{
            display: none;
        }
        .tab-content.active{
            display: block;
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
                                    <?php if(isset($userfetch["profile_picture"]) && $userfetch["profile_picture"]): ?>
                                        <img src="../uploads/profile_pictures/<?php echo htmlspecialchars($userfetch["profile_picture"]); ?>" alt="" width="100%" style="border-radius:50%">
                                    <?php else: ?>
                                        <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                    <?php endif; ?>
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
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
                    <td class="menu-btn menu-icon-home " >
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Dashboard</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">My Patients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-feedback">
                        <a href="feedback.php" class="non-style-link-menu"><div><p class="menu-text">Feedback</p></a></div>
                    </td>
                </tr>

                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-heart">
                        <a href="../health-tips-public.php" class="non-style-link-menu"><div><p class="menu-text">Health Tips</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report">
                        <a href="generate_report.php" class="non-style-link-menu"><div><p class="menu-text">Patient Documents</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-notification">
                        <a href="notifications.php" class="non-style-link-menu notification-link"><div><p class="menu-text">Notifications</p><?php if($unreadNotificationsCount > 0): ?><span class="notification-dot"></span><?php endif; ?></div></a>
                    </td>
                </tr>
                
            </table>
        </div>
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr >
                    <td colspan="1" class="nav-bar" >
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;margin-left:20px;">Patient Records</p>
                    </td>
                    <td width="25%">
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
                    <td colspan="4" >
                        <center>
                        <div class="filter-container" style="width:95%;">
                            <h3>Patient Selection</h3>
                            <form method="GET" action="">
                                <label for="patient_id" class="form-label">Select Patient:</label>
                                <select name="patient_id" id="patient_id" class="form-input" onchange="this.form.submit()">
                                    <option value="">-- Select a Patient --</option>
                                    <?php 
                                    while($patient = $patients_result->fetch_assoc()) {
                                        $selected = ($selected_patient == $patient["pid"]) ? "selected" : "";
                                        echo "<option value='".$patient["pid"]."' $selected>".$patient["pname"]." (".$patient["pemail"].")</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                        </center>
                    </td>
                </tr>
                
                <?php if ($selected_patient): ?>
                <tr>
                    <td colspan="4">
                        <center>
                        <div class="filter-container" style="width:95%;">
                            <h3>Add New Medical Record</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="patient_id" value="<?php echo $selected_patient; ?>">
                                <input type="hidden" name="add_record" value="1">
                                
                                <div class="form-element">
                                    <label for="visit_date" class="form-label">Visit Date:</label>
                                    <input type="date" name="visit_date" id="visit_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="form-element">
                                    <label for="chief_complaint" class="form-label">Chief Complaint:</label>
                                    <textarea name="chief_complaint" id="chief_complaint" class="form-input" rows="3"></textarea>
                                </div>
                                
                                <div class="form-element">
                                    <label for="diagnosis" class="form-label">Diagnosis:</label>
                                    <textarea name="diagnosis" id="diagnosis" class="form-input" rows="3"></textarea>
                                </div>
                                
                                <div class="form-element">
                                    <label for="treatment_plan" class="form-label">Treatment Plan:</label>
                                    <textarea name="treatment_plan" id="treatment_plan" class="form-input" rows="3"></textarea>
                                </div>
                                
                                <div class="form-element">
                                    <label for="notes" class="form-label">Notes:</label>
                                    <textarea name="notes" id="notes" class="form-input" rows="3"></textarea>
                                </div>
                                
                                <h4>Medications</h4>
                                <div id="medications-container">
                                    <div class="medication-item">
                                        <div class="form-element">
                                            <label class="form-label">Medicine Name:</label>
                                            <input type="text" name="medications[0][name]" class="form-input">
                                        </div>
                                        <div class="form-element">
                                            <label class="form-label">Dosage:</label>
                                            <input type="text" name="medications[0][dosage]" class="form-input" placeholder="e.g., 500mg">
                                        </div>
                                        <div class="form-element">
                                            <label class="form-label">Frequency:</label>
                                            <input type="text" name="medications[0][frequency]" class="form-input" placeholder="e.g., Twice daily">
                                        </div>
                                        <div class="form-element">
                                            <label class="form-label">Duration:</label>
                                            <input type="text" name="medications[0][duration]" class="form-input" placeholder="e.g., 7 days">
                                        </div>
                                        <div class="form-element">
                                            <label class="form-label">Instructions:</label>
                                            <textarea name="medications[0][instructions]" class="form-input" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" onclick="addMedicationField()" class="btn-primary btn" style="margin-bottom: 15px;">Add Another Medication</button>
                                
                                <div class="form-element">
                                    <input type="submit" value="Save Medical Record" class="btn-submit">
                                </div>
                            </form>
                        </div>
                        </center>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4">
                        <center>
                        <div class="filter-container" style="width:95%;">
                            <h3>Medical Records for Selected Patient</h3>
                            <?php if ($records_result && $records_result->num_rows > 0): ?>
                                <?php while($record = $records_result->fetch_assoc()): ?>
                                    <div class="record-card">
                                        <div class="record-header">
                                            <div class="record-title">Visit on <?php echo date("M j, Y", strtotime($record["visit_date"])); ?></div>
                                            <div class="record-date">Recorded on <?php echo date("M j, Y g:i A", strtotime($record["created_date"])); ?></div>
                                        </div>
                                        
                                        <?php if ($record["chief_complaint"]): ?>
                                            <div class="section-title">Chief Complaint</div>
                                            <p><?php echo nl2br(htmlspecialchars($record["chief_complaint"])); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($record["diagnosis"]): ?>
                                            <div class="section-title">Diagnosis</div>
                                            <p><?php echo nl2br(htmlspecialchars($record["diagnosis"])); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($record["treatment_plan"]): ?>
                                            <div class="section-title">Treatment Plan</div>
                                            <p><?php echo nl2br(htmlspecialchars($record["treatment_plan"])); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($record["notes"]): ?>
                                            <div class="section-title">Notes</div>
                                            <p><?php echo nl2br(htmlspecialchars($record["notes"])); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        // Get medications for this record
                                        $sql_meds = "SELECT * FROM medications WHERE record_id = ?";
                                        $stmt_meds = $database->prepare($sql_meds);
                                        $stmt_meds->bind_param("i", $record["record_id"]);
                                        $stmt_meds->execute();
                                        $meds_result = $stmt_meds->get_result();
                                        
                                        if ($meds_result->num_rows > 0):
                                        ?>
                                            <div class="section-title">Medications</div>
                                            <?php while($med = $meds_result->fetch_assoc()): ?>
                                                <div class="medication-item">
                                                    <strong><?php echo htmlspecialchars($med["medicine_name"]); ?></strong>
                                                    <?php if ($med["dosage"]): ?>
                                                        <div>Dosage: <?php echo htmlspecialchars($med["dosage"]); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($med["frequency"]): ?>
                                                        <div>Frequency: <?php echo htmlspecialchars($med["frequency"]); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($med["duration"]): ?>
                                                        <div>Duration: <?php echo htmlspecialchars($med["duration"]); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($med["instructions"]): ?>
                                                        <div>Instructions: <?php echo nl2br(htmlspecialchars($med["instructions"])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        // Get vital signs for this record
                                        $sql_vitals = "SELECT * FROM vital_signs WHERE record_id = ?";
                                        $stmt_vitals = $database->prepare($sql_vitals);
                                        $stmt_vitals->bind_param("i", $record["record_id"]);
                                        $stmt_vitals->execute();
                                        $vitals_result = $stmt_vitals->get_result();
                                        
                                        if ($vitals_result->num_rows > 0):
                                            $vital = $vitals_result->fetch_assoc();
                                        ?>
                                            <div class="section-title">Vital Signs</div>
                                            <div class="medication-item">
                                                <?php if ($vital["bp_systolic"] && $vital["bp_diastolic"]): ?>
                                                    <div>Blood Pressure: <?php echo $vital["bp_systolic"]; ?>/<?php echo $vital["bp_diastolic"]; ?> mmHg</div>
                                                <?php endif; ?>
                                                <?php if ($vital["heart_rate"]): ?>
                                                    <div>Heart Rate: <?php echo $vital["heart_rate"]; ?> bpm</div>
                                                <?php endif; ?>
                                                <?php if ($vital["temperature"]): ?>
                                                    <div>Temperature: <?php echo $vital["temperature"]; ?> °C</div>
                                                <?php endif; ?>
                                                <?php if ($vital["respiratory_rate"]): ?>
                                                    <div>Respiratory Rate: <?php echo $vital["respiratory_rate"]; ?> breaths/min</div>
                                                <?php endif; ?>
                                                <?php if ($vital["oxygen_saturation"]): ?>
                                                    <div>Oxygen Saturation: <?php echo $vital["oxygen_saturation"]; ?>%</div>
                                                <?php endif; ?>
                                                <?php if ($vital["weight"]): ?>
                                                    <div>Weight: <?php echo $vital["weight"]; ?> kg</div>
                                                <?php endif; ?>
                                                <?php if ($vital["height"]): ?>
                                                    <div>Height: <?php echo $vital["height"]; ?> cm</div>
                                                <?php endif; ?>
                                                <?php if ($vital["bmi"]): ?>
                                                    <div>BMI: <?php echo $vital["bmi"]; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Add Vital Signs Form -->
                                        <div class="section-title">Add Vital Signs</div>
                                        <form method="POST" action="" style="margin-top: 10px;">
                                            <input type="hidden" name="record_id" value="<?php echo $record["record_id"]; ?>">
                                            <input type="hidden" name="add_vitals" value="1">
                                            
                                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                                                <div>
                                                    <label class="form-label">BP Systolic (mmHg)</label>
                                                    <input type="number" name="bp_systolic" class="form-input" placeholder="e.g., 120">
                                                </div>
                                                <div>
                                                    <label class="form-label">BP Diastolic (mmHg)</label>
                                                    <input type="number" name="bp_diastolic" class="form-input" placeholder="e.g., 80">
                                                </div>
                                                <div>
                                                    <label class="form-label">Heart Rate (bpm)</label>
                                                    <input type="number" name="heart_rate" class="form-input" placeholder="e.g., 72">
                                                </div>
                                                <div>
                                                    <label class="form-label">Temperature (°C)</label>
                                                    <input type="number" step="0.1" name="temperature" class="form-input" placeholder="e.g., 37.0">
                                                </div>
                                                <div>
                                                    <label class="form-label">Respiratory Rate</label>
                                                    <input type="number" name="respiratory_rate" class="form-input" placeholder="breaths/min">
                                                </div>
                                                <div>
                                                    <label class="form-label">Oxygen Saturation (%)</label>
                                                    <input type="number" step="0.1" name="oxygen_saturation" class="form-input" placeholder="e.g., 98.5">
                                                </div>
                                                <div>
                                                    <label class="form-label">Weight (kg)</label>
                                                    <input type="number" step="0.1" name="weight" class="form-input" placeholder="e.g., 70.5">
                                                </div>
                                                <div>
                                                    <label class="form-label">Height (cm)</label>
                                                    <input type="number" name="height" class="form-input" placeholder="e.g., 175">
                                                </div>
                                            </div>
                                            
                                            <div style="margin-top: 10px;">
                                                <input type="submit" value="Save Vital Signs" class="btn-primary btn" style="padding: 8px 15px;">
                                            </div>
                                        </form>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No medical records found for this patient.</p>
                            <?php endif; ?>
                        </div>
                        </center>
                    </td>
                </tr>
                <?php endif; ?>
                
            </table>
        </div>
    </div>

    <script>
        let medicationIndex = 1;
        
        function addMedicationField() {
            const container = document.getElementById('medications-container');
            const newMedication = document.createElement('div');
            newMedication.className = 'medication-item';
            newMedication.innerHTML = `
                <div class="form-element">
                    <label class="form-label">Medicine Name:</label>
                    <input type="text" name="medications[${medicationIndex}][name]" class="form-input">
                </div>
                <div class="form-element">
                    <label class="form-label">Dosage:</label>
                    <input type="text" name="medications[${medicationIndex}][dosage]" class="form-input" placeholder="e.g., 500mg">
                </div>
                <div class="form-element">
                    <label class="form-label">Frequency:</label>
                    <input type="text" name="medications[${medicationIndex}][frequency]" class="form-input" placeholder="e.g., Twice daily">
                </div>
                <div class="form-element">
                    <label class="form-label">Duration:</label>
                    <input type="text" name="medications[${medicationIndex}][duration]" class="form-input" placeholder="e.g., 7 days">
                </div>
                <div class="form-element">
                    <label class="form-label">Instructions:</label>
                    <textarea name="medications[${medicationIndex}][instructions]" class="form-input" rows="2"></textarea>
                </div>
            `;
            container.appendChild(newMedication);
            medicationIndex++;
        }
    </script>
</body>
</html>