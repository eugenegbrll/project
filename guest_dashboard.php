<?php
session_start();
require 'db.php';

$courses_query = "SELECT course_id, course_name, description, created_at FROM courses ORDER BY created_at DESC LIMIT 6";
$courses_result = $conn->query($courses_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Guest_Dashboard</title>
    <link rel="stylesheet" href="guest_dashboard.css">
</head>
<body>

<header>
    <div class="container">
        <h1><a href="guest_dashboard.php" style="color:white;text-decoration:none">EduQuest</a></h1>
        <nav>
            <p><a href="register.php">Register</a></p>
            <p><a href="login.php">Login</a></p>
        </nav>
    </div>
</header>

<main>
    <section class="hero-section">
        <div class="hero-content">
            <h2>Selamat Datang di EduQuest!</h2>
            <p>Platform pembelajaran online terbaik untuk meningkatkan pengetahuan dan keterampilan Anda</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn-primary">Mulai Belajar Gratis</a>
                <a href="#courses" class="btn-secondary">Jelajahi Kursus</a>
            </div>
        </div>
    </section>

    <section class="features-section">
        <h2>Mengapa Memilih EduQuest?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Materi Berkualitas</h3>
                <p>Akses ke berbagai kursus berkualitas tinggi yang dirancang oleh ahli</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏰</div>
                <h3>Belajar Fleksibel</h3>
                <p>Belajar kapan saja dan di mana saja sesuai dengan jadwal Anda</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Quiz Interaktif</h3>
                <p>Uji pemahaman Anda dengan quiz yang menantang dan mendapatkan skor</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Lacak Kemajuan</h3>
                <p>Monitor perkembangan belajar Anda dengan dashboard yang informatif</p>
            </div>
        </div>
    </section>

    <section class="courses-section" id="courses">
        <h2>Kursus Populer</h2>
        <p class="section-subtitle">Jelajahi berbagai kursus yang tersedia di platform kami</p>
        
        <div class="courses-grid">
            <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                <?php while($course = $courses_result->fetch_assoc()): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        </div>
                        <div class="course-body">
                            <p><?php echo htmlspecialchars(substr($course['description'], 0, 120)); ?>...</p>
                        </div>
                        <div class="course-footer">
                            <span class="course-date">📅 <?php echo date('d M Y', strtotime($course['created_at'])); ?></span>
                            <div class="locked-badge">
                                <span><a href="login.php" style="color:white; text-decoration:none">🔒 Login untuk akses</a></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-courses">
                    <p>Belum ada kursus tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="register-prompt">
            <h3>Ingin mengakses semua kursus?</h3>
            <p>Daftar sekarang dan mulai perjalanan belajar Anda!</p>
            <a href="register.php" class="btn-primary">Daftar Sekarang</a>
        </div>
    </section>

    <section class="stats-section">
        <h2>EduQuest dalam Angka</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">10+</div>
                <div class="stat-label">Siswa Aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">20+</div>
                <div class="stat-label">Kursus Tersedia</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10+</div>
                <div class="stat-label">Quiz Diselesaikan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">95%</div>
                <div class="stat-label">Kepuasan Pengguna</div>
            </div>
        </div>
    </section>
</main>

<footer>
    <?php include 'footer.html'; ?>
</footer>

</body>
</html>