<?php
session_start();

if(isset($_SESSION["user"])){
    if($_SESSION["user"]=="" || $_SESSION['usertype']!='p'){
        header("location: ../login.php");
        exit();
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
    exit();
}

include("../connection.php");
include("../config.php");

if(isset($_SESSION["booking_details"])) {
    $booking    = $_SESSION["booking_details"];
    $scheduleid = $booking["scheduleid"];
    $total_fee  = $booking["fee"];
    $apponum    = $booking["apponum"];
}
else if(isset($_GET['scheduleid']) && isset($_GET['fee'])) {
    $scheduleid = $_GET['scheduleid'];
    $total_fee  = $_GET['fee'];

    $sql2 = "SELECT COUNT(*) AS total FROM appointment WHERE scheduleid=?";
    $stmt2 = $database->prepare($sql2);
    $stmt2->bind_param("i", $scheduleid);
    $stmt2->execute();
    $result12 = $stmt2->get_result()->fetch_assoc();
    $apponum = $result12['total'] + 1;
    $stmt2->close();
}
else{
    header("location: index.php");
    exit();
}


$sql = "SELECT s.*, d.docname, d.docemail
        FROM schedule s
        JOIN doctor d ON s.docid = d.docid
        WHERE s.scheduleid = ?";
$stmt = $database->prepare($sql);
$stmt->bind_param("i", $scheduleid);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();
$stmt->close();

if(!$schedule){
    header("location: schedule.php");
    exit();
}

$consultation_fee     = $schedule["session_fee"];
$hospital_service_fee = HOSPITAL_SERVICE_FEE;

/* Patient */
$sql_patient = "SELECT * FROM patient WHERE pemail=?";
$stmt_patient = $database->prepare($sql_patient);
$stmt_patient->bind_param("s", $useremail);
$stmt_patient->execute();
$patient = $stmt_patient->get_result()->fetch_assoc();
$stmt_patient->close();

$error_message = "";

/* FORM SUBMIT */
if($_POST){

    $card_number  = $_POST['card_number'] ?? '';
    $expiry_month = $_POST['expiry_month'] ?? '';
    $expiry_year  = $_POST['expiry_year'] ?? '';
    $cvv          = $_POST['cvv'] ?? '';
    $card_holder  = $_POST['card_holder'] ?? '';

    if(empty($card_number) || empty($expiry_month) || empty($expiry_year) || empty($cvv) || empty($card_holder)){
        $error_message = "Please fill in all card details.";
    }
    elseif(!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $card_number))){
        $error_message = "Invalid card number.";
    }
    elseif(!preg_match('/^\d{3}$/', $cvv)){
        $error_message = "Invalid CVV.";
    }
    else{

        /* Simulated payment success */
        $sql = "INSERT INTO appointment(pid, apponum, scheduleid, appodate)
                VALUES (?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iiis",
            $patient['pid'],
            $apponum,
            $scheduleid,
            date('Y-m-d')
        );
        $stmt->execute();
        $appointment_id = $database->insert_id;
        $stmt->close();

        /* Store payment */
        $txn = "TXN".time().rand(1000,9999);
        $pay = "INSERT INTO payments
                (patient_id, appointment_id, amount, payment_method, transaction_id, payment_status)
                VALUES (?, ?, ?, 'Card', ?, 'completed')";
        $pstmt = $database->prepare($pay);
        $pstmt->bind_param("iids",
            $patient['pid'],
            $appointment_id,
            $total_fee,
            $txn
        );
        $pstmt->execute();
        $pstmt->close();

        unset($_SESSION["booking_details"]);
        header("location: payment-success.php?apponum=$apponum&scheduleid=$scheduleid");
        exit();
    }
}
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
        
    <title>Card Details</title>
    <style>
        .payment-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section-header h1 {
            color: #000000;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .section-header p {
            color: #333333;
            font-size: 1.1rem;
        }
        
        .payment-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .payment-layout {
                grid-template-columns: 1fr;
            }
        }
        
        .details-summary {
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            padding: 30px;
        }
        
        .payment-form-container {
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            padding: 30px;
            color: #000000;
        }
        
        .summary-title, .form-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .summary-item {
            margin-bottom: 15px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        .summary-label {
            font-weight: 500;
            color: #666666;
        }
        
        .summary-value {
            font-weight: 600;
        }
        
        .total-amount {
            font-size: 1.8rem;
            color: #000000;
            font-weight: 700;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        
        .card-form {
            display: flex;
            flex-direction: column;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333333;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dddddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #000000;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .pay-button {
            background: #000000;
            color: #ffffff;
            border: none;
            padding: 15px;
            font-size: 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        .pay-button:hover {
            background: #333333;
        }
        
        .security-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666666;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background: #ffecec;
            border: 1px solid #ffcccc;
            color: #cc0000;
        }
    </style>
</head>
<body>
    <?php include("../connection.php"); ?>
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
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="payment.php?scheduleid=<?php echo $scheduleid; ?>&fee=<?php echo $total_fee; ?>" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <div class="section-header">
                            <h1>Secure Card Payment</h1>
                            <p>Enter your card details to complete your appointment booking</p>
                        </div>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo date('Y-m-d'); ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="payment-layout">
                            <div class="details-summary">
                                <h2 class="summary-title">Appointment Details</h2>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Doctor</span>
                                    <span class="summary-value"><?php echo htmlspecialchars($schedule['docname']);?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Specialty</span>
                                    <span class="summary-value">
                                        <?php
                                        $spec_sql = "SELECT sname FROM specialties WHERE id=?";
                                        $spec_stmt = $database->prepare($spec_sql);
                                        $spec_stmt->bind_param("i", $schedule['specialties']);
                                        $spec_stmt->execute();
                                        $spec_result = $spec_stmt->get_result();
                                        $specialty = $spec_result->fetch_assoc();
                                        echo htmlspecialchars($specialty['sname'] ?? 'General');
                                        $spec_stmt->close();
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Session Title</span>
                                    <span class="summary-value"><?php echo htmlspecialchars($schedule['title']);?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Date & Time</span>
                                    <span class="summary-value">
                                        <?php echo date("M j, Y", strtotime($schedule['scheduledate']));?> at 
                                        <?php echo date("g:i A", strtotime($schedule['scheduletime']));?>
                                    </span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Appointment #</span>
                                    <span class="summary-value">#<?php echo $apponum; ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Consultation Fee</span>
                                    <span class="summary-value">LKR <?php echo number_format($consultation_fee, 2); ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Hospital Service Fee</span>
                                    <span class="summary-value">LKR <?php echo number_format($hospital_service_fee, 2); ?></span>
                                </div>
                                
                                <div class="total-amount">
                                    Total: LKR <?php echo number_format($total_fee, 2); ?>
                                </div>
                            </div>
                            
                            <div class="payment-form-container">
                                <h2 class="form-title">Card Details</h2>
                                
                                <?php if($error_message): ?>
                                    <div class="message error"><?php echo $error_message; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" class="card-form" id="payment-form">
                                    <div class="form-group">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" name="card_number" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCardNumber(this)" required>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Expiry Month</label>
                                            <select name="expiry_month" class="form-input" required>
                                                <option value="">Month</option>
                                                <?php for($i=1; $i<=12; $i++): ?>
                                                    <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Expiry Year</label>
                                            <select name="expiry_year" class="form-input" required>
                                                <option value="">Year</option>
                                                <?php 
                                                $currentYear = date('Y');
                                                for($i=$currentYear; $i<=$currentYear+10; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">CVV</label>
                                            <input type="text" name="cvv" class="form-input" placeholder="123" maxlength="3" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Card Holder Name</label>
                                        <input type="text" name="card_holder" class="form-input" placeholder="John Doe" value="<?php echo htmlspecialchars($patient['pname']); ?>" required>
                                    </div>
                                    
                                    <button type="submit" class="pay-button">
                                        Pay LKR <?php echo number_format($total_fee, 2); ?>
                                    </button>
                                </form>
                                
                                <div class="security-info">
                                    <div class="security-item">
                                        <span>🔒</span>
                                        <span>256-bit SSL</span>
                                    </div>
                                    <div class="security-item">
                                        <span>🛡️</span>
                                        <span>PCI DSS</span>
                                    </div>
                                    <div class="security-item">
                                        <span>💳</span>
                                        <span>Visa/Mastercard</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <script>
        function formatCardNumber(input) {
            
            let value = input.value.replace(/\D/g, '');

            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }

            input.value = formattedValue.substring(0, 19);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const cvvInput = document.querySelector('input[name="cvv"]');
            if (cvvInput) {
                cvvInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 3);
                });
            }
            
            const cardNumberInput = document.querySelector('input[name="card_number"]');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9\s]/g, '');
                });
            }
        });
    </script>
</body>
</html>

