-- Database untuk aplikasi web-mathdosman

CREATE DATABASE IF NOT EXISTS `web-mathdosman` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `web-mathdosman`;

-- Tabel pengguna (hanya untuk admin aplikasi)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel paket soal
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    subject_id INT NULL,
    materi VARCHAR(150) NULL,
    submateri VARCHAR(150) NULL,
    description TEXT NULL,
    show_answers_public TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_packages_status (status),
    KEY idx_packages_subject (subject_id),
    KEY idx_packages_subject_status (subject_id, status)
);

-- Tabel mata pelajaran (bank soal)
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL
);

-- Master materi dan submateri (untuk dropdown saat input soal)
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_material (subject_id, name),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submaterials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_submaterial (material_id, name),
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
);

-- Tabel bank soal pilihan ganda
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    pertanyaan TEXT NOT NULL,
    gambar_pertanyaan VARCHAR(255) NULL,
    tipe_soal VARCHAR(50) NOT NULL DEFAULT 'pg',
    pilihan_1 TEXT NULL,
    gambar_pilihan_1 VARCHAR(255) NULL,
    pilihan_2 TEXT NULL,
    gambar_pilihan_2 VARCHAR(255) NULL,
    pilihan_3 TEXT NULL,
    gambar_pilihan_3 VARCHAR(255) NULL,
    pilihan_4 TEXT NULL,
    gambar_pilihan_4 VARCHAR(255) NULL,
    pilihan_5 TEXT NULL,
    gambar_pilihan_5 VARCHAR(255) NULL,
    jawaban_benar TEXT NULL,
        status_soal ENUM('draft','published') NOT NULL DEFAULT 'draft',
        materi VARCHAR(255) DEFAULT NULL,
        submateri VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_questions_subject (subject_id),
    KEY idx_questions_status (status_soal),
    KEY idx_questions_subject_status (subject_id, status_soal),
    KEY idx_questions_created_at (created_at),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Relasi paket soal -> butir soal
CREATE TABLE IF NOT EXISTS package_questions (
    package_id INT NOT NULL,
    question_id INT NOT NULL,
    question_number INT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (package_id, question_id),
    KEY idx_package_questions_qid (question_id),
    UNIQUE KEY uniq_package_question_number (package_id, question_number),
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- User admin contoh (password: 123456)
INSERT INTO users (username, password_hash, name, role) VALUES
('admin', '$2y$10$2cKMz2pKAt0np3IvSwyCxOZ7rJjk1z/6GkVGR1Zir/Tc1sOzoVnTu', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    name = VALUES(name),
    role = VALUES(role);
