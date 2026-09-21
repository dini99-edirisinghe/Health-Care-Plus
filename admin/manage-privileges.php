<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Manage Privileges</title>
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

    include("../connection.php");

    if ($_POST) {
        if (isset($_POST['assign_privilege'])) {
            $docid = $_POST['docid'];
            $privilege_name = $_POST['privilege_name'];
            $permission_level = $_POST['permission_level'];

            $sql = "INSERT INTO doctor_privileges (docid, privilege_name, permission_level) VALUES (?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("iss", $docid, $privilege_name, $permission_level);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete_privilege'])) {
            $privilege_id = $_POST['privilege_id'];

            $sql = "DELETE FROM doctor_privileges WHERE privilege_id = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $privilege_id);
            $stmt->execute();
            $stmt->close();
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
                    <td class="menu-btn menu-icon-doctor menu-active menu-icon-doctor-active">
                        <a href="doctors.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Doctors</p></a></div>
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
                    <td class="menu-btn menu-icon-payment">
                        <a href="payment_reports.php" class="non-style-link-menu"><div><p class="menu-text">Payment Reports</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="doctors.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Manage Doctor Privileges</p>
                                           
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
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Assign New Privilege</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;">
                        <center>
                        <form method="post" action="">
                            <table width="90%" class="sub-table scrolldown add-doc-form-container" border="0">
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="docid" class="form-label">Select Doctor: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <select name="docid" class="box" required>
                                            <option value="" disabled selected hidden>Choose Doctor</option>
                                            <?php
                                            $doctors = $database->query("SELECT * FROM doctor ORDER BY docname ASC");
                                            while($doctor = $doctors->fetch_assoc()) {
                                                echo "<option value='".$doctor['docid']."'>".$doctor['docname']." (".$doctor['docemail'].")</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="privilege_name" class="form-label">Privilege Name: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="text" name="privilege_name" class="input-text" placeholder="e.g., View Reports, Manage Patients" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="permission_level" class="form-label">Permission Level: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <select name="permission_level" class="box" required>
                                            <option value="read">Read Only</option>
                                            <option value="write">Read/Write</option>
                                            <option value="admin">Administrator</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="submit" name="assign_privilege" value="Assign Privilege" class="login-btn btn-primary btn">
                                    </td>
                                </tr>
                            </table>
                        </form>
                        </center>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4" style="padding-top:30px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">Existing Privileges</p>
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
                                    Doctor Name
                                </th>
                                <th class="table-headin">
                                    Privilege
                                </th>
                                <th class="table-headin">
                                    Permission Level
                                </th>
                                <th class="table-headin">
                                    Actions
                                </th>
                        </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                            $sqlmain = "SELECT dp.*, d.docname, d.docemail FROM doctor_privileges dp JOIN doctor d ON dp.docid = d.docid ORDER BY d.docname";
                            $result = $database->query($sqlmain);

                            if($result->num_rows==0){
                                echo '<tr>
                                <td colspan="4">
                                <br><br><br><br>
                                <center>
                                <img src="../img/notfound.svg" width="25%">
                                
                                <br>
                                <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No privileges found!</p>
                                </center>
                                <br><br><br><br>
                                </td>
                                </tr>';
                                
                            }
                            else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $privilege_id=$row["privilege_id"];
                                    $docname=$row["docname"];
                                    $docemail=$row["docemail"];
                                    $privilege_name=$row["privilege_name"];
                                    $permission_level=$row["permission_level"];
                                    
                                    echo '<tr>
                                        <td>
                                        '.substr($docname,0,30).'<br>
                                        <small>'.$docemail.'</small>
                                        </td>
                                        <td>
                                        '.substr($privilege_name,0,30).'
                                        </td>
                                        <td>
                                        '.ucfirst($permission_level).'
                                        </td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                        <form method="post" action="">
                                            <input type="hidden" name="privilege_id" value="'.$privilege_id.'">
                                            <input type="submit" name="delete_privilege" value="Remove" class="login-btn btn-primary-soft btn button-icon btn-delete">
                                        </form>
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

