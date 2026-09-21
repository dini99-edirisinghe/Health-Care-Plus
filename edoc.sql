-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 08:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edoc`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aemail` varchar(255) NOT NULL,
  `apassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aemail`, `apassword`) VALUES
('admin@edoc.com', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK');

-- --------------------------------------------------------

--
-- Table structure for table `allergies`
--

CREATE TABLE `allergies` (
  `allergy_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `allergen` varchar(255) NOT NULL,
  `reaction` varchar(255) DEFAULT NULL,
  `severity` enum('mild','moderate','severe') DEFAULT 'moderate',
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT current_timestamp(),
  `resolved_date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appoid` int(11) NOT NULL,
  `pid` int(10) DEFAULT NULL,
  `apponum` int(3) DEFAULT NULL,
  `scheduleid` int(10) DEFAULT NULL,
  `appodate` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appoid`, `pid`, `apponum`, `scheduleid`, `appodate`) VALUES
(37, 8, 1, 14, '2026-01-23');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_conversations`
--

CREATE TABLE `chatbot_conversations` (
  `conversation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `message_type` enum('user','bot') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `docid` int(11) NOT NULL,
  `docemail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docpassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docnic` varchar(15) DEFAULT NULL,
  `doctel` varchar(15) DEFAULT NULL,
  `specialties` int(2) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT 2000.00,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`docid`, `docemail`, `docname`, `docpassword`, `docnic`, `doctel`, `specialties`, `consultation_fee`, `profile_picture`) VALUES
