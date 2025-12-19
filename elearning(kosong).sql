-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 10, 2025 at 06:50 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elearning`
--

-- --------------------------------------------------------

--
-- Table structure for table `boss_battles`
--

CREATE TABLE `boss_battles` (
  `boss_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `boss_name` varchar(100) NOT NULL,
  `boss_health` int(11) DEFAULT 100,
  `boss_description` text DEFAULT NULL,
  `boss_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `boss_type` enum('escape','maze','mystery','combat') DEFAULT 'combat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boss_battles`
--

-- --------------------------------------------------------

--
-- Table structure for table `boss_battle_results`
--

CREATE TABLE `boss_battle_results` (
  `result_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `boss_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `is_defeated` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `defeated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boss_battle_results`
--


-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `teacher_id` int(11) DEFAULT NULL,
  `teacher_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--
INSERT INTO courses (course_id, course_name, description, teacher_id, teacher_name) VALUES
(1, 'Matematika', 'Materi Matematika', 1, 'Guru 1'),
(2, 'Fisika', 'Dasar-dasar Fisika', 1, 'Guru 1'),
(3, 'Kimia', 'Konsep dasar Kimia', 1, 'Guru 1'),
(4, 'Biologi', 'Ilmu kehidupan', 1, 'Guru 1'),
(5, 'Informatika', 'Dasar komputer dan logika', 1, 'Guru 1'),
(6, 'Ekonomi', 'Dasar ilmu ekonomi', 2, 'Guru 2'),
(7, 'Geografi', 'Ilmu bumi dan lingkungan', 2, 'Guru 2'),
(8, 'Sejarah', 'Sejarah Indonesia', 2, 'Guru 2'),
(9, 'Sosiologi', 'Ilmu sosial', 2, 'Guru 2'),
(10, 'Bahasa Indonesia', 'Bahasa dan sastra', 2, 'Guru 2');


-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `material_title` varchar(100) NOT NULL,
  `material_content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO materials (material_id, course_id, material_title, material_content, level) VALUES
(1, 1, 'Persamaan Linear', 'Mengenal persamaan linear satu variabel', 1),
(2, 2, 'Hukum Newton', 'Konsep gaya dan gerak', 1),
(3, 3, 'Atom dan Unsur', 'Struktur atom dasar', 1),
(4, 4, 'Sistem Pencernaan', 'Organ pencernaan manusia', 1),
(5, 5, 'Algoritma Dasar', 'Logika dan langkah sistematis', 1),
(6, 6, 'Permintaan dan Penawaran', 'Konsep pasar', 1),
(7, 7, 'Peta dan Skala', 'Membaca peta', 1),
(8, 8, 'Proklamasi Kemerdekaan', 'Sejarah 17 Agustus 1945', 1),
(9, 9, 'Interaksi Sosial', 'Bentuk-bentuk interaksi sosial', 1),
(10, 10, 'Teks Narasi', 'Ciri dan struktur teks narasi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `material_completions`
--

CREATE TABLE `material_completions` (
  `completion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_completions`
--

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `quiz_id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` enum('A','B','C','D') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO quizzes (quiz_id, material_id, question, option_a, option_b, option_c, option_d, correct_answer) VALUES

-- 
-- MATEMATIKA (Material 1)
-- 
(1, 1, '2x + 3 = 7, nilai x adalah?', '1', '2', '3', '4', 'B'),
(2, 1, 'Bentuk umum persamaan linear satu variabel adalah?', 'ax + b = 0', 'ax² + bx + c = 0', 'a/b = c', 'x² = a', 'A'),
(3, 1, 'Jika x = 3, maka nilai 5x adalah?', '8', '10', '15', '20', 'C'),
(4, 1, '2x = 10, nilai x adalah?', '2', '5', '10', '20', 'B'),
(5, 1, 'Persamaan linear memiliki pangkat tertinggi?', '0', '1', '2', '3', 'B'),

-- 
-- FISIKA (Material 2)
-- 
(6, 2, 'Hukum Newton I disebut hukum?', 'Aksi reaksi', 'Inersia', 'Gravitasi', 'Momentum', 'B'),
(7, 2, 'Satuan gaya dalam SI adalah?', 'Joule', 'Newton', 'Watt', 'Pascal', 'B'),
(8, 2, 'Alat untuk mengukur gaya adalah?', 'Termometer', 'Barometer', 'Dinamometer', 'Voltmeter', 'C'),
(9, 2, 'Gaya dapat menyebabkan benda?', 'Diam saja', 'Berubah warna', 'Berubah bentuk', 'Menguap', 'C'),
(10, 2, 'Hukum Newton II membahas tentang?', 'Inersia', 'Gaya dan percepatan', 'Aksi reaksi', 'Gravitasi', 'B'),

-- 
-- KIMIA (Material 3)
-- 
(11, 3, 'Partikel bermuatan negatif adalah?', 'Proton', 'Neutron', 'Elektron', 'Ion', 'C'),
(12, 3, 'Jumlah proton menentukan?', 'Massa atom', 'Nomor atom', 'Isotop', 'Ion', 'B'),
(13, 3, 'Inti atom terdiri dari?', 'Elektron', 'Proton dan neutron', 'Ion', 'Molekul', 'B'),
(14, 3, 'Atom netral berarti jumlah proton dan elektron?', 'Berbeda', 'Tidak tentu', 'Sama', 'Nol', 'C'),
(15, 3, 'Partikel atom yang tidak bermuatan adalah?', 'Proton', 'Elektron', 'Neutron', 'Ion', 'C'),

-- 
-- BIOLOGI (Material 4)
-- 
(16, 4, 'Fungsi lambung adalah?', 'Menyerap air', 'Mencerna protein', 'Menyaring darah', 'Mengatur suhu', 'B'),
(17, 4, 'Pencernaan pertama kali terjadi di?', 'Lambung', 'Usus halus', 'Mulut', 'Kerongkongan', 'C'),
(18, 4, 'Enzim ptialin terdapat di?', 'Lambung', 'Mulut', 'Usus', 'Hati', 'B'),
(19, 4, 'Fungsi usus halus adalah?', 'Menyerap air', 'Mencerna dan menyerap sari makanan', 'Menghancurkan protein', 'Mengatur suhu', 'B'),
(20, 4, 'Organ pencernaan terpanjang adalah?', 'Lambung', 'Usus halus', 'Usus besar', 'Kerongkongan', 'B'),

-- 
-- INFORMATIKA (Material 5)
-- 
(21, 5, 'Algoritma adalah?', 'Bahasa pemrograman', 'Langkah sistematis', 'Perangkat keras', 'Jaringan', 'B'),
(22, 5, 'Algoritma harus bersifat?', 'Acak', 'Tidak jelas', 'Sistematis', 'Berulang', 'C'),
(23, 5, 'Contoh algoritma dalam kehidupan sehari-hari?', 'Menonton TV', 'Memasak mie instan', 'Tidur', 'Berjalan', 'B'),
(24, 5, 'Flowchart digunakan untuk?', 'Menggambar', 'Menulis kode', 'Menyusun algoritma', 'Mengedit video', 'C'),
(25, 5, 'Urutan langkah algoritma harus?', 'Terbalik', 'Acak', 'Berurutan', 'Rahasia', 'C'),

-- 
-- EKONOMI (Material 6)
-- 
(26, 6, 'Jika harga naik, permintaan akan?', 'Naik', 'Tetap', 'Turun', 'Hilang', 'C'),
(27, 6, 'Permintaan adalah?', 'Jumlah barang yang ditawarkan', 'Jumlah barang yang dibeli', 'Jumlah barang yang diminta', 'Jumlah produksi', 'C'),
(28, 6, 'Jika harga turun, permintaan akan?', 'Turun', 'Naik', 'Tetap', 'Hilang', 'B'),
(29, 6, 'Hukum permintaan menyatakan harga dan permintaan bersifat?', 'Sejalan', 'Berbanding lurus', 'Berbanding terbalik', 'Tetap', 'C'),
(30, 6, 'Contoh kebutuhan primer adalah?', 'Televisi', 'Mobil', 'Makanan', 'Emas', 'C'),

-- 
-- GEOGRAFI (Material 7)
-- 
(31, 7, 'Skala peta 1:100.000 artinya?', '1 cm = 1 km', '1 cm = 100 km', '1 cm = 100 m', '1 cm = 10 km', 'A'),
(32, 7, 'Peta digunakan untuk?', 'Menghias dinding', 'Menunjukkan lokasi', 'Mengukur suhu', 'Menentukan waktu', 'B'),
(33, 7, 'Skala besar menunjukkan wilayah?', 'Luas', 'Sempit', 'Negara', 'Benua', 'B'),
(34, 7, 'Contoh skala angka adalah?', '1 : 100.000', 'Legenda', 'Warna', 'Simbol', 'A'),
(35, 7, 'Garis lintang digunakan untuk menentukan?', 'Waktu', 'Iklim', 'Letak astronomis', 'Ketinggian', 'C'),

-- 
-- SEJARAH (Material 7)
-- 
(36, 8, 'Proklamasi dibacakan oleh?', 'Soedirman', 'Soekarno', 'Hatta', 'Sjahrir', 'B'),
(37, 8, 'Proklamasi kemerdekaan terjadi pada tahun?', '1944', '1945', '1946', '1950', 'B'),
(38, 8, 'Naskah proklamasi diketik oleh?', 'Soekarno', 'Sayuti Melik', 'Hatta', 'Ahmad Soebardjo', 'B'),
(39, 8, 'Tempat pembacaan proklamasi adalah?', 'Istana Negara', 'Rengasdengklok', 'Pegangsaan Timur', 'Monas', 'C'),
(40, 8, 'Tujuan proklamasi adalah?', 'Menguasai wilayah', 'Menyatakan kemerdekaan', 'Menjajah', 'Perdagangan', 'B'),

-- 
-- SOSIOLOGI (Material 9)
-- 
(41, 9, 'Interaksi sosial adalah?', 'Hubungan antar individu', 'Pertentangan sosial', 'Isolasi sosial', 'Konflik sosial', 'A'),
(42, 9, 'Contoh interaksi sosial positif adalah?', 'Bullying', 'Kerjasama', 'Perkelahian', 'Diskriminasi', 'B'),
(43, 9, 'Sosialisasi bertujuan untuk?', 'Mengisolasi individu', 'Membentuk norma sosial', 'Meningkatkan konflik', 'Mengurangi interaksi', 'B'),
(44, 9, 'Norma sosial berfungsi untuk?', 'Membingungkan masyarakat', 'Mengatur perilaku', 'Meningkatkan konflik', 'Mengisolasi individu', 'B'),
(45, 9, 'Contoh bentuk interaksi sosial adalah?', 'Pertengkaran', 'Kerjasama dalam kelompok', 'Isolasi diri', 'Diskriminasi', 'B'),


-- 
-- BAHASA INDONESIA (Material 10)
-- 
(46, 10, 'Teks narasi berfungsi untuk?', 'Membujuk', 'Menghibur', 'Menjelaskan', 'Mengkritik', 'B'),
(47, 10, 'Teks narasi berisi?', 'Langkah-langkah', 'Cerita', 'Pendapat', 'Data', 'B'),
(48, 10, 'Tokoh dalam narasi disebut?', 'Objek', 'Subjek', 'Pelaku', 'Tema', 'C'),
(49, 10, 'Latar dalam cerita meliputi?', 'Tema', 'Amanat', 'Waktu dan tempat', 'Judul', 'C'),
(50, 10, 'Contoh teks narasi adalah?', 'Cerpen', 'Pidato', 'Laporan', 'Iklan', 'A');


-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `result_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_answer` varchar(1) NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `student_course_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

-- --------------------------------------------------------

--
-- Table structure for table `todo_list`
--

CREATE TABLE `todo_list` (
  `todo_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `task_description` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todo_list`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `full_name` varchar(100) NOT NULL,
  `favorite_animal` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users (user_id, username, password, role, full_name, favorite_animal) VALUES
(1, 'admin1', '$2a$12$4x8ctF4D18135Yi9nmKvleeDUxmb93dXgJF1OQDLAnfTrWkvRm15O', 'admin', 'Guru 1', ''),
(2, 'admin2', '$2a$12$5fK/8km86cpnfIZQOyg5A..dE9zrX2big/9nF9zkiHnSga/pDG/Ci', 'admin', 'Guru 2', '');

-- Optional enhancement: Add a table to store aggregated material scores
-- This improves performance for the graph and allows historical tracking

CREATE TABLE `material_scores` (
  `score_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `correct_answers` int(11) NOT NULL DEFAULT 0,
  `total_questions` int(11) NOT NULL,
  `score_percentage` decimal(5,2) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`score_id`),
  UNIQUE KEY `unique_user_material_score` (`user_id`, `material_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_material_id` (`material_id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_user_completed` (`user_id`, `completed_at`),
  KEY `idx_course_scores` (`course_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `boss_battles`
--
ALTER TABLE `boss_battles`
  ADD PRIMARY KEY (`boss_id`),
  ADD UNIQUE KEY `unique_material_boss` (`material_id`);

--
-- Indexes for table `boss_battle_results`
--
ALTER TABLE `boss_battle_results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `unique_user_boss` (`user_id`,`boss_id`),
  ADD KEY `boss_id` (`boss_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `fk_teacher_id` (`teacher_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `material_completions`
--
ALTER TABLE `material_completions`
  ADD PRIMARY KEY (`completion_id`),
  ADD UNIQUE KEY `unique_user_material` (`user_id`,`material_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `unique_user_quiz` (`user_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`student_course_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `todo_list`
--
ALTER TABLE `todo_list`
  ADD PRIMARY KEY (`todo_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boss_battles`
--
ALTER TABLE `boss_battles`
  MODIFY `boss_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `boss_battle_results`
--
ALTER TABLE `boss_battle_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `material_completions`
--
ALTER TABLE `material_completions`
  MODIFY `completion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `student_course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `todo_list`
--
ALTER TABLE `todo_list`
  MODIFY `todo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `boss_battles`
--
ALTER TABLE `boss_battles`
  ADD CONSTRAINT `boss_battles_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `boss_battle_results`
--
ALTER TABLE `boss_battle_results`
  ADD CONSTRAINT `boss_battle_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `boss_battle_results_ibfk_2` FOREIGN KEY (`boss_id`) REFERENCES `boss_battles` (`boss_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `boss_battle_results_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_teacher_id` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_completions`
--
ALTER TABLE `material_completions`
  ADD CONSTRAINT `material_completions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_completions_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `todo_list`
--
ALTER TABLE `todo_list`
  ADD CONSTRAINT `todo_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Recalculate all course progress after import
UPDATE student_courses sc
SET progress = (
    SELECT ROUND(
        IFNULL(
            (COUNT(DISTINCT mc.material_id) * 100.0) / 
            NULLIF((SELECT COUNT(*) FROM materials m WHERE m.course_id = sc.course_id), 0),
            0
        )
    )
    FROM material_completions mc
    INNER JOIN materials m ON mc.material_id = m.material_id
    WHERE mc.user_id = sc.user_id 
    AND m.course_id = sc.course_id
)
WHERE EXISTS (
    SELECT 1 FROM courses c WHERE c.course_id = sc.course_id
);

-- Ensure progress doesn't exceed 100
UPDATE student_courses SET progress = 100 WHERE progress > 100;

-- Ensure progress is at least 0
UPDATE student_courses SET progress = 0 WHERE progress IS NULL;
