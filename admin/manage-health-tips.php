<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Manage Health Tips</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        
        .health-tips-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .tip-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .tip-title {
            font-size: 1.4em;
            font-weight: bold;
            color: #333;
        }
        
        .tip-date {
            color: #777;
            font-size: 0.9em;
        }
        
        .tip-content {
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .tip-actions {
            margin-top: 15px;
        }
        
        .btn-delete {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background: #cc0000;
        }
        
        .add-tip-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-actions {
            text-align: center;
        }
        
        .form-actions input[type="submit"] {
            background: #000000;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        
        .form-actions input[type="submit"]:hover {
            background: #333;
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

    // Handle form submission for adding new health tip
    if ($_POST) {
        if (isset($_POST['add_tip'])) {
            $admin_email = $_SESSION["user"];
            $title = $_POST['title'];
            $content = $_POST['content'];
            
            // Handle file upload if image is provided
            $image_path = null;
            if (isset($_FILES['tip_image']) && $_FILES['tip_image']['error'] == 0) {
                $upload_dir = "../img/tips/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = $_FILES['tip_image']['name'];
                $file_tmp = $_FILES['tip_image']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = array("jpg", "jpeg", "png", "gif");
                
                if (in_array($file_ext, $allowed_ext)) {
                    $unique_name = uniqid() . "." . $file_ext;
                    $image_path = $upload_dir . $unique_name;
                    
                    if (move_uploaded_file($file_tmp, $image_path)) {
                        $image_path = substr($image_path, 3); // Remove '../' from path
                    } else {
                        $image_path = null;
                    }
                }
            }
            
            $sql = "INSERT INTO health_tips (admin_email, title, content, tip_image) VALUES (?, ?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ssss", $admin_email, $title, $content, $image_path);
            
            if ($stmt->execute()) {
                $database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS target_url VARCHAR(255) NULL");
                $database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");
                
                // Send notifications to all doctors
                $doctor_query = $database->query("SELECT docid FROM doctor");
                if ($doctor_query) {
                    while ($doctor = $doctor_query->fetch_assoc()) {
                        $doctor_id = (int)$doctor['docid'];
                        $notification_message = $database->real_escape_string("New health tip published: " . $title);
                        $target_url = $database->real_escape_string("../health-tips-public.php");
                        $database->query("INSERT INTO session_notifications (scheduleid, docid, message, notify_date, status, target_url) VALUES (0, $doctor_id, '$notification_message', NOW(), 'pending', '$target_url')");
                    }
                }
                
                // Send notifications to all patients
                $patient_query = $database->query("SELECT pid FROM patient");
                if ($patient_query) {
                    while ($patient = $patient_query->fetch_assoc()) {
                        $patient_id = (int)$patient['pid'];
                        $notification_message = $database->real_escape_string("New health tip published: " . $title);
                        $target_url = $database->real_escape_string("../health-tips-public.php");
                        $database->query("INSERT INTO session_notifications (scheduleid, patid, message, notify_date, status, target_url) VALUES (0, $patient_id, '$notification_message', NOW(), 'pending', '$target_url')");
                    }
                }
                
                $success_message = "Health tip added successfully!";
            } else {
                $error_message = "Failed to add health tip.";
            }
            $stmt->close();
        } 
        elseif (isset($_POST['delete_tip'])) {
            $tip_id = $_POST['tip_id'];
            
            // Get the tip to delete to remove associated image
            $get_tip_sql = "SELECT tip_image FROM health_tips WHERE tip_id = ?";
            $get_stmt = $database->prepare($get_tip_sql);
            $get_stmt->bind_param("i", $tip_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            $tip = $result->fetch_assoc();
            
            if ($tip && $tip['tip_image']) {
                $image_path = "../" . $tip['tip_image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $delete_sql = "DELETE FROM health_tips WHERE tip_id = ?";
            $delete_stmt = $database->prepare($delete_sql);
            $delete_stmt->bind_param("i", $tip_id);
            
            if ($delete_stmt->execute()) {
                $success_message = "Health tip deleted successfully!";
            } else {
                $error_message = "Failed to delete health tip.";
            }
            $delete_stmt->close();
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
                                    <p class="profile-subtitle">admin@gmail.com</p>
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
                    <td class="menu-btn menu-icon-doctor ">
                        <a href="doctors.php" class="non-style-link-menu "><div><p class="menu-text">Doctors</p></a></div>
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
                    <td class="menu-btn menu-icon-heart active-menu-icon">
                        <a href="manage-health-tips.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Health Tips</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <!-- Horizontal bar with Back button, time, and date -->
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                        <a href="index.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Manage Health Tips</p>
                                           
                    </td>
                    <td width="30%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                            date_default_timezone_set('Asia/Kolkata');
                            $date = date('Y-m-d');
                            echo $date;
                            ?>
                        </p>
                    </td>
                    
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        
                
          

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #c3e6cb;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            
            <!-- Add New Health Tips window -->
            <div class="health-tips-container">
                <div class="add-tip-form">
                    <h2>Add New Health Tip</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" placeholder="Enter tip title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea name="content" id="content" placeholder="Enter health tip content" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="tip_image">Image (Optional)</label>
                            <input type="file" name="tip_image" id="tip_image" accept="image/*">
                        </div>
                        
                        <div class="form-actions">
                            <input type="submit" name="add_tip" value="Add Health Tip" class="btn-primary">
                        </div>
                    </form>
                </div>

                   

                <!-- Display existing health tips -->
                <h2>Published Health Tips</h2>
                <?php
                $sql = "SELECT ht.*, a.aemail as admin_name FROM health_tips ht LEFT JOIN admin a ON ht.admin_email = a.aemail ORDER BY ht.created_at DESC";
                $result = $database->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($tip = $result->fetch_assoc()) {
                        echo '<div class="tip-card">';
                        echo '<div class="tip-header">';
                        echo '<div class="tip-title">' . htmlspecialchars($tip['title']) . '</div>';
                        echo '<div class="tip-date">' . date('M j, Y g:i A', strtotime($tip['created_at'])) . '</div>';
                        echo '</div>';
                        
                        if ($tip['tip_image']) {
                            echo '<img src="../' . htmlspecialchars($tip['tip_image']) . '" alt="Health Tip Image" style="max-width: 100%; height: auto; margin: 10px 0; border-radius: 4px;">';
                        }
                        
                        echo '<div class="tip-content">' . nl2br(htmlspecialchars($tip['content'])) . '</div>';
                        
                        echo '<div class="tip-actions">';
                        echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this health tip?\');">';
                        echo '<input type="hidden" name="tip_id" value="' . $tip['tip_id'] . '">';
                        echo '<input type="submit" name="delete_tip" value="Delete" class="btn-delete">';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No health tips published yet.</p>';
                }
                ?>
            </div>

             </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>