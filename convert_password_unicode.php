<?php
$mysqli = new mysqli('localhost', 'root', '', 'edoc');
if ($mysqli->connect_error) {
    file_put_contents(__DIR__ . '/convert_password_unicode.log', 'CONNECT_ERR: ' . $mysqli->connect_error . PHP_EOL);
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
            $alter = "ALTER TABLE `$table` MODIFY `$field` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL";
            if ($mysqli->query($alter)) {
                $log[] = "ALTER_OK $table.$field";
            } else {
                $log[] = 'ALTER_ERR ' . $table . '.' . $field . ' ' . $mysqli->error;
            }

            $convert = "UPDATE `$table` SET `$field` = CONVERT($field USING utf8mb4) WHERE `$field` IS NOT NULL";
            if ($mysqli->query($convert)) {
                $log[] = "CONVERT_OK $table.$field";
            } else {
                $log[] = 'CONVERT_ERR ' . $table . '.' . $field . ' ' . $mysqli->error;
            }
        }
    }
    $res->close();
}
$mysqli->close();
file_put_contents(__DIR__ . '/convert_password_unicode.log', implode(PHP_EOL, $log) . PHP_EOL);
