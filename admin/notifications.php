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

$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $database->query("DELETE FROM session_notifications WHERE id=$id");
    header("location: notifications.php");
    exit();
}

// Get all notifications (doctor and patient)
$notifications = $database->query("
    SELECT sn.id, sn.message, sn.notify_date, sn.status, 
           d.docname, d.docid, p.pname, p.pid, sn.patid,
           s.title 
    FROM session_notifications sn
    LEFT JOIN doctor d ON sn.docid = d.docid
    LEFT JOIN patient p ON sn.patid = p.pid
    LEFT JOIN schedule s ON sn.scheduleid = s.scheduleid
    WHERE sn.docid IS NOT NULL OR sn.patid IS NOT NULL
    ORDER BY sn.notify_date DESC
");

$doctors = $database->query("SELECT docid, docname FROM doctor ORDER BY docname");
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Notifications</title>
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
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-section h1 {
            color: #123a68;
            margin: 0;
            font-size: 28px;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #5c636a;
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
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .notif-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 12px;
        }
        .notif-table th {
            background: linear-gradient(90deg, #0d6efd 0%, #2a8cff 100%);
            color: #fff;
            padding: 14px 16px;
            text-align: left;
            font-size: 14px;
        }
        .notif-table td {
            padding: 16px;
            border-bottom: 1px solid #e8eef7;
            vertical-align: middle;
        }
        .notif-row:hover {
            background: #f7fbff;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pending {
            background: #ffe8e8;
            color: #c62828;
        }
        .status-seen {
            background: #eaf7ea;
            color: #2e7d32;
        }
        .recipient-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .recipient-doctor {
            background: #e8f4fd;
            color: #0b5ed7;
        }
        .recipient-patient {
            background: #fff3cd;
            color: #856404;
        }
        .btn-delete {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .btn-send {
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-send:hover {
            background: #0b5ed7;
        }
        .empty-state {
            text-align: center;
            color: #68809a;
            padding: 40px 16px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <h1>🔔 System Notifications</h1>
            <a href="index.php" class="btn-back">← Back to Dashboard</a>
        </div>

        <!-- Send Doctor Notification -->
        <div class="section">
            <h2>Send Notification to Doctor</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="docid">Select Doctor:</label>
                    <select name="docid" id="docid" required>
                        <option value="">-- Choose a doctor --</option>
                        <?php 
                        $doctors_result = $database->query("SELECT docid, docname FROM doctor ORDER BY docname");
                        while($doctor = $doctors_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo (int)$doctor['docid']; ?>">
                                <?php echo htmlspecialchars($doctor['docname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="doc_message">Message:</label>
                    <textarea name="doc_message" id="doc_message" required placeholder="Enter notification message..."></textarea>
                </div>

                <button type="submit" name="send_doctor_notification" class="btn-send">Send to Doctor</button>
            </form>
        </div>

        <!-- All Notifications History -->
        <div class="section">
            <h2>📋 All Notifications History</h2>
            <table class="notif-table">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Recipient</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($notifications->num_rows == 0): ?>
                        <tr><td colspan="5" class="empty-state">No notifications sent yet.</td></tr>
                    <?php else: while ($row = $notifications->fetch_assoc()): 
                        $statusClass = ($row['status'] === 'pending') ? 'status-pending' : 'status-seen';
                        $recipient_name = '';
                        $recipient_type = '';
                        $recipient_class = '';
                        
                        if ($row['docid'] !== null && $row['docid'] != 0) {
                            $recipient_type = 'Doctor';
                            $recipient_name = htmlspecialchars($row['docname']);
                            $recipient_class = 'recipient-doctor';
                        } elseif ($row['patid'] !== null && $row['patid'] != 0) {
                            $recipient_type = 'Patient';
                            $recipient_name = htmlspecialchars($row['pname']);
                            $recipient_class = 'recipient-patient';
                        }
                    ?>
                        <tr class="notif-row">
                            <td style="color: #1e3f67; font-weight: 500;"><?php echo htmlspecialchars($row['message']); ?></td>
                            <td>
                                <span class="recipient-badge <?php echo $recipient_class; ?>">
                                    <?php echo $recipient_type; ?> - <?php echo $recipient_name; ?>
                                </span>
                            </td>
                            <td style="font-size: 13px; color: #5d7698;"><?php echo date('d-m-Y H:i', strtotime($row['notify_date'])); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <a href="notifications.php?delete=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this notification?')">
                                    <button class="btn-delete">Delete</button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Handle sending doctor notification
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_doctor_notification'])) {
        $docid = (int)$_POST['docid'];
        $message = trim($_POST['doc_message']);

        if ($docid > 0 && !empty($message)) {
            $message_safe = $database->real_escape_string($message);
            $insert_result = $database->query("INSERT INTO session_notifications (scheduleid, docid, message, notify_date, status) VALUES (0, $docid, '$message_safe', NOW(), 'pending')");
            if ($insert_result) {
                echo '<script>alert("Notification sent successfully!"); window.location.href = "notifications.php";</script>';
            }
        }
    }
    ?>
</body>
</html>
