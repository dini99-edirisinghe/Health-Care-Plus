<?php
session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
}

include("../connection.php");

// Ensure table has patid column for patient notifications
$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");

$error_message = "";
$success_message = "";

// Handle sending notification to specific patient
if(isset($_POST['send_patient_notification'])){
    $patid = (int)$_POST['patient_id'];
    $message = $_POST['message'];
    
    if(empty($message)){
        $error_message = "Please enter a message.";
    } else {
        $message_safe = $database->real_escape_string($message);
        $insert_result = $database->query("INSERT INTO session_notifications (patid, message, notify_date, status) VALUES ($patid, '$message_safe', NOW(), 'pending')");
        if($insert_result){
            $success_message = "Notification sent to patient successfully!";
        } else {
            $error_message = "Failed to send notification.";
        }
    }
}

// Handle broadcast to all patients/pensioners
if(isset($_POST['broadcast_notification'])){
    $message = $_POST['broadcast_message'];
    
    if(empty($message)){
        $error_message = "Please enter a message.";
    } else {
        $message_safe = $database->real_escape_string($message);
        $patients_query = $database->query("SELECT pid FROM patient");
        $count = 0;
        
        if($patients_query){
            while($patient = $patients_query->fetch_assoc()){
                $pat_id = (int)$patient['pid'];
                if($database->query("INSERT INTO session_notifications (patid, message, notify_date, status) VALUES ($pat_id, '$message_safe', NOW(), 'pending')")){
                    $count++;
                }
            }
        }
        
        $success_message = "Broadcast notification sent to $count patients!";
    }
}

$patients = $database->query("SELECT pid, pname, pemail FROM patient ORDER BY pname");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Notifications</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4f8ff 0%, #eef4ff 100%);
            font-family: 'Inter', Arial, sans-serif;
            color: #23374d;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 24px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(12, 63, 122, 0.12);
        }
        .header {
            margin-bottom: 30px;
        }
        .header h1 {
            color: #123a68;
            margin: 0 0 8px;
        }
        .header p {
            color: #5d7698;
            margin: 0;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e8eef7;
            border-radius: 12px;
            background: #fafbfd;
        }
        .section h2 {
            color: #0d6efd;
            margin-top: 0;
            font-size: 18px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e3f67;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d0dce6;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #0d6efd;
            color: white;
        }
        .btn-primary:hover {
            background: #0b5ed7;
        }
        .btn-success {
            background: #198754;
            color: white;
        }
        .btn-success:hover {
            background: #157347;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5c636a;
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #ffe8e8;
            color: #c62828;
            border: 1px solid #ffb3ba;
        }
        .success {
            background: #eaf7ea;
            color: #2e7d32;
            border: 1px solid #b7e4c7;
        }
        .notification-example {
            background: #f0f6ff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
            color: #5d7698;
            border-left: 3px solid #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h1>📢 Patient Notifications</h1>
                    <p>Send notifications to patients/pensioners about system updates, health tips, and other important information.</p>
                </div>
                <a href="index.php" style="text-decoration: none;"><button class="btn btn-secondary">← Back</button></a>
            </div>
        </div>

        <?php if($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <!-- Individual Patient Notification -->
        <div class="section">
            <h2>Send to Individual Patient</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="patient_id">Select Patient:</label>
                    <select name="patient_id" id="patient_id" required>
                        <option value="">-- Choose a patient --</option>
                        <?php if($patients->num_rows > 0): while($patient = $patients->fetch_assoc()): ?>
                            <option value="<?php echo (int)$patient['pid']; ?>">
                                <?php echo htmlspecialchars($patient['pname']) . " (" . htmlspecialchars($patient['pemail']) . ")"; ?>
                            </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea name="message" id="message" required placeholder="Enter your notification message..."></textarea>
                    <div class="notification-example">
                        💡 Example: "We have updated the EPIMAN system. Please check your account for new features."
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" name="send_patient_notification" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>

        <!-- Broadcast to All Patients -->
        <div class="section">
            <h2>Broadcast to All Patients/Pensioners</h2>
            <p style="color: #5d7698; margin-top: 0;">Send a notification to all patients at once.</p>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="broadcast_message">Broadcast Message:</label>
                    <textarea name="broadcast_message" id="broadcast_message" required placeholder="Enter your broadcast message..."></textarea>
                    <div class="notification-example">
                        💡 Examples:<br>
                        • "EPIMAN system maintenance scheduled for Sunday 2:00 AM. Thank you for your patience."<br>
                        • "New health tips available! Check the Health Tips section for the latest wellness information."<br>
                        • "Important: Please update your profile information to ensure smooth service delivery."
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" name="broadcast_notification" class="btn btn-success">Send Broadcast</button>
                </div>
            </form>
        </div>

        <!-- Recent Notifications Sent -->
        <div class="section">
            <h2>📋 Recent Notifications Sent</h2>
            <?php
                $recent_notifications = $database->query("
                    SELECT sn.id, sn.message, sn.notify_date, sn.status, p.pname 
                    FROM session_notifications sn 
                    LEFT JOIN patient p ON sn.patid = p.pid 
                    WHERE sn.patid IS NOT NULL 
                    ORDER BY sn.notify_date DESC 
                    LIMIT 10
                ");
            ?>
            <?php if($recent_notifications && $recent_notifications->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #e8eef7;">
                            <th style="padding: 12px; text-align: left; color: #1e3f67; font-weight: 600; border-bottom: 2px solid #d0dce6;">Message</th>
                            <th style="padding: 12px; text-align: left; color: #1e3f67; font-weight: 600; border-bottom: 2px solid #d0dce6;">Patient</th>
                            <th style="padding: 12px; text-align: left; color: #1e3f67; font-weight: 600; border-bottom: 2px solid #d0dce6;">Sent Date</th>
                            <th style="padding: 12px; text-align: left; color: #1e3f67; font-weight: 600; border-bottom: 2px solid #d0dce6;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($notif = $recent_notifications->fetch_assoc()): 
                            $status_color = ($notif['status'] === 'pending') ? '#ffe8e8' : '#eaf7ea';
                            $status_text_color = ($notif['status'] === 'pending') ? '#c62828' : '#2e7d32';
                        ?>
                            <tr style="border-bottom: 1px solid #e8eef7; hover: background #f7fbff;">
                                <td style="padding: 12px; color: #1e3f67;"><?php echo htmlspecialchars($notif['message']); ?></td>
                                <td style="padding: 12px; color: #5d7698;"><?php echo htmlspecialchars($notif['pname'] ?? 'Broadcast'); ?></td>
                                <td style="padding: 12px; color: #5d7698; font-size: 13px;"><?php echo date('d-m-Y H:i', strtotime($notif['notify_date'])); ?></td>
                                <td style="padding: 12px;">
                                    <span style="background: <?php echo $status_color; ?>; color: <?php echo $status_text_color; ?>; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($notif['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #68809a; text-align: center; padding: 20px; font-style: italic;">No notifications sent yet.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f0f6ff; border-radius: 12px;">
            <h3 style="color: #0d6efd; margin-top: 0;">ℹ️ How It Works</h3>
            <ul style="color: #5d7698; line-height: 1.8;">
                <li>Patients/Pensioners will see a notification indicator in their dashboard menu</li>
                <li>Clicking on a notification automatically marks it as viewed and navigates to the relevant page</li>
                <li>Patients can delete notifications they no longer need</li>
                <li>Notifications appear in real-time on their notifications page</li>
            </ul>
        </div>
    </div>
</body>
</html>
