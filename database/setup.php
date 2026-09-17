<?php

// Prevent public web execution
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: Database setup can only be executed via the command-line interface (CLI).\n");
}

echo "Setting up FixMyDevice Database...\n";

require_once __DIR__ . '/../config/database.php';

try {
    $dbInstance = Database::getInstance();
    $pdo = $dbInstance->connect();

    // 1. Create Users
    $pdo->exec("
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
    ");

    // 2. Create Categories
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        icon VARCHAR(50) DEFAULT 'tv',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Create Tickets
    $pdo->exec("
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
        technician_id INT DEFAULT NULL,
        estimated_cost DECIMAL(10, 2) DEFAULT 0.00,
        preferred_date DATE DEFAULT NULL,
        attachment_url VARCHAR(255) DEFAULT NULL,
        location_lat DECIMAL(10, 7) DEFAULT NULL,
        location_lng DECIMAL(10, 7) DEFAULT NULL,
        location_address VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_tkt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_tkt_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        CONSTRAINT fk_tkt_tech FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Create History
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS ticket_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        changed_by_user_id INT NOT NULL,
        status_from VARCHAR(50),
        status_to VARCHAR(50) NOT NULL,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_hist_tkt FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        CONSTRAINT fk_hist_usr FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Create Comments
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS ticket_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        is_internal TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_cmnt_tkt FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        CONSTRAINT fk_cmnt_usr FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 6. Create Reviews
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL UNIQUE,
        user_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_rev_tkt FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        CONSTRAINT fk_rev_usr FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "[OK] Tables verified / created successfully.\n";

    // 1. Seed Categories if empty
    $catCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($catCount === 0) {
        $categories = [
            ['Smart TVs & Displays', 'smart-tvs', 'tv', 'LED, OLED, QLED, Smart TVs, and monitors repair & screen replacement.'],
            ['Refrigerators & Freezers', 'refrigerators', 'refrigerator', 'Single door, double door, side-by-side, and commercial freezer maintenance.'],
            ['Washing Machines & Dryers', 'washing-machines', 'washer', 'Front load, top load, semi-automatic washers and clothes dryers.'],
            ['Air Conditioners & HVAC', 'air-conditioners', 'wind', 'Split ACs, window ACs, inverter AC servicing and gas refill.'],
            ['Laptops, PCs & Electronics', 'laptops-pcs', 'laptop', 'Laptops, desktop PCs, motherboards, power supply and hardware upgrades.'],
            ['Microwave Ovens & Kitchenware', 'microwaves', 'microwave', 'Microwave ovens, induction cooktops, dishwashers, and small kitchen electronics.']
        ];

        $catStmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
        foreach ($categories as $cat) {
            $catStmt->execute($cat);
        }
        echo "[OK] Categories seeded.\n";
    } else {
        echo "[OK] Categories already exist.\n";
    }

    // 2. Seed Default Operational Staff Accounts if empty
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'technician')")->fetchColumn();
    if ($userCount === 0) {
        $adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $techPasswordHash = password_hash('tech123', PASSWORD_DEFAULT);

        $users = [
            ['System Admin', 'admin@fixmydevice.com', $adminPasswordHash, '+1 800 555 0199', '100 Service HQ Blvd, Tech City', 'admin'],
            ['Alex Miller (Technician)', 'tech@fixmydevice.com', $techPasswordHash, '+1 800 555 0244', 'Technician Center Hub 4', 'technician']
        ];

        $userStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $userStmt->execute($u);
        }
        echo "[OK] Default Admin & Technician accounts seeded.\n";
    } else {
        echo "[OK] Operational staff accounts already exist.\n";
    }

    echo "FixMyDevice Setup Completed Successfully!\n";

} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}
