<?php
$mysqli = new mysqli('localhost', 'root', '', 'edoc');
if ($mysqli->connect_error) {
    echo 'CONNECT_ERR: ' . $mysqli->connect_error . PHP_EOL;
    exit(1);
}
$query = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='edoc' AND TABLE_NAME='pashan'";
$result = $mysqli->query($query);
if (!$result) {
    echo 'QUERY_ERR: ' . $mysqli->error . PHP_EOL;
    exit(1);
}
while ($row = $result->fetch_assoc()) {
    echo $row['TABLE_NAME'] . '|' . $row['COLUMN_NAME'] . '|' . $row['DATA_TYPE'] . PHP_EOL;
}
$result->close();
$mysqli->close();
