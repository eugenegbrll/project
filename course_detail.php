<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$check = $conn->query("SELECT * FROM student_courses WHERE user_id = $user_id AND course_id = $course_id");
if ($check->num_rows == 0) {
    echo "Kamu belum terdaftar di course ini.";
    exit();
}

$course = $conn->query("SELECT * FROM courses WHERE course_id = $course_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['course_name']) ?></title>
    <link rel="stylesheet" href="course_detail.css">
</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
        }
    </script>

    <header>
        <div class="bar">
            <h1><a href="student_dashboard.php">EduQuest</a></h1>
            <nav>
                <button id="theme-toggle">🌙</button>
                <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="student_profile.php" class="prof">Profile</a></p>
                <p><a href="logout.php" class="logout">Logout</a></p>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <a href="student_dashboard.php" class="back-link">⬅ Kembali ke Dashboard</a>
            
            <h1><?= htmlspecialchars($course['course_name']) ?></h1>
            
            <?php if (!empty($course['description'])): ?>
                <p class="course-desc"><?= htmlspecialchars($course['description']) ?></p>
            <?php endif; ?>
            
            <p><strong>Pengajar:</strong> <?= htmlspecialchars($course['teacher_name']) ?></p>

            <h2>Materi Pelajaran</h2>

            <?php
            $materials = $conn->query("SELECT * FROM materials WHERE course_id = $course_id ORDER BY level ASC");
            
            while ($mat = $materials->fetch_assoc()) {
                $material_id = $mat['material_id'];
                $check_complete = $conn->query("SELECT * FROM material_completions WHERE user_id = $user_id AND material_id = $material_id");
                $is_completed = $check_complete->num_rows > 0;
                $class = $is_completed ? 'material-box completed' : 'material-box';
                
                echo "<div class='$class'>";
                echo "<h3>📖 Level {$mat['level']}: " . htmlspecialchars($mat['material_title']) . "</h3>";
                
                if ($is_completed) {
                    echo "<p class='status-complete'><strong>✅ Materi Selesai</strong></p>";
                }
                
                echo "<p class='mat-content'>" . nl2br(htmlspecialchars($mat['material_content'])) . "</p>";
                
                if (!empty($mat['file_path']) && file_exists($mat['file_path'])) {
                    echo "<div class='file-preview'>";
                    echo "<h4>📎 File Materi</h4>";
                    echo "<p class='file-name'>" . htmlspecialchars($mat['file_name']) . "</p>";
                    echo "<a href='{$mat['file_path']}' download class='download-btn'>⬇️ Download File</a>";
                    echo "</div>";
                }
                
                $quiz_check = $conn->query("SELECT COUNT(*) as total FROM quizzes WHERE material_id = $material_id");
                $quiz_count = $quiz_check->fetch_assoc()['total'];
                
                if ($quiz_count > 0) {
                    echo "<a href='quiz.php?material_id=$material_id' class='btn'>Mulai Quiz ($quiz_count)</a>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </main>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>

    <script>
        const btn = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme === 'dark') {
            btn.textContent = '☀️';
        }

        btn.addEventListener('click', () => {
            let theme = 'light';
            if (document.body.getAttribute('data-theme') !== 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                theme = 'dark';
                btn.textContent = '☀️';
            } else {
                document.body.removeAttribute('data-theme');
                btn.textContent = '🌙';
            }
            localStorage.setItem('theme', theme);
        });
    </script>
</body>
</html>
