<?php
session_start();


if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];


include("../connection.php");


$userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
$userfetch = $userrow->fetch_assoc();

if (!$userfetch) {
    header("location: ../login.php");
    exit();
}

$userid = $userfetch["docid"];
$username = $userfetch["docname"];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/profile_pictures/";
    
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");
    
    
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false && in_array($file_extension, $allowed_extensions)) {
        
        $new_filename = "doctor_" . $userid . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            
            $sql_update = "UPDATE doctor SET profile_picture=? WHERE docid=?";
            $stmt_update = $database->prepare($sql_update);
            $stmt_update->bind_param("si", $new_filename, $userid);
            
            if ($stmt_update->execute()) {
                $upload_message = "Profile picture updated successfully!";
                $upload_status = "success";
            } else {
                $upload_message = "Error updating profile picture in database.";
                $upload_status = "error";
            }
            $stmt_update->close();
        } else {
            $upload_message = "Error uploading file.";
            $upload_status = "error";
        }
    } else {
        $upload_message = "Invalid file type. Please upload a valid image file (JPG, JPEG, PNG, GIF).";
        $upload_status = "error";
    }
}


$current_picture = isset($userfetch["profile_picture"]) ? $userfetch["profile_picture"] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Upload Profile Picture</title>
    <style>
        .profile-picture-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .current-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3b82f6;
            margin: 0 auto 20px;
        }
        
        .upload-form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin: 20px 0;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-button {
            display: inline-block;
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .file-input-button:hover {
            background: #2563eb;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .upload-button {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        
        .upload-button:hover {
            background: #059669;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #64748b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .back-button:hover {
            background: #475569;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px">
                                    <?php if ($current_picture): ?>
                                        <img src="../uploads/profile_pictures/<?php echo htmlspecialchars($current_picture); ?>" alt="Profile Picture" width="100%" style="border-radius:50%">
                                    <?php else: ?>
                                        <img src="../img/user.png" alt="Profile Picture" width="100%" style="border-radius:50%">
                                    <?php endif; ?>
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username, 0, 13); ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail, 0, 22); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-home">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">My Patients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings menu-active menu-icon-settings-active">
                        <a href="settings.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;">
                <tr>
                    <td width="13%">
                        <a href="settings.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Upload Profile Picture</p>
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
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4">
                        <div class="profile-picture-container">
                            <h2>Your Profile Picture</h2>
                            <?php if ($current_picture): ?>
                                <img src="../uploads/profile_pictures/<?php echo htmlspecialchars($current_picture); ?>" alt="Current Profile Picture" class="current-picture">
                                <p>Current profile picture</p>
                            <?php else: ?>
                                <img src="../img/user.png" alt="Default Profile Picture" class="current-picture">
                                <p>No profile picture uploaded yet</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="upload-form">
                            <h3>Upload New Profile Picture</h3>
                            <p style="color: #666; margin-bottom: 20px;">Select an image file (JPG, JPEG, PNG, GIF) to upload as your profile picture.</p>
                            
                            <?php if (isset($upload_message)): ?>
                                <div class="message <?php echo $upload_status; ?>">
                                    <?php echo $upload_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="file-input-wrapper">
                                    <div class="file-input-button">
                                        Choose File
                                    </div>
                                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
                                </div>
                                <div class="file-name" id="file-name">No file chosen</div>
                                
                                <button type="submit" class="upload-button">Upload Profile Picture</button>
                            </form>
                            

                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <script>
        
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>