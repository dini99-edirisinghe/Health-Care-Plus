<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/font-inter.css">
    <link rel="stylesheet" href="css/enhanced-login.css">
        
    <title>Sign Up</title>
</head>
<body>
<?php

session_start();

$_SESSION["user"]="";
$_SESSION["usertype"]="";

date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$_SESSION["date"]=$date;

$error = '';
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); 
}

// Handle form submissions
if($_POST){
    include("connection.php");
    
    // Check which step we're on
    if(isset($_POST['step']) && $_POST['step'] == 'personal_details'){
        // Process personal details
        $nic = $_POST['nic'];
        $check_nic_query = "SELECT pnic FROM patient WHERE pnic=?";
        $stmt = $database->prepare($check_nic_query);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "NIC already registered!";
            $_SESSION["personal"]=array(
                'fname'=>$_POST['fname'],
                'lname'=>$_POST['lname'],
                'address'=>$_POST['address'],
                'nic'=>$_POST['nic'],
                'dob'=>$_POST['dob']
            );
            header("location: unified-signup.php");
            exit();
        } else {
            $_SESSION["personal"]=array(
                'fname'=>$_POST['fname'],
                'lname'=>$_POST['lname'],
                'address'=>$_POST['address'],
                'nic'=>$_POST['nic'],
                'dob'=>$_POST['dob']
            );
            // Show account creation form
            $show_account_form = true;
        }
    } elseif(isset($_POST['step']) && $_POST['step'] == 'account_creation'){
        // Process account creation
        $fname = isset($_SESSION["personal"]["fname"]) ? $_SESSION["personal"]["fname"] : "";
        $lname = isset($_SESSION["personal"]["lname"]) ? $_SESSION["personal"]["lname"] : "";
        $address = isset($_SESSION["personal"]["address"]) ? $_SESSION["personal"]["address"] : "";
        $nic = isset($_SESSION["personal"]["nic"]) ? $_SESSION["personal"]["nic"] : "";
        $dob = isset($_SESSION["personal"]["dob"]) ? $_SESSION["personal"]["dob"] : "";
        
        $email = isset($_POST['newemail']) ? $_POST['newemail'] : "";
        $tele = isset($_POST['tele']) ? $_POST['tele'] : "";
        $password = isset($_POST['newpassword']) ? $_POST['newpassword'] : "";
        $cpassword = isset($_POST['cpassword']) ? $_POST['cpassword'] : "";
        
        if ($password !== $cpassword) {
            $_SESSION['error'] = "Passwords do not match!";
            header("location: unified-signup.php");
            exit();
        }
        
        $check_email_query = "SELECT pemail FROM patient WHERE pemail=?";
        $stmt = $database->prepare($check_email_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email already registered!";
            header("location: unified-signup.php");
            exit();
        }
        
        $check_webuser_query = "SELECT email FROM webuser WHERE email=?";
        $stmt = $database->prepare($check_webuser_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email already registered in system!";
            header("location: unified-signup.php");
            exit();
        }
        
        $check_nic_query = "SELECT pnic FROM patient WHERE pnic=?";
        $stmt = $database->prepare($check_nic_query);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "NIC already registered!";
            header("location: unified-signup.php");
            exit();
        }
        
        $check_phone_query = "SELECT ptel FROM patient WHERE ptel=?";
        $stmt = $database->prepare($check_phone_query);
        $stmt->bind_param("s", $tele);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Phone number already registered!";
            header("location: unified-signup.php");
            exit();
        }
        
        $full_name = $fname . " " . $lname;
        // Hash the password for secure storage
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($insert_query);
        $stmt->bind_param("sssssss", $email, $full_name, $hashed_password, $address, $nic, $dob, $tele);
        
        if ($stmt->execute()) {
            $insert_webuser_query = "INSERT INTO webuser (email, usertype) VALUES (?, 'p')";
            $stmt2 = $database->prepare($insert_webuser_query);
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $stmt2->close();
            
            $stmt->close();
            
            unset($_SESSION["personal"]);
            
            $_SESSION['user'] = $email;
            $_SESSION['usertype'] = 'p';
            
            // Send welcome email
            require_once 'includes/email-utility.php';
            $emailSent = EmailUtility::sendWelcomeEmail($email, $full_name);
            if (!$emailSent) {
                error_log("Mailjet welcome email failed for patient: {$email}");
            }
            
            header('location: patient/index.php');
            exit();
        } else {
            $stmt->close();
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("location: unified-signup.php");
            exit();
        }
    }
} else {
    // Show personal details form by default
    $show_personal_form = true;
}
?>
    <!-- Back to Home Button -->
    <a href="index.php" class="home-button">← Back to Home</a>
    
    <div class="login-container">
        <?php if(!isset($show_account_form)): ?>
        <!-- Personal Details Form -->
        <h1 class="welcome-text">Let's Get Started</h1>
        <p class="sub-text-login">Add Your Personal Details to Continue</p>
        <p class="sub-text-login" style="font-size: 14px; margin-top: -10px;">This signup is for patients. Doctors should contact admin.</p>
        
        <?php if ($error != ''): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" >
            <input type="hidden" name="step" value="personal_details">
            <div class="label-td">
                <label for="name" class="form-label">Name: </label>
            </div>
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <input type="text" name="fname" class="input-text" placeholder="First Name" required style="flex: 1;" 
                    value="<?php echo isset($_SESSION['personal']['fname']) ? htmlspecialchars($_SESSION['personal']['fname']) : ''; ?>">
                <input type="text" name="lname" class="input-text" placeholder="Last Name" required style="flex: 1;" 
                    value="<?php echo isset($_SESSION['personal']['lname']) ? htmlspecialchars($_SESSION['personal']['lname']) : ''; ?>">
            </div>
            <div class="label-td">
                <label for="address" class="form-label">Address: </label>
                <input type="text" name="address" class="input-text" placeholder="Address" required 
                    value="<?php echo isset($_SESSION['personal']['address']) ? htmlspecialchars($_SESSION['personal']['address']) : ''; ?>">
            </div>
            
            <div class="label-td">
                <label for="nic" class="form-label">NIC: </label>
                <input type="text" name="nic" class="input-text" placeholder="NIC Number" required 
                    value="<?php echo isset($_SESSION['personal']['nic']) ? htmlspecialchars($_SESSION['personal']['nic']) : ''; ?>">
            </div>
            
            <div class="label-td">
                <label for="dob" class="form-label">Date of Birth: </label>
                <input type="date" name="dob" class="input-text" required 
                    value="<?php echo isset($_SESSION['personal']['dob']) ? htmlspecialchars($_SESSION['personal']['dob']) : ''; ?>">
            </div>
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <input type="reset" value="Reset" class="login-btn" style="background: linear-gradient(135deg, #D8EBFA 0%, #b8d6f0 100%); color: #1b62b3; box-shadow: 0 4px 12px rgba(216, 235, 250, 0.3);">
                <input type="submit" value="Next" class="login-btn">
            </div>
            
            <div class="signup-link">
                <span class="sub-text">Already have an account? </span>
                <a href="login.php" class="hover-link1">Login</a>
            </div>
        </form>
        <?php else: ?>
        <!-- Account Creation Form -->
        <h1 class="welcome-text">Create Account</h1>
        <p class="sub-text-login">It's Okay, Now Create Your User Account.</p>
        <p class="sub-text-login" style="font-size: 14px; margin-top: -10px;">This account creation is for patients.</p>
        
        <form action="" method="POST" >
            <input type="hidden" name="step" value="account_creation">
            <div class="label-td">
                <label for="newemail" class="form-label">Email: </label>
                <input type="email" name="newemail" class="input-text" placeholder="Email Address" required 
                    value="<?php echo isset($_SESSION['personal']['email']) ? htmlspecialchars($_SESSION['personal']['email']) : ''; ?>">
            </div>
            
            <div class="label-td">
                <label for="tele" class="form-label">Mobile Number: </label>
                <input type="tel" name="tele" class="input-text"  placeholder="ex: 0712345678" pattern="[0]{1}[0-9]{9}" 
                    value="<?php echo isset($_SESSION['personal']['tele']) ? htmlspecialchars($_SESSION['personal']['tele']) : ''; ?>">
            </div>
            
            <div class="label-td">
                <label for="newpassword" class="form-label">Create New Password: </label>
                <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
            </div>
            
            <div class="label-td">
                <label for="cpassword" class="form-label">Confirm Password: </label>
                <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" required>
            </div>
     
            <?php if ($error != ''): ?>
            <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <input type="reset" value="Reset" class="login-btn" style="background: linear-gradient(135deg, #D8EBFA 0%, #b8d6f0 100%); color: #1b62b3; box-shadow: 0 4px 12px rgba(216, 235, 250, 0.3);">
                <input type="submit" value="Sign Up" class="login-btn">
            </div>
            
            <div class="signup-link">
                <span class="sub-text">Already have an account? </span>
                <a href="login.php" class="hover-link1">Login</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>