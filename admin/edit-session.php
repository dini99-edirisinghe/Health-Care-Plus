<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }

    if($_POST){
        
        include("../connection.php");
        include("../config.php"); 
        // Validate that required fields are present
        if (!isset($_POST["title"]) || !isset($_POST["docid"]) || !isset($_POST["nop"]) || 
            !isset($_POST["date"]) || !isset($_POST["time"]) || !isset($_POST["scheduleid"])) {
            header("location: schedule.php?action=error&error=missing_fields");
            exit();
        }
        
        $title = trim($_POST["title"]);
        $docid = (int)$_POST["docid"]; // Ensure it's an integer to prevent injection
        $nop = (int)$_POST["nop"]; // Ensure it's an integer
        $date = $_POST["date"];
        $time = $_POST["time"];
        $scheduleid = (int)$_POST["scheduleid"]; // Ensure it's an integer
        $fee_input = $_POST["fee"] ?? '';
        $fee = !empty($fee_input) ? floatval($fee_input) : null;
        
        // Check if the doctor already has a session at the same time on the same date (excluding the current session being edited)
        $check_sql = "SELECT * FROM schedule WHERE docid = ? AND scheduledate = ? AND scheduletime = ? AND scheduleid != ?";
        $check_stmt = $database->prepare($check_sql);
        $check_stmt->bind_param("issi", $docid, $date, $time, $scheduleid);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0){
            // If there's already a session scheduled at the same time for the same doctor on the same date
            header("location: schedule.php?action=error&error=duplicate_session");
            exit();
        }
        
        if ($fee === null) {
            $update_sql = "UPDATE schedule SET docid=?, title=?, scheduledate=?, scheduletime=?, nop=? WHERE scheduleid=?";
            $stmt = $database->prepare($update_sql);
            $stmt->bind_param("isssii", $docid, $title, $date, $time, $nop, $scheduleid);
        } else {
            $update_sql = "UPDATE schedule SET docid=?, title=?, scheduledate=?, scheduletime=?, nop=?, session_fee=? WHERE scheduleid=?";
            $stmt = $database->prepare($update_sql);
            $stmt->bind_param("isssidi", $docid, $title, $date, $time, $nop, $fee, $scheduleid);
        }
        
        if($stmt->execute()) {
            header("location: schedule.php?action=session-updated&title=" . urlencode($title));
        } else {
            header("location: schedule.php?action=error&error=update_failed");
        }
        exit();
        
    }

?>