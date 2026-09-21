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
    $userid= $userfetch["pid"];
    $username=$userfetch["pname"];

    if($_POST){
        if(isset($_POST["booknow"])){
            $apponum=$_POST["apponum"];
            $scheduleid=$_POST["scheduleid"];
            $date=$_POST["date"];
            $fee = $_POST["fee"];
            
            // Get doctor information for the email
            $sql_schedule = "SELECT s.*, d.docname FROM schedule s JOIN doctor d ON s.docid=d.docid WHERE s.scheduleid=?";
            $stmt_schedule = $database->prepare($sql_schedule);
            $stmt_schedule->bind_param("i", $scheduleid);
            $stmt_schedule->execute();
            $result_schedule = $stmt_schedule->get_result();
            $schedule = $result_schedule->fetch_assoc();
            $doctorName = $schedule["docname"];
            $scheduledate = $schedule["scheduledate"];
            $scheduletime = $schedule["scheduletime"];
            $stmt_schedule->close();
            
            $_SESSION["booking_details"] = array(
                "pid" => $userid,
                "apponum" => $apponum,
                "scheduleid" => $scheduleid,
                "appodate" => $date,
                "fee" => $fee
            );
            
            // Send appointment confirmation email
            require_once '../includes/email-utility.php';
            EmailUtility::sendAppointmentConfirmation($useremail, $username, $doctorName, $scheduledate, $scheduletime, $apponum);
            
            header("location: payment.php?scheduleid=".$scheduleid."&fee=".$fee);
        }
    }
 ?>

