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
        $title=$_POST["title"];
        $docid=$_POST["docid"];
        $nop=$_POST["nop"];
        $date=$_POST["date"];
        $time=$_POST["time"];
        $fee = !empty($_POST["fee"]) ? $_POST["fee"] : "NULL";
        
        // Check if the doctor already has a session at the same time or overlapping time on the same date
        // Assuming standard appointment duration of 30 minutes for overlap detection
        $check_sql = "SELECT * FROM schedule WHERE docid = $docid AND scheduledate = '$date'";
        $check_result = $database->query($check_sql);
        
        // Convert the new appointment time to seconds for comparison
        $new_time_parts = explode(':', $time);
        $new_time_seconds = ($new_time_parts[0] * 3600) + ($new_time_parts[1] * 60) + ($new_time_parts[2] ?? 0);
        
        while($row = $check_result->fetch_assoc()) {
            $existing_time = $row['scheduletime'];
            $existing_time_parts = explode(':', $existing_time);
            $existing_time_seconds = ($existing_time_parts[0] * 3600) + ($existing_time_parts[1] * 60) + ($existing_time_parts[2] ?? 0);
            
            // Check if times overlap (assuming 30-minute appointments)
            $appointment_duration = 1800; // 30 minutes in seconds
            $new_end_time = $new_time_seconds + $appointment_duration;
            $existing_end_time = $existing_time_seconds + $appointment_duration;
            
            // Overlap occurs if: (new_start < existing_end) AND (existing_start < new_end)
            if (($new_time_seconds < $existing_end_time) && ($existing_time_seconds < $new_end_time)) {
                // If there's an overlapping session scheduled for the same doctor on the same date
                header("location: schedule.php?action=error&error=duplicate_session");
                exit();
            }
        }
        
        if ($fee == "NULL") {
            
            $sql="insert into schedule (docid,title,scheduledate,scheduletime,nop) values ($docid,'$title','$date','$time',$nop);";
        } else {
            
            $sql="insert into schedule (docid,title,scheduledate,scheduletime,nop,session_fee) values ($docid,'$title','$date','$time',$nop,$fee);";
        }
        
        $result= $database->query($sql);
        header("location: schedule.php?action=session-added&title=$title");
        
    }

?>