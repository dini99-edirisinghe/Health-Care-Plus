<?php
$mysqli = new mysqli('localhost', 'root', '', 'edoc');
if ($mysqli->connect_error) {
    file_put_contents(__DIR__ . '/update_password_charset.log', 'CONNECT_ERR: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

$tables = ['admin', 'doctor', 'patient'];
$log = [];
foreach ($tables as $table) {
    $res = $mysqli->query("SHOW COLUMNS FROM `$table`");
    if (!$res) {
        $log[] = 'SHOW_ERR ' . $table . ' ' . $mysqli->error;
        continue;
    }
    while ($col = $res->fetch_assoc()) {
        $field = $col['Field'];
        $low = strtolower($field);
        if (strpos($low, 'password') !== false || $low === 'pwd' || $low === 'pass') {
            $sql = "ALTER TABLE `$table` MODIFY `$field` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL";
            if ($mysqli->query($sql)) {
                $log[] = "UPDATED $table.$field";
            } else {
                $log[] = 'ALTER_ERR ' . $table . '.' . $field . ' ' . $mysqli->error;
            }
        }
    }
    $res->close();
}
$mysqli->close();
file_put_contents(__DIR__ . '/update_password_charset.log', implode(PHP_EOL, $log) . PHP_EOL);
