<?php

include("../connection.php");

session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];

$sqlmain = "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();

if (!$userfetch) {
    header("location: ../login.php");
    exit();
}

$userid = $userfetch["pid"];
$username = $userfetch["pname"];

$database->query("ALTER TABLE session_notifications ADD COLUMN IF NOT EXISTS patid INT DEFAULT NULL");
$unreadNotificationsQuery = $database->query("SELECT COUNT(*) AS unread_count FROM session_notifications WHERE patid = $userid AND status = 'pending'");
$unreadNotificationsCount = 0;
if ($unreadNotificationsQuery && $unreadNotificationsQuery->num_rows > 0) {
    $unreadNotificationRow = $unreadNotificationsQuery->fetch_assoc();
    $unreadNotificationsCount = (int)$unreadNotificationRow['unread_count'];
}

function saveConversation($database, $patient_id, $message, $response) {
    // Save user message
    $stmt = $database->prepare("INSERT INTO chatbot_conversations (patient_id, message, response, message_type) VALUES (?, ?, '', 'user')");
    $stmt->bind_param("is", $patient_id, $message);
    $stmt->execute();
    
    // Save bot response
    $stmt = $database->prepare("INSERT INTO chatbot_conversations (patient_id, message, response, message_type) VALUES (?, '', ?, 'bot')");
    $stmt->bind_param("is", $patient_id, $response);
    $stmt->execute();
}

