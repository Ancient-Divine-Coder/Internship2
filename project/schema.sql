-- Run this in phpMyAdmin (create database `pravah2026` first, then import/run this)

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  tagline VARCHAR(150) DEFAULT '',
  total_seats INT NOT NULL,
  booked_seats INT NOT NULL DEFAULT 0
);

INSERT INTO events (name, tagline, total_seats) VALUES
('Code Surge', 'Hackathon', 60),
('Robo Rumble', 'Robotics battle', 40),
('Rhythm Riot', 'Dance competition', 80),
('Cipher Hunt', 'Campus treasure hunt', 50),
('Arena Clash', 'Gaming tournament', 100);

CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  college_id VARCHAR(50) NOT NULL,
  branch VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  event_id INT NOT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id)
);
