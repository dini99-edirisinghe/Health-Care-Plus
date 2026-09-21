<?php
include("connection.php");

echo "<h1>System Maintenance Tools</h1>";

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'check_passwords':
            checkPasswords();
            break;
        case 'hash_passwords':
            hashPasswords();
            break;
        case 'test_db':
            testDatabase();
            break;
        case 'install_chatbot':
            installChatbot();
            break;
        case 'add_profile_columns':
            addProfileColumns();
            break;
        case 'remove_comments':
            removeComments();
            break;
        case 'populate_database':
            populateDatabase();
            break;
        case 'anonymize_data':
            anonymizeDatabase();
            break;
        case 'optimize_performance':
            optimizePerformance();
            break;
        default:
            showMenu();
    }
} else {
    showMenu();
}

function showMenu() {
    echo "<h2>Maintenance Options</h2>";
    echo "<ul>";
    echo "<li><a href='?action=check_passwords'>Check Password Security</a></li>";
    echo "<li><a href='?action=hash_passwords'>Hash Plain Text Passwords</a></li>";
    echo "<li><a href='?action=test_db'>Test Database Connection</a></li>";
    echo "<li><a href='?action=install_chatbot'>Install Chatbot Tables</a></li>";
    echo "<li><a href='?action=add_profile_columns'>Add Profile Picture Columns</a></li>";
    echo "<li><a href='?action=remove_comments'>Remove Comments from Files</a></li>";
    echo "<li><a href='?action=populate_database'>Populate Database with Sample Data</a></li>";
    echo "<li><a href='?action=anonymize_data'>Anonymize All Database Content</a></li>";
    echo "<li><a href='?action=optimize_performance'>Optimize Database Performance</a></li>";
    echo "</ul>";
}

function checkPasswords() {
    global $database;
    echo "<h2>Password Security Check</h2>";

    $tables = [
        ["patient", "pid", "pemail", "ppassword", "Patients"],
        ["admin", "aemail", "aemail", "apassword", "Admins"],
        ["doctor", "docid", "docemail", "docpassword", "Doctors"]
    ];

    foreach ($tables as $tableInfo) {
        list($table, $idField, $emailField, $passwordField, $title) = $tableInfo;

        $result = $database->query("SELECT `$emailField` AS email, `$passwordField` AS password FROM `$table`");
        if ($result && $result->num_rows > 0) {
            echo "<h3>" . htmlspecialchars($title) . ":</h3>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>Email: " . htmlspecialchars($row['email']) . " | Password: [HIDDEN]</li>";
            }
            echo "</ul>";
        }
    }
}

function hashPasswords() {
    global $database;
    echo "<h2>Password Hashing Process</h2>";

    $tables = [
        ["patient", "pid", "pemail", "ppassword", "patient"],
        ["admin", "aemail", "aemail", "apassword", "admin"],
        ["doctor", "docid", "docemail", "docpassword", "doctor"]
    ];

    foreach ($tables as $tableInfo) {
        list($table, $idField, $emailField, $passwordField, $label) = $tableInfo;

        $sql = "SELECT `$idField` AS id, `$emailField` AS email, `$passwordField` AS password FROM `$table`";
        $result = $database->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $email = $row['email'];
                $plainPassword = $row['password'];

                if (substr($plainPassword, 0, 4) !== '$2y$') {
                    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
                    $stmt = $database->prepare("UPDATE `$table` SET `$passwordField`=? WHERE `$idField`=?");
                    if ($stmt) {
                        $stmt->bind_param("si", $hashedPassword, $id);
                        if ($stmt->execute()) {
                            echo "Hashed password for " . htmlspecialchars($label) . ": " . htmlspecialchars($email) . "<br>";
                        } else {
                            echo "Error hashing password for " . htmlspecialchars($label) . ": " . htmlspecialchars($email) . "<br>";
                        }
                        $stmt->close();
                    } else {
                        echo "Failed to prepare update for " . htmlspecialchars($label) . " passwords: " . htmlspecialchars($database->error) . "<br>";
                    }
                } else {
                    echo "Password for " . htmlspecialchars($label) . " " . htmlspecialchars($email) . " is already hashed.<br>";
                }
            }
        }
    }

    echo "Password hashing process completed.";
}

