<?php
// Email utility functions for the doctor appointment system using SendGrid API v3

// Load SendGrid configuration
require_once dirname(__DIR__) . '/config-sendgrid.php';

class EmailUtility {
    
    private static function getApiKey() {
        $key = getenv('BREVO_API_KEY');
        if (!$key) {
            $key = getenv('SENDGRID_API_KEY');
        }
        if (!$key && defined('BREVO_API_KEY')) {
            $key = BREVO_API_KEY;
        }
        if (!$key && defined('SENDGRID_API_KEY')) {
            $key = SENDGRID_API_KEY;
        }
        if (!$key) {
            error_log("ERROR: Email API key not found in environment variables or configuration");
            return '';
        }
        return $key;
    }
    
    private static $senderEmail = 'xmxm7221@gmail.com';
    private static $senderName = 'HealthCarePlus';
    
    private static function getApiUrl($apiKey) {
        if (stripos($apiKey, 'xkeysib-') === 0) {
            return 'https://api.brevo.com/v3/smtp/email';
        }
        return 'https://api.sendgrid.com/v3/mail/send';
    }

    private static function buildEmailPayload($apiKey, $toEmail, $toName, $subject, $textContent, $htmlContent) {
        if (stripos($apiKey, 'xkeysib-') === 0) {
            return array(
                'sender' => array(
                    'name' => self::$senderName,
                    'email' => self::$senderEmail
                ),
                'to' => array(
                    array(
                        'email' => $toEmail,
                        'name' => $toName
                    )
                ),
                'subject' => $subject,
                'htmlContent' => $htmlContent,
                'textContent' => $textContent
            );
        }

        return array(
            'personalizations' => array(
                array(
                    'to' => array(
                        array(
                            'email' => $toEmail,
                            'name' => $toName
                        )
                    )
                )
            ),
            'from' => array(
                'email' => self::$senderEmail,
                'name' => self::$senderName
            ),
            'subject' => $subject,
            'content' => array(
                array(
                    'type' => 'text/plain',
                    'value' => $textContent
                ),
                array(
                    'type' => 'text/html',
                    'value' => $htmlContent
                )
            )
        );
    }

    private static function sendEmailViaAPI($toEmail, $toName, $subject, $textContent, $htmlContent) {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return false;
        }

