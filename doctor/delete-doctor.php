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
    

    
    include("../connection.php");
    $sqlmain= "select * from doctor where docemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s",$useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["docid"];
    $username=$userfetch["docname"];

    
    if($_GET){
        
        include("../connection.php");
        $id=$_GET["id"];
        
        // Verify that the doctor is trying to delete their own account
        if($id != $userid){
            header("location: settings.php");
            exit();
        }
        
        $sqlmain= "select * from doctor where docid=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result001 = $stmt->get_result();
        $email=($result001->fetch_assoc())["docemail"];

        // Delete from webuser table
        $sqlmain= "delete from webuser where email=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$email);
        $stmt->execute();

        // Delete from doctor table
        $sqlmain= "delete from doctor where docemail=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$email);
        $stmt->execute();

        
        header("location: ../logout.php");
    }


?>