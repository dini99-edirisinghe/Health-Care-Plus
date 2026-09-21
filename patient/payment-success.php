<?php
session_start();

if(isset($_SESSION["user"])) {
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p') {
        header("location: ../login.php");
    } else {
        $useremail=$_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}

include("../connection.php");
include("../config.php"); // Include config to get HOSPITAL_SERVICE_FEE constant

$apponum = $_GET['apponum'] ?? '';
$scheduleid = $_GET['scheduleid'] ?? '';

$sql = "SELECT s.*, d.docname, d.docemail FROM schedule s JOIN doctor d ON s.docid=d.docid WHERE s.scheduleid=?";
$stmt = $database->prepare($sql);
$stmt->bind_param("i", $scheduleid);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();
$stmt->close();

$sql_patient = "SELECT * FROM patient WHERE pemail=?";
$stmt_patient = $database->prepare($sql_patient);
$stmt_patient->bind_param("s", $useremail);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
$patient = $result_patient->fetch_assoc();
$stmt_patient->close();

// Calculate fee including hospital service fee
$base_fee = $schedule['session_fee'] ?? $schedule['consultation_fee'];
$fee = $base_fee + HOSPITAL_SERVICE_FEE;

// Send payment receipt email (without attachment)
require_once '../includes/email-utility.php';
EmailUtility::sendPaymentReceipt($useremail, $patient['pname'], $apponum, $schedule['docname'], $schedule['scheduledate'], $fee);

$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS target_url VARCHAR(255) NULL");
$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");

// Notify doctor about payment
$doctor_id = (int)$schedule['docid'];
$notification_message = $database->real_escape_string("Payment received for appointment #$apponum. Please review the booking.");
$target_url = $database->real_escape_string("appointment.php");
$database->query("INSERT INTO session_notifications (scheduleid, docid, message, notify_date, status, target_url) VALUES ({$schedule['scheduleid']}, $doctor_id, '$notification_message', NOW(), 'pending', '$target_url')");

// Notify patient about payment confirmation
$patient_id = (int)$patient['pid'];
$patient_notification_message = $database->real_escape_string("Payment confirmed for appointment #$apponum with Dr. " . $schedule['docname'] . " on " . $schedule['scheduledate']);
$patient_notification_url = $database->real_escape_string("appointment.php");
$database->query("INSERT INTO session_notifications (scheduleid, patid, message, notify_date, status, target_url) VALUES ({$schedule['scheduleid']}, $patient_id, '$patient_notification_message', NOW(), 'seen', '$patient_notification_url')");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/patient.css">
        
    <title>Payment Success</title>
    <style>
        .success-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .success-title {
            font-size: 2.5rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .success-message {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .appointment-details {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        
        .detail-value {
            font-weight: 500;
            color: #000;
        }
        
        .actions {
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #000000;
            color: white;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-download {
            background: #4CAF50;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>

    <script>
        // Show success message at the top and make it disappear after 3 seconds
        window.onload = function() {
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'block';
            
            // Hide the message after 3 seconds
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 3000);
        };
    </script>
</head>
<body>
    
    <!-- Success Message at the top of the page -->
    <div id="successMessage" class="success-message-top" style="display: none; background: #d4edda; color: #155724; padding: 15px; text-align: center; font-size: 1.2rem; border: 1px solid #c3e6cb; margin: 20px; border-radius: 5px;">
        Appointment booked successfully
    </div>
    
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <?php if(isset($userfetch["profile_picture"]) && $userfetch["profile_picture"]): ?>
                                        <img src="../uploads/profile_pictures/<?php echo htmlspecialchars($userfetch["profile_picture"]); ?>" alt="" width="100%" style="border-radius:50%">
                                    <?php else: ?>
                                        <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                    <?php endif; ?>
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($patient['pname'],0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
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
                    <td class="menu-btn menu-icon-home " >
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-payment">
                        <a href="payment_reports.php" class="non-style-link-menu"><div><p class="menu-text">My Payments</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr>
                    <td colspan="4">
                        <div class="success-container">
                            <div class="success-icon">✓</div>
                            <h1 class="success-title">Payment Successful!</h1>
                            <p class="success-message">
                                Your payment has been processed successfully and your appointment has been confirmed.<br>
                                Thank you for choosing our healthcare services.
                            </p>
                            
                            <div class="appointment-details">
                                <h2>Appointment Details</h2>
                                <div class="detail-row">
                                    <span class="detail-label">Doctor:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($schedule['docname']);?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Session:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($schedule['title']);?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Date & Time:</span>
                                    <span class="detail-value">
                                        <?php echo date("M j, Y", strtotime($schedule['scheduledate']));?> at 
                                        <?php echo date("g:i A", strtotime($schedule['scheduletime']));?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Appointment Number:</span>
                                    <span class="detail-value">#<?php echo htmlspecialchars($apponum);?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Total Fee:</span>
                                    <span class="detail-value">LKR <?php echo number_format($fee, 2);?></span>
                                </div>
                            </div>
                            
                            <div class="actions">
                                <a href="appointment.php" class="btn btn-primary">View My Appointments</a>
                                <a href="index.php" class="btn btn-secondary">Back to Home</a>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>