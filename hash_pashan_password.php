<?php
include 'connection.php';

function output($message) {
    echo $message . "<br>\n";
}

$table = 'pashan';
$searchValue = 'Pashan';

output('<h1>Protect Pashan Password</h1>');

$result = $database->query("SHOW TABLES LIKE '" . $database->real_escape_string($table) . "'");
if (!$result) {
    output('Database error: ' . htmlspecialchars($database->error));
    exit;
}
if ($result->num_rows === 0) {
    output('Table "' . htmlspecialchars($table) . '" does not exist in the edoc database.');
    exit;
}

$columns = [];
$columnsResult = $database->query("SHOW COLUMNS FROM `" . $database->real_escape_string($table) . "`");
if (!$columnsResult) {
    output('Failed to inspect columns for table "' . htmlspecialchars($table) . '": ' . htmlspecialchars($database->error));
    exit;
}

$passwordCols = [];
$nameCols = [];
$allCols = [];
while ($col = $columnsResult->fetch_assoc()) {
    $field = $col['Field'];
    $allCols[] = $field;
    $low = strtolower($field);
    if (strpos($low, 'password') !== false || $low === 'pwd' || $low === 'pass') {
        $passwordCols[] = $field;
    }
    if (strpos($low, 'name') !== false || strpos($low, 'user') !== false || strpos($low, 'email') !== false) {
        $nameCols[] = $field;
    }
}
$columnsResult->close();

if (empty($passwordCols)) {
    output('No password-like column found in table "' . htmlspecialchars($table) . '".');
    output('Existing columns: ' . htmlspecialchars(implode(', ', $allCols)));
    exit;
}

$whereClauses = [];
foreach ($nameCols as $col) {
    $whereClauses[] = "LOWER(`" . $database->real_escape_string($col) . "`) LIKE '%" . $database->real_escape_string(strtolower($searchValue)) . "%'";
}

if (empty($whereClauses)) {
    foreach ($allCols as $col) {
        $whereClauses[] = "LOWER(`" . $database->real_escape_string($col) . "`) LIKE '%" . $database->real_escape_string(strtolower($searchValue)) . "%'";
    }
}

$query = "SELECT * FROM `" . $database->real_escape_string($table) . "` WHERE " . implode(' OR ', $whereClauses) . " LIMIT 10";
$output = $database->query($query);
if (!$output) {
    output('Failed to search for Pashan row: ' . htmlspecialchars($database->error));
    exit;
}

if ($output->num_rows === 0) {
    output('No rows matching "' . htmlspecialchars($searchValue) . '" were found in table "' . htmlspecialchars($table) . '".');
    exit;
}

$primaryKeys = [];
$keyResult = $database->query("SHOW KEYS FROM `" . $database->real_escape_string($table) . "` WHERE Key_name = 'PRIMARY'");
if ($keyResult) {
    while ($key = $keyResult->fetch_assoc()) {
        $primaryKeys[] = $key['Column_name'];
    }
    $keyResult->close();
}

if (empty($primaryKeys)) {
    output('Warning: no primary key detected on table "' . htmlspecialchars($table) . '". Row updates will use all column values as the WHERE clause.');
}

$updatedCount = 0;
while ($row = $output->fetch_assoc()) {
    $rowIdentifier = [];
    $whereParts = [];
    $whereValues = [];
    if (!empty($primaryKeys)) {
        foreach ($primaryKeys as $pk) {
            $whereParts[] = "`" . $database->real_escape_string($pk) . "` = ?";
            $whereValues[] = $row[$pk];
            $rowIdentifier[] = $pk . '=' . $row[$pk];
        }
    } else {
        foreach ($allCols as $col) {
            $whereParts[] = "`" . $database->real_escape_string($col) . "` = ?";
            $whereValues[] = $row[$col];
        }
        $rowIdentifier[] = 'row=' . json_encode($row);
    }

    foreach ($passwordCols as $passwordCol) {
        if (!array_key_exists($passwordCol, $row)) {
            continue;
        }
        $current = $row[$passwordCol];
        if ($current === null || $current === '') {
            output('Skipping empty password field `' . htmlspecialchars($passwordCol) . '` for row ' . htmlspecialchars(implode(', ', $rowIdentifier)) . '.');
            continue;
        }
        if (preg_match('/^\$2[ay]\$/', $current) || preg_match('/^\$argon2/', $current)) {
            output('Password in column `' . htmlspecialchars($passwordCol) . '` for row ' . htmlspecialchars(implode(', ', $rowIdentifier)) . ' is already hashed.');
            continue;
        }

        $hashed = password_hash($current, PASSWORD_DEFAULT);
        $updateSql = "UPDATE `" . $database->real_escape_string($table) . "` SET `" . $database->real_escape_string($passwordCol) . "` = ? WHERE " . implode(' AND ', $whereParts) . " LIMIT 1";
        $stmt = $database->prepare($updateSql);
        if (!$stmt) {
            output('Failed to prepare update for row ' . htmlspecialchars(implode(', ', $rowIdentifier)) . ': ' . htmlspecialchars($database->error));
            continue;
        }

        $types = str_repeat('s', count($whereValues) + 1);
        $params = array_merge([$hashed], $whereValues);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            output('Hashed password in column `' . htmlspecialchars($passwordCol) . '` for row ' . htmlspecialchars(implode(', ', $rowIdentifier)) . '.');
            $updatedCount++;
        } else {
            output('Failed to update row ' . htmlspecialchars(implode(', ', $rowIdentifier)) . ': ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
    }
}

output('Completed. Updated passwords: ' . $updatedCount);
$database->close();
