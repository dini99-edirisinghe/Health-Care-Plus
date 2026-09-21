<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/patient.css">
        
    <title>Health Tips</title>
    <style>
        .health-tips-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .tip-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .tip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .tip-title {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }
        
        .tip-meta {
            display: flex;
            gap: 15px;
            color: #777;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .tip-date {
            font-weight: bold;
        }
        
        .tip-content {
            margin: 15px 0;
            line-height: 1.7;
            color: #444;
        }
        
        .tip-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #eee;
        }
        
        .no-tips {
            text-align: center;
            padding: 40px;
            color: #777;
            font-size: 1.2em;
        }
        
        .health-tips-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000000;
        }
        
        .health-tips-title {
            font-size: 2em;
            color: #000000;
            margin: 0;
        }
        
        .back-button {
            padding: 8px 16px;
            background: #000000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .back-button:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    include("../connection.php");

    // Get patient information
    $useremail = $_SESSION["user"];
    $sql = "SELECT * FROM patient WHERE pemail = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $result = $stmt->get_result();
    $userfetch = $result->fetch_assoc();
    $patient_name = $userfetch["pname"];
    $patient_id = $userfetch["pid"];

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
                                    <p class="profile-title"><?php echo substr($patient_name,0,13)  ?>..</p>
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
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-home">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-heart active-menu-icon">
                        <a href="health-tips.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Health Tips</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report">
                        <a href="reports.php" class="non-style-link-menu"><div><p class="menu-text">My Reports</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-payment">
                        <a href="payment_reports.php" class="non-style-link-menu"><div><p class="menu-text">My Payments</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-chatbot">
                        <a href="chatbot.php" class="non-style-link-menu"><div><p class="menu-text">Medical Assistant</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-feedback">
                        <a href="feedback.php" class="non-style-link-menu"><div><p class="menu-text">Feedback</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></div></a>
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
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Health Tips</p>
                                           
                    </td>
                    <td width="15%">
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
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
            </table>

            <!-- Health Tips Section -->
            <div class="health-tips-container">
                <?php
                // Fetch all health tips ordered by newest first
                $sql = "SELECT ht.*, a.aemail as admin_name FROM health_tips ht LEFT JOIN admin a ON ht.admin_email = a.aemail ORDER BY ht.created_at DESC";
                $result = $database->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($tip = $result->fetch_assoc()) {
                        echo '<div class="tip-card">';
                        echo '<div class="tip-header">';
                        echo '<div class="tip-title">' . htmlspecialchars($tip['title']) . '</div>';
                        echo '</div>';
                        
                        echo '<div class="tip-meta">';
                        echo '<div class="tip-date">Posted: ' . date('M j, Y g:i A', strtotime($tip['created_at'])) . '</div>';
                        echo '</div>';
                        
                        if ($tip['tip_image']) {
                            echo '<img src="../' . htmlspecialchars($tip['tip_image']) . '" alt="Health Tip Image" class="tip-image">';
                        }
                        
                        echo '<div class="tip-content">' . nl2br(htmlspecialchars($tip['content'])) . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-tips">';
                    echo '<p>No health tips available yet.</p>';
                    echo '<p>Please check back later for helpful health information!</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>