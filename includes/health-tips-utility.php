<?php
// Function to get recent health tips
function getRecentHealthTips($database, $limit = 5) {
    $sql = "SELECT ht.*, a.aemail as admin_name FROM health_tips ht LEFT JOIN admin a ON ht.admin_email = a.aemail ORDER BY ht.created_at DESC LIMIT ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tips = [];
    while ($tip = $result->fetch_assoc()) {
        $tips[] = $tip;
    }
    
    $stmt->close();
    return $tips;
}

// Function to get all health tips
function getAllHealthTips($database) {
    $sql = "SELECT ht.*, a.aemail as admin_name FROM health_tips ht LEFT JOIN admin a ON ht.admin_email = a.aemail ORDER BY ht.created_at DESC";
    $result = $database->query($sql);
    
    $tips = [];
    while ($tip = $result->fetch_assoc()) {
        $tips[] = $tip;
    }
    
    return $tips;
}

// Function to get a specific health tip by ID
function getHealthTipById($database, $tip_id) {
    $sql = "SELECT ht.*, a.aemail as admin_name FROM health_tips ht LEFT JOIN admin a ON ht.admin_email = a.aemail WHERE ht.tip_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $tip_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tip = $result->fetch_assoc();
    $stmt->close();
    
    return $tip;
}

// Function to format the health tip content for display
function formatHealthTipContent($content) {
    return nl2br(htmlspecialchars($content));
}
?>