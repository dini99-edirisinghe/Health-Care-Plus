USE edoc;

CREATE TABLE IF NOT EXISTS health_tips (
    tip_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_email VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    tip_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_email) REFERENCES admin(aemail)
);

-- Insert a sample health tip
INSERT INTO health_tips (admin_email, title, content, tip_image) VALUES 
('admin@edoc.com', 'Stay Hydrated', 'Drinking enough water is essential for maintaining good health. Aim for at least 8 glasses of water per day to keep your body functioning optimally.', NULL);

INSERT INTO health_tips (admin_email, title, content, tip_image) VALUES 
('admin@edoc.com', 'Exercise Regularly', 'Regular physical activity helps maintain a healthy weight, reduces the risk of chronic diseases, and improves mental health. Aim for at least 150 minutes of moderate exercise per week.', NULL);