<?php
require_once __DIR__ . '/db.php';

echo "<h2>กำลังตั้งค่าฐานข้อมูล...</h2>";

try {
    // 1. ตาราง users (เก็บทั้ง admin และนักศึกษา)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            fullname VARCHAR(100) NOT NULL,
            role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
            email VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            province VARCHAR(100) DEFAULT NULL,
            district VARCHAR(100) DEFAULT NULL,
            subdistrict VARCHAR(100) DEFAULT NULL,
            zipcode VARCHAR(10) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 1.1 ตารางกิจกรรม (activities) พร้อมช่วงเวลาประเมิน
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            is_open TINYINT(1) NOT NULL DEFAULT 1,
            start_time DATETIME DEFAULT NULL,
            end_time DATETIME DEFAULT NULL,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. ตารางช่วงเวลาเปิด-ปิดการประเมิน (evaluation_periods)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS evaluation_periods (
            id INT PRIMARY KEY DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 0,
            start_time DATETIME DEFAULT NULL,
            end_time DATETIME DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. ตารางข้อคำถาม (questions) ผูกกับ activities
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            activity_id INT NOT NULL DEFAULT 1,
            question_text TEXT NOT NULL,
            question_type ENUM('rating', 'text') NOT NULL DEFAULT 'rating',
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. ตารางการส่งแบบประเมิน (evaluations)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS evaluations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            activity_id INT NOT NULL DEFAULT 1,
            submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            suggestion TEXT DEFAULT NULL,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
            UNIQUE KEY uq_student_activity (student_id, activity_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. ตารางคำตอบรายข้อ (evaluation_answers)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS evaluation_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            evaluation_id INT NOT NULL,
            question_id INT NOT NULL,
            score TINYINT NOT NULL CHECK (score BETWEEN 1 AND 5),
            FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "<p style='color: green;'>✓ สร้างตารางเรียบร้อยแล้ว</p>";

    // --- ตรวจสอบและใส่ข้อมูลเริ่มต้น (Seed Data) ---

    // 1. เพิ่มผู้ดูแลระบบ (Admin)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = $pdo->prepare("
            INSERT INTO users (username, password, fullname, role) 
            VALUES ('admin', ?, 'ผู้ดูแลระบบ', 'admin')
        ");
        $insert_admin->execute([$admin_password]);
        echo "<p>✓ เพิ่มผู้ดูแลระบบ (admin) เรียบร้อย</p>";
    }

    // 2. ข้อมูลตั้งต้นช่วงเวลาประเมิน
    $stmt = $pdo->prepare("SELECT id FROM evaluation_periods WHERE id = 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("
            INSERT INTO evaluation_periods (id, is_active, start_time, end_time) 
            VALUES (1, 0, '2026-07-13 00:37:00', '2026-07-24 00:18:00')
        ");
        echo "<p>✓ เพิ่มค่าตั้งต้นช่วงเวลาประเมินเรียบร้อย</p>";
    }

    // 3. เพิ่มข้อคำถามตั้งต้น 8 ข้อ
    $stmt = $pdo->query("SELECT COUNT(*) FROM questions");
    if ($stmt->fetchColumn() == 0) {
        $default_questions = [
            [1, 'ความเหมาะสมกับเวลาและสถานที่', 'rating', 'active'],
            [2, 'ความพึงพอใจต่อสถานที่จัดงาน', 'rating', 'active'],
            [3, 'กิจกรรมมีความน่าสนใจและสนุกสนาน', 'rating', 'active'],
            [4, 'กิจกรรมมีความสามัคคีปรองดองระหว่างรุ่นพี่ รุ่นน้อง', 'rating', 'active'],
            [5, 'ความพึงพอใจต่ออาหาร และเครื่องดื่ม', 'rating', 'active'],
            [6, 'ความพึงพอใจต่อการเข้าร่วมกิจกรรม', 'rating', 'active'],
            [7, 'ภาพรวมของกิจกรรม ', 'rating', 'active'],
            [8, 'ข้อเสนอแนะอื่นๆ', 'text', 'active'],
        ];

        $insert_q = $pdo->prepare("
            INSERT INTO questions (sort_order, question_text, question_type, status) 
            VALUES (?, ?, ?, ?)
        ");
        foreach ($default_questions as $q) {
            $insert_q->execute($q);
        }
        echo "<p>✓ เพิ่มข้อคำถามตั้งต้น 8 ข้อเรียบร้อย</p>";
    }

    // 4. เพิ่มรายชื่อนักศึกษา 25 คน
    $students = [
        ['68642206001-8', 'นางสาวณัฏฐณิชา จันทร์มูล'],
        ['68642206002-6', 'นางสาวจรรยภรณ์ วรรณประเวศ'],
        ['68642206003-4', 'นายชโยดม มือแข็ง'],
        ['68642206004-2', 'นายณัฐพนธ์ ทองมา'],
        ['68642206006-7', 'นายดนุสรณ์ พย่อม'],
        ['68642206007-5', 'นายธิติสรณ์ มะโนวัง'],
        ['68642206008-3', 'นายธีรัชชา แซ่ซิน'],
        ['68642206009-1', 'นางสาวพรรณนารา พอใจ'],
        ['68642206010-9', 'นางสาวภัทร์ฤทัย บุญศรี'],
        ['68642206011-7', 'นางสาววาสิณี บุญสุข'],
        ['68642206012-5', 'นายศรหิรัณย์ คำยอด'],
        ['68642206013-3', 'นายหัสพงศ์ แสนคำ'],
        ['68642206014-1', 'นายกฤตพรต กวางแก้ว'],
        ['68642206015-8', 'นางสาวขนิษฐา สาใจ'],
        ['68642206016-6', 'นายจตุพร ปินตาเชื้อ'],
        ['68642206017-4', 'นายเจษฎา หมอยา'],
        ['68642206018-2', 'นางสาวณัฐศิริญา ปวงเหมือง'],
        ['68642206020-8', 'นางสาวณัฐธิดา หงษ์ห้า'],
        ['68642206022-4', 'นางสาววิภาดา เถี่ยงหนุน'],
        ['68642206026-5', 'นางสาวขวัญจิรา จารุจันทร์'],
        ['68642206027-3', 'นายกรณัดนัย เรือนน้อย'],
        ['68642206028-1', 'นายจีรเดช แก้วร่วมวงค์'],
        ['68642206029-9', 'นางสาวณัชชา ตามเพียร'],
        ['68642206031-5', 'นายภีม ทองจันทร์'],
        ['68642206032-3', 'นายภีรพัตร ชมภู'],
    ];

    $insert_std = $pdo->prepare("
        INSERT IGNORE INTO users (username, password, fullname, role) 
        VALUES (?, ?, ?, 'student')
    ");

    foreach ($students as $std) {
        $student_code = $std[0];
        $student_name = $std[1];
        // รหัสผ่านเริ่มต้นเท่ากับรหัสนักศึกษา
        $hashed_pwd = password_hash($student_code, PASSWORD_DEFAULT);
        $insert_std->execute([$student_code, $hashed_pwd, $student_name]);
    }
    echo "<p>✓ เพิ่มข้อมูลนักศึกษา 25 คนเรียบร้อย</p>";

    echo "<h3 style='color: green;'>ติดตั้งฐานข้อมูลเสร็จสมบูรณ์เรียบร้อยแล้ว!</h3>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "</p>";
}
