

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

;
;
;
;

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `aemail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`aemail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin` (`aemail`, `apassword`) VALUES
('admin@edoc.com', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK');

DROP TABLE IF EXISTS `appointment`;
CREATE TABLE IF NOT EXISTS `appointment` (
  `appoid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(10) DEFAULT NULL,
  `apponum` int(3) DEFAULT NULL,
  `scheduleid` int(10) DEFAULT NULL,
  `appodate` date DEFAULT NULL,
  PRIMARY KEY (`appoid`),
  KEY `pid` (`pid`),
  KEY `scheduleid` (`scheduleid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

INSERT INTO `appointment` (`appoid`, `pid`, `apponum`, `scheduleid`, `appodate`) VALUES
(1, 1, 1, 1, '2022-06-03');

DROP TABLE IF EXISTS `doctor`;
CREATE TABLE IF NOT EXISTS `doctor` (
  `docid` int(11) NOT NULL AUTO_INCREMENT,
  `docemail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docpassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docnic` varchar(15) DEFAULT NULL,
  `doctel` varchar(15) DEFAULT NULL,
  `specialties` int(2) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT 2000.00,
  PRIMARY KEY (`docid`),
  KEY `specialties` (`specialties`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

INSERT INTO `doctor` (`docid`, `docemail`, `docname`, `docpassword`, `docnic`, `doctel`, `specialties`) VALUES
(1, 'doctor@edoc.com', 'Test Doctor', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '000000000', '0110000000', 1);

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `pemail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ppassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paddress` varchar(255) DEFAULT NULL,
  `pnic` varchar(15) DEFAULT NULL,
  `pdob` date DEFAULT NULL,
  `ptel` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

INSERT INTO `patient` (`pid`, `pemail`, `pname`, `ppassword`, `paddress`, `pnic`, `pdob`, `ptel`) VALUES
(1, 'patient@edoc.com', 'Test Patient', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', 'Sri Lanka', '0000000000', '2000-01-01', '0120000000'),
(2, 'emhashenudara@gmail.com', 'Hashen Udara', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', 'Sri Lanka', '0110000000', '2022-06-03', '0700000000');

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `scheduleid` int(11) NOT NULL AUTO_INCREMENT,
  `docid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL,
  `session_fee` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`scheduleid`),
  KEY `docid` (`docid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

INSERT INTO `schedule` (`scheduleid`, `docid`, `title`, `scheduledate`, `scheduletime`, `nop`) VALUES
(1, '1', 'Test Session', '2050-01-01', '18:00:00', 50),
(2, '1', '1', '2022-06-10', '20:36:00', 1),
(3, '1', '12', '2022-06-10', '20:33:00', 1),
(4, '1', '1', '2022-06-10', '12:32:00', 1),
(5, '1', '1', '2022-06-10', '20:35:00', 1),
(6, '1', '12', '2022-06-10', '20:35:00', 1),
(7, '1', '1', '2022-06-24', '20:36:00', 1),
(8, '1', '12', '2022-06-10', '13:33:00', 1);

DROP TABLE IF EXISTS `specialties`;
CREATE TABLE IF NOT EXISTS `specialties` (
  `id` int(2) NOT NULL,
  `sname` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `specialties` (`id`, `sname`) VALUES
(1, 'Accident and emergency medicine'),
(2, 'Allergology'),
(3, 'Anaesthetics'),
(4, 'Biological hematology'),
(5, 'Cardiology'),
(6, 'Child psychiatry'),
(7, 'Clinical biology'),
(8, 'Clinical chemistry'),
(9, 'Clinical neurophysiology'),
(10, 'Clinical radiology'),
(11, 'Dental, oral and maxillo-facial surgery'),
(12, 'Dermato-venerology'),
(13, 'Dermatology'),
(14, 'Endocrinology'),
(15, 'Gastro-enterologic surgery'),
(16, 'Gastroenterology'),
(17, 'General hematology'),
(18, 'General Practice'),
(19, 'General surgery'),
(20, 'Geriatrics'),
(21, 'Immunology'),
(22, 'Infectious diseases'),
(23, 'Internal medicine'),
(24, 'Laboratory medicine'),
(25, 'Maxillo-facial surgery'),
(26, 'Microbiology'),
(27, 'Nephrology'),
(28, 'Neuro-psychiatry'),
(29, 'Neurology'),
(30, 'Neurosurgery'),
(31, 'Nuclear medicine'),
(32, 'Obstetrics and gynecology'),
(33, 'Occupational medicine'),
(34, 'Ophthalmology'),
(35, 'Orthopaedics'),
(36, 'Otorhinolaryngology'),
(37, 'Paediatric surgery'),
(38, 'Paediatrics'),
(39, 'Pathology'),
(40, 'Pharmacology'),
(41, 'Physical medicine and rehabilitation'),
(42, 'Plastic surgery'),
(43, 'Podiatric Medicine'),
(44, 'Podiatric Surgery'),
(45, 'Psychiatry'),
(46, 'Public health and Preventive Medicine'),
(47, 'Radiology'),
(48, 'Radiotherapy'),
(49, 'Respiratory medicine'),
(50, 'Rheumatology'),
(51, 'Stomatology'),
(52, 'Thoracic surgery'),
(53, 'Tropical medicine'),
(54, 'Urology'),
(55, 'Vascular surgery'),
(56, 'Venereology');

DROP TABLE IF EXISTS `webuser`;
CREATE TABLE IF NOT EXISTS `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

START TRANSACTION;

INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@edoc.com', 'a'),
('doctor@edoc.com', 'd'),
('patient@edoc.com', 'p'),
('emhashenudara@gmail.com', 'p');
COMMIT;

;
;
;

DROP TABLE IF EXISTS `health_tips`;
CREATE TABLE IF NOT EXISTS `health_tips` (
  `tip_id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_email` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `tip_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `health_tips` (`admin_email`, `title`, `content`, `tip_image`) VALUES
('admin@edoc.com', 'Stay Hydrated', 'Drinking enough water is essential for maintaining good health. Aim for at least 8 glasses of water per day to keep your body functioning optimally.', NULL),
('admin@edoc.com', 'Exercise Regularly', 'Regular physical activity helps maintain a healthy weight, reduces the risk of chronic diseases, and improves mental health. Aim for at least 150 minutes of moderate exercise per week.', NULL);

COMMIT;

DROP TABLE IF EXISTS `doctor_privileges`;
CREATE TABLE IF NOT EXISTS `doctor_privileges` (
  `privilege_id` int(11) NOT NULL AUTO_INCREMENT,
  `docid` int(11) DEFAULT NULL,
  `privilege_name` varchar(50) DEFAULT NULL,
  `permission_level` enum('read','write','admin') DEFAULT 'read',
  PRIMARY KEY (`privilege_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('patient','doctor','admin') DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `response` text,
  `responded_by` int(11) DEFAULT NULL,
  `responded_date` datetime DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `reports`;
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` enum('appointments','payments','doctors','patients') DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `report_data` longtext,
  `file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `manual_tickets`;
CREATE TABLE IF NOT EXISTS `manual_tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `issued_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `ticket_details` text,
  PRIMARY KEY (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `chatbot_conversations`;
CREATE TABLE IF NOT EXISTS `chatbot_conversations` (
  `conversation_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `message_type` enum('user','bot') NOT NULL,
  PRIMARY KEY (`conversation_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `symptoms_medicines`;
CREATE TABLE IF NOT EXISTS `symptoms_medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `symptom` varchar(255) NOT NULL,
  `recommended_medicine` varchar(255) NOT NULL,
  `specialty_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `symptoms_medicines` (`symptom`, `recommended_medicine`, `specialty_id`) VALUES
('fever', 'Paracetamol', 18),
('headache', 'Aspirin', 23),
('cough', 'Dextromethorphan', 22),
('cold', 'Chlorpheniramine', 22),
('stomach ache', 'Omeprazole', 16),
('nausea', 'Domperidone', 16),
('back pain', 'Ibuprofen', 35),
('joint pain', 'Naproxen', 35),
('sore throat', 'Amoxicillin', 22),
('runny nose', 'Loratadine', 2),
('allergy', 'Cetirizine', 2),
('rash', 'Hydrocortisone', 13),
('diarrhea', 'Loperamide', 16),
('constipation', 'Psyllium', 16),
('insomnia', 'Melatonin', 45),
('anxiety', 'Lorazepam', 45),
('depression', 'Sertraline', 45);

-- EMR (Electronic Medical Records) Module Tables
-- Table for storing patient medical records
DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE IF NOT EXISTS `medical_records` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `chief_complaint` text,
  `diagnosis` text,
  `treatment_plan` text,
  `notes` text,
  `created_by` int(11) NOT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`record_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient medications
DROP TABLE IF EXISTS `medications`;
CREATE TABLE IF NOT EXISTS `medications` (
  `medication_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` text,
  `prescribed_by` int(11) NOT NULL,
  `prescribed_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`medication_id`),
  KEY `record_id` (`record_id`),
  KEY `prescribed_by` (`prescribed_by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient allergies
DROP TABLE IF EXISTS `allergies`;
CREATE TABLE IF NOT EXISTS `allergies` (
  `allergy_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `allergen` varchar(255) NOT NULL,
  `reaction` varchar(255) DEFAULT NULL,
  `severity` enum('mild','moderate','severe') DEFAULT 'moderate',
  `notes` text,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `resolved_date` datetime DEFAULT NULL,
  PRIMARY KEY (`allergy_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient lab test results
DROP TABLE IF EXISTS `lab_tests`;
CREATE TABLE IF NOT EXISTS `lab_tests` (
  `test_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `test_name` varchar(255) NOT NULL,
  `test_date` date NOT NULL,
  `results` text,
  `reference_range` varchar(255) DEFAULT NULL,
  `units` varchar(50) DEFAULT NULL,
  `interpretation` text,
  `ordered_by` int(11) NOT NULL,
  `performed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`test_id`),
  KEY `patient_id` (`patient_id`),
  KEY `record_id` (`record_id`),
  KEY `ordered_by` (`ordered_by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient vital signs
DROP TABLE IF EXISTS `vital_signs`;
CREATE TABLE IF NOT EXISTS `vital_signs` (
  `vital_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `bp_systolic` int(11) DEFAULT NULL,
  `bp_diastolic` int(11) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `oxygen_saturation` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`vital_id`),
  KEY `record_id` (`record_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient immunizations
DROP TABLE IF EXISTS `immunizations`;
CREATE TABLE IF NOT EXISTS `immunizations` (
  `immunization_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `vaccine_date` date NOT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `administered_by` int(11) NOT NULL,
  `notes` text,
  `next_due_date` date DEFAULT NULL,
  PRIMARY KEY (`immunization_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient family medical history
DROP TABLE IF EXISTS `family_history`;
CREATE TABLE IF NOT EXISTS `family_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `condition` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `age_at_diagnosis` int(11) DEFAULT NULL,
  `notes` text,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Table for storing patient surgical history
DROP TABLE IF EXISTS `surgical_history`;
CREATE TABLE IF NOT EXISTS `surgical_history` (
  `surgery_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `procedure_name` varchar(255) NOT NULL,
  `procedure_date` date NOT NULL,
  `surgeon` varchar(255) DEFAULT NULL,
  `hospital` varchar(255) DEFAULT NULL,
  `notes` text,
  `complications` text,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`surgery_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Payments table for storing patient payment details
DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `appointment_id` INT,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(50),
  `transaction_id` VARCHAR(100),
  `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `patient_id` (`patient_id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Sample payment records
INSERT INTO `payments` (`payment_id`, `patient_id`, `appointment_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `payment_date`) VALUES
(1, 1, 1, 50.00, 'Credit Card', 'TXN001', 'completed', '2022-06-03 10:30:00'),
(2, 2, 1, 75.00, 'Bank Transfer', 'TXN002', 'completed', '2022-06-04 14:22:00');

-- Update reports table to include patient document type
ALTER TABLE `reports` MODIFY COLUMN `report_type` ENUM('appointments','payments','doctors','patients','medical') DEFAULT NULL;

-- Add patient_id column to reports table for patient documents
ALTER TABLE `reports` ADD COLUMN `patient_id` INT(11) DEFAULT NULL AFTER `report_id`;
ALTER TABLE `reports` ADD INDEX `idx_patient_id` (`patient_id`);

-- Create medical_documents table for doctor-generated reports
DROP TABLE IF EXISTS `medical_documents`;
CREATE TABLE IF NOT EXISTS `medical_documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `document_type` varchar(100) DEFAULT 'patient_document',
  `document_content` longtext,
  `file_path` varchar(255) DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `generated_by` int(11) NOT NULL,
  PRIMARY KEY (`document_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

COMMIT;
