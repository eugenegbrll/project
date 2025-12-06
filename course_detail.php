<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Ambil info course + progress
$sql = "SELECT c.*, sc.progress 
        FROM courses c 
        LEFT JOIN student_courses sc 
        ON c.course_id = sc.course_id AND sc.user_id = ?
        WHERE c.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo "Course tidak ditemukan";
    exit();
}

// Ambil materials
$sql_m = "SELECT * FROM materials WHERE course_id = ? ORDER BY level ASC";
$stmt_m = $conn->prepare($sql_m);
$stmt_m->bind_param("i", $course_id);
$stmt_m->execute();
$materials = $stmt_m->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Course</title>
</head>
<body>

<a href="student_dashboard.php">⬅ Kembali</a>

<h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
<p><strong>Guru:</strong> <?php echo htmlspecialchars($course['teacher_name']); ?></p>
<p><?php echo htmlspecialchars($course['description']); ?></p>

<h3>Progress Kamu: <?php echo $course['progress']; ?>%</h3>
<hr>

<h2>Daftar Materi</h2>

<?php
while ($m = $materials->fetch_assoc()) {
    echo "<div style='margin-bottom:20px;'>
            <h3>Level {$m['level']} - " . htmlspecialchars($m['material_title']) . "</h3>
            <p>" . nl2br(htmlspecialchars($m['material_content'])) . "</p>";

    // Tampilkan "Kerjakan Quiz" untuk material ini
    echo "<a href='quiz.php?material_id={$m['material_id']}'>Kerjakan Quiz ➜</a>";

    echo "</div><hr>";
}
?>

</body>
</html>
