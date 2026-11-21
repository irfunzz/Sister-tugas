-- Skema Database Sistem Chat Terdistribusi
CREATE DATABASE IF NOT EXISTS distributed_chat;
USE distributed_chat;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (sender_id, receiver_id, created_at),
    INDEX idx_receiver (receiver_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun awal untuk pengujian 3 user (password default: 'password123')
-- Hash dihasilkan menggunakan BCRYPT: $2y$10$wN2E3w0dD32ZJvH0X1.tSeoY9aXG5dG4y3VvT1S8T5CjI9vG0hW3W (contoh standar PHP password_hash)
INSERT IGNORE INTO users (id, username, email, password_hash, full_name) VALUES
(1, 'user1', 'user1@distribusys.local', '$2y$10$lhYzvVTCZmGN.4zcZ3tteez4W4XITHtTciS41tWJDGiNMuDOEueiS', 'User Satu'),
(2, 'user2', 'user2@distribusys.local', '$2y$10$lhYzvVTCZmGN.4zcZ3tteez4W4XITHtTciS41tWJDGiNMuDOEueiS', 'User Dua'),
(3, 'user3', 'user3@distribusys.local', '$2y$10$lhYzvVTCZmGN.4zcZ3tteez4W4XITHtTciS41tWJDGiNMuDOEueiS', 'User Tiga');
