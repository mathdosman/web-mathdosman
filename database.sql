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

-- Tabel siswa (akun untuk login siswa)
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_siswa VARCHAR(120) NOT NULL,
    kelas VARCHAR(30) NOT NULL,
    rombel VARCHAR(30) NOT NULL,
    no_hp VARCHAR(30) NULL,
    no_hp_ortu VARCHAR(30) NULL,
    foto VARCHAR(255) NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_students_kelas (kelas),
    KEY idx_students_rombel (rombel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Master kelas/rombel (untuk dropdown & validasi data siswa)
CREATE TABLE IF NOT EXISTS kelas_rombels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelas VARCHAR(30) NOT NULL,
    rombel VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uniq_kelas_rombels (kelas, rombel),
    KEY idx_kelas_rombels_kelas (kelas),
    KEY idx_kelas_rombels_rombel (rombel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel paket soal
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    subject_id INT NULL,
    materi VARCHAR(150) NULL,
    submateri VARCHAR(150) NULL,
    intro_content_id INT NULL,
    description TEXT NULL,
    show_answers_public TINYINT(1) NOT NULL DEFAULT 0,
    is_exam TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_packages_status (status),
    KEY idx_packages_subject (subject_id),
    KEY idx_packages_subject_status (subject_id, status),
    KEY idx_packages_intro_content (intro_content_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel konten (materi/berita)
CREATE TABLE IF NOT EXISTS contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('materi','berita') NOT NULL DEFAULT 'materi',
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    materi VARCHAR(150) NULL,
    submateri VARCHAR(150) NULL,
    excerpt TEXT NULL,
    content_html MEDIUMTEXT NOT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_contents_type_status (type, status),
    KEY idx_contents_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    penyelesaian TEXT NULL,
        status_soal ENUM('draft','published') NOT NULL DEFAULT 'draft',
        materi VARCHAR(255) DEFAULT NULL,
        submateri VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_questions_subject (subject_id),
    KEY idx_questions_status (status_soal),
    KEY idx_questions_subject_status (subject_id, status_soal),
    KEY idx_questions_created_at (created_at),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel penugasan siswa (paket soal -> siswa)
-- Ditaruh setelah packages/questions agar FOREIGN KEY bisa dibuat.
CREATE TABLE IF NOT EXISTS student_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    package_id INT NOT NULL,
    jenis ENUM('tugas','ujian') NOT NULL DEFAULT 'tugas',
    duration_minutes INT NULL,
    judul VARCHAR(200) NULL,
    catatan TEXT NULL,
    -- Jika 1: siswa boleh melihat detail jawaban + kunci setelah status DONE.
    -- Default 0 untuk menjaga kerahasiaan kunci.
    allow_review_details TINYINT(1) NOT NULL DEFAULT 0,
    -- Token 6 digit (opsional) untuk akses/validasi ujian dari sisi admin.
    token_code CHAR(6) NULL,
    status ENUM('assigned','done') NOT NULL DEFAULT 'assigned',
    correct_count INT NULL,
    total_count INT NULL,
    score DECIMAL(5,2) NULL,
    graded_at TIMESTAMP NULL DEFAULT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL DEFAULT NULL,
    -- Jika tidak NULL: ujian terkunci (siswa pernah keluar), perlu reset admin.
    exam_revoked_at TIMESTAMP NULL DEFAULT NULL,
    -- Jika 1: acak urutan soal untuk UJIAN.
    shuffle_questions TINYINT(1) NOT NULL DEFAULT 0,
    -- Jika 1: acak urutan opsi pilihan ganda untuk UJIAN.
    shuffle_options TINYINT(1) NOT NULL DEFAULT 0,
    due_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_sa_student (student_id),
    KEY idx_sa_package (package_id),
    KEY idx_sa_token (token_code),
    KEY idx_sa_exam_revoked (exam_revoked_at),
    KEY idx_sa_started (started_at),
    KEY idx_sa_due (due_at),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel jawaban siswa per penugasan (untuk hitung nilai otomatis)
CREATE TABLE IF NOT EXISTS student_assignment_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    question_id INT NOT NULL,
    answer TEXT NULL,
    is_correct TINYINT(1) NULL,
    answered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uniq_saa (assignment_id, question_id),
    KEY idx_saa_student (student_id),
    KEY idx_saa_assignment (assignment_id),
    KEY idx_saa_question (question_id),
    FOREIGN KEY (assignment_id) REFERENCES student_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analitik sederhana: hitung total view per halaman (paket/konten)
CREATE TABLE IF NOT EXISTS page_views (
    kind ENUM('package','content') NOT NULL,
    item_id INT NOT NULL,
    views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_viewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (kind, item_id),
    KEY idx_page_views_views (views),
    KEY idx_page_views_last_viewed (last_viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel skor mini game matematika (publik + siswa)
CREATE TABLE IF NOT EXISTS math_game_scores (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    kelas VARCHAR(50) DEFAULT NULL,
    rombel VARCHAR(50) DEFAULT NULL,
    mode VARCHAR(20) NOT NULL DEFAULT 'addsub',
    score INT NOT NULL,
    questions_answered INT NOT NULL,
    max_level INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_student_id (student_id),
    KEY idx_score (score),
    KEY idx_created_at (created_at),
    KEY idx_mode (mode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
