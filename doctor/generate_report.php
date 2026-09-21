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

$sqlmain= "select * from doctor where docemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();
$doctor_id=$userfetch["docid"];
$username=$userfetch["docname"];

$message = "";

if ($_POST) {
    $patient_id = $_POST["patient_id"];
    $document_type = $_POST["document_type"];
    $document_title = $_POST["document_title"];
    $document_content = $_POST["document_content"];
    
    // Handle image upload if present
    $image_path = null;
    if (isset($_FILES['document_image']) && $_FILES['document_image']['error'] == 0) {
        $upload_dir = '../uploads/document_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $file_ext = strtolower(pathinfo($_FILES['document_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            $new_filename = uniqid() . '_' . basename($_FILES['document_image']['name']);
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['document_image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/document_images/' . $new_filename;
            } else {
                $message = "Error uploading image file.";
            }
        } else {
            $message = "Invalid image file type. Only JPG, JPEG, PNG, GIF, BMP are allowed.";
        }
    }
    
    // Check if document_image column exists in the table
    $check_column_sql = "SHOW COLUMNS FROM medical_documents LIKE 'document_image'";
    $column_result = $database->query($check_column_sql);
    $column_exists = $column_result->num_rows > 0;
    
    // Insert document with or without image based on whether column exists
    if ($column_exists && $image_path) {
        $sql = "INSERT INTO medical_documents (patient_id, doctor_id, document_type, document_title, document_content, document_image, generated_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iissssi", $patient_id, $doctor_id, $document_type, $document_title, $document_content, $image_path, $doctor_id);
    } else {
        $sql = "INSERT INTO medical_documents (patient_id, doctor_id, document_type, document_title, document_content, generated_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iisssi", $patient_id, $doctor_id, $document_type, $document_title, $document_content, $doctor_id);
    }
    
    if ($stmt->execute()) {
        $report_type = "medical";
        $report_data = "Medical Document: " . $document_title . "\n\n" . $document_content;
        $sql_report = "INSERT INTO reports (patient_id, report_type, generated_by, report_data) VALUES (?, ?, ?, ?)";
        $stmt_report = $database->prepare($sql_report);
        $stmt_report->bind_param("isss", $patient_id, $report_type, $doctor_id, $report_data);
        $stmt_report->execute();
        
        // Get the ID of the newly inserted medical document
        $document_id = $database->insert_id;
        $message = "Report generated successfully! Document ID: " . $document_id;
    } else {
        $message = "Error generating report: " . $database->error;
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
    <title>Generate Patient Document</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .form-element{
            margin-bottom: 15px;
        }
        .form-label{
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-input, .form-textarea, .form-select{
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-textarea{
            min-height: 200px;
            resize: vertical;
        }
        .btn-submit{
            background: #2196F3;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-submit:hover{
            background: #1976D2;
        }
        .message{
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success{
            background: #e8f5e9;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }
        .error{
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
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
                    <td class="menu-btn menu-icon-heart">
                        <a href="../health-tips-public.php" class="non-style-link-menu"><div><p class="menu-text">Health Tips</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report menu-active menu-icon-report-active">
                        <a href="generate_report.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Patient Documents</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-feedback">
                        <a href="feedback.php" class="non-style-link-menu"><div><p class="menu-text">Feedback</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
                
            </table>
        </div>
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr >
                    <td colspan="1" class="nav-bar" >
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;margin-left:20px;">Generate Patient Document</p>
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
                    <td colspan="4">
                        <center>
                        <div style="width:95%; max-width:600px; margin: 20px auto;">
                            <?php if ($message): ?>
                                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="filter-container" style="text-align: left; padding: 30px;">
                                    <h3>Generate New Patient Document</h3>
                                    
                                    <div class="form-element">
                                        <label for="patient_id" class="form-label">Select Patient:</label>
                                        <select name="patient_id" id="patient_id" class="form-select" required>
                                            <option value="">-- Select a Patient --</option>
                                            <?php 
                                            $patients_result->data_seek(0); // Reset result pointer
                                            while($patient = $patients_result->fetch_assoc()) {
                                                echo "<option value='".$patient["pid"]."'>".$patient["pname"]." (".$patient["pemail"].")</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-element">
                                        <label for="document_type" class="form-label">Document Type:</label>
                                        <select name="document_type" id="document_type" class="form-select" required>
                                            <option value="">-- Select Document Type --</option>
                                            <option value="diagnosis">Diagnosis Report</option>
                                            <option value="prescription">Prescription</option>
                                            <option value="lab_report">Lab Report</option>
                                            <option value="discharge_summary">Discharge Summary</option>
                                            <option value="follow_up">Follow-up Instructions</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-element">
                                        <label for="document_title" class="form-label">Document Title:</label>
                                        <input type="text" name="document_title" id="document_title" class="form-input" placeholder="Enter document title" required>
                                    </div>
                                    
                                    <div class="form-element">
                                        <label for="document_content" class="form-label">Document Content:</label>
                                        <textarea name="document_content" id="document_content" class="form-textarea" placeholder="Enter document content..." required></textarea>
                                    </div>
                                    
                                    <div class="form-element">
                                        <label for="document_image" class="form-label">Upload Image (optional):</label>
                                        <input type="file" name="document_image" id="document_image" class="form-input" accept="image/*">
                                        <small>Allowed formats: JPG, JPEG, PNG, GIF, BMP</small>
                                    </div>
                                    
                                    <div class="form-element">
                                        <input type="submit" value="Generate Report" class="btn-submit">
                                    </div>
                                </div>
                            </form>
                        </div>
                        </center>
                    </td>
                </tr>
                
            </table>
        </div>
    </div>
</body>
</html>