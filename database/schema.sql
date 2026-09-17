-- FixMyDevice Hardware Ticketing System Schema
-- Production Ready MySQL Schema

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'technician', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'tv',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    device_name VARCHAR(150) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model_number VARCHAR(100),
    serial_number VARCHAR(100),
    warranty_status ENUM('in_warranty', 'out_of_warranty', 'unknown') DEFAULT 'out_of_warranty',
    issue_title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'assigned', 'in_diagnosis', 'awaiting_parts', 'repair_in_progress', 'ready', 'resolved', 'cancelled') DEFAULT 'pending',
    technician_id INT NULL,
    estimated_cost DECIMAL(10, 2) DEFAULT 0.00,
    preferred_date DATE NULL,
    attachment_url VARCHAR(255) NULL,
    location_lat DECIMAL(10, 7) DEFAULT NULL,
    location_lng DECIMAL(10, 7) DEFAULT NULL,
    location_address VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ticket_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    changed_by_user_id INT NOT NULL,
    status_from VARCHAR(50),
    status_to VARCHAR(50) NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    is_internal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Seed: Service Categories
INSERT INTO categories (id, name, slug, icon, description) VALUES
(1, 'Smart TVs & Displays', 'smart-tvs', 'tv', 'LED, OLED, QLED, Smart TVs, and monitors repair & screen replacement.'),
(2, 'Refrigerators & Freezers', 'refrigerators', 'refrigerator', 'Single door, double door, side-by-side, and commercial freezer maintenance.'),
(3, 'Washing Machines & Dryers', 'washing-machines', 'washer', 'Front load, top load, semi-automatic washers and clothes dryers.'),
(4, 'Air Conditioners & HVAC', 'air-conditioners', 'wind', 'Split ACs, window ACs, inverter AC servicing and gas refill.'),
(5, 'Laptops, PCs & Electronics', 'laptops-pcs', 'laptop', 'Laptops, desktop PCs, motherboards, power supply and hardware upgrades.'),
(6, 'Microwave Ovens & Kitchenware', 'microwaves', 'microwave', 'Microwave ovens, induction cooktops, dishwashers, and small kitchen electronics.')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Initial Seed: Default Staff Accounts (passwords: admin123, tech123)
INSERT INTO users (id, name, email, password_hash, phone, address, role) VALUES
(1, 'System Admin', 'admin@fixmydevice.com', '$2y$10$wO3mYkE1Jq1J.J9gA4rLteXfU9fIe9Z6x4P3Q8p1kYgL7fM8o0k2S', '+1 800 555 0199', '100 Service HQ Blvd, Tech City', 'admin'),
(2, 'Alex Miller (Technician)', 'tech@fixmydevice.com', '$2y$10$yF3mZkE2Jq2J.K9hB5sMteYgV0gJf0Z7y5Q4R9q2lZhM8gN9p1k3T', '+1 800 555 0244', 'Technician Center Hub 4', 'technician')
ON DUPLICATE KEY UPDATE email=VALUES(email);
