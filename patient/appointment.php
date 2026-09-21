<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Appointments</title>
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
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }

}else{
    header("location: ../login.php");
}

include("../connection.php");

// Fetch patient info
$sqlmain= "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["pid"];
$username=$userfetch["pname"];

$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");
$unreadNotificationsQuery = $database->query("SELECT COUNT(*) AS unread_count FROM session_notifications WHERE patid = $userid AND status = 'pending'");
$unreadNotificationsCount = 0;
if ($unreadNotificationsQuery && $unreadNotificationsQuery->num_rows > 0) {
    $unreadNotificationRow = $unreadNotificationsQuery->fetch_assoc();
    $unreadNotificationsCount = (int)$unreadNotificationRow['unread_count'];
}

// Fetch appointments
$sqlmain= "select appointment.appoid,schedule.scheduleid,schedule.title,doctor.docname,patient.pname,
schedule.scheduledate,schedule.scheduletime,appointment.apponum,appointment.appodate 
from schedule 
inner join appointment on schedule.scheduleid=appointment.scheduleid 
inner join patient on patient.pid=appointment.pid 
inner join doctor on schedule.docid=doctor.docid  
where patient.pid=$userid ";

if($_POST){
    if(!empty($_POST["sheduledate"])){
        $sheduledate=$_POST["sheduledate"];
        $sqlmain.=" and schedule.scheduledate='$sheduledate' ";
    }
}

$sqlmain.=" order by appointment.appodate asc";
$result= $database->query($sqlmain);

