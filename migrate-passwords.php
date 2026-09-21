<?php
/**
 * One-Time Password Migration Script
 * 
 * This script hashes all plain text passwords in the database (patient, doctor, admin tables).
 * Run this script ONCE from your browser, then DELETE this file immediately after.
 * 
 * DO NOT leave this file on your server after migration.
 */

include("connection.php");

echo "<html><head><title>Password Migration</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:40px auto;padding:20px;background:#f5f5f5;}";
echo ".box{background:white;padding:20px;border-radius:8px;margin:15px 0;box-shadow:0 2px 8px rgba(0,0,0,0.1);}";
echo ".success{color:#28a745;font-weight:bold;} .skip{color:#6c757d;} .error{color:#dc3545;}";
echo "h1{color:#0A76D8;} h2{color:#333;border-bottom:2px solid #0A76D8;padding-bottom:8px;}";
echo "</style></head><body>";
echo "<h1>Password Migration Tool</h1>";
echo "<div class='box'><p>This tool hashes all plain text passwords in the database using bcrypt.</p></div>";

$total_migrated = 0;
$total_skipped = 0;

// --- PATIENT TABLE ---
echo "<div class='box'><h2>Patients</h2>";
$stmt = $database->prepare("SELECT pid, pemail, ppassword FROM patient");
$stmt->execute();
$result = $stmt->get_result();
$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}
$stmt->close();

$patient_migrated = 0;
$patient_skipped = 0;
foreach ($patients as $p) {
    $pwd = $p['ppassword'];
    if (!empty($pwd) && substr($pwd, 0, 4) !== '$2y$') {
        $hashed = password_hash($pwd, PASSWORD_DEFAULT);
        $update_stmt = $database->prepare("UPDATE patient SET ppassword=? WHERE pid=?");
        $update_stmt->bind_param("si", $hashed, $p['pid']);
        $update_stmt->execute();
        $update_stmt->close();
        echo "<p class='success'>✓ Hashed password for: " . htmlspecialchars($p['pemail']) . "</p>";
        $patient_migrated++;
    } else {
        echo "<p class='skip'>— Already hashed or empty: " . htmlspecialchars($p['pemail']) . "</p>";
        $patient_skipped++;
    }
}
echo "<p><strong>Patients:</strong> $patient_migrated hashed, $patient_skipped skipped</p></div>";
$total_migrated += $patient_migrated;
$total_skipped += $patient_skipped;

// --- DOCTOR TABLE ---
echo "<div class='box'><h2>Doctors</h2>";
$stmt = $database->prepare("SELECT docid, docemail, docpassword FROM doctor");
$stmt->execute();
$result = $stmt->get_result();
$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}
$stmt->close();

$doctor_migrated = 0;
$doctor_skipped = 0;
foreach ($doctors as $d) {
    $pwd = $d['docpassword'];
    if (!empty($pwd) && substr($pwd, 0, 4) !== '$2y$') {
        $hashed = password_hash($pwd, PASSWORD_DEFAULT);
        $update_stmt = $database->prepare("UPDATE doctor SET docpassword=? WHERE docid=?");
        $update_stmt->bind_param("si", $hashed, $d['docid']);
        $update_stmt->execute();
        $update_stmt->close();
        echo "<p class='success'>✓ Hashed password for: " . htmlspecialchars($d['docemail']) . "</p>";
        $doctor_migrated++;
    } else {
        echo "<p class='skip'>— Already hashed or empty: " . htmlspecialchars($d['docemail']) . "</p>";
        $doctor_skipped++;
    }
}
echo "<p><strong>Doctors:</strong> $doctor_migrated hashed, $doctor_skipped skipped</p></div>";
$total_migrated += $doctor_migrated;
$total_skipped += $doctor_skipped;

// --- ADMIN TABLE ---
echo "<div class='box'><h2>Admins</h2>";
$stmt = $database->prepare("SELECT aemail, apassword FROM admin");
$stmt->execute();
$result = $stmt->get_result();
$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
$stmt->close();

$admin_migrated = 0;
$admin_skipped = 0;
foreach ($admins as $a) {
    $pwd = $a['apassword'];
    if (!empty($pwd) && substr($pwd, 0, 4) !== '$2y$') {
        $hashed = password_hash($pwd, PASSWORD_DEFAULT);
        $update_stmt = $database->prepare("UPDATE admin SET apassword=? WHERE aemail=?");
        $update_stmt->bind_param("ss", $hashed, $a['aemail']);
        $update_stmt->execute();
        $update_stmt->close();
        echo "<p class='success'>✓ Hashed password for: " . htmlspecialchars($a['aemail']) . "</p>";
        $admin_migrated++;
    } else {
        echo "<p class='skip'>— Already hashed or empty: " . htmlspecialchars($a['aemail']) . "</p>";
        $admin_skipped++;
    }
}
echo "<p><strong>Admins:</strong> $admin_migrated hashed, $admin_skipped skipped</p></div>";
$total_migrated += $admin_migrated;
$total_skipped += $admin_skipped;

// --- SUMMARY ---
echo "<div class='box' style='border-left:4px solid #0A76D8;'>";
echo "<h2>Migration Complete</h2>";
echo "<p><strong>Total passwords hashed:</strong> $total_migrated</p>";
echo "<p><strong>Total already hashed (skipped):</strong> $total_skipped</p>";
echo "</div>";

echo "<div class='box' style='background:#fff3cd;border-left:4px solid #ffc107;'>";
echo "<h2>⚠ Important: Delete This File</h2>";
echo "<p style='color:#856404;'><strong>Please delete <code>migrate-passwords.php</code> from your server immediately after running it.</strong></p>";
echo "<p>This file should not remain on your server as it can re-hash passwords unnecessarily.</p>";
echo "</div>";

echo "</body></html>";
?>
