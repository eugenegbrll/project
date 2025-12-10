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
    <title><?= htmlspecialchars($course['course_name']) ?></title>
    <link rel="stylesheet" href="course_detail.css">
</head>
<body>
    <header>
        <div class="bar">
            <h1>EduQuest</h1>
            <nav>
                <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="logout.php">Logout</a></p>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
        <a href="student_dashboard.php" class="back-link">⬅ Kembali ke Dashboard</a>
        
        <h1><?= htmlspecialchars($course['course_name']) ?></h1>
        
        <?php if (!empty($course['description'])): ?>
            <p style="color: #666; font-size: 16px;"><?= htmlspecialchars($course['description']) ?></p>
        <?php endif; ?>
        
        <p><strong>Pengajar:</strong> <?= htmlspecialchars($course['teacher_name']) ?></p>

        <h2>Materi Pelajaran</h2>

        <?php
        $materials = $conn->query("SELECT * FROM materials WHERE course_id = $course_id ORDER BY level ASC");
        
        if ($materials->num_rows == 0) {
            echo "<p>Belum ada materi tersedia.</p>";
        }

        while ($mat = $materials->fetch_assoc()) {
            $material_id = $mat['material_id'];
            
            $check_complete = $conn->query("SELECT * FROM material_completions WHERE user_id = $user_id AND material_id = $material_id");
            $is_completed = $check_complete->num_rows > 0;
            
            $class = $is_completed ? 'material-box completed' : 'material-box';
            
            echo "<div class='$class'>";
            echo "<h3>📖 Level {$mat['level']}: " . htmlspecialchars($mat['material_title']) . "</h3>";
            
            if ($is_completed) {
                echo "<p style='color: green;'><strong>✅ Materi Selesai</strong></p>";
            }
            
            echo "<p>" . nl2br(htmlspecialchars($mat['material_content'])) . "</p>";
            
            if (!empty($mat['file_path']) && file_exists($mat['file_path'])) {
                echo "<div class='file-preview'>";
                echo "<h4>";
                
                $file_type = strtolower($mat['file_type']);
                $file_icon = '📎';
                $can_preview = false;
                
                if (in_array($file_type, ['mp4', 'mov'])) {
                    $file_icon = '🎥';
                    $can_preview = true;
                    echo "$file_icon Lihat Video Materi</h4>";
                    echo "<video controls>";
                    echo "<source src='{$mat['file_path']}' type='video/" . ($file_type == 'mov' ? 'quicktime' : 'mp4') . "'>";
                    echo "Browser Anda tidak mendukung video.</video>";
                } elseif ($file_type == 'mp3') {
                    $file_icon = '🎵';
                    $can_preview = true;
                    echo "$file_icon Dengarkan Audio Materi</h4>";
                    echo "<audio controls>";
                    echo "<source src='{$mat['file_path']}' type='audio/mpeg'>";
                    echo "Browser Anda tidak mendukung audio.</audio>";
                } elseif (in_array($file_type, ['jpg', 'jpeg', 'png'])) {
                    $file_icon = '🖼️';
                    $can_preview = true;
                    echo "$file_icon Lihat Gambar Materi</h4>";
                    echo "<img src='{$mat['file_path']}' alt='" . htmlspecialchars($mat['material_title']) . "'>";
                } elseif (in_array($file_type, ['pdf'])) {
                    $file_icon = '📄';
                    echo "$file_icon Dokumen PDF</h4>";
                    echo "<p>File: " . htmlspecialchars($mat['file_name']) . "</p>";
                } elseif (in_array($file_type, ['ppt', 'pptx'])) {
                    $file_icon = '📊';
                    echo "$file_icon Presentasi PowerPoint</h4>";
                    echo "<p>File: " . htmlspecialchars($mat['file_name']) . "</p>";
                } elseif (in_array($file_type, ['docx'])) {
                    $file_icon = '📝';
                    echo "$file_icon Dokumen Word</h4>";
                    echo "<p>File: " . htmlspecialchars($mat['file_name']) . "</p>";
                } else {
                    echo "$file_icon File Materi</h4>";
                    echo "<p>File: " . htmlspecialchars($mat['file_name']) . "</p>";
                }
                
                echo "<a href='{$mat['file_path']}' download='" . htmlspecialchars($mat['file_name']) . "' class='download-btn'>⬇️ Download File</a>";
                echo "</div>";
            }
            
            $quiz_check = $conn->query("SELECT COUNT(*) as total FROM quizzes WHERE material_id = $material_id");
            $quiz_count = $quiz_check->fetch_assoc()['total'];
            
            if ($quiz_count > 0) {
                echo "<p style='margin-top: 15px;'><strong>🎯 Kerjakan Quiz:</strong></p>";
                echo "<a href='quiz.php?material_id=$material_id' class='btn'>Mulai Quiz ($quiz_count pertanyaan)</a>";
            } else {
                echo "<p style='color: #999; margin-top: 15px;'><em>Quiz belum tersedia untuk materi ini.</em></p>";
            }
            
            if (!$is_completed && $quiz_count == 0) {
                echo "<form method='POST' action='mark_complete.php' style='display: inline;'>
                        <input type='hidden' name='material_id' value='$material_id'>
                        <input type='hidden' name='course_id' value='$course_id'>
                        <button type='submit' class='btn btn-success'>✅ Tandai Selesai</button>
                      </form>";
            }
            
            echo "</div>";
        }
        ?>
    </div>
    </main>
    

    <footer style="margin-top: 40px;">
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>