date_default_timezone_set('Asia/Kolkata');
$now = new DateTime();

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
                <td class="menu-btn menu-icon-appoinment  menu-active menu-icon-appoinment-active">
                    <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">My Bookings</p></div></a>
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
                <td class="menu-btn menu-icon-settings">
                    <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></div></a>
                </td>
            </tr>
            <tr class="menu-row" >
                <td class="menu-btn menu-icon-heart">
                    <a href="../health-tips-public.php" class="non-style-link-menu"><div><p class="menu-text">Health Tips</p></div></a>
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
                    <p style="font-size: 23px;padding-left:12px;font-weight: 600;">My Bookings history</p>
                </td>
                <td width="15%">
                    <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                        Today's Date
                    </p>
                    <p class="heading-sub12" style="padding: 0;margin: 0;">
                        <?php echo date('Y-m-d'); ?>
                    </p>
                </td>
                <td width="10%">
                    <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="padding-top:10px;width: 100%;" >
                    <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">My Bookings (<?php echo $result->num_rows; ?>)</p>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="padding-top:0px;width: 100%;" >
                    <center>
                        <table class="filter-container" border="0" >
                        <tr>
                            <td width="10%"></td> 
                            <td width="5%" style="text-align: center;">Date:</td>
                            <td width="30%">
                                <form action="" method="post">
                                    <input type="date" name="sheduledate" id="date" class="input-text filter-container-items" style="margin: 0;width: 95%;">
                            </td>
                            <td width="12%">
                                <input type="submit"  name="filter" value=" Filter" class=" btn-primary-soft btn button-icon btn-filter"  style="padding: 15px; margin :0;width:100%">
                                </form>
                            </td>
                        </tr>
                        </table>
                    </center>
                </td>
            </tr>

            <tr>
               <td colspan="4">
                   <center>
                    <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0" style="border:none">
                            <tbody>
                            <?php
                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="7">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{
                                    for ( $x=0; $x<($result->num_rows);$x++){
                                        echo "<tr>";
                                        for($q=0;$q<3;$q++){
                                            $row=$result->fetch_assoc();
                                            if (!isset($row)) break;

                                            $scheduleid=$row["scheduleid"];
                                            $title=$row["title"];
                                            $docname=$row["docname"];
                                            $scheduledate=$row["scheduledate"];
                                            $scheduletime=$row["scheduletime"];
                                            $apponum=$row["apponum"];
                                            $appodate=$row["appodate"];
                                            $appoid=$row["appoid"];

                                            if($scheduleid=="") break;

                                            // Calculate time difference
                                            $appointment_datetime = new DateTime($scheduledate . ' ' . $scheduletime);
                                            $interval = $now->diff($appointment_datetime);
                                            if ($appointment_datetime > $now) {
                                                $time_diff = $interval->format('%a days, %h hours, %i minutes remaining');
                                                $is_disabled = '';
                                            } else {
                                                $time_diff = $interval->format('%a days, %h hours, %i minutes passed');
                                                $is_disabled = 'disabled style="opacity:0.5; cursor:not-allowed;"';
                                            }

                                            echo '
                                            <td style="width: 25%;">
                                                    <div  class="dashboard-items search-items"  >
                                                        <div style="width:100%;">
                                                            <div class="h3-search">
                                                                Booking Date: '.substr($appodate,0,30).'<br>
                                                                Reference Number: OC-000-'.$appoid.'
                                                            </div>
                                                            <div class="h1-search">
                                                                '.substr($title,0,21).'<br>
                                                            </div>
                                                            <div class="h3-search">
                                                                Appointment Number:<div class="h1-search">0'.$apponum.'</div>
                                                            </div>
                                                            <div class="h3-search">
                                                                '.substr($docname,0,30).'
                                                            </div>
                                                            <div class="h4-search">
                                                                Scheduled Date: '.$scheduledate.'<br>
                                                                Starts: <b>@'.substr($scheduletime,0,5).'</b> ('.$time_diff.')
                                                            </div>
                                                            <br>
                                                            <a href="?action=drop&id='.$appoid.'&title='.$title.'&doc='.$docname.'">
                                                                <button  class="login-btn btn-primary-soft btn " '.$is_disabled.'><font class="tn-in-text">Cancel Booking</font></button>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>';

                                        }
                                        echo "</tr>";
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

// Popup section remains unchanged
if($_GET){
    $id=$_GET["id"];
    $action=$_GET["action"];
    if($action=='booking-added'){
        echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                <br><br>
                    <h2>Booking Successfully.</h2>
                    <a class="close" href="appointment.php">&times;</a>
                    <div class="content">
                    Your Appointment number is '.$id.'.<br><br>
                        
                    </div>
                    <div style="display: flex;justify-content: center;">
                    
                    <a href="appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                    <br><br><br><br>
                    </div>
                </center>
        </div>
        </div>
        ';
    }elseif($action=='drop'){
        $title=$_GET["title"];
        $docname=$_GET["doc"];
        
        echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2>Are you sure?</h2>
                    <a class="close" href="appointment.php">&times;</a>
                    <div class="content">
                        You want to Cancel this Appointment?<br><br>
                        Session Name: &nbsp;<b>'.substr($title,0,40).'</b><br>
                        Doctor name&nbsp; : <b>'.substr($docname,0,40).'</b><br><br>
                        
                    </div>
                    <div style="display: flex;justify-content: center;">
                    <a href="delete-appointment.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                    <a href="appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                    </div>
                </center>
        </div>
        </div>
        '; 
    }elseif($action=='view'){
        $sqlmain= "select * from doctor where docid=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row=$result->fetch_assoc();
        $name=$row["docname"];
        $email=$row["docemail"];
        $spe=$row["specialties"];
        
        $sqlmain= "select sname from specialties where id=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$spe);
        $stmt->execute();
        $spcil_res = $stmt->get_result();
        $spcil_array= $spcil_res->fetch_assoc();
        $spcil_name=$spcil_array["sname"];
        $nic=$row['docnic'];
        $tele=$row['doctel'];
        echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2></h2>
                    <a class="close" href="doctors.php">&times;</a>
                    <div class="content">
                        HealthCarePlus<br>
                        
                    </div>
                    <div style="display: flex;justify-content: center;">
                    <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                    
                        <tr>
                            <td>
                                <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                            </td>
                        </tr>
                        
                        <tr>
                            
                            <td class="label-td" colspan="2">
                                <label for="name" class="form-label">Name: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                '.$name.'<br><br>
                            </td>
                            
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="Email" class="form-label">Email: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                            '.$email.'<br><br>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="nic" class="form-label">NIC: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                            '.$nic.'<br><br>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="Tele" class="form-label">Telephone: </label>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                            '.$tele.'<br><br>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td" colspan="2">
                                <label for="spec" class="form-label">Specialties: </label>
                                
                            </td>
                        </tr>
                        <tr>
                        <td class="label-td" colspan="2">
                        '.$spcil_name.'<br><br>
                        </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="doctors.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" >

                            </td>
            
                        </tr>

                    </table>
                    </div>
                </center>
                <br><br>
        </div>
        </div>
        ';  
    }
}

?>
</body>
</html>
