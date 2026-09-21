<?php
session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
}

include("../connection.php");

$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid = $userfetch['pid'];

// Ensure table has patid column for patient notifications
$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $database->query("DELETE FROM session_notifications WHERE id=$id AND patid=$userid");
    header("location: notifications.php");
    exit();
}

if (isset($_GET['view'])) {
    $id = (int)$_GET['view'];
    $database->query("UPDATE session_notifications SET status='seen' WHERE id=$id AND patid=$userid");
    $notif_query = $database->query("SELECT * FROM session_notifications WHERE id=$id AND patid=$userid");
    if ($notif_query && $notif_query->num_rows > 0) {
        $notif = $notif_query->fetch_assoc();
        $targetUrl = getNotificationTargetUrl($notif['message']);
        header("location: $targetUrl");
        exit();
    }
}

$notifications = $database->query("SELECT id, message, notify_date, status FROM session_notifications WHERE patid=$userid ORDER BY notify_date DESC");

function getNotificationTargetUrl($message) {
    $msg = strtolower($message);
    if (strpos($msg, 'health tip') !== false || strpos($msg, 'health tips') !== false) {
        return '../health-tips-public.php';
    }
    if (strpos($msg, 'payment') !== false) {
        return 'appointment.php';
    }
    if (strpos($msg, 'appointment') !== false || strpos($msg, 'booking') !== false) {
        return 'appointment.php';
    }
    if (strpos($msg, 'cancel') !== false || strpos($msg, 'canceled') !== false || strpos($msg, 'cancellation') !== false) {
        return 'appointment.php';
    }
    if (strpos($msg, 'epiman') !== false || strpos($msg, 'system') !== false) {
        return 'index.php';
    }
    return 'index.php';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Notifications</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4f8ff 0%, #eef4ff 100%);
            font-family: 'Inter', Arial, sans-serif;
            color: #23374d;
        }
        .page-shell {
            max-width: 1100px;
            margin: 30px auto;
            padding: 24px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(12, 63, 122, 0.12);
        }
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #123a68;
            margin: 0;
        }
        .page-subtitle {
            color: #5d7698;
            margin: 4px 0 0;
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
        .notif-message {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1e3f67;
        }
        .notif-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #eaf4ff;
            color: #0d6efd;
            font-size: 16px;
        }
        .notif-link {
            color: #0d6efd;
            text-decoration: none;
            display: block;
        }
        .notif-link:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-pending {
            background: #ffe8e8;
            color: #c62828;
        }
        .status-seen {
            background: #eaf7ea;
            color: #2e7d32;
        }
        .btn-delete {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 8px;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
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
    <div class="page-shell">
        <div class="page-head">
            <div>
                <h2 class="page-title">My Notifications</h2>
                <p class="page-subtitle">Open any alert to jump to the relevant section of the system.</p>
            </div>
            <a href="index.php" class="non-style-link"><button class="btn-primary-soft btn">Back</button></a>
        </div>

        <table class="notif-table">
            <thead>
                <tr>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($notifications->num_rows == 0): ?>
                    <tr><td colspan="4" class="empty-state">No notifications yet.</td></tr>
                <?php else: while ($row = $notifications->fetch_assoc()): 
                    $targetUrl = getNotificationTargetUrl($row['message']);
                    $statusClass = ($row['status'] === 'pending') ? 'status-pending' : 'status-seen';
                ?>
                    <tr class="notif-row">
                        <td>
                            <a href="notifications.php?view=<?php echo (int)$row['id']; ?>" class="notif-link">
                                <div class="notif-message">
                                    <span class="notif-icon">🔔</span>
                                    <span><?php echo htmlspecialchars($row['message']); ?></span>
                                </div>
                            </a>
                        </td>
                        <td><?php echo date('d-m-Y H:i', strtotime($row['notify_date'])); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <a href="notifications.php?delete=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this notification?')"><button class="btn-delete">Delete</button></a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
