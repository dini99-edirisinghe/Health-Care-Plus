<?php

include("connection.php");

$sql = "SELECT d.*, s.sname as specialty_name FROM doctor d LEFT JOIN specialties s ON d.specialties = s.id";
$result = $database->query($sql);
$doctors = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

$sql_specialties = "SELECT COUNT(*) as total FROM specialties";
$result_specialties = $database->query($sql_specialties);
$specialties_count = $result_specialties->fetch_assoc()['total'];

$sql_appointments = "SELECT COUNT(*) as total FROM appointment";
$result_appointments = $database->query($sql_appointments);
$appointments_count = $result_appointments->fetch_assoc()['total'];

$sql_patients = "SELECT COUNT(*) as total FROM patient";
$result_patients = $database->query($sql_patients);
$patients_count = $result_patients->fetch_assoc()['total'];

$database->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthCarePlus - Hospital Management System</title>
    <link rel="stylesheet" href="css/font-inter.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --gray: #64748b;
            --light: #f1f5f9;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .logo-icon {
            font-size: 1.5rem;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }
        
        .btn-primary {
            background: var(--primary);
            border: 2px solid var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--white);
            padding: 100px 0;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 40px;
            opacity: 0.9;
        }
        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .features-section {
            padding: 100px 0;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .section-header p {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }
        
        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 25px;
            color: var(--primary);
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .feature-card p {
            color: var(--gray);
            line-height: 1.7;
        }

        .stats-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .stat-item {
            animation: fadeInUp 1s ease-out;
            text-align: center;
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.2rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .enhanced-stats-section {
            padding: 80px 0;
            background: var(--white);
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }
        
        .chart-container {
            background: var(--light);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .chart-container h3 {
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .chart-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .doctors-section {
            padding: 100px 20px;
            background: #f1f5f9;
        }
        
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 60px auto 0;
        }
        
        .doctor-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            animation: fadeInUp 0.8s ease-out;
        }
        
        .doctor-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .doctor-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }
        
        .doctor-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .doctor-specialty {
            color: var(--primary);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .doctor-card p {
            color: var(--gray);
            line-height: 1.7;
            font-size: 1rem;
        }
        
        .doctor-fee {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--success);
            margin: 15px 0;
        }
        
        .doctor-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .doctor-experience {
            font-size: 0.9rem;
            color: var(--gray);
            font-weight: 500;
        }
        
        .doctor-rating {
            color: #ffc107;
            font-size: 1rem;
        }
        
        .book-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }
        
        .book-button:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        .testimonials-section {
            padding: 100px 20px;
            background: white;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 60px auto 0;
        }
        
        .testimonial-card {
            background: var(--light);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .testimonial-card:before {
            content: """;
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 5rem;
            color: rgba(37, 99, 235, 0.1);
            font-family: Georgia, serif;
            line-height: 1;
        }
        
        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--dark);
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .author-info h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .author-info p {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0;
        }

        .newsletter-section {
            padding: 80px 20px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            text-align: center;
        }
        
        .newsletter-content h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .newsletter-content p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            gap: 10px;
        }
        
        .newsletter-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
        }
        
        .newsletter-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .newsletter-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .faq-section {
            padding: 100px 20px;
            background: var(--light);
        }
        
        .faq-container {
            max-width: 800px;
            margin: 50px auto 0;
        }
        
        .faq-item {
            background: var(--white);
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .faq-question {
            padding: 20px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-toggle {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .faq-item.active .faq-answer {
            padding: 0 20px 20px;
            max-height: 500px;
        }
        
        .faq-item.active .faq-toggle {
            transform: rotate(45deg);
        }

        .cta-section {
            padding: 100px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            text-align: center;
        }
        
        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta-content p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        footer {
            background: var(--dark);
            color: var(--light);
            padding: 60px 0 30px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--primary);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 12px;
        }
        
        .footer-column ul li a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-column ul li a:hover {
            color: var(--primary);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray);
            font-size: 0.9rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .nav-links {
                display: none;
            }
            
            .newsletter-form {
                flex-direction: column;
            }
            
            .newsletter-button {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo"><span class="logo-icon">🏥</span> Health<span>Care</span>Plus</a>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="#doctors">Doctors</a>
                    <a href="#services">Services</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Comprehensive Hospital Management System</h1>
            <p>Streamlined medical consultations, appointment scheduling, electronic health records, and integrated hospital management for healthcare providers and patients.</p>
            <div class="hero-buttons">
                <a href="signup.php" class="btn btn-primary">Create Patient Portal</a>
                <a href="#doctors" class="btn btn-outline" style="color: white; border-color: white;">Meet Our Physicians</a>
            </div>
        </div>
    </section>

    <section class="features-section" id="services">
        <div class="container">
            <div class="section-header">
                <h2>Hospital Management Features</h2>
                <p>Our comprehensive system streamlines all aspects of hospital operations</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3>Patient Management</h3>
                    <p>Complete patient records with medical history, diagnoses, treatments, and follow-ups in one centralized system.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👨‍⚕️</div>
                    <h3>Doctor Scheduling</h3>
                    <p>Efficient appointment scheduling with real-time availability, automated reminders, and rescheduling options.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📄</div>
                    <h3>Medical Records</h3>
                    <p>Electronic health records with secure access, prescription management, and test result integration.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💊</div>
                    <h3>Pharmacy Integration</h3>
                    <p>Prescription generation, medication tracking, and pharmacy network connectivity for seamless care.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔬</div>
                    <h3>Laboratory Results</h3>
                    <p>Digital lab result management with instant notifications and historical trend analysis.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Billing System</h3>
                    <p>Automated billing, insurance processing, and payment tracking for transparent financial management.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number doctors"><?php echo count($doctors); ?>+</div>
                    <div class="stat-label">Board-Certified Physicians</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number patients"><?php echo $patients_count; ?>+</div>
                    <div class="stat-label">Patient Records Managed</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?php echo $appointments_count; ?>+</div>
                    <div class="stat-label">Medical Appointments</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?php echo $specialties_count; ?>+</div>
                    <div class="stat-label">Medical Specialties</div>
                </div>
            </div>
        </div>
    </section>

    <section class="enhanced-stats-section" id="about">
        <div class="container">
            <div class="section-header">
                <h2>Hospital Analytics Dashboard</h2>
                <p>Data-driven insights for optimal healthcare delivery</p>
            </div>
            
            <div class="charts-grid">
                <div class="chart-container">
                    <h3>Patient Outcomes</h3>
                    <div class="chart-wrapper">
                        <div id="doctor-patient-chart"></div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>Appointment Trends</h3>
                    <div class="chart-wrapper">
                        <div id="appointment-trends-chart"></div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>Department Distribution</h3>
                    <div class="chart-wrapper">
                        <div id="specialties-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="doctors-section" id="doctors">
        <div class="container">
            <div class="section-header">
                <h2>Our Medical Staff</h2>
                <p>Highly qualified physicians and healthcare professionals committed to excellence</p>
            </div>
            
            <div class="doctors-grid">
                <?php if (!empty($doctors)): ?>
                    <?php foreach (array_slice($doctors, 0, 3) as $doctor): ?>
                    <div class="doctor-card">
                        <div class="doctor-avatar">
                            <?php 
                            
                            $names = explode(' ', $doctor['docname']);
                            $initials = '';
                            foreach ($names as $name) {
                                $initials .= strtoupper(substr($name, 0, 1));
                            }
                            echo substr($initials, 0, 2);
                            ?>
                        </div>
                        <h3><?php echo htmlspecialchars($doctor['docname']); ?></h3>
                        <div class="doctor-specialty"><?php echo htmlspecialchars($doctor['specialty_name'] ?? 'General Practitioner'); ?></div>
                        <?php if (!empty($doctor['consultation_fee'])): ?>
                            <div class="doctor-fee">LKR <?php echo number_format($doctor['consultation_fee'], 2); ?></div>
                        <?php endif; ?>
                        <p>Board-certified physician with extensive clinical experience and commitment to patient-centered care.</p>
                        <div class="doctor-meta">
                            <span class="doctor-experience">15+ years experience</span>
                            <span class="doctor-rating">★★★★★</span>
                        </div>
                        <a href="login.php" class="book-button">Schedule Consultation</a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="doctor-card">
                        <div class="doctor-avatar">JD</div>
                        <h3>Dr. Jane Smith</h3>
                        <div class="doctor-specialty">Cardiology</div>
                        <div class="doctor-fee">LKR 3,500.00</div>
                        <p>Board-certified cardiologist with expertise in preventive cardiology and heart disease management.</p>
                        <div class="doctor-meta">
                            <span class="doctor-experience">18 years experience</span>
                            <span class="doctor-rating">★★★★★</span>
                        </div>
                        <a href="login.php" class="book-button">Schedule Consultation</a>
                    </div>
                    
                    <div class="doctor-card">
                        <div class="doctor-avatar">MP</div>
                        <h3>Dr. Michael Brown</h3>
                        <div class="doctor-specialty">Pediatrics</div>
                        <div class="doctor-fee">LKR 2,800.00</div>
                        <p>Pediatric specialist with focus on child development and family-centered healthcare approaches.</p>
                        <div class="doctor-meta">
                            <span class="doctor-experience">12 years experience</span>
                            <span class="doctor-rating">★★★★★</span>
                        </div>
                        <a href="login.php" class="book-button">Schedule Consultation</a>
                    </div>
                    
                    <div class="doctor-card">
                        <div class="doctor-avatar">SD</div>
                        <h3>Dr. Sarah Davis</h3>
                        <div class="doctor-specialty">Dermatology</div>
                        <div class="doctor-fee">LKR 4,200.00</div>
                        <p>Dermatologist specializing in skin cancer detection and cosmetic dermatology procedures.</p>
                        <div class="doctor-meta">
                            <span class="doctor-experience">14 years experience</span>
                            <span class="doctor-rating">★★★★★</span>
                        </div>
                        <a href="login.php" class="book-button">Schedule Consultation</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>Patient Experiences</h2>
                <p>Real stories from patients who trust our healthcare system</p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">The comprehensive care I received at HealthCarePlus was exceptional. From appointment scheduling to follow-up care, everything was seamless and professional.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">RK</div>
                        <div class="author-info">
                            <h4>Robert K.</h4>
                            <p>Cardiac Care Patient</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <p class="testimonial-text">As a parent, I appreciate how easy it is to manage my children's healthcare appointments. The pediatric team is wonderful and the online system is intuitive.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">SJ</div>
                        <div class="author-info">
                            <h4>Sarah J.</h4>
                            <p>Pediatric Care Parent</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <p class="testimonial-text">The electronic health records system saved me during my emergency visit. Doctors had immediate access to my medical history, which expedited my treatment.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MT</div>
                        <div class="author-info">
                            <h4>Michael T.</h4>
                            <p>Emergency Care Patient</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="newsletter-section" id="contact">
        <div class="container">
            <div class="newsletter-content">
                <h2>Healthcare Updates</h2>
                <p>Subscribe to receive medical news, health tips from our specialists, and hospital announcements.</p>
                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                    <button type="submit" class="newsletter-button">Subscribe to Health News</button>
                </form>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2>Hospital System FAQs</h2>
                <p>Common questions about our healthcare management platform</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        How do I access my medical records?
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>After registering and verifying your account, you can access your complete medical records through the patient portal. All records are securely encrypted and comply with healthcare privacy regulations.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        What specialties do your doctors cover?
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>We offer comprehensive care across 50+ medical specialties including Cardiology, Pediatrics, Dermatology, Orthopedics, Neurology, Oncology, and Emergency Medicine. New specialties are added regularly.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        How does the appointment system work?
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Our intelligent scheduling system shows real-time doctor availability, estimated wait times, and allows 24/7 booking. Automated reminders reduce no-show rates and rescheduling is available up to 2 hours before appointment.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Is telemedicine available?
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we offer secure video consultations for follow-up visits, prescription renewals, and certain initial consultations. Our HIPAA-compliant platform ensures privacy and quality care from licensed physicians.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        How are prescriptions handled?
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Electronic prescriptions are sent directly to your preferred pharmacy. You can track prescription status, request refills, and view medication history through your patient portal with pharmacist consultation available.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        What emergency services are available?
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Our 24/7 emergency department is staffed with board-certified emergency physicians. Critical care services include trauma care, cardiac emergencies, stroke response, and pediatric emergencies with specialized equipment.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Experience Modern Hospital Management</h2>
                <p>Join our integrated healthcare system with electronic medical records, streamlined appointments, and comprehensive patient care management. Your health is our priority.</p>
                <a href="signup.php" class="btn btn-primary" style="background: white; color: var(--primary); border-color: white;">Create Patient Account</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>HealthCarePlus Hospital</h3>
                    <p>Comprehensive healthcare management system providing integrated medical services, electronic health records, and patient-centered care solutions.</p>
                    <div class="social-links">
                        <a href="#">f</a>
                        <a href="#">t</a>
                        <a href="#">in</a>
                        <a href="#">ig</a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Departments</h3>
                    <ul>
                        <li><a href="#">Emergency Medicine</a></li>
                        <li><a href="#">Cardiology</a></li>
                        <li><a href="#">Pediatrics</a></li>
                        <li><a href="#">Orthopedics</a></li>
                        <li><a href="#">Neurology</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Medical Services</h3>
                    <ul>
                        <li><a href="#">Primary Care</a></li>
                        <li><a href="#">Specialist Consultations</a></li>
                        <li><a href="#">Diagnostic Imaging</a></li>
                        <li><a href="#">Laboratory Services</a></li>
                        <li><a href="#">Surgical Procedures</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Hospital Information</h3>
                    <ul>
                        <li>🏥 123 Medical Center, Colombo</li>
                        <li>📞 Emergency: +94 11 234 5678</li>
                        <li>✉️ info@healthcareplus.lk</li>
                        <li>🕒 24/7 Emergency Services</li>
                        <li>🕒 Outpatient: 8:00 AM - 8:00 PM</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2023 HealthCarePlus Hospital Management System. All rights reserved. | HIPAA Compliant | Joint Commission Certified</p>
            </div>
        </div>
    </footer>

    <script src="js/charts.js"></script>
    <script>
        
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                faqItem.classList.toggle('active');
            });
        });
    </script>
</body>
</html>