<?php
/**
 * Email Testing Script
 * Test email functionality for Brevo API integration
 */

require_once(__DIR__ . '/includes/email-utility.php');

// Check if form was submitted
$testResult = null;
$testMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
    $testName = isset($_POST['name']) ? trim($_POST['name']) : '';
    $testType = isset($_POST['type']) ? $_POST['type'] : 'welcome';
    
    if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $testResult = false;
        $testMessage = 'Please enter a valid email address.';
    } elseif (empty($testName)) {
        $testResult = false;
        $testMessage = 'Please enter a name.';
    } else {
        // Send test email based on type
        if ($testType === 'welcome') {
            $testResult = EmailUtility::sendWelcomeEmail($testEmail, $testName);
            $testMessage = $testResult ? "Welcome email sent successfully to $testEmail!" : "Failed to send welcome email to $testEmail. Check logs for details.";
        } elseif ($testType === 'doctor') {
            $testPassword = 'TestPass123!';
            $testResult = EmailUtility::sendDoctorWelcomeEmail($testEmail, $testName, $testPassword);
            $testMessage = $testResult ? "Doctor welcome email sent successfully to $testEmail!" : "Failed to send doctor email to $testEmail. Check logs for details.";
        } elseif ($testType === 'appointment') {
            $testResult = EmailUtility::sendAppointmentConfirmation($testEmail, $testName, 'Dr. John Smith', '2026-07-15', '2:00 PM', 'APT-001');
            $testMessage = $testResult ? "Appointment confirmation sent successfully to $testEmail!" : "Failed to send appointment email to $testEmail. Check logs for details.";
        } elseif ($testType === 'payment') {
            $testResult = EmailUtility::sendPaymentReceipt($testEmail, $testName, 'APT-001', 'Dr. John Smith', '2026-07-15', 5000);
            $testMessage = $testResult ? "Payment receipt sent successfully to $testEmail!" : "Failed to send payment receipt to $testEmail. Check logs for details.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Testing - HealthCarePlus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input[type="email"],
        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group select {
            cursor: pointer;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .alert {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #0c5aa0;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Testing</h1>
        <p class="subtitle">Test HealthCarePlus Email Functionality</p>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="test@example.com" 
                    required 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="John Doe" 
                    required 
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="type">Email Type *</label>
                <select id="type" name="type" required>
                    <option value="welcome">Welcome Email (Patient)</option>
                    <option value="doctor">Doctor Welcome Email</option>
                    <option value="appointment">Appointment Confirmation</option>
                    <option value="payment">Payment Receipt</option>
                </select>
            </div>
            
            <button type="submit">Send Test Email</button>
        </form>
        
        <?php if ($testResult !== null): ?>
            <div class="alert <?php echo $testResult ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($testMessage); ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>📝 Test Instructions:</strong>
            1. Enter your test email address<br>
            2. Enter a name<br>
            3. Select email type<br>
            4. Click "Send Test Email"<br>
            5. Check your email for the message<br><br>
            <strong>📊 API Details:</strong><br>
            API: SendGrid v3<br>
            Sender: HealthCarePlus (anushadilshanqp@hmail.com)<br>
            Check logs/mailjet.log for details
        </div>
    </div>
</body>
</html>