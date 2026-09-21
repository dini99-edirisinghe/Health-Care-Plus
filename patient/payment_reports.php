<?php

session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }

}else{
    header("location: ../login.php");
}

include("../connection.php");

$sqlmain= "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();
$pid= $userfetch["pid"];
$pname=$userfetch["pname"];

$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");
$unreadNotificationsQuery = $database->query("SELECT COUNT(*) AS unread_count FROM session_notifications WHERE patid = $pid AND status = 'pending'");
$unreadNotificationsCount = 0;
if ($unreadNotificationsQuery && $unreadNotificationsQuery->num_rows > 0) {
    $unreadNotificationRow = $unreadNotificationsQuery->fetch_assoc();
    $unreadNotificationsCount = (int)$unreadNotificationRow['unread_count'];
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
        
    <title>My Payment Reports</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .payment-status-completed {
            color: green;
            font-weight: bold;
        }
        .payment-status-pending {
            color: orange;
            font-weight: bold;
        }
        .payment-status-failed {
            color: red;
            font-weight: bold;
        }
        .payment-status-refunded {
            color: blue;
            font-weight: bold;
        }
        .abc.scroll {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #2196F3 #f1f1f1;
        }
        .abc.scroll::-webkit-scrollbar {
            width: 8px;
        }
        .abc.scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .abc.scroll::-webkit-scrollbar-thumb {
            background: #2196F3;
            border-radius: 4px;
        }
        .abc.scroll::-webkit-scrollbar-thumb:hover {
            background: #1976D2;
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
                                    <p class="profile-title"><?php echo substr($pname,0,13)  ?>..</p>
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
                    <td class="menu-btn menu-icon-home" >
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Home</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></div></a>
                    </td>
                </tr>
                
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-feedback">
                        <a href="feedback.php" class="non-style-link-menu"><div><p class="menu-text">Feedback</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report">
                        <a href="reports.php" class="non-style-link-menu"><div><p class="menu-text">My Reports</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-payment menu-active menu-icon-payment-active">
                        <a href="payment_reports.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">My Payments</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-chatbot">
                        <a href="chatbot.php" class="non-style-link-menu"><div><p class="menu-text">Medical Assistant</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-heart">
                        <a href="../health-tips-public.php" class="non-style-link-menu"><div><p class="menu-text">Health Tips</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-notification">
                        <a href="notifications.php" class="non-style-link-menu notification-link"><div><p class="menu-text">Notifications</p><?php if($unreadNotificationsCount > 0): ?><span class="notification-dot"></span><?php endif; ?></div></a>
                    </td>
                </tr>
            </table>
        </div>
        <?php 
        $selecttype="My Payment Reports";
        $current = "My Payment Reports";
        ?>

        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="index.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">My Payment Reports</p>
                                           
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
                
                <tr>
                    <td colspan="4" style="padding-top:10px;width: 100%;" >
                        
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">My Payment History (<?php 
                        $sql_payments = "SELECT COUNT(*) as count FROM payments WHERE patient_id = ?";
                        $stmt_payments = $database->prepare($sql_payments);
                        $stmt_payments->bind_param("i", $pid);
                        $stmt_payments->execute();
                        $count_result = $stmt_payments->get_result();
                        $count_row = $count_result->fetch_assoc();
                        echo $count_row['count'];
                        ?>)</p>
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
                                        Payment ID
                                    </th>
                                    <th class="table-headin">
                                        Appointment ID
                                    </th>
                                    <th class="table-headin">
                                        Amount
                                    </th>
                                    <th class="table-headin">
                                        Payment Method
                                    </th>
                                    <th class="table-headin">
                                        Transaction ID
                                    </th>
                                    <th class="table-headin">
                                        Status
                                    </th>
                                    <th class="table-headin">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            
                                <?php
                                
                                $sql_payments = "SELECT * FROM payments WHERE patient_id = ? ORDER BY payment_date DESC";
                                $stmt_payments = $database->prepare($sql_payments);
                                $stmt_payments->bind_param("i", $pid);
                                $stmt_payments->execute();
                                $payments_result = $stmt_payments->get_result();

                                if($payments_result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="7">
                                    <br><br>
                                    <center>
                                    <p class="heading-main12" >No payment records found.</p>
                                    </center>
                                    <br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{

                                    while($payment = $payments_result->fetch_assoc()){
                                        $payment_id = $payment["payment_id"];
                                        $appointment_id = $payment["appointment_id"];
                                        $amount = $payment["amount"];
                                        $payment_method = $payment["payment_method"];
                                        $transaction_id = $payment["transaction_id"];
                                        $payment_status = $payment["payment_status"];
                                        $payment_date = date("M j, Y g:i A", strtotime($payment["payment_date"]));
                                        
                                        echo '<tr >
                                            <td>
                                            #'.$payment_id.'
                                            </td>
                                            <td>
                                            '.($appointment_id ? $appointment_id : 'N/A').'
                                            </td>
                                            <td>
                                            LKR '.number_format($amount, 2).'
                                            </td>
                                            <td>
                                            '.($payment_method ? $payment_method : 'N/A').'
                                            </td>
                                            <td>
                                            '.($transaction_id ? $transaction_id : 'N/A').'
                                            </td>
                                            <td>
                                            <span class="payment-status-'.$payment_status.'">'.ucfirst($payment_status).'</span>
                                            </td>
                                            <td>
                                            '.$payment_date.'
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
    </div>
</body>
</html>