function testDatabase() {
    global $database;
    echo "<h2>Database Connection Test</h2>";
    
    if ($database->connect_error) {
        echo "Connection failed: " . $database->connect_error;
    } else {
        echo "Connected successfully<br>";
        
        $result = $database->query("SHOW TABLES");
        if ($result) {
            echo "<h3>Tables in database:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        }
        
        $result = $database->query("SELECT * FROM webuser");
        if ($result) {
            echo "<h3>Web Users:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>Email: " . $row['email'] . " | Type: " . $row['usertype'] . "</li>";
            }
            echo "</ul>";
        }
    }
}

function installChatbot() {
    global $database;
    echo "<h2>Installing Chatbot Tables</h2>";
    
    $sql = file_get_contents('chatbot_tables.sql');
    
    if ($database->multi_query($sql)) {
        do {
            if ($result = $database->store_result()) {
                $result->free();
            }
        } while ($database->more_results() && $database->next_result());
        
        echo "Chatbot tables installed successfully!";
    } else {
        echo "Error installing chatbot tables: " . $database->error;
    }
}

function addProfileColumns() {
    global $database;
    echo "<h2>Adding Profile Picture Columns</h2>";
    
    $sql_patient = "ALTER TABLE patient ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL";
    
    if ($database->query($sql_patient) === TRUE) {
        echo "Profile picture column added to patient table successfully<br>";
    } else {
        echo "Error adding profile picture column to patient table: " . $database->error . "<br>";
    }
    
    $sql_doctor = "ALTER TABLE doctor ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL";
    
    if ($database->query($sql_doctor) === TRUE) {
        echo "Profile picture column added to doctor table successfully<br>";
    } else {
        echo "Error adding profile picture column to doctor table: " . $database->error . "<br>";
    }
}

function removeComments() {
    echo "<h2>Removing Comments from Files</h2>";
    
    function removePhpComments($content) {
        // Remove single-line comments
        $content = preg_replace('~//.*(?=\n|$)~', '', $content);
        // Remove multi-line comments
        $content = preg_replace('~/\*.*?\*/~s', '', $content);
        return $content;
    }
    
    function removeSqlComments($content) {
        // Remove single-line comments
        $content = preg_replace('~--.*(?=\n|$)~', '', $content);
        // Remove multi-line comments
        $content = preg_replace('~/\*.*?\*/~s', '', $content);
        return $content;
    }
    
    function removeCssComments($content) {
        // Remove multi-line comments
        $content = preg_replace('~/\*.*?\*/~s', '', $content);
        return $content;
    }
    
    function processFile($filePath) {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [false, 0, 0];
        }
        
        $originalLines = substr_count($content, "\n") + 1;
        
        $cleanedContent = '';
        if (preg_match('/\.php$/i', $filePath)) {
            $cleanedContent = removePhpComments($content);
        } elseif (preg_match('/\.sql$/i', $filePath)) {
            $cleanedContent = removeSqlComments($content);
        } elseif (preg_match('/\.css$/i', $filePath)) {
            $cleanedContent = removeCssComments($content);
        } else {
            return [false, 0, 0];
        }
        
        $result = file_put_contents($filePath, $cleanedContent);
        
        if ($result === false) {
            return [false, 0, 0];
        }
        
        $cleanedLines = substr_count($cleanedContent, "\n") + 1;
        $linesRemoved = $originalLines - $cleanedLines;
        
        return [true, $originalLines, $cleanedLines];
    }
    
    $baseDir = __DIR__;
    $extensions = ['php', 'sql', 'css'];
    
    $totalFiles = 0;
    $processedFiles = 0;
    $totalLinesRemoved = 0;
    
    echo "Starting comment removal process...<br>";
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $fileInfo) {
        $filePath = $fileInfo->getPathname();
        
        // Skip backup directories
        if (strpos($filePath, 'backup') !== false) {
            continue;
        }
        
        $extension = strtolower($fileInfo->getExtension());
        if (in_array($extension, $extensions)) {
            $totalFiles++;
            list($success, $origLines, $cleanLines) = processFile($filePath);
            
            if ($success) {
                $processedFiles++;
                $linesRemoved = $origLines - $cleanedLines;
                $totalLinesRemoved += $linesRemoved;
                echo "Processed: $filePath ($linesRemoved lines removed)<br>";
            } else {
                echo "Failed to process: $filePath<br>";
            }
        }
    }
    
    echo "<br>Summary:<br>";
    echo "Total files found: $totalFiles<br>";
    echo "Files processed: $processedFiles<br>";
    echo "Total lines removed: $totalLinesRemoved<br>";
}

