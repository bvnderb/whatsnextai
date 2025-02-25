-- Create database
CREATE DATABASE procrastinator_db;
USE procrastinator_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks table
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    mood_level INT NOT NULL CHECK (mood_level BETWEEN 1 AND 5),
    estimated_time INT NOT NULL, -- in minutes
    deadline DATETIME,
    priority INT DEFAULT 1 CHECK (priority BETWEEN 1 AND 5),
    status ENUM('pending', 'completed', 'archived') DEFAULT 'pending',
    category_id INT,
    is_recurring BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Recurring tasks settings
CREATE TABLE recurring_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    task_id INT NOT NULL,
    interval_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    interval_value INT NOT NULL,
    last_generated DATETIME,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Task categories
CREATE TABLE task_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) DEFAULT '#000000',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes
CREATE INDEX idx_tasks_user_id ON tasks(user_id);
CREATE INDEX idx_tasks_deadline ON tasks(deadline);
CREATE INDEX idx_tasks_mood_level ON tasks(mood_level);