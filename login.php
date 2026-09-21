<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/font-inter.css">
    <link rel="stylesheet" href="css/enhanced-login.css">
        
    <title>Login</title>
</head>
<body>
    <!-- Back to Home Button -->
    <a href="index.php" class="home-button">← Back to Home</a>
    
    <?php
    
    

    session_start();

    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    
    
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"]=$date;
    
    
    include("connection.php");

    
    if($_POST){

        $email=$_POST['useremail'];
        $password=$_POST['userpassword'];
        
        $error='<label for="promter" class="form-label"></label>';
        $success='';

        // Get usertype from webuser table
        $stmt = $database->prepare("SELECT usertype FROM webuser WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows==1){
            $utype = $result->fetch_assoc()['usertype'];
            $stmt->close();
            
            if ($utype == 'p') {
                // Patient login (supports both hashed and plain text passwords)
                $stmt = $database->prepare("SELECT * FROM patient WHERE pemail=?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                
                if ($checker->num_rows==1){
                    $patient = $checker->fetch_assoc();
                    $stored_password = $patient['ppassword'];
                    $password_valid = false;
                    
                    // Check if password is hashed (bcrypt hashes start with $2y$)
                    if (substr($stored_password, 0, 4) === '$2y$') {
                        $password_valid = password_verify($password, $stored_password);
                    } else {
                        // Plain text password - direct comparison (backward compatibility)
                        $password_valid = ($stored_password == $password);
                        
                        // Auto-upgrade to hashed password on successful login
                        if ($password_valid) {
                            $new_hash = password_hash($password, PASSWORD_DEFAULT);
                            $update_stmt = $database->prepare("UPDATE patient SET ppassword=? WHERE pid=?");
                            $update_stmt->bind_param("si", $new_hash, $patient['pid']);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                    }
                    
                    if ($password_valid) {
                        $_SESSION['user']=$email;
                        $_SESSION['usertype']='p';
                        echo '<script>alert("Login Successful!"); window.location.href = "patient/index.php";</script>';
                    } else {
                        $error='<label for="promter" class="error-message">Wrong credentials: Invalid email or password</label>';
                    }
                } else {
                    $error='<label for="promter" class="error-message">Wrong credentials: Invalid email or password</label>';
                }
                $stmt->close();
                
            } elseif ($utype == 'a') {
                // Admin login (supports both hashed and plain text passwords)
                $stmt = $database->prepare("SELECT * FROM admin WHERE aemail=?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                
                if ($checker->num_rows==1){
                    $admin = $checker->fetch_assoc();
                    $stored_password = $admin['apassword'];
                    $password_valid = false;
                    
                    // Check if password is hashed (bcrypt hashes start with $2y$)
                    if (substr($stored_password, 0, 4) === '$2y$') {
                        $password_valid = password_verify($password, $stored_password);
                    } else {
                        // Plain text password - direct comparison (backward compatibility)
                        $password_valid = ($stored_password == $password);
                        
                        // Auto-upgrade to hashed password on successful login
                        if ($password_valid) {
                            $new_hash = password_hash($password, PASSWORD_DEFAULT);
                            $update_stmt = $database->prepare("UPDATE admin SET apassword=? WHERE aemail=?");
                            $update_stmt->bind_param("ss", $new_hash, $email);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                    }
                    
                    if ($password_valid) {
                        $_SESSION['user']=$email;
                        $_SESSION['usertype']='a';
                        echo '<script>alert("Login Successful!"); window.location.href = "admin/index.php";</script>';
                    } else {
                        $error='<label for="promter" class="error-message">Wrong credentials: Invalid email or password</label>';
                    }
                } else {
                    $error='<label for="promter" class="error-message">Wrong credentials: Invalid email or password</label>';
                }
                $stmt->close();
                
            } elseif ($utype == 'd') {
                // Doctor login (supports both hashed and plain text passwords)
                $stmt = $database->prepare("SELECT * FROM doctor WHERE docemail=?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                
                if ($checker->num_rows==1){
                    $doctor = $checker->fetch_assoc();
                    $stored_password = $doctor['docpassword'];
                    $password_valid = false;
                    
                    // Check if password is hashed (bcrypt hashes start with $2y$)
                    if (substr($stored_password, 0, 4) === '$2y$') {
                        // Hashed password - use password_verify
                        $password_valid = password_verify($password, $stored_password);
                    } else {
                        // Plain text password - direct comparison (backward compatibility)
                        $password_valid = ($stored_password == $password);
                        
                        // Auto-upgrade to hashed password on successful login
                        if ($password_valid) {
                            $new_hash = password_hash($password, PASSWORD_DEFAULT);
                            $update_stmt = $database->prepare("UPDATE doctor SET docpassword=? WHERE docid=?");
                            $update_stmt->bind_param("si", $new_hash, $doctor['docid']);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                    }
                    
                    if ($password_valid) {
                        $_SESSION['user']=$email;
                        $_SESSION['usertype']='d';
                        echo '<script>alert("Login Successful!"); window.location.href = "doctor/index.php";</script>';
                    } else {
                        $error='<label for="promter" class="error-message">Wrong credentials: Invalid email or password</label>';
                    }
                } else {
                    $error='<label for="promter" class="error-message">Wrong credentials: Invalid email or password</label>';
                }
                $stmt->close();
            }
        } else {
            $stmt->close();
            $error='<label for="promter" class="error-message">We cant found any account for this email.</label>';
        }
    }else{
        $error='<label for="promter" class="error-message">&nbsp;</label>';
    }
    ?>

    <div class="login-container">
        <h1 class="welcome-text">Welcome Back!</h1>
        <p class="sub-text-login">Login with your details to continue</p>
        
        <form action="" method="POST">
            <div class="label-td">
                <label for="useremail" class="form-label">Email:</label>
                <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
            </div>
            
            <div class="label-td">
                <label for="userpassword" class="form-label">Password:</label>
                <input type="password" name="userpassword" class="input-text" placeholder="Password" required>
            </div>
            
            <div><?php echo $error ?></div>
            
            <input type="submit" value="Login" class="login-btn">
            
            <div class="signup-link">
                <span class="sub-text">Don't have an account? </span>
                <a href="signup.php" class="hover-link1">Sign Up</a>
            </div>
        </form>
    </div>
</body>
</html>