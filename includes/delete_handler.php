<?php
// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/email-utility.php';

// Generic delete handler for various entities
function handleDelete($entityType, $redirectPage) {
    global $database;
    
    if (!isset($_SESSION["user"])) {
        header("location: ../login.php");
        exit();
    }
    
    // Validate user type based on entity type
    $validUserTypes = [
        'appointment' => ['p', 'd'], // Patients and doctors can delete appointments
        'doctor' => ['a'],           // Only admins can delete doctors
        'session' => ['d', 'a'],     // Doctors and admins can delete sessions
    ];
    
    $userType = $_SESSION['usertype'];
    if (!isset($validUserTypes[$entityType]) || !in_array($userType, $validUserTypes[$entityType])) {
        header("location: ../login.php");
        exit();
    }
    
    if ($_GET) {
        include("../connection.php");
        $id = $_GET["id"] ?? '';
        
        if (empty($id)) {
            header("location: $redirectPage");
            exit();
        }
        
        // Sanitize ID to prevent SQL injection
        $id = intval($id);

        $scheduleData = null;
        $appointments = [];

        if ($entityType === 'session') {
            $sessionInfoStmt = $database->prepare(
                "SELECT s.scheduleid, s.title, s.scheduledate, s.scheduletime, d.docid, d.docname, d.docemail 
                FROM schedule s 
                JOIN doctor d ON s.docid = d.docid 
                WHERE s.scheduleid = ?"
            );
            $sessionInfoStmt->bind_param("i", $id);
            $sessionInfoStmt->execute();
            $sessionInfoResult = $sessionInfoStmt->get_result();
            if ($sessionInfoResult && $sessionInfoResult->num_rows > 0) {
                $scheduleData = $sessionInfoResult->fetch_assoc();
            }
            $sessionInfoStmt->close();

            if ($scheduleData) {
                $appointmentsStmt = $database->prepare(
                    "SELECT a.apponum, p.pid, p.pname, p.pemail 
                    FROM appointment a 
                    JOIN patient p ON a.pid = p.pid 
                    WHERE a.scheduleid = ?"
                );
                $appointmentsStmt->bind_param("i", $id);
                $appointmentsStmt->execute();
                $appointmentsResult = $appointmentsStmt->get_result();
                while ($row = $appointmentsResult->fetch_assoc()) {
                    $appointments[] = $row;
                }
                $appointmentsStmt->close();
            }
        }

        // Delete based on entity type
        switch ($entityType) {
            case 'appointment':
                $sql = "DELETE FROM appointment WHERE appoid = ?";
                break;
            case 'doctor':
                $sql = "DELETE FROM doctor WHERE docid = ?";
                break;
            case 'session':
                $sql = "DELETE FROM schedule WHERE scheduleid = ?";
                break;
            default:
                header("location: $redirectPage");
                exit();
        }
        
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($entityType === 'session' && $scheduleData) {
            $bookedCount = count($appointments);
            foreach ($appointments as $appointment) {
                $patientSent = EmailUtility::sendSessionCancellationToPatient(
                    $appointment['pemail'],
                    $appointment['pname'],
                    $scheduleData['docname'],
                    $scheduleData['title'],
                    $scheduleData['scheduledate'],
                    $scheduleData['scheduletime']
                );
                if (!$patientSent) {
                    error_log("Session cancellation email failed for patient: " . $appointment['pemail']);
                }
            }

            $doctorSent = EmailUtility::sendSessionCancellationToDoctor(
                $scheduleData['docemail'],
                $scheduleData['docname'],
                $scheduleData['title'],
                $scheduleData['scheduledate'],
                $scheduleData['scheduletime'],
                $bookedCount
            );
            if (!$doctorSent) {
                error_log("Session cancellation email failed for doctor: " . $scheduleData['docemail']);
            }

            $database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS target_url VARCHAR(255) NULL");
            $database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");

            foreach ($appointments as $appointment) {
                $notification_message = $database->real_escape_string("Session cancelled: {$scheduleData['title']} on {$scheduleData['scheduledate']}");
                $patient_target_url = $database->real_escape_string("appointment.php");
                $database->query("INSERT INTO session_notifications (scheduleid, patid, message, notify_date, status, target_url) VALUES ({$scheduleData['scheduleid']}, {$appointment['pid']}, '$notification_message', NOW(), 'pending', '$patient_target_url')");
            }

            $notification_message = $database->real_escape_string("Session cancelled: {$scheduleData['title']} on {$scheduleData['scheduledate']}");
            $doctor_target_url = $database->real_escape_string("appointment.php");
            $database->query("INSERT INTO session_notifications (scheduleid, docid, message, notify_date, status, target_url) VALUES ({$scheduleData['scheduleid']}, {$scheduleData['docid']}, '$notification_message', NOW(), 'pending', '$doctor_target_url')");
        }

        if ($entityType === 'appointment') {
            $notification_check = $database->prepare("SELECT s.docid, a.apponum, a.pid FROM appointment a JOIN schedule s ON a.scheduleid = s.scheduleid WHERE a.appoid = ?");
            $notification_check->bind_param("i", $id);
            $notification_check->execute();
            $notification_result = $notification_check->get_result();
            if ($notification_result && $notification_result->num_rows > 0) {
                $notification_row = $notification_result->fetch_assoc();
                $doctor_id = (int)$notification_row['docid'];
                $patient_id = (int)$notification_row['pid'];
                $apponum = $notification_row['apponum'] ?? 'unknown';
                $database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS target_url VARCHAR(255) NULL");
                $database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");
                
                // Notify doctor about cancellation
                $notification_message = $database->real_escape_string("Appointment booking canceled for reference #$apponum.");
                $target_url = $database->real_escape_string("appointment.php");
                $database->query("INSERT INTO session_notifications (scheduleid, docid, message, notify_date, status, target_url) VALUES (0, $doctor_id, '$notification_message', NOW(), 'pending', '$target_url')");
                
                // Notify patient about cancellation
                $patient_notification_message = $database->real_escape_string("Your appointment #$apponum has been cancelled.");
                $patient_target_url = $database->real_escape_string("appointment.php");
                $database->query("INSERT INTO session_notifications (scheduleid, patid, message, notify_date, status, target_url) VALUES (0, $patient_id, '$patient_notification_message', NOW(), 'pending', '$patient_target_url')");
            }
        }
        
        header("location: $redirectPage");
        exit();
    }
}
?>