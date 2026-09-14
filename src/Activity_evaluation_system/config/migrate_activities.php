<?php
require_once __DIR__ . '/db.php';

echo "=== Starting Migration: Multi-Activity Support ===\n";

try {
    // 1. สร้างตาราง activities
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
    echo "✓ Table 'activities' ready.\n";

    // Seed default activities if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM activities");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO activities (id, name, description, status) VALUES
            (1, 'กิจกรรม สานสัมพันธ์พี่น้อง IT', 'กิจกรรมสานสัมพันธ์ระหว่างรุ่นพี่และรุ่นน้อง สาขาวิชาเทคโนโลยีสารสนเทศ', 'active'),
            (2, 'กิจกรรม วันกีฬาสี', 'การแข่งขันกีฬาเพื่อความสามัคคีและส่งเสริมสุขภาพ', 'active'),
            (3, 'กิจกรรม ไหว้ครู', 'พิธีไหว้ครูประจำปีการศึกษา แสดงความกตัญญูกตเวทิตาแด่ครูบาอาจารย์', 'active');
        ");
        echo "✓ Seeded default activities (1: สานสัมพันธ์, 2: กีฬาสี, 3: ไหว้ครู).\n";
    }

    // 2. เพิ่มคอลัมน์ activity_id ในตาราง questions ถ้ายังไม่มี
    $check_col = $pdo->query("SHOW COLUMNS FROM questions LIKE 'activity_id'");
    if (!$check_col->fetch()) {
        $pdo->exec("
            ALTER TABLE questions 
            ADD COLUMN activity_id INT NOT NULL DEFAULT 1 AFTER id,
            ADD CONSTRAINT fk_questions_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE;
        ");
        echo "✓ Added column 'activity_id' to 'questions' table.\n";
    } else {
        echo "• Column 'activity_id' already exists in 'questions'.\n";
    }

    // 3. เพิ่มคอลัมน์ activity_id ในตาราง evaluations ถ้ายังไม่มี
    $check_eval_col = $pdo->query("SHOW COLUMNS FROM evaluations LIKE 'activity_id'");
    if (!$check_eval_col->fetch()) {
        try {
            $pdo->exec("ALTER TABLE evaluations DROP FOREIGN KEY evaluations_ibfk_1");
            $pdo->exec("ALTER TABLE evaluations DROP INDEX student_id");
            $pdo->exec("ALTER TABLE evaluations ADD CONSTRAINT fk_evaluations_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE");
        } catch (Exception $e) {
            // Might already be modified
        }

        $pdo->exec("
            ALTER TABLE evaluations 
            ADD COLUMN activity_id INT NOT NULL DEFAULT 1 AFTER student_id,
            ADD CONSTRAINT fk_evaluations_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
            ADD UNIQUE KEY uq_student_activity (student_id, activity_id);
        ");
        echo "✓ Added column 'activity_id' and composite unique key (student_id, activity_id) to 'evaluations'.\n";
    } else {
        echo "• Column 'activity_id' already exists in 'evaluations'.\n";
    }

    echo "=== Migration Completed Successfully ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
