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
        $email=$_POST['email'];
        $name=$_POST['name'];
        $password=isset($_POST['password']) ? $_POST['password'] : '';
        $cpassword=isset($_POST['cpassword']) ? $_POST['cpassword'] : '';
        $id=$_POST['id00'];
        $nic=$_POST['nic'];
        $tele=$_POST['Tele'];
        $spec=$_POST['spec'];
        $oldemail=$_POST['oldemail'];
        
        // Check if passwords match (when provided)
        if (!empty($password) && $password != $cpassword) {
            $error='2';
        } else {
            // Check for email uniqueness
            $stmt = $database->prepare("select doctor.docid from doctor inner join webuser on doctor.docemail=webuser.email where webuser.email=?;");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["docid"];
            }else{
                $id2=$id;
            }
            
            if($id2!=$id){
                $error='1';
            }else{
                // Update with or without password change
                if (!empty($password)) {
                    // Password provided: hash and update
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $database->prepare("update doctor set docemail=?,docname=?,docpassword=?,docnic=?,doctel=?,specialties=? where docid=?");
                    $stmt->bind_param("sssssii", $email, $name, $hashed_password, $nic, $tele, $spec, $id);
                } else {
                    // No password change (admin editing without password fields)
                    $stmt = $database->prepare("update doctor set docemail=?,docname=?,docnic=?,doctel=?,specialties=? where docid=?");
                    $stmt->bind_param("ssssii", $email, $name, $nic, $tele, $spec, $id);
                }
                $stmt->execute();
                $stmt->close();
                
                $stmt = $database->prepare("update webuser set email=? where email=?");
                $stmt->bind_param("ss", $email, $oldemail);
                $stmt->execute();
                $stmt->close();
                
                $error= '4';
            }
        }
    
    
        
    }else{
        
        $error='3';
    }
    
    header("location: doctors.php?action=edit&error=".$error."&id=".$id);
    ?>