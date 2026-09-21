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
        
    <title>Feedback</title>
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
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }

    include("../includes/message_handler.php");

    include("../connection.php");

    $sqlmain= "select * from doctor where docemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s",$useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch=$userrow->fetch_assoc();
    $docid= $userfetch["docid"];
    $docname=$userfetch["docname"];

    // Get unread notification count for doctor
    $unreadNotificationsQuery = $database->query("SELECT COUNT(*) AS unread_count FROM session_notifications WHERE docid = $docid AND status = 'pending'");
    $unreadNotificationsCount = 0;
    if ($unreadNotificationsQuery && $unreadNotificationsQuery->num_rows > 0) {
        $unreadNotificationRow = $unreadNotificationsQuery->fetch_assoc();
        $unreadNotificationsCount = (int)$unreadNotificationRow['unread_count'];
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
                                    <?php if(isset($userfetch["profile_picture"]) && $userfetch["profile_picture"]): ?>
                                        <img src="../uploads/profile_pictures/<?php echo htmlspecialchars($userfetch["profile_picture"]); ?>" alt="" width="100%" style="border-radius:50%">
                                    <?php else: ?>
                                        <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                    <?php endif; ?>
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($docname,0,13)  ?>..</p>
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
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">My Sessions</p></div></a>
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
                    <td class="menu-btn menu-icon-feedback active">
                        <a href="feedback.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Feedback</p></a></div>
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
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="index.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Feedback</p>
                                           
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
                    <td colspan="4" style="padding-top:0px;">
                        <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    Patient
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
                                    Events
                                </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                            
                            $sqlmain = "SELECT f.*, p.pname as patient_name FROM feedback f JOIN patient p ON f.user_id = p.pid WHERE f.user_type='patient' AND f.response IS NOT NULL ORDER BY f.responded_date DESC";
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
                                    $subject=$row["subject"];
                                    $created_date=$row["created_date"];
                                    $response=$row["response"];
                                    $patient_name=$row["patient_name"];
                                    $responded_date=$row["responded_date"];
                                    
                                    $status = 'Responded';
                                    $status_color = 'green';
                                    
                                    echo '<tr>
                                        <td>
                                        '.$patient_name.'
                                        </td>
                                        <td>
                                        '.$subject.'
                                        </td>
                                        <td>
                                        '.substr($responded_date,0,16).'
                                        </td>
                                        <td>
                                        <span style="color:'.$status_color.'">'.$status.'</span>
                                        </td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                            <a href="?action=view&id='.$feedback_id.'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-view">View</button></a>
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

        $sql = "SELECT f.*, p.pname as patient_name FROM feedback f JOIN patient p ON f.user_id = p.pid WHERE f.feedback_id=? AND f.user_type='patient'";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $feedback_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $feedback = $result->fetch_assoc();
        $stmt->close();
        
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
                                    Patient: '.$feedback['patient_name'].'<br><br>
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
                                    <label for="response" class="form-label">Admin Response: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.nl2br($feedback['response']).'<br><br>
                                </td>
                            </tr>
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
    ?>
</body>
</html>