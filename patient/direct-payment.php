<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}

include("../connection.php");
include("../config.php");

if(isset($_GET['scheduleid']) && isset($_GET['fee'])) {
    $scheduleid = $_GET['scheduleid'];
    $fee = $_GET['fee'];
    header("location: card-details.php?scheduleid=$scheduleid&fee=$fee");
    exit();
} else {
    header("location: index.php");
    exit();
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
        
    <title>Direct Card Payment</title>
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
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-input {
            padding: 15px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 1rem;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #333333;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .pay-button {
            background: #000000;
            color: #ffffff;
            border: 1px solid #000000;
            padding: 18px;
            font-size: 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 10px;
        }
        
        .pay-button:hover {
            background: #333333;
            border: 1px solid #333333;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        
        .error {
            background: #ffeeee;
            color: #cc0000;
            border: 1px solid #ffcccc;
        }
        
        .success {
            background: #eeffee;
            color: #006600;
            border: 1px solid #cce5cc;
        }
        
        .security-info {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            color: #666666;
            font-size: 0.9rem;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            gap: 5px;
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
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Patient</p>
                                    <p class="profile-subtitle"><?php echo $useremail;?></p>
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
        
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                        <a href="payment.php?scheduleid=<?php echo $scheduleid; ?>&fee=<?php echo $fee; ?>" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Direct Card Payment</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="payment-container">
                            <div class="section-header">
                                <h1>Direct Card Payment</h1>
                                <p>Enter your card details to complete your appointment</p>
                            </div>
                            
                            <?php if($success_message): ?>
                                <div class="message success">
                                    <h2 style="margin-top: 0;">Payment Successful!</h2>
                                    <p><?php echo $success_message; ?></p>
                                    <div style="margin-top: 20px;">
                                        <a href="appointment.php" class="pay-button" style="display: inline-block; text-decoration: none;">View My Appointments</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="payment-layout">
                                    <div class="details-summary">
                                        <h2 class="summary-title">Appointment Summary</h2>
                                        
                                        <div class="summary-item">
                                            <span class="summary-label">Doctor</span>
                                            <span class="summary-value"><?php echo htmlspecialchars($schedule['docname']);?></span>
                                        </div>
                                        
                                        <div class="summary-item">
                                            <span class="summary-label">Specialization</span>
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
                                            <span class="summary-label">Session</span>
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
                            <?php endif; ?>
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

