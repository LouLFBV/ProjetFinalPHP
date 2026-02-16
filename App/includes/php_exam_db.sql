-- Création de la base de données
CREATE DATABASE IF NOT EXISTS php_exam;
USE php_exam;

-- Table User [cite: 68]
CREATE TABLE User (
    id INT AUTO_INCREMENT PRIMARY KEY, -- [cite: 70]
    username VARCHAR(255) UNIQUE NOT NULL, -- [cite: 19, 74]
    password VARCHAR(255) NOT NULL, -- [cite: 75]
    email VARCHAR(255) UNIQUE NOT NULL, -- [cite: 19, 75]
    balance DECIMAL(10, 2) DEFAULT 0.00, -- [cite: 76]
    profile_pic VARCHAR(255), -- [cite: 77]
    role ENUM('user', 'admin') DEFAULT 'user' -- [cite: 78]
);

-- Table Article [cite: 69]
CREATE TABLE Article (
    id INT AUTO_INCREMENT PRIMARY KEY, -- [cite: 71]
    name VARCHAR(255) NOT NULL, -- [cite: 79]
    description TEXT, -- [cite: 80]
    price DECIMAL(10, 2) NOT NULL, -- [cite: 81]
    publish_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- [cite: 82]
    author_id INT, -- [cite: 83]
    image_url VARCHAR(255), -- [cite: 84]
    FOREIGN KEY (author_id) REFERENCES User(id) ON DELETE CASCADE
);

-- Table Stock [cite: 90]
CREATE TABLE Stock (
    id INT AUTO_INCREMENT PRIMARY KEY, -- [cite: 91]
    article_id INT NOT NULL, -- [cite: 92]
    quantity INT DEFAULT 0, -- [cite: 93]
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE
);

-- Table Cart [cite: 85]
CREATE TABLE Cart (
    id INT AUTO_INCREMENT PRIMARY KEY, -- [cite: 87]
    user_id INT NOT NULL, -- [cite: 88]
    article_id INT NOT NULL, -- [cite: 89]
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE
);

-- Table Invoice [cite: 86]
CREATE TABLE Invoice (
    id INT AUTO_INCREMENT PRIMARY KEY, -- [cite: 94]
    user_id INT NOT NULL, -- [cite: 95]
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- [cite: 96]
    amount DECIMAL(10, 2) NOT NULL, -- [cite: 97]
    billing_address VARCHAR(255), -- [cite: 98]
    billing_city VARCHAR(255), -- [cite: 99]
    billing_zipcode VARCHAR(20), -- [cite: 100]
    FOREIGN KEY (user_id) REFERENCES User(id)
);