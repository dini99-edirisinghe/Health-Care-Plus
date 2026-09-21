<?php
include("../connection.php");
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
$sqlmain = "select * from doctor where docemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();

if (!$userfetch) {
    header("location: ../login.php");
    exit();
}

$userid = $userfetch["docid"];
$username = $userfetch["docname"];

// Get unread notification count for doctor
$unreadNotificationsQuery = $database->query("SELECT COUNT(*) AS unread_count FROM session_notifications WHERE docid = $userid AND status = 'pending'");
$unreadNotificationsCount = 0;
if ($unreadNotificationsQuery && $unreadNotificationsQuery->num_rows > 0) {
    $unreadNotificationRow = $unreadNotificationsQuery->fetch_assoc();
    $unreadNotificationsCount = (int)$unreadNotificationRow['unread_count'];
}

// Get all patients who have had conversations with the chatbot where this specific doctor was recommended
$sql = "SELECT DISTINCT p.pid, p.pname, p.pemail 
        FROM patient p 
        JOIN chatbot_conversations cc ON p.pid = cc.patient_id 
        WHERE cc.response LIKE ? 
        ORDER BY p.pname";

$search_term = "%Dr. " . $username . "%";
$stmt = $database->prepare($sql);
$stmt->bind_param("s", $search_term);
$stmt->execute();
$patients = $stmt->get_result();

// Get conversations for a specific patient if selected
$selected_patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;
$selected_patient_name = "";

if ($selected_patient_id) {
    // Get patient name
    $sql = "SELECT pname FROM patient WHERE pid = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $selected_patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selected_patient_name = $row['pname'];
    }
    
    // Get conversations for this patient that mention this doctor
    $sql = "SELECT message, response, timestamp, message_type 
            FROM chatbot_conversations 
            WHERE patient_id = ? 
            AND (response LIKE ? OR message LIKE ?)
            ORDER BY timestamp ASC";
    
    $search_term1 = "%Dr. " . $username . "%";
    $search_term2 = "%Dr. " . $username . "%";
    
    $stmt = $database->prepare($sql);
    $stmt->bind_param("iss", $selected_patient_id, $search_term1, $search_term2);
    $stmt->execute();
    $conversations = $stmt->get_result();
} else {
    $conversations = null;
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
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/chatbot.css">
        
    <title>Chatbot Messages</title>
    <style>
        .chat-container {
            max-width: 100%;
            margin: 0;
            padding: 20px;
        }
        
        .patient-list {
            width: 30%;
            float: left;
            padding-right: 20px;
            border-right: 1px solid #ddd;
            height: 600px;
            overflow-y: auto;
        }
        
        .chat-area {
            width: 70%;
            float: left;
            padding-left: 20px;
        }
        
        .patient-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .patient-item:hover {
            background-color: #f5f5f5;
        }
        
        .patient-item.active {
            background-color: #e3f2fd;
            font-weight: bold;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .user-message {
            background-color: #e3f2fd;
            margin-left: auto;
            text-align: right;
        }
        
        .bot-message {
            background-color: #f5f5f5;
            margin-right: auto;
        }
        
        .message-sender {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .message-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        
        .no-conversation {
            text-align: center;
            color: #666;
            padding: 50px;
        }
        
        .clear {
            clear: both;
        }
        
        /* Hide the menu */
        .menu {
            display: none;
        }
        
        /* Full width for dash-body when menu is hidden */
        .dash-body {
            margin-left: 0 !important;
            width: 100% !important;
        }
    </style>
</head>
<body>
    <?php // Menu is intentionally omitted to provide full-screen view ?>
    <div class="dash-body" style="margin-top: 15px">
        <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;">
            <tr>
                <td colspan="4">
                    <div class="chat-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                            <h2>Chatbot Messages</h2>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="notifications.php" style="text-decoration: none;">
                                    <button class="btn-primary btn notification-link" style="padding: 10px 20px;">🔔 Notifications<?php if($unreadNotificationsCount > 0): ?><span class="notification-dot"></span><?php endif; ?></button>
                                </a>
                                <a href="index.php" style="text-decoration: none;">
                                    <button class="btn-primary btn" style="padding: 10px 20px;">← Back to Dashboard</button>
                                </a>
                            </div>
                        </div>
                        <p>View messages from patients who were recommended to see you by the chatbot</p>
                        
                        <div class="patient-list">
                            <h3>Patients</h3>
                            <?php if ($patients->num_rows > 0): ?>
                                <?php while ($patient = $patients->fetch_assoc()): ?>
                                    <div class="patient-item <?php echo ($selected_patient_id == $patient['pid']) ? 'active' : ''; ?>" 
                                         onclick="window.location.href='chatbot.php?patient_id=<?php echo $patient['pid']; ?>'">
                                        <div><?php echo htmlspecialchars($patient['pname']); ?></div>
                                        <div style="font-size: 0.9em; color: #666;"><?php echo htmlspecialchars($patient['pemail']); ?></div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No patients have been recommended to see you yet.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-area">
                            <?php if ($selected_patient_id && $conversations): ?>
                                <h3>Conversation with <?php echo htmlspecialchars($selected_patient_name); ?></h3>
                                <div class="chat-history">
                                    <?php if ($conversations->num_rows > 0): ?>
                                        <?php while ($conv = $conversations->fetch_assoc()): ?>
                                            <?php if ($conv['message_type'] == 'user'): ?>
                                                <div class="message user-message">
                                                    <div class="message-sender">Patient</div>
                                                    <div><?php echo nl2br(htmlspecialchars($conv['message'])); ?></div>
                                                    <div class="message-time"><?php echo date('M j, Y g:i A', strtotime($conv['timestamp'])); ?></div>
                                                </div>
                                            <?php else: ?>
                                                <div class="message bot-message">
                                                    <div class="message-sender">Chatbot</div>
                                                    <div><?php echo nl2br(htmlspecialchars($conv['response'])); ?></div>
                                                    <div class="message-time"><?php echo date('M j, Y g:i A', strtotime($conv['timestamp'])); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="no-conversation">
                                            <p>No conversation history found.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-conversation">
                                    <h3>Select a patient to view conversation</h3>
                                    <p>Choose a patient from the list on the left to see their chatbot conversation history.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>