        $data = self::buildEmailPayload($apiKey, $toEmail, $toName, $subject, $textContent, $htmlContent);
        $payload = json_encode($data);
        $apiUrl = self::getApiUrl($apiKey);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );

        if (stripos($apiKey, 'xkeysib-') === 0) {
            $headers[] = 'api-key: ' . $apiKey;
        } else {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            $message = "cURL Error: " . $error . " | To: " . $toEmail . " | Subject: " . $subject;
            error_log($message);
            self::logEmailResult($message);
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $message = "Email sent successfully to: " . $toEmail . " with subject: " . $subject . " | HTTP Code: " . $httpCode;
            error_log($message);
            self::logEmailResult($message);
            return true;
        } else {
            $message = "API Error: HTTP Code " . $httpCode . ", Response: " . $response . " | To: " . $toEmail . " | Subject: " . $subject;
            error_log($message);
            self::logEmailResult($message);
            return false;
        }
    }
    
    public static function sendAppointmentConfirmation($patientEmail, $patientName, $doctorName, $appointmentDate, $appointmentTime, $appointmentNumber) {
        $subject = 'Appointment Confirmation - HealthCarePlus';
        $htmlContent = self::getAppointmentConfirmationTemplate($patientName, $doctorName, $appointmentDate, $appointmentTime, $appointmentNumber);
        $textContent = self::getAppointmentConfirmationText($patientName, $doctorName, $appointmentDate, $appointmentTime, $appointmentNumber);

        return self::sendEmailViaAPI($patientEmail, $patientName, $subject, $textContent, $htmlContent);
    }

    public static function sendDoctorWelcomeEmail($doctorEmail, $doctorName, $password) {
        $subject = 'Welcome to HealthCarePlus - Your Doctor Account';
        $htmlContent = self::getDoctorWelcomeTemplate($doctorName, $doctorEmail, $password);
        $textContent = self::getDoctorWelcomeText($doctorName, $doctorEmail, $password);
        
        return self::sendEmailViaAPI($doctorEmail, $doctorName, $subject, $textContent, $htmlContent);
    }
    
    public static function sendWelcomeEmail($patientEmail, $patientName) {
        $subject = 'Welcome to HealthCarePlus!';
        $htmlContent = self::getWelcomeEmailTemplate($patientName);
        $textContent = self::getWelcomeEmailText($patientName);
        
        return self::sendEmailViaAPI($patientEmail, $patientName, $subject, $textContent, $htmlContent);
    }
    
    public static function sendPaymentReceipt($patientEmail, $patientName, $appointmentNumber, $doctorName, $appointmentDate, $totalFee) {
        $subject = 'Payment Receipt - Appointment #' . $appointmentNumber;
        $htmlContent = self::getPaymentReceiptTemplate($patientName, $appointmentNumber, $doctorName, $appointmentDate, $totalFee);
        $textContent = self::getPaymentReceiptText($patientName, $appointmentNumber, $doctorName, $appointmentDate, $totalFee);
        
        return self::sendEmailViaAPI($patientEmail, $patientName, $subject, $textContent, $htmlContent);
    }

    public static function sendSessionCancellationToPatient($patientEmail, $patientName, $doctorName, $sessionTitle, $appointmentDate, $appointmentTime) {
        $subject = 'Session Cancellation Notice';
        $htmlContent = self::getSessionCancellationPatientTemplate($patientName, $doctorName, $sessionTitle, $appointmentDate, $appointmentTime);
        $textContent = self::getSessionCancellationPatientText($patientName, $doctorName, $sessionTitle, $appointmentDate, $appointmentTime);

        return self::sendEmailViaAPI($patientEmail, $patientName, $subject, $textContent, $htmlContent);
    }

    public static function sendSessionCancellationToDoctor($doctorEmail, $doctorName, $sessionTitle, $appointmentDate, $appointmentTime, $bookedCount) {
        $subject = 'Session Cancelled Notice';
        $htmlContent = self::getSessionCancellationDoctorTemplate($doctorName, $sessionTitle, $appointmentDate, $appointmentTime, $bookedCount);
        $textContent = self::getSessionCancellationDoctorText($doctorName, $sessionTitle, $appointmentDate, $appointmentTime, $bookedCount);

        return self::sendEmailViaAPI($doctorEmail, $doctorName, $subject, $textContent, $htmlContent);
    }

    private static function getAppointmentConfirmationText($patientName, $doctorName, $appointmentDate, $appointmentTime, $appointmentNumber) {
        return "Dear {$patientName},\n\n" .
            "Your appointment has been successfully booked with the following details:\n\n" .
            "Doctor: {$doctorName}\n" .
            "Date: {$appointmentDate}\n" .
            "Time: {$appointmentTime}\n" .
            "Appointment Number: #{$appointmentNumber}\n\n" .
            "Please arrive 10 minutes before your scheduled appointment time.\n" .
            "If you need to reschedule or cancel your appointment, please contact us at least 2 hours in advance.\n\n" .
            "Thank you for choosing HealthCarePlus!";
    }
    
    private static function getAppointmentConfirmationTemplate($patientName, $doctorName, $appointmentDate, $appointmentTime, $appointmentNumber) {
        return "
        <html>
        <head>
            <title>Appointment Confirmation</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #000000;'>Appointment Confirmation</h2>
                <p>Dear {$patientName},</p>
                <p>Your appointment has been successfully booked with the following details:</p>
                <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Doctor:</strong> {$doctorName}</p>
                    <p><strong>Date:</strong> {$appointmentDate}</p>
                    <p><strong>Time:</strong> {$appointmentTime}</p>
                    <p><strong>Appointment Number:</strong> #{$appointmentNumber}</p>
                </div>
                <p>Please arrive 10 minutes before your scheduled appointment time.</p>
                <p>If you need to reschedule or cancel your appointment, please contact us at least 2 hours in advance.</p>
                <p>Thank you for choosing HealthCarePlus!</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
    
    private static function getDoctorWelcomeTemplate($doctorName, $doctorEmail, $password) {
        return "
        <html>
        <head><title>Welcome to HealthCarePlus</title></head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #000000;'>Welcome to HealthCarePlus, Dr. {$doctorName}!</h2>
                <p>Your doctor account has been created by the administrator. Below are your login credentials:</p>
                <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Email:</strong> {$doctorEmail}</p>
                    <p><strong>Password:</strong> {$password}</p>
                </div>
                <p>Please log in and change your password as soon as possible to keep your account secure.</p>
                <p>If you have any questions, please contact the administrator.</p>
                <p>Best regards,<br>The HealthCarePlus Team</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
    
    private static function getDoctorWelcomeText($doctorName, $doctorEmail, $password) {
        return "Dear Dr. {$doctorName},\n\n" .
            "Your doctor account has been created by the administrator. Below are your login credentials:\n\n" .
            "Email: {$doctorEmail}\n" .
            "Password: {$password}\n\n" .
            "Please log in and change your password as soon as possible to keep your account secure.\n\n" .
            "If you have any questions, please contact the administrator.\n\n" .
            "Best regards,\nThe HealthCarePlus Team";
    }
    
    private static function getWelcomeEmailTemplate($patientName) {
        return "
        <html>
        <head>
            <title>Welcome to HealthCarePlus</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #000000;'>Welcome to HealthCarePlus!</h2>
                <p>Dear {$patientName},</p>
                <p>Thank you for registering with HealthCarePlus. Your account has been successfully created.</p>
                <p>You can now:</p>
                <ul>
                    <li>Book appointments with our specialist doctors</li>
                    <li>View your appointment history</li>
                    <li>Update your personal information</li>
                    <li>Receive notifications about your appointments</li>
                </ul>
                <p>We're committed to providing you with the best healthcare services.</p>
                <p>If you have any questions, please feel free to contact our support team.</p>
                <p>Best regards,<br>The HealthCarePlus Team</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
    
    private static function getWelcomeEmailText($patientName) {
        return "Dear {$patientName},\n\n" .
            "Thank you for registering with HealthCarePlus. Your account has been successfully created.\n\n" .
            "You can now:\n" .
            "- Book appointments with our specialist doctors\n" .
            "- View your appointment history\n" .
            "- Update your personal information\n" .
            "- Receive notifications about your appointments\n\n" .
            "We're committed to providing you with the best healthcare services.\n\n" .
            "If you have any questions, please feel free to contact our support team.\n\n" .
            "Best regards,\nThe HealthCarePlus Team";
    }
    
    private static function getPaymentReceiptText($patientName, $appointmentNumber, $doctorName, $appointmentDate, $totalFee) {
        return "Dear {$patientName},\n\n" .
            "Thank you for your payment. Here are the details of your transaction:\n\n" .
            "Appointment Number: #{$appointmentNumber}\n" .
            "Doctor: {$doctorName}\n" .
            "Appointment Date: {$appointmentDate}\n" .
            "Total Amount Paid: LKR " . number_format($totalFee, 2) . "\n\n" .
            "This receipt serves as proof of payment for your appointment.\n\n" .
            "If you have any questions about this transaction, please contact our billing department.\n\n" .
            "Thank you for choosing HealthCarePlus!";
    }

    private static function getSessionCancellationPatientText($patientName, $doctorName, $sessionTitle, $appointmentDate, $appointmentTime) {
        return "Dear {$patientName},\n\n" .
            "We regret to inform you that your scheduled session \"{$sessionTitle}\" with Dr. {$doctorName} on {$appointmentDate} at {$appointmentTime} has been cancelled.\n\n" .
            "We are sorry for any inconvenience this may cause. Please contact us if you would like help rescheduling or booking another session.\n\n" .
            "Thank you for your understanding,\n" .
            "The HealthCarePlus Team";
    }

    private static function getSessionCancellationPatientTemplate($patientName, $doctorName, $sessionTitle, $appointmentDate, $appointmentTime) {
        return "
        <html>
        <head>
            <title>Session Cancellation</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #000000;'>Session Cancelled</h2>
                <p>Dear {$patientName},</p>
                <p>We regret to inform you that your scheduled session <strong>\"{$sessionTitle}\"</strong> with Dr. {$doctorName} on <strong>{$appointmentDate}</strong> at <strong>{$appointmentTime}</strong> has been cancelled.</p>
                <p>We are sorry for any inconvenience this may cause. Please contact us if you would like help rescheduling or booking another session.</p>
                <p>Thank you for your understanding.</p>
                <p>Best regards,<br>The HealthCarePlus Team</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }

    private static function getSessionCancellationDoctorText($doctorName, $sessionTitle, $appointmentDate, $appointmentTime, $bookedCount) {
        $patientLabel = $bookedCount === 1 ? '1 patient was' : $bookedCount . ' patients were';
        return "Dear Dr. {$doctorName},\n\n" .
            "The scheduled session \"{$sessionTitle}\" on {$appointmentDate} at {$appointmentTime} has been cancelled.\n\n" .
            "{$patientLabel} booked for this session and have been notified.\n\n" .
            "If you need any help rescheduling or managing your appointments, please contact the administration team.\n\n" .
            "Best regards,\nThe HealthCarePlus Team";
    }

    private static function getSessionCancellationDoctorTemplate($doctorName, $sessionTitle, $appointmentDate, $appointmentTime, $bookedCount) {
        $patientLabel = $bookedCount === 1 ? '1 patient was' : $bookedCount . ' patients were';
        return "
        <html>
        <head>
            <title>Session Cancelled</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #000000;'>Session Cancelled</h2>
                <p>Dear Dr. {$doctorName},</p>
                <p>The scheduled session <strong>\"{$sessionTitle}\"</strong> on <strong>{$appointmentDate}</strong> at <strong>{$appointmentTime}</strong> has been cancelled.</p>
                <p>{$patientLabel} booked for this session and have been notified.</p>
                <p>If you need any help rescheduling or managing your appointments, please contact the administration team.</p>
                <p>Best regards,<br>The HealthCarePlus Team</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }

    private static function logEmailResult($message) {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/mailjet.log';
        $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
    
    private static function getPaymentReceiptTemplate($patientName, $appointmentNumber, $doctorName, $appointmentDate, $totalFee) {
        return "
        <html>
        <head>
            <title>Payment Receipt</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #000000;'>Payment Receipt</h2>
                <p>Dear {$patientName},</p>
                <p>Thank you for your payment. Here are the details of your transaction:</p>
                <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Appointment Number:</strong> #{$appointmentNumber}</p>
                    <p><strong>Doctor:</strong> {$doctorName}</p>
                    <p><strong>Appointment Date:</strong> {$appointmentDate}</p>
                    <p><strong>Total Amount Paid:</strong> LKR " . number_format($totalFee, 2) . "</p>
                    <p><strong>Status:</strong> <span style='color: green;'>PAID</span></p>
                </div>
                <p>This receipt serves as proof of payment for your appointment.</p>
                <p>If you have any questions about this transaction, please contact our billing department.</p>
                <p>Thank you for choosing HealthCarePlus!</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
}
?>