function populateDatabase() {
    global $database;
    echo "<h2>Populating Database with Sample Data</h2>";
    
    $patient_names = [
        "Kamal Perera", "Nimal Rajapaksha", "Sunil Fernando", "Ruwan Silva", "Anura Kumara",
        "Chaminda Bandara", "Dinesh Gunawardena", "Pradeep Jayasinghe", "Thilakarathne Liyanage", "Roshan Peiris",
        "Mahesh Wijesinghe", "Saman Kumara", "Upul Tharanga", "Asanka Gurusinha", "Arjuna Ranatunga",
        "Muttiah Muralitharan", "Kumar Sangakkara", "Mahela Jayawardene", "Tillakaratne Dilshan", "Angelo Mathews",
        "Lasith Malinga", "Thisara Perera", "Dushmantha Chameera", "Lahiru Thirimanne", "Dimuth Karunaratne",
        "Kusal Mendis", "Niroshan Dickwella", "Dhananjaya de Silva", "Suranga Lakmal", "Rangana Herath",
        "Jeevan Mendis", "Thilan Samaraweera", "Kaushalya Weeraratne", "Malinda Warnakulasuriya", "Shantha Kalavitigoda",
        "Rajitha Hettiarachchi", "Chinthaka Jayasinghe", "Nuwan Kulasekara", "Rangana Prasanna", "Tharindu Kaushal",
        "Milinda Siriwardana", "Kaveen Bandara", "Dinesh Chandimal", "Akila Dananjaya", "Jeffrey Vandersay",
        "Lakshan Sandakan", "Binura Fernando", "Kasun Rajitha", "Oshada Fernando", "Pathum Nissanka"
    ];
    
    $doctor_names = [
        "Dr. Ananda Rajapaksha", "Dr. Sunil Fernando", "Dr. Ruwan Perera", "Dr. Kamal Silva", "Dr. Nimal Kumara",
        "Dr. Chaminda Bandara", "Dr. Dinesh Gunawardena", "Dr. Pradeep Jayasinghe", "Dr. Thilak Liyanage", "Dr. Roshan Peiris",
        "Dr. Mahesh Wijesinghe", "Dr. Saman Kumara", "Dr. Upul Tharanga", "Dr. Asanka Gurusinha", "Dr. Arjuna Ranatunga",
        "Dr. Muttiah Muralitharan", "Dr. Kumar Sangakkara", "Dr. Mahela Jayawardene", "Dr. Tillakaratne Dilshan", "Dr. Angelo Mathews"
    ];
    
    $addresses = [
        "Colombo", "Kandy", "Galle", "Jaffna", "Matara", "Trincomalee", "Batticaloa", "Ratnapura", "Badulla", "Anuradhapura",
        "Polonnaruwa", "Dambulla", "Nuwara Eliya", "Kegalle", "Puttalam", "Kurunegala", "Kalutara", "Hambantota", "Matale", "Vavuniya"
    ];
    
    $specialties = range(1, 56); 
    
    echo "Checking existing records...<br>";
    
    $patientCount = $database->query("SELECT COUNT(*) FROM patient")->fetch_row()[0];
    $doctorCount = $database->query("SELECT COUNT(*) FROM doctor")->fetch_row()[0];
    
    echo "Existing patients: $patientCount<br>";
    echo "Existing doctors: $doctorCount<br>";
    
    $patientsToInsert = max(0, 50 - $patientCount);
    echo "Need to insert $patientsToInsert more patients<br>";
    
    for ($i = 0; $i < $patientsToInsert; $i++) {
        $name = $patient_names[$i];
        $email = strtolower(str_replace(' ', '.', $name)) . "@pensioner.lk";
        $password = "pass" . ($i + 100);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $address = $addresses[array_rand($addresses)];
        $nic = "NIC" . str_pad($i + 1000, 6, '0', STR_PAD_LEFT);
        $dob = date('Y-m-d', strtotime('-' . rand(60, 85) . ' years')); 
        $phone = "07" . rand(10000000, 99999999);
        
        try {
            // Insert patient
            $stmt = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $email, $name, $hashed_password, $address, $nic, $dob, $phone);
            
            if ($stmt->execute()) {
                // Insert webuser
                $stmt2 = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'p')");
                $stmt2->bind_param("s", $email);
                $stmt2->execute();
                $stmt2->close();
                
                echo "Inserted patient: $name<br>";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            if ($database->errno == 1062) { // Duplicate entry
                echo "Patient $name already exists<br>";
            } else {
                echo "Error inserting patient $name: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    $doctorsToInsert = max(0, 20 - $doctorCount);
    echo "Need to insert $doctorsToInsert more doctors<br>";
    
    for ($i = 0; $i < $doctorsToInsert; $i++) {
        $name = $doctor_names[$i];
        $email = strtolower(str_replace(' ', '.', str_replace('Dr. ', '', $name))) . "@doctor.lk";
        $password = "docpass" . ($i + 100);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $nic = "DOC" . str_pad($i + 1000, 6, '0', STR_PAD_LEFT);
        $phone = "07" . rand(10000000, 99999999);
        $specialty = $specialties[array_rand($specialties)];
        $fee = rand(1500, 5000); 
        
        try {
            // Insert doctor
            $stmt = $database->prepare("INSERT INTO doctor (docemail, docname, docpassword, docnic, doctel, specialties, consultation_fee) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssis", $email, $name, $hashed_password, $nic, $phone, $specialty, $fee);
            
            if ($stmt->execute()) {
                // Insert webuser
                $stmt2 = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'd')");
                $stmt2->bind_param("s", $email);
                $stmt2->execute();
                $stmt2->close();
                
                echo "Inserted doctor: $name<br>";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            if ($database->errno == 1062) { // Duplicate entry
                echo "Doctor $name already exists<br>";
            } else {
                echo "Error inserting doctor $name: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "Successfully populated database!<br>";
    
    // Display patients
    echo "<h3>Patients (Pensioners)</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Address</th><th>NIC</th><th>DOB</th><th>Phone</th></tr>";
    
    $result = $database->query("SELECT pid, pname, pemail, paddress, pnic, pdob, ptel FROM patient ORDER BY pid DESC LIMIT 50");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['pid'] . "</td>";
        echo "<td>" . $row['pname'] . "</td>";
        echo "<td>" . $row['pemail'] . "</td>";
        echo "<td>" . $row['paddress'] . "</td>";
        echo "<td>" . $row['pnic'] . "</td>";
        echo "<td>" . $row['pdob'] . "</td>";
        echo "<td>" . $row['ptel'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Display doctors
    echo "<h3>Doctors</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>NIC</th><th>Phone</th><th>Specialty ID</th><th>Fee</th></tr>";
    
    $result = $database->query("SELECT docid, docname, docemail, docnic, doctel, specialties, consultation_fee FROM doctor ORDER BY docid DESC LIMIT 20");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['docid'] . "</td>";
        echo "<td>" . $row['docname'] . "</td>";
        echo "<td>" . $row['docemail'] . "</td>";
        echo "<td>" . $row['docnic'] . "</td>";
        echo "<td>" . $row['doctel'] . "</td>";
        echo "<td>" . $row['specialties'] . "</td>";
        echo "<td>" . $row['consultation_fee'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

function anonymizeDatabase() {
    global $database;
    echo "<h2>Anonymize edoc Database</h2>";

    $result = $database->query("SHOW TABLES");
    if (!$result) {
        echo "<p style='color:red;'>Unable to list tables: " . htmlspecialchars($database->error) . "</p>";
        return;
    }

    $tables = [];
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $tables[] = $row[0];
    }

    if (empty($tables)) {
        echo "<p>No tables found in the database.</p>";
        return;
    }

    foreach ($tables as $table) {
        echo "<h3>Processing table: " . htmlspecialchars($table) . "</h3>";

        $describe = $database->query("DESCRIBE `" . $database->real_escape_string($table) . "`");
        if (!$describe) {
            echo "<p style='color:red;'>Unable to describe table " . htmlspecialchars($table) . ": " . htmlspecialchars($database->error) . "</p>";
            continue;
        }

        $columns = [];
        $primaryKey = [];
        while ($col = $describe->fetch_assoc()) {
            if ($col['Key'] === 'PRI') {
                $primaryKey[] = $col['Field'];
            }
            if (!shouldSkipColumn($col)) {
                $columns[] = $col;
            }
        }

        if (empty($primaryKey)) {
            echo "<p style='color:orange;'>Skipping table because it has no primary key.</p>";
            continue;
        }

        if (empty($columns)) {
            echo "<p>No columns to anonymize for this table.</p>";
            continue;
        }

        $rows = $database->query("SELECT * FROM `" . $database->real_escape_string($table) . "`");
        if (!$rows) {
            echo "<p style='color:red;'>Unable to read rows from " . htmlspecialchars($table) . ": " . htmlspecialchars($database->error) . "</p>";
            continue;
        }

        $rowCount = 0;
        $updatedCount = 0;

        while ($row = $rows->fetch_assoc()) {
            $updateFields = [];
            $updateValues = [];
            foreach ($columns as $col) {
                $updateFields[] = "`" . $col['Field'] . "` = ?";
                $updateValues[] = anonymizeValue($col, $row[$col['Field']]);
            }

            $whereParts = [];
            $whereValues = [];
            foreach ($primaryKey as $pk) {
                $whereParts[] = "`" . $pk . "` = ?";
                $whereValues[] = $row[$pk];
            }

            $sql = "UPDATE `" . $database->real_escape_string($table) . "` SET " . implode(', ', $updateFields) . " WHERE " . implode(' AND ', $whereParts);
            $stmt = $database->prepare($sql);
            if (!$stmt) {
                echo "<p style='color:red;'>Failed to prepare update for " . htmlspecialchars($table) . ": " . htmlspecialchars($database->error) . "</p>";
                break;
            }

            $bindValues = array_merge($updateValues, $whereValues);
            $types = str_repeat('s', count($bindValues));
            bindParams($stmt, $types, $bindValues);

            if ($stmt->execute()) {
                $updatedCount++;
            } else {
                echo "<p style='color:red;'>Failed to anonymize row in " . htmlspecialchars($table) . ": " . htmlspecialchars($stmt->error) . "</p>";
            }
            $stmt->close();
            $rowCount++;
        }

        echo "<p>Rows read: $rowCount, rows anonymized: $updatedCount</p>";
    }
}

function shouldSkipColumn(array $column) {
    $name = strtolower($column['Field']);
    $type = strtolower($column['Type']);

    if ($column['Key'] === 'PRI' || strpos($column['Extra'], 'auto_increment') !== false) {
        return true;
    }

    if (preg_match('/(^|_)(id|pid|docid|doctor_id|patient_id|appointment_id|record_id|scheduleid|tip_id|privilege_id|history_id|test_id|ticket_id|document_id|medication_id|report_id|payment_id|allergy_id|immunization_id)$/', $name)) {
        return true;
    }

    return false;
}

function anonymizeValue(array $column, $currentValue) {
    if ($currentValue === null) {
        return null;
    }

    $type = strtolower($column['Type']);
    $name = strtolower($column['Field']);

    if (strpos($name, 'email') !== false) {
        $hash = hash('sha256', $currentValue);
        return substr($hash, 0, 16) . '@example.com';
    }

    if (preg_match('/^enum\((.*)\)$/', $type, $matches) || preg_match('/^set\((.*)\)$/', $type, $matches)) {
        $options = str_getcsv($matches[1], ',', "'");
        if (empty($options)) {
            return '';
        }
        return $options[array_rand($options)];
    }

    if (strpos($name, 'password') !== false) {
        return password_hash($currentValue, PASSWORD_DEFAULT);
    }

    if (preg_match('/^(varchar|char|text|tinytext|mediumtext|longtext|binary|varbinary|blob)/', $type)) {
        return hash('sha256', $name . '|' . $currentValue . '|' . microtime(true) . '|' . mt_rand());
    }

    if (preg_match('/^date$/', $type)) {
        return date('Y-m-d', mt_rand(strtotime('1970-01-01'), strtotime('2000-12-31')));
    }

    if (preg_match('/^(datetime|timestamp)$/', $type)) {
        return date('Y-m-d H:i:s', mt_rand(strtotime('1970-01-01'), strtotime('2000-12-31')));
    }

    if (preg_match('/^time$/', $type)) {
        return sprintf('%02d:%02d:%02d', mt_rand(0, 23), mt_rand(0, 59), mt_rand(0, 59));
    }

    if (preg_match('/^(tinyint|smallint|mediumint|int|bigint)/', $type)) {
        return (string) mt_rand(1, 999999);
    }

    if (preg_match('/^(decimal|float|double)/', $type)) {
        return number_format(mt_rand(1, 999999) / 100, 2, '.', '');
    }

    return hash('sha256', $name . '|' . $currentValue . '|' . microtime(true) . '|' . mt_rand());
}

function bindParams($stmt, $types, array &$params) {
    $args = [];
    $args[] = $types;
    foreach ($params as $key => $value) {
        $args[] = &$params[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $args);
}

function optimizePerformance() {
    global $database;
    echo "<h2>Database Performance Optimization</h2>";
    echo "<p>Adding indexes to improve login and query performance...</p>";
    
    $indexes = [
        "ALTER TABLE `patient` ADD INDEX `idx_pemail` (`pemail`)",
        "ALTER TABLE `doctor` ADD INDEX `idx_docemail` (`docemail`)",
        "ALTER TABLE `admin` ADD INDEX `idx_aemail` (`aemail`)",
        "ALTER TABLE `patient` ADD INDEX `idx_pemail_ppassword` (`pemail`, `ppassword`)",
        "ALTER TABLE `doctor` ADD INDEX `idx_docemail_docpassword` (`docemail`, `docpassword`)",
        "ALTER TABLE `admin` ADD INDEX `idx_aemail_apassword` (`aemail`, `apassword`)",
        "ALTER TABLE `webuser` ADD INDEX `idx_email_usertype` (`email`, `usertype`)",
        "ALTER TABLE `schedule` ADD INDEX `idx_scheduledate` (`scheduledate`)",
        "ALTER TABLE `appointment` ADD INDEX `idx_appodate` (`appodate`)"
    ];
    
    $successCount = 0;
    $failCount = 0;
    
    foreach ($indexes as $indexSql) {
        if ($database->query($indexSql)) {
            echo "<p style='color: green;'>✓ Added index: " . htmlspecialchars($indexSql) . "</p>";
            $successCount++;
        } else {
            // Check if it's a duplicate key error (index already exists)
            if ($database->errno == 1061 || $database->errno == 1060) {
                echo "<p style='color: orange;'>⚠ Index already exists: " . htmlspecialchars($indexSql) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to add index: " . htmlspecialchars($indexSql) . "<br>Error: " . $database->error . "</p>";
                $failCount++;
            }
        }
    }
    
    echo "<h3>Optimization Summary</h3>";
    echo "<p>Successfully added: " . $successCount . " indexes</p>";
    echo "<p>Already existed: " . $failCount . " indexes</p>";
    echo "<p style='color: blue; font-weight: bold;'>Performance optimization completed!</p>";
}

$database->close();
?>