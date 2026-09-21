<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctor</title>
    <style>
        .popup{
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

    if($_POST){
        
        include("../connection.php");
        $result= $database->query("select * from webuser");
        $name=$_POST['name'];
        $nic=$_POST['nic'];
        $spec=$_POST['spec'];
        $email=$_POST['email'];
        $tele=$_POST['Tele'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];

        
        if ($password==$cpassword){
            $error='3';
            // Use prepared statement for email uniqueness check
            $stmt = $database->prepare("select * from webuser where email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows==1){
                $error='1';
            }else{
                // Hash the password for secure storage
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Use prepared statement for secure insertion
                $stmt = $database->prepare("insert into doctor(docemail,docname,docpassword,docnic,doctel,specialties) values(?,?,?,?,?,?)");
                $stmt->bind_param("sssssi", $email, $name, $hashed_password, $nic, $tele, $spec);
                $stmt->execute();
                $stmt->close();
                
                // Use prepared statement for webuser insertion
                $stmt = $database->prepare("insert into webuser values(?,'d')");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();
                
                // Send welcome email with login credentials (plain text password)
                require_once('../includes/email-utility.php');
                $emailSent = EmailUtility::sendDoctorWelcomeEmail($email, $name, $password);
                if (!$emailSent) {
                    error_log("Mailjet doctor welcome email failed for doctor: {$email}");
                }
                
                $error= '4';
                
            }
            
        }else{
            $error='2';
        }

    }else{
        
        $error='3';
    }
    
    header("location: doctors.php?action=add&error=".$error);
    ?>

</body>
</html>