function getMedicineRecommendation($database, $symptom) {
    $stmt = $database->prepare("SELECT recommended_medicine, specialty_id FROM symptoms_medicines WHERE symptom LIKE ?");
    $search_term = "%" . strtolower($symptom) . "%";
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getSpecialtyName($database, $specialty_id) {
    $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
    $stmt->bind_param("i", $specialty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['sname'];
    }
    return "General Practitioner";
}

function getDoctorsBySpecialty($database, $specialty_id) {
    $stmt = $database->prepare("SELECT docid, docname FROM doctor WHERE specialties = ? LIMIT 3");
    $stmt->bind_param("i", $specialty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    return $doctors;
}

function getAllDoctors($database) {
    $stmt = $database->prepare("SELECT docid, docname, specialties FROM doctor LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    return $doctors;
}

function getDoctorDetails($database, $docid) {
    $stmt = $database->prepare("SELECT d.docname, s.sname as specialty_name FROM doctor d LEFT JOIN specialties s ON d.specialties = s.id WHERE d.docid = ?");
    $stmt->bind_param("i", $docid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getDoctorSchedule($database, $docid) {
    $stmt = $database->prepare("SELECT scheduleid, title, scheduledate, scheduletime FROM schedule WHERE docid = ? AND scheduledate >= CURDATE() ORDER BY scheduledate ASC LIMIT 1");
    $stmt->bind_param("s", $docid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

$response = "";
if (isset($_POST['message']) && !empty($_POST['message'])) {
    $user_message = trim($_POST['message']);
    
    // Convert to lowercase for easier matching
    $lower_message = strtolower($user_message);
    
    // Greetings
    if (strpos($lower_message, 'hello') !== false || strpos($lower_message, 'hi') !== false || strpos($lower_message, 'hey') !== false) {
        $response = "Hello $username! I'm Anna, your medical assistant. How can I help you today? You can describe your symptoms or disease, and I'll recommend suitable medicine and doctors.";
    }
    // Thank you responses
    elseif (strpos($lower_message, 'thank') !== false) {
        $response = "You're welcome! Is there anything else I can help you with?";
    }
    // Goodbye responses
    elseif (strpos($lower_message, 'bye') !== false || strpos($lower_message, 'goodbye') !== false) {
        $response = "Goodbye $username! Take care and stay healthy!";
    }
    // Doctor/specialist related queries
    elseif (strpos($lower_message, 'doctor') !== false || strpos($lower_message, 'specialist') !== false || strpos($lower_message, 'find') !== false || strpos($lower_message, 'need') !== false || strpos($lower_message, 'see') !== false) {
        // Keywords mapped to specialty IDs
        $specialty_keywords = [
            'cardio' => 5, 'heart' => 5, 'chest pain' => 5, 'hypertension' => 5, 'angina' => 5,
            'derma' => 13, 'skin' => 13, 'rash' => 13, 'acne' => 13, 'eczema' => 13, 'psoriasis' => 13, 'urticaria' => 13,
            'ortho' => 35, 'bone' => 35, 'back pain' => 35, 'joint pain' => 35, 'arthritis' => 35, 'osteoporosis' => 35,
            'pedia' => 38, 'child' => 38, 'baby' => 38, 'infant' => 38,
            'eye' => 34, 'vision' => 34, 'blurry' => 34, 'see' => 34, 'conjunctivitis' => 34,
            'dent' => 11, 'tooth' => 11, 'teeth' => 11, 'mouth' => 11,
            'neuro' => 29, 'brain' => 29, 'headache' => 29, 'seizure' => 29, 'epilepsy' => 29, 'migraine' => 29,
            'psych' => 45, 'mental' => 45, 'depression' => 45, 'anxiety' => 45, 'panic disorder' => 45, 'bipolar disorder' => 45, 'obsessive compulsive disorder' => 45,
            'gyno' => 32, 'women' => 32, 'pregnancy' => 32, 'period' => 32, 'menstrual cramps' => 32,
            'ent' => 36, 'nose' => 36, 'ear' => 36, 'throat' => 36, 'sinus' => 36, 'sinusitis' => 36,
            'gastro' => 16, 'stomach' => 16, 'belly' => 16, 'digest' => 16, 'acid reflux' => 16, 'ulcer' => 16, 'irritable bowel syndrome' => 16,
            'pulmo' => 49, 'lung' => 49, 'breath' => 49, 'asthma' => 49, 'bronchitis' => 49, 'pneumonia' => 49,
            'endocrine' => 14, 'diabetes' => 14, 'hypothyroidism' => 14,
            'infectious' => 22, 'malaria' => 22, 'typhoid' => 22, 'urinary tract infection' => 22,
            'hematology' => 17, 'anemia' => 17,
            'kidney' => 54, 'stones' => 54, 'kidney stones' => 54,
            'liver' => 24, 'hepatitis' => 24
        ];
        
        $found_specialty = false;
        $specialty_id = 18; // Default to General Practice
        $matched_keyword = '';
        
        // Check if any keyword matches
        foreach ($specialty_keywords as $keyword => $id) {
            if (strpos($lower_message, $keyword) !== false) {
                $specialty_id = $id;
                $found_specialty = true;
                $matched_keyword = $keyword;
                break;
            }
        }
        
        $specialty_name = getSpecialtyName($database, $specialty_id);
        $doctors = getDoctorsBySpecialty($database, $specialty_id);
        
        if (!empty($doctors)) {
            $doctor_list = "";
            foreach ($doctors as $doctor) {
                $doctor_list .= "- Dr. " . $doctor['docname'] . " (ID: " . $doctor['docid'] . ")\n";
            }
            
            $response = "For $specialty_name issues related to $matched_keyword, I recommend consulting a specialist. Here are some doctors in this field:\n\n" . $doctor_list . "\nTo book an appointment with one of these doctors:\n1. Go to 'Scheduled Sessions' in your dashboard\n2. Select the doctor you want to see\n3. Choose an available time slot\n4. Confirm your booking and proceed to payment\n\nWould you like more specific instructions on how to book?";
        } else {
            $response = "For $specialty_name issues, I recommend consulting a specialist. You can view all our doctors and their specialties in the 'All Doctors' section of your dashboard.\n\nTo book an appointment:\n1. Go to 'All Doctors' in your dashboard\n2. Browse or search for a doctor in the $specialty_name specialty\n3. Click on the doctor to view their available sessions\n4. Select a time slot that works for you\n5. Confirm your booking and proceed to payment";
        }
    }
    // Disease/symptom checking and treatment suggestions
    else {
        $detected_conditions = [];
        $condition_keywords = [
            // Existing symptoms
            'fever' => 'fever',
            'temperature' => 'fever',
            'headache' => 'headache',
            'migraine' => 'headache',
            'cough' => 'cough',
            'cold' => 'cold',
            'flu' => 'cold',
            'stomach' => 'stomach ache',
            'belly' => 'stomach ache',
            'nausea' => 'nausea',
            'vomit' => 'nausea',
            'back pain' => 'back pain',
            'joint pain' => 'joint pain',
            'sore throat' => 'sore throat',
            'throat' => 'sore throat',
            'runny nose' => 'runny nose',
            'nose' => 'runny nose',
            'allergy' => 'allergy',
            'rash' => 'rash',
            'skin' => 'rash',
            'diarrhea' => 'diarrhea',
            'constipation' => 'constipation',
            'insomnia' => 'insomnia',
            'sleep' => 'insomnia',
            'anxiety' => 'anxiety',
            'nervous' => 'anxiety',
            'depression' => 'depression',
            'sad' => 'depression',
            
            // New diseases
            'asthma' => 'asthma',
            'bronchitis' => 'bronchitis',
            'pneumonia' => 'pneumonia',
            'hypertension' => 'hypertension',
            'high blood pressure' => 'hypertension',
            'angina' => 'angina',
            'diabetes' => 'diabetes',
            'hypothyroidism' => 'hypothyroidism',
            'epilepsy' => 'epilepsy',
            'migraine' => 'migraine',
            'acid reflux' => 'acid reflux',
            'ulcer' => 'ulcer',
            'irritable bowel syndrome' => 'irritable bowel syndrome',
            'ibs' => 'irritable bowel syndrome',
            'arthritis' => 'arthritis',
            'osteoporosis' => 'osteoporosis',
            'malaria' => 'malaria',
            'typhoid' => 'typhoid',
            'uti' => 'urinary tract infection',
            'urinary tract infection' => 'urinary tract infection',
            'eczema' => 'eczema',
            'psoriasis' => 'psoriasis',
            'urticaria' => 'urticaria',
            'hives' => 'urticaria',
            'panic disorder' => 'panic disorder',
            'bipolar disorder' => 'bipolar disorder',
            'ocd' => 'obsessive compulsive disorder',
            'obsessive compulsive disorder' => 'obsessive compulsive disorder',
            'anemia' => 'anemia',
            'vitamin deficiency' => 'vitamin deficiency',
            'menstrual cramps' => 'menstrual cramps',
            'conjunctivitis' => 'conjunctivitis',
            'pink eye' => 'conjunctivitis',
            'sinusitis' => 'sinusitis',
            'kidney stones' => 'kidney stones',
            'stones' => 'kidney stones',
            'hepatitis' => 'hepatitis'
        ];
        
        // Detect conditions in the user's message
        foreach ($condition_keywords as $keyword => $condition) {
            if (strpos($lower_message, $keyword) !== false) {
                $detected_conditions[$condition] = true;
            }
        }
        
        // If conditions were detected, provide recommendations
        if (!empty($detected_conditions)) {
            $recommendations = [];
            $referral_needed = false;
            $specialty_ids = [];
            
            foreach (array_keys($detected_conditions) as $condition) {
                $medicine = getMedicineRecommendation($database, $condition);
                if ($medicine) {
                    $specialty_name = getSpecialtyName($database, $medicine['specialty_id']);
                    $med = $medicine['recommended_medicine'];
                    if ($condition == 'fever') {
                        $recommendations[] = "For fever, $med may help reduce temperature temporarily, but it does not treat the underlying cause. Fever can indicate a serious infection, especially in children, the elderly, or if it persists or is very high.";
                    } else {
                        $recommendations[] = "For $condition, general over-the-counter options may include $med, but this is not a prescription. Please consult a doctor or pharmacist before taking any medication.";
                    }
                    $specialty_ids[] = $medicine['specialty_id'];
                    
                    // Flag conditions that definitely need specialist care
                    if (in_array($medicine['specialty_id'], [5, 13, 14, 24, 29, 34, 35, 45, 54])) { 
                        $referral_needed = true;
                    }
                } else {
                    $recommendations[] = "For $condition, general care is recommended.";
                }
            }
            
            $response = "Please note: I can provide general health information, but I cannot diagnose or prescribe medication. ";
            $response .= implode(" ", $recommendations);
            
            // If specialist care is needed, suggest appropriate doctors
            if ($referral_needed && !empty($specialty_ids)) {
                // Get the most common specialty for the detected conditions
                $specialty_counts = array_count_values($specialty_ids);
                arsort($specialty_counts);
                $most_common_specialty = key($specialty_counts);
                
                $specialty_name = getSpecialtyName($database, $most_common_specialty);
                $doctors = getDoctorsBySpecialty($database, $most_common_specialty);
                
                if (!empty($doctors)) {
                    $doctor_name = $doctors[0]['docname'];
                    $doctor_id = $doctors[0]['docid'];
                    
                    // Check if the doctor has available schedules
                    $schedule = getDoctorSchedule($database, $doctor_id);
                    
                    $response .= "\n\nI recommend consulting with Dr. $doctor_name, a specialist in $specialty_name. ";
                    $response .= "Please only take medication as directed by a qualified healthcare professional. ";
                    
                    if ($schedule) {
                        $response .= "Dr. $doctor_name has an available session on " . $schedule['scheduledate'] . " at " . $schedule['scheduletime'] . ". ";
                        $response .= "To book this session:\n";
                        $response .= "1. <a href='booking.php?id=" . $schedule['scheduleid'] . "' style='color: #0066cc; text-decoration: underline;'>Click here to book directly with Dr. $doctor_name</a>\n";
                        $response .= "2. Review the appointment details\n";
                        $response .= "3. Confirm your booking and proceed to payment\n";
                        $response .= "4. Visit the hospital at the scheduled time";
                    } else {
                        $response .= "To book an appointment with Dr. $doctor_name:\n";
                        $response .= "1. <a href='schedule.php' style='color: #0066cc; text-decoration: underline;'>Click here to view all scheduled sessions</a>\n";
                        $response .= "2. Search for Dr. $doctor_name\n";
                        $response .= "3. Select an available time slot\n";
                        $response .= "4. Confirm your booking and proceed to payment\n";
                        $response .= "5. Visit the hospital at the scheduled time";
                    }
                } else {
                    $response .= " Since you have conditions that may require specialized care from a $specialty_name specialist, I recommend finding a specialist. You can view all our doctors in the 'All Doctors' section of your dashboard.";
                }
            } else {
                $response .= " This information is general guidance only and not a medical diagnosis. If symptoms persist or worsen, please consult with a healthcare professional.";
            }
        }
        // If no recognizable conditions or commands, provide help
        else {
            // Short messages likely need clarification
            if (strlen($user_message) <= 3) {
                $response = "I'm sorry, I didn't quite understand that. Could you please provide more details about your condition? For example, you can ask: 'I have asthma', 'What medicine for diabetes?', or 'Find a cardiologist'.";
            } else {
                $response = "I'm sorry, I didn't quite understand that. I'm Anna, your medical assistant. I can help you with common diseases, recommend medicines, or direct you to the right specialist. Please describe your condition or disease.";
            }
        }
    }
    
    // Save the conversation
    saveConversation($database, $userid, $user_message, $response);
}

// Load conversation history
$stmt = $database->prepare("SELECT message, response, message_type, timestamp FROM chatbot_conversations WHERE patient_id = ? ORDER BY timestamp ASC LIMIT 20");
$stmt->bind_param("i", $userid);
$stmt->execute();
$conversations = $stmt->get_result();

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
        
    <title>Anna - Medical Chatbot</title>
    <style>
        /* Hide the menu */
        .menu {
            display: none;
        }
        
        /* Full width for dash-body when menu is hidden */
        .dash-body {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .chat-container {
            max-width: 100%;
            margin: 0;
            padding: 20px;
        }
        
        /* Ensure back button is always visible */
        .back-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        /* Adjust chat container to accommodate fixed button */
        .chat-container-with-button {
            padding-top: 20px;
        }
        
        /* Enhanced beautification styles */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .chat-header {
            background: linear-gradient(135deg, var(--primarycolor), #0056b3);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 24px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .chat-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chat-header-icon {
            margin-right: 12px;
            font-size: 28px;
        }
        
        .chat-history {
            background: #f8f9fa;
            height: 520px;
            overflow-y: auto;
            padding: 24px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .welcome-message {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            max-width: 600px;
            margin: 20px auto;
            padding: 36px;
        }
        
        .message {
            border-radius: 20px;
            padding: 16px 20px;
            margin-bottom: 22px;
            max-width: 85%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .user-message {
            background: linear-gradient(135deg, var(--primarycolor), #0066cc);
            color: white;
            margin-left: auto;
            text-align: right;
            border-bottom-right-radius: 6px;
        }
        
        .bot-message {
            background: white;
            color: #333;
            border: 1px solid #eaeaea;
            border-bottom-left-radius: 6px;
        }
        
        .message-sender {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .chat-input-container {
            background: white;
            border-radius: 0 0 16px 16px;
            padding: 24px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.03);
        }
        
        #messageInput {
            flex: 1;
            padding: 16px 22px;
            border: 2px solid #eaeaea;
            border-radius: 32px;
            outline: none;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fafafa;
        }
        
        .chat-input button {
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--primarycolor), #0066cc);
            color: white;
            border: none;
            border-radius: 32px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(10, 118, 216, 0.25);
        }
        
        .chat-input button:hover {
            background: linear-gradient(135deg, #0066cc, #0056b3);
            box-shadow: 0 6px 16px rgba(10, 118, 216, 0.35);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php // Menu is intentionally omitted to provide full-screen view ?>
    <div class="back-button-container">
        <a href="index.php" style="text-decoration: none;">
            <button class="btn-primary btn" style="padding: 10px 20px;">← Back to Dashboard</button>
        </a>
    </div>
    <div class="dash-body" style="margin-top: 15px">
        <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;">
            <tr>
                <td colspan="4">
                    <div class="chat-container chat-container-with-button">
                        <div class="chat-header">
                            <h2><span class="chat-header-icon">💬</span> Anna - Your Medical Assistant</h2>
                            <p>Describe your disease or symptoms for personalized recommendations</p>
                        </div>
                        
                        <div class="chat-history" id="chatHistory">
                            <?php if ($conversations->num_rows == 0): ?>
                                <div class="welcome-message">
                                    <h3>Welcome, <?php echo htmlspecialchars($username); ?>!</h3>
                                    <p>Hello! I'm Anna, your medical assistant.</p>
                                    <p>I can help you with:</p>
                                    <ul>
                                        <li>Disease diagnosis and medicine recommendations</li>
                                        <li>Finding the right specialist for your condition</li>
                                        <li>Direct booking links to see recommended doctors</li>
                                        <li>Guidance on taking prescribed medicines</li>
                                    </ul>
                                    <p>Try asking: "I have diabetes" or "What medicine for asthma?" or "Find a cardiologist"</p>
                                </div>
                            <?php else: ?>
                                <?php while ($conv = $conversations->fetch_assoc()): ?>
                                    <?php if ($conv['message_type'] == 'user'): ?>
                                        <div class="message user-message">
                                            <div class="message-sender"><span class="user-avatar">U</span>You</div>
                                            <div><?php echo htmlspecialchars($conv['message']); ?></div>
                                            <div class="message-time"><?php echo date('g:i A', strtotime($conv['timestamp'])); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="message bot-message">
                                            <div class="message-sender"><span class="bot-avatar">A</span>Anna</div>
                                            <div><?php echo nl2br(htmlspecialchars($conv['response'])); ?></div>
                                            <div class="message-time"><?php echo date('g:i A', strtotime($conv['timestamp'])); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-input-container">
                            <form method="post" class="chat-input" id="chatForm">
                                <input type="text" name="message" id="messageInput" placeholder="Describe your disease, symptoms or ask a question..." autocomplete="off" required>
                                <button type="submit">Send</button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <script>
        // Scroll to bottom of chat history
        document.addEventListener('DOMContentLoaded', function() {
            var chatHistory = document.getElementById('chatHistory');
            chatHistory.scrollTop = chatHistory.scrollHeight;
            
            // Focus on input field
            document.getElementById('messageInput').focus();
        });
        
        // Show typing indicator when submitting form
        document.getElementById('chatForm').addEventListener('submit', function() {
            // Create typing indicator
            var typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            typingIndicator.id = 'typingIndicator';
            typingIndicator.innerHTML = '<span></span><span></span><span></span>';
            document.querySelector('.chat-history').appendChild(typingIndicator);
            typingIndicator.style.display = 'block';
            
            // Scroll to bottom
            var chatHistory = document.getElementById('chatHistory');
            chatHistory.scrollTop = chatHistory.scrollHeight;
            
            // Remove typing indicator after 1 second
            setTimeout(function() {
                if (document.getElementById('typingIndicator')) {
                    document.getElementById('typingIndicator').remove();
                }
                chatHistory.scrollTop = chatHistory.scrollHeight;
            }, 1000);
        });
        
        // Allow sending message with Enter key
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').submit();
            }
        });
        
        // Animation for new messages
        document.getElementById('chatForm').addEventListener('submit', function() {
            setTimeout(function() {
                var messages = document.querySelectorAll('.message');
                var lastMessage = messages[messages.length - 1];
                if (lastMessage) {
                    lastMessage.style.opacity = '0';
                    lastMessage.style.transform = 'translateY(10px)';
                    setTimeout(function() {
                        lastMessage.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        lastMessage.style.opacity = '1';
                        lastMessage.style.transform = 'translateY(0)';
                    }, 10);
                }
            }, 100);
        });
    </script>
</body>
</html>