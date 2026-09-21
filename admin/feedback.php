<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/messages.css">
        
    <title>Feedback Management</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
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
    
    

    include("../includes/message_handler.php");
    
    
    include("../connection.php");
    
    
    if ($_POST) {
        if (isset($_POST['respond_feedback'])) {
            $feedback_id = $_POST['feedback_id'];
            $response = $_POST['response'];
            $admin_id = 1; 
            
            
            $sql = "UPDATE feedback SET response=?, responded_by=?, responded_date=NOW() WHERE feedback_id=?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("sii", $response, $admin_id, $feedback_id);
            if ($stmt->execute()) {
                setMessage("Response sent successfully!", "success");
            } else {
                setMessage("There was an error sending your response. Please try again.", "error");
            }
            $stmt->close();
            
            
            header("Location: feedback.php");
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
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor ">
                        <a href="doctors.php" class="non-style-link-menu "><div><p class="menu-text">Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu "><div><p class="menu-text">Schedule</p></div></a>
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
                    <td class="menu-btn menu-icon-feedback active">
                        <a href="feedback.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Feedback</p></a></div>
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
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Feedback Management</p>
                                           
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
               
                <!-- Display messages -->
                <tr>
                    <td colspan="4">
                        <?php displayMessages(); ?>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4" style="padding-top:30px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Feedback Messages</p>
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
                                    From
                                </th>
                                <th class="table-headin">
                                    Subject
                                </th>
                                <th class="table-headin">
                                    Date
                                </th>
                                <th class="table-headin">
                                    Status
                                </th>
                                <th class="table-headin">
                                    Actions
                                </th>
                        </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                            
                            $sqlmain = "SELECT f.*, p.pname as patient_name, d.docname as doctor_name FROM feedback f LEFT JOIN patient p ON (f.user_id = p.pid AND f.user_type = 'patient') LEFT JOIN doctor d ON (f.user_id = d.docid AND f.user_type = 'doctor') ORDER BY f.created_date DESC";
                            $result = $database->query($sqlmain);

                            if($result->num_rows==0){
                                echo '<tr>
                                <td colspan="5">
                                <br><br><br><p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49);text-align:center;">No feedback messages found.</p><br><br><br>
                                </td>
                                </tr>';
                                

                            }else{
                                for ($x=0;$x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $feedback_id=$row["feedback_id"];
                                    $user_type=$row["user_type"];
                                    $subject=$row["subject"];
                                    $created_date=$row["created_date"];
                                    $response=$row["response"];
                                    $user_name = ($user_type == 'patient') ? $row["patient_name"] : (($user_type == 'doctor') ? $row["doctor_name"] : 'Admin');
                                    
                                    $status = ($response) ? 'Responded' : 'Pending';
                                    $status_color = ($response) ? 'green' : 'orange';
                                    
                                    echo '<tr>
                                        <td>
                                        '.ucfirst($user_type).': '.$user_name.'
                                        </td>
                                        <td>
                                        '.$subject.'
                                        </td>
                                        <td>
                                        '.substr($created_date,0,16).'
                                        </td>
                                        <td>
                                        <span style="color:'.$status_color.'">'.$status.'</span>
                                        </td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                            <a href="?action=view&id='.$feedback_id.'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-view">View</button></a>
                                            '.(($response) ? '' : '&nbsp;&nbsp;&nbsp;<a href="?action=respond&id='.$feedback_id.'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-reply">Reply</button></a>').'
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
    
    <?php
    
    if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
        $feedback_id = $_GET['id'];
        
        $sql = "SELECT f.*, p.pname as patient_name, d.docname as doctor_name FROM feedback f LEFT JOIN patient p ON (f.user_id = p.pid AND f.user_type = 'patient') LEFT JOIN doctor d ON (f.user_id = d.docid AND f.user_type = 'doctor') WHERE f.feedback_id=?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $feedback_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $feedback = $result->fetch_assoc();
        $stmt->close();
        
        $user_type = $feedback['user_type'];
        $user_name = ($user_type == 'patient') ? $feedback["patient_name"] : (($user_type == 'doctor') ? $feedback["doctor_name"] : 'Admin');
        
        echo '
        <div id="popup1" class="overlay">
            <div class="popup">
                <center>
                    <h2>Feedback Details</h2>
                    <a class="close" href="feedback.php">&times;</a>
                    <div class="content">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Feedback Details</p><br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="from" class="form-label">From: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.ucfirst($feedback['user_type']).': '.$user_name.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="subject" class="form-label">Subject: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$feedback['subject'].'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="message" class="form-label">Message: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.nl2br($feedback['message']).'<br><br>
                                </td>
                            </tr>';
                            

        if ($feedback['response']) {
            echo '
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="response" class="form-label">Admin Response: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.nl2br($feedback['response']).'<br><br>
                                </td>
                            </tr>';
        }
                            

        echo '
                            <tr>
                                <td colspan="2">
                                    <a href="feedback.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </center>
            </div>
        </div>';
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'respond' && isset($_GET['id'])) {
        $feedback_id = $_GET['id'];
        
        $sql = "SELECT f.*, p.pname as patient_name, d.docname as doctor_name FROM feedback f LEFT JOIN patient p ON (f.user_id = p.pid AND f.user_type = 'patient') LEFT JOIN doctor d ON (f.user_id = d.docid AND f.user_type = 'doctor') WHERE f.feedback_id=?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $feedback_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $feedback = $result->fetch_assoc();
        $stmt->close();
        
        $user_type = $feedback['user_type'];
        $user_name = ($user_type == 'patient') ? $feedback["patient_name"] : (($user_type == 'doctor') ? $feedback["doctor_name"] : 'Admin');
        
        echo '
        <div id="popup1" class="overlay">
            <div class="popup">
                <center>
                    <h2>Respond to Feedback</h2>
                    <a class="close" href="feedback.php">&times;</a>
                    <div class="content">
                        <form method="post" action="">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                <tr>
                                    <td>
                                        <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Feedback Details</p><br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="from" class="form-label">From: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        '.ucfirst($feedback['user_type']).': '.$user_name.'<br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="subject" class="form-label">Subject: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        '.$feedback['subject'].'<br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="message" class="form-label">Message: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        '.nl2br($feedback['message']).'<br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="response" class="form-label">Your Response: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="hidden" name="feedback_id" value="'.$feedback_id.'">
                                        <textarea name="response" class="input-text" placeholder="Enter your response..." rows="5" required></textarea><br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="submit" name="respond_feedback" value="Send Response" class="login-btn btn-primary btn">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </center>
            </div>
        </div>';
    }
    ?>
</body>
</html>