CREATE DATABASE IF NOT EXISTS db_student DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_student;

SET NAMES utf8mb4;
SET CHARACTER_SET_CLIENT = utf8mb4;
SET CHARACTER_SET_RESULTS = utf8mb4;
SET CHARACTER_SET_CONNECTION = utf8mb4;

-- --------------------------------------------------------
-- ตาราง tb_users (ผู้ใช้งานระบบ / บัญชีเข้าสู่ระบบ)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tb_users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL COMMENT 'ชื่อผู้ใช้',
  `password` VARCHAR(255) NOT NULL COMMENT 'รหัสผ่าน (Hashed)',
  `fullname` VARCHAR(100) NOT NULL COMMENT 'ชื่อ-นามสกุล',
  `role` VARCHAR(20) DEFAULT 'admin' COMMENT 'สิทธิ์การใช้งาน',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่มบัญชีผู้ใช้เริ่มต้น (admin / admin123)
INSERT IGNORE INTO `tb_users` (`username`, `password`, `fullname`, `role`) VALUES
('admin', '$2y$10$ip0L9FagLNgFjQvIaID15OJ7N0vvf3TA4NOS7wWCtct3hLpW/Yga2', 'ผู้ดูแลระบบ', 'admin');

-- --------------------------------------------------------
-- ตาราง tb_major (สาขาวิชา)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tb_major` (
  `major_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `major_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`major_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `tb_major` (`major_code`, `major_name`, `remark`) VALUES
('AI', 'ปัญญาประดิษฐ์', 'AI และ Data Science'),
('CS', 'วิทยาการคอมพิวเตอร์', 'หลักสูตรปรับปรุง 2565'),
('GIS', 'ภูมิสารสนเทศ', 'หลักสูตร 4 ปี'),
('IT', 'เทคโนโลยีสารสนเทศ', 'หลักสูตร 4 ปี'),
('SE', 'วิศวกรรมซอฟต์แวร์', 'หลักสูตรใหม่');

-- --------------------------------------------------------
-- ตาราง tb_student (นักศึกษา)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tb_student` (
    `StudentID` VARCHAR(15) PRIMARY KEY COMMENT 'รหัสนักศึกษา',
    `Student_Name` VARCHAR(50) NOT NULL COMMENT 'ชื่อ',
    `Student_Surname` VARCHAR(50) NOT NULL COMMENT 'นามสกุล',
    `Student_Website` VARCHAR(255) NULL COMMENT 'เว็บไซต์/พอร์ตโฟลิโอ',
    `major_code` VARCHAR(50) NULL COMMENT 'รหัสสาขาวิชา',
    CONSTRAINT `fk_student_major` FOREIGN KEY (`major_code`) REFERENCES `tb_major` (`major_code`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tb_student` (`StudentID`, `Student_Name`, `Student_Surname`, `Student_Website`, `major_code`) VALUES
('67642206024-1','นายปัญญาวุฒิ','สถิตคุณวุฒิ','https://github.com/panyawut', 'CS'),
('68642206001-8','นางสาวณัฏฐณิชา','จันทร์มูล','https://natthanicha.dev', 'CS'),
('68642206003-4','นายชโยดม','มือแข็ง','https://chayodom.com', 'CS'),
('68642206004-2','นายณัฐพนธ์','ทองมา','', 'CS'),
('68642206006-7','นายดนุสรณ์','พย่อม','', 'CS'),
('68642206007-5','นายธิติสรณ์','มะโนวัง','', 'SE'),
('68642206008-3','นายธีรัชชา','เเซ่ซิน','', 'SE'),
('68642206009-1','นางสาวพรรณนารา','พอใจ','', 'SE'),
('68642206010-9','นางสาวภัทธ์ฤทัย','บุญศรี','', 'SE'),
('68642206011-7','นางสาววาสิณี','บุญสุข','', 'SE'),
('68642206012-5','นายศรหิรัณย์','คำยอด','', 'IT'),
('68642206013-3','นายหัสพงศ์','แสนคำ','', 'IT'),
('68642206014-1','นายกฤตพรต','กวางแก้ว','', 'IT'),
('68642206015-8','นางสาวขนิษฐา','สาใจ','', 'IT'),
('68642206016-6','นายจตุพร','ปินตาเชื้อ','', 'IT'),
('68642206017-4','นายเจษฎา','หมอยา','', 'AI'),
('68642206018-2','นางสาวณัฐศิริญา','ปวงเหมือง','', 'AI'),
('68642206020-8','นางสาวนัฐธิดา','หงษ์ห้า','', 'AI'),
('68642206022-4','นางสาววิภาดา','เกี๋ยงหนุน','', 'AI'),
('68642206026-5','นางสาวขวัญจิรา','จารุจันทร์','', 'AI'),
('68642206027-3','นายกรณ์ดนัย','เรือนน้อย','', 'GIS'),
('68642206028-1','นายจีรเดช','แก้วร่วมวงค์','', 'GIS'),
('68642206029-9','นางสาวณัชชา','ตามเพียร','', 'GIS'),
('68642206031-5','นายภีม','ทองจันทร์','', 'GIS'),
('68642206032-3','นายภีรพัตร','ชมภู','', 'GIS');
