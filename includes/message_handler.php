<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function setMessage($message, $type = 'info', $title = '') {
    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = array();
    }
    
    $_SESSION['messages'][] = array(
        'message' => $message,
        'type' => $type,
        'title' => $title
    );
}

function getMessages() {
    $messages = array();
    if (isset($_SESSION['messages'])) {
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
    }
    return $messages;
}

function displayMessages() {
    $messages = getMessages();
    
    if (empty($messages)) {
        return;
    }
    
    echo '<div class="messages-container">';
    
    foreach ($messages as $msg) {
        $type = htmlspecialchars($msg['type']);
        $title = !empty($msg['title']) ? '<strong>' . htmlspecialchars($msg['title']) . '</strong><br>' : '';
        $message = htmlspecialchars($msg['message']);

        $icon = '';
        $colorClass = '';
        
        switch ($type) {
            case 'success':
                $icon = '✓';
                $colorClass = 'message-success';
                break;
            case 'error':
                $icon = '✗';
                $colorClass = 'message-error';
                break;
            case 'warning':
                $icon = '⚠';
                $colorClass = 'message-warning';
                break;
            case 'info':
            default:
                $icon = 'ℹ';
                $colorClass = 'message-info';
                break;
        }
        
        echo '<div class="message-box ' . $colorClass . '">';
        echo '<span class="message-icon">' . $icon . '</span>';
        echo '<div class="message-content">';
        if ($title) {
            echo $title;
        }
        echo $message;
        echo '</div>';
        echo '<span class="message-close" onclick="this.parentElement.style.display=\'none\'">×</span>';
        echo '</div>';
    }
    
    echo '</div>';
}

function showMessage($message, $type = 'info') {
    $messages = array(
        array(
            'message' => $message,
            'type' => $type,
            'title' => ''
        )
    );
    
    echo '<div class="messages-container">';
    
    foreach ($messages as $msg) {
        $type = htmlspecialchars($msg['type']);
        $message = htmlspecialchars($msg['message']);

        $icon = '';
        $colorClass = '';
        
        switch ($type) {
            case 'success':
                $icon = '✓';
                $colorClass = 'message-success';
                break;
            case 'error':
                $icon = '✗';
                $colorClass = 'message-error';
                break;
            case 'warning':
                $icon = '⚠';
                $colorClass = 'message-warning';
                break;
            case 'info':
            default:
                $icon = 'ℹ';
                $colorClass = 'message-info';
                break;
        }
        
        echo '<div class="message-box ' . $colorClass . '">';
        echo '<span class="message-icon">' . $icon . '</span>';
        echo '<div class="message-content">' . $message . '</div>';
        echo '<span class="message-close" onclick="this.parentElement.style.display=\'none\'">×</span>';
        echo '</div>';
    }
    
    echo '</div>';
}
?>