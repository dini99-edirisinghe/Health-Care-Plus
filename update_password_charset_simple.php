<?php
$mysqli = new mysqli('localhost', 'root', '', 'edoc');
if ($mysqli->connect_error) {
    file_put_contents(__DIR__ . '/charset_check.txt', 'connect_error:' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}
file_put_contents(__DIR__ . '/charset_check.txt', 'connected' . PHP_EOL);
$mysqli->close();