(5, 'nimal.perera@hcp-hospital.lk', 'Dr. Nimal Perera', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '077-123-4501', 17, 2000.00, NULL),
(6, 'anjana.k@hcp-hospital.lk', 'Dr. Anjana Kularatne', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '075-889-4410', 17, 2000.00, NULL),
(7, 'ruwan.d@hcp-hospital.lk', 'Dr. Ruwan Dissanayake', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '071-334-2201', 18, 2000.00, NULL),
(8, 'sachini.f@hcp-hospital.lk', 'Dr. Sachini Fernando', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '071-456-7821', 2, 2000.00, NULL),
(9, 'ajith.s@hcp-hospital.lk', 'Dr. Ajith Senanayake', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '072-667-8821', 3, 2000.00, NULL),
(10, 'kavindu.j@hcp-hospital.lk', 'Dr. Kavindu Jayasinghe', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '075-334-1120', 4, 2000.00, NULL),
(11, 'sanduni.a@hcp-hospital.lk', 'Dr. Sanduni Amarasinghe', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '077-221-9981', 5, 2000.00, NULL),
(12, 'imesha.r@hcp-hospital.lk', 'Dr. Imesha Rathnayake', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '076-210-8899', 11, 2000.00, NULL),
(13, 'thilini.b@hcp-hospital.lk', 'Dr. Thilini Bandara', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '075-112-9034', 12, 2000.00, NULL),
(14, 'tharindu.g@hcp-hospital.lk', 'Dr. Tharindu Gunasekara', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '072-998-4412', 13, 2000.00, NULL),
(15, 'malith.w@hcp-hospital.lk', 'Dr. Malith Wijesinghe', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '071-889-7712', 15, 2000.00, NULL),
(16, 'roshan.w@hcp-hospital.lk', 'Dr. Roshan Wijetunga', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '070-554-2234', 56, 2000.00, NULL),
(17, 'supun.k@hcp-hospital.lk', 'Dr. Supun Karunarathne', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '078-112-3309', 55, 2000.00, NULL),
(18, 'malithi.s@hcp-hospital.lk', 'Dr. Malithi Senanayake', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '078-667-9012', 51, 2000.00, NULL),
(19, 'yasasmi.a@hcp-hospital.lk', 'Dr. Yasasmi Aluwihare', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '077-443-1209', 49, 2000.00, NULL),
(20, 'chamath.a@hcp-hospital.lk', 'Dr. Chamath Abeykoon', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '074-330-4455', 39, 2000.00, NULL),
(21, 'nadeesha.k@hcp-hospital.lk', 'Dr. Nadeesha Karunaratne', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '071-889-3321', 31, 2000.00, NULL),
(22, 'harsha.e@hcp-hospital.lk', 'Dr. Harsha Ekanayake', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '070-221-8890', 14, 2000.00, NULL),
(23, 'pasindu.h@hcp-hospital.lk', 'Dr. Pasindu Herath', NULL, '-', '072-441-6677', 32, 2000.00, NULL),
(24, 'sewmini.l@hcp-hospital.lk', 'Dr. Sewmini Liyanage', '$2y$10$ZBRvCeJWvao1FY93C6IgteD6kkQObcU6UIQSUtG0Ur32XNMVdIyDK', '-', '076-432-7778', 10, 2000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_privileges`
--

CREATE TABLE `doctor_privileges` (
  `privilege_id` int(11) NOT NULL,
  `docid` int(11) DEFAULT NULL,
  `privilege_name` varchar(50) DEFAULT NULL,
  `permission_level` enum('read','write','admin') DEFAULT 'read'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `doctor_privileges`
--

INSERT INTO `doctor_privileges` (`privilege_id`, `docid`, `privilege_name`, `permission_level`) VALUES
(3, 3, 'qq', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `family_history`
--

CREATE TABLE `family_history` (
  `history_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `condition` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `age_at_diagnosis` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('patient','doctor','admin') DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `responded_date` datetime DEFAULT NULL,
  `created_date` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_tips`
--

CREATE TABLE `health_tips` (
  `tip_id` int(11) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `tip_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_tips`
--

INSERT INTO `health_tips` (`tip_id`, `admin_email`, `title`, `content`, `tip_image`, `created_at`, `updated_at`) VALUES
(1, 'admin@edoc.com', 'Stay Hydrated', 'Drinking enough water is essential for maintaining good health. Aim for at least 8 glasses of water per day to keep your body functioning optimally.', NULL, '2026-01-11 15:10:21', '2026-01-11 15:10:21'),
(2, 'admin@edoc.com', 'Exercise Regularly', 'Regular physical activity helps maintain a healthy weight, reduces the risk of chronic diseases, and improves mental health. Aim for at least 150 minutes of moderate exercise per week.', NULL, '2026-01-11 15:10:21', '2026-01-11 15:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `immunizations`
--

CREATE TABLE `immunizations` (
  `immunization_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `vaccine_date` date NOT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `administered_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `next_due_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_tests`
--

CREATE TABLE `lab_tests` (
  `test_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `test_name` varchar(255) NOT NULL,
  `test_date` date NOT NULL,
  `results` text DEFAULT NULL,
  `reference_range` varchar(255) DEFAULT NULL,
  `units` varchar(50) DEFAULT NULL,
  `interpretation` text DEFAULT NULL,
  `ordered_by` int(11) NOT NULL,
  `performed_date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manual_tickets`
--

CREATE TABLE `manual_tickets` (
  `ticket_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `issued_date` datetime DEFAULT current_timestamp(),
  `ticket_details` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_documents`
--

CREATE TABLE `medical_documents` (
  `document_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `document_type` varchar(100) DEFAULT 'medical_report',
  `document_content` longtext DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_date` datetime DEFAULT current_timestamp(),
  `generated_by` int(11) NOT NULL,
  `document_image` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `chief_complaint` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` datetime DEFAULT current_timestamp(),
  `updated_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medication_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `prescribed_by` int(11) NOT NULL,
  `prescribed_date` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `pid` int(11) NOT NULL,
  `pemail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ppassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paddress` varchar(255) DEFAULT NULL,
  `pnic` varchar(15) DEFAULT NULL,
  `pdob` date DEFAULT NULL,
  `ptel` varchar(15) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `report_type` enum('appointments','payments','doctors','patients','medical') DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_date` datetime DEFAULT current_timestamp(),
  `report_data` longtext DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `document_image` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL,
  `docid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL,
  `session_fee` decimal(10,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `id` int(2) NOT NULL,
  `sname` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `specialties`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `surgical_history`
--

CREATE TABLE `surgical_history` (
  `surgery_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `procedure_name` varchar(255) NOT NULL,
  `procedure_date` date NOT NULL,
  `surgeon` varchar(255) DEFAULT NULL,
  `hospital` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `complications` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_date` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `symptoms_medicines`
--

CREATE TABLE `symptoms_medicines` (
  `id` int(11) NOT NULL,
  `symptom` varchar(255) NOT NULL,
  `recommended_medicine` varchar(255) NOT NULL,
  `specialty_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `symptoms_medicines`
--

INSERT INTO `symptoms_medicines` (`id`, `symptom`, `recommended_medicine`, `specialty_id`) VALUES
(1, 'fever', 'Paracetamol', 18),
(2, 'headache', 'Aspirin', 23),
(3, 'cough', 'Dextromethorphan', 22),
(4, 'cold', 'Chlorpheniramine', 22),
(5, 'stomach ache', 'Omeprazole', 16),
(6, 'nausea', 'Domperidone', 16),
(7, 'back pain', 'Ibuprofen', 35),
(8, 'joint pain', 'Naproxen', 35),
(9, 'sore throat', 'Amoxicillin', 22),
(10, 'runny nose', 'Loratadine', 2),
(11, 'allergy', 'Cetirizine', 2),
(12, 'rash', 'Hydrocortisone', 13),
(13, 'diarrhea', 'Loperamide', 16),
(14, 'constipation', 'Psyllium', 16),
(15, 'insomnia', 'Melatonin', 45),
(16, 'anxiety', 'Lorazepam', 45),
(17, 'depression', 'Sertraline', 45);

-- --------------------------------------------------------

--
-- Table structure for table `vital_signs`
--

CREATE TABLE `vital_signs` (
  `vital_id` int(11) NOT NULL,
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
  `recorded_date` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `vital_signs`
--

INSERT INTO `vital_signs` (`vital_id`, `record_id`, `bp_systolic`, `bp_diastolic`, `heart_rate`, `temperature`, `respiratory_rate`, `oxygen_saturation`, `weight`, `height`, `bmi`, `recorded_by`, `recorded_date`) VALUES
(1, 1, 0, 0, 0, 0.00, 0, 0.00, 0.00, 0.00, NULL, 2, '2025-12-20 15:29:25'),
(2, 1, 1, -1, 1, 0.10, 1, 0.10, 0.10, 1.00, 999.99, 2, '2025-12-20 15:29:42'),
(3, 2, -1, 1, -1, 0.00, 0, 0.00, 0.00, 1.00, NULL, 3, '2025-12-20 17:16:53'),
(4, 3, -1, 1, 0, -0.10, -1, 0.00, -0.10, -1.00, -999.99, 3, '2026-01-23 18:33:46');

-- --------------------------------------------------------

--
-- Table structure for table `webuser`
--

CREATE TABLE `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `webuser`
--

INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@edoc.com', 'a'),
('nimal.perera@hcp-hospital.lk', 'd'),
('anjana.k@hcp-hospital.lk', 'd'),
('ruwan.d@hcp-hospital.lk', 'd'),
('sachini.f@hcp-hospital.lk', 'd'),
('ajith.s@hcp-hospital.lk', 'd'),
('kavindu.j@hcp-hospital.lk', 'd'),
('sanduni.a@hcp-hospital.lk', 'd'),
('imesha.r@hcp-hospital.lk', 'd'),
('thilini.b@hcp-hospital.lk', 'd'),
('tharindu.g@hcp-hospital.lk', 'd'),
('malith.w@hcp-hospital.lk', 'd'),
('roshan.w@hcp-hospital.lk', 'd'),
('supun.k@hcp-hospital.lk', 'd'),
('malithi.s@hcp-hospital.lk', 'd'),
('yasasmi.a@hcp-hospital.lk', 'd'),
('chamath.a@hcp-hospital.lk', 'd'),
('nadeesha.k@hcp-hospital.lk', 'd'),
('harsha.e@hcp-hospital.lk', 'd'),
('pasindu.h@hcp-hospital.lk', 'd'),
('sewmini.l@hcp-hospital.lk', 'd');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aemail`);

--
-- Indexes for table `allergies`
--
ALTER TABLE `allergies`
  ADD PRIMARY KEY (`allergy_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `scheduleid` (`scheduleid`);

--
-- Indexes for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`docid`),
  ADD KEY `specialties` (`specialties`);

--
-- Indexes for table `doctor_privileges`
--
ALTER TABLE `doctor_privileges`
  ADD PRIMARY KEY (`privilege_id`);

--
-- Indexes for table `family_history`
--
ALTER TABLE `family_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `health_tips`
--
ALTER TABLE `health_tips`
  ADD PRIMARY KEY (`tip_id`);

--
-- Indexes for table `immunizations`
--
ALTER TABLE `immunizations`
  ADD PRIMARY KEY (`immunization_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD PRIMARY KEY (`test_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `record_id` (`record_id`),
  ADD KEY `ordered_by` (`ordered_by`);

--
-- Indexes for table `manual_tickets`
--
ALTER TABLE `manual_tickets`
  ADD PRIMARY KEY (`ticket_id`);

--
-- Indexes for table `medical_documents`
--
ALTER TABLE `medical_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medication_id`),
  ADD KEY `record_id` (`record_id`),
  ADD KEY `prescribed_by` (`prescribed_by`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_patient_id` (`patient_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleid`),
  ADD KEY `docid` (`docid`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surgical_history`
--
ALTER TABLE `surgical_history`
  ADD PRIMARY KEY (`surgery_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `symptoms_medicines`
--
ALTER TABLE `symptoms_medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD PRIMARY KEY (`vital_id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `webuser`
--
ALTER TABLE `webuser`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allergies`
--
ALTER TABLE `allergies`
  MODIFY `allergy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `docid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `doctor_privileges`
--
ALTER TABLE `doctor_privileges`
  MODIFY `privilege_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `family_history`
--
ALTER TABLE `family_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `health_tips`
--
ALTER TABLE `health_tips`
  MODIFY `tip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `immunizations`
--
ALTER TABLE `immunizations`
  MODIFY `immunization_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_tests`
--
ALTER TABLE `lab_tests`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manual_tickets`
--
ALTER TABLE `manual_tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_documents`
--
ALTER TABLE `medical_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `surgical_history`
--
ALTER TABLE `surgical_history`
  MODIFY `surgery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `symptoms_medicines`
--
ALTER TABLE `symptoms_medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `vital_signs`
--
ALTER TABLE `vital_signs`
  MODIFY `vital_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
