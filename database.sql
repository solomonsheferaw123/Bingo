-- Database structure for Dallol Bingo
CREATE TABLE IF NOT EXISTS shops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    percentage DECIMAL(5,2) DEFAULT 0.20,
    prepaid TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent') NOT NULL,
    shop_id INT,
    balance DECIMAL(15,2) DEFAULT 0.00,
    phone_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    stake DECIMAL(10,2) NOT NULL,
    players_count INT NOT NULL,
    total_pool DECIMAL(10,2) NOT NULL,
    house_cut DECIMAL(10,2) NOT NULL,
    agent_cut DECIMAL(10,2) NOT NULL,
    win_amount DECIMAL(10,2) NOT NULL,
    win_card VARCHAR(50),
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'game_win', 'game_stake') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id)
);

-- Sample Data
INSERT INTO shops (name, percentage) VALUES ('Test Shop', 0.20) ON DUPLICATE KEY UPDATE name=name;
INSERT INTO users (username, password, role, shop_id, balance) 
SELECT 'agent1', 'agent123', 'agent', 1, 1000.00 FROM DUAL 
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='agent1');

INSERT INTO users (username, password, role, balance) 
SELECT 'admin', 'admin123', 'admin', 0 FROM DUAL 
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');
