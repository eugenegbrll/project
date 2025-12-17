<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$teacher_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? 0;

$verify = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?");
$verify->bind_param("ii", $course_id, $teacher_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$course = $result->fetch_assoc();

if (isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $description = !empty($_POST['description']) ? $_POST['description'] : null;

    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, description = ? WHERE course_id = ? AND teacher_id = ?");
    $stmt->bind_param("ssii", $course_name, $description, $course_id, $teacher_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php?success=course_updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Course</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="admin_dashboard.php" style="color:white;text-decoration:none;">EduQuest</a></h1>
            <nav>
                <a href="edit_course.php?course_id=<?= $material['course_id'] ?>">← Kembali ke Course</a>
                <p>Selamat Datang, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="admin_profile.php" class="prof" style="color: white; text-decoration: none;">Profile</a></p>
                <p><a href="logout.php" style="color:rgb(255, 62, 62);font-size:medium;">Logout</a></p>
            </nav>
        </div>
    </header>

    <h1>Edit Course</h1>
    
    <div class="material-list">
        <form method="POST">
            <input type="hidden" name="update_course">
            
            <label>Nama Course:</label>
            <input type="text" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required><br><br>
            
            <label>Deskripsi Course:</label>
            <textarea name="description" placeholder="Deskripsi Course (opsional)"><?= htmlspecialchars($course['description'] ?? '') ?></textarea><br><br>
            
            <button type="submit">Update Course</button>
            <a href="admin_dashboard.php"><button type="button">Batal</button></a>
        </form>
    </div>

    <hr>

    <h2>Materi dalam Course Ini</h2>
    <div class="material-list">
        <?php
        $materials = $conn->query("SELECT m.*, 
                                   (SELECT COUNT(*) FROM quizzes WHERE material_id = m.material_id) as quiz_count 
                                   FROM materials m 
                                   WHERE m.course_id = $course_id");
        
        if ($materials->num_rows == 0) {
            echo "<p>Belum ada materi dalam course ini.</p>";
        } else {
            while ($mat = $materials->fetch_assoc()) {
                echo "<div class='course-box'>
                        <h3>" . htmlspecialchars($mat['material_title']) . " (Level " . $mat['level'] . ")</h3>";
                
                if (!empty($mat['file_name'])) {
                    echo "<p style='color: blue;'>📎 " . htmlspecialchars($mat['file_name']) . "</p>";
                }
                
                echo "<p style='color: #888;'>📝 " . $mat['quiz_count'] . " quiz</p>";
                echo "<a href='edit_material.php?material_id=" . $mat['material_id'] . "'>[Edit Materi]</a> ";
                echo "<a href='edit_quiz.php?material_id=" . $mat['material_id'] . "'>[Edit Quiz]</a>";
                echo "</div>";
            }
        }
        ?>
    </div>


    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>