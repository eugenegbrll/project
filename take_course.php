<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM courses 
        WHERE course_id NOT IN (
            SELECT course_id FROM student_courses WHERE user_id = ?
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ambil Course</title>
</head>
<body>
<h2>Pilih Course Baru</h2>
<a href="student_dashboard.php">⬅ Kembali</a>
<br><br>

<?php
if ($result->num_rows == 0) {
    echo "<p>Kamu sudah mengambil semua course 👍</p>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "<div style='margin-bottom: 10px;'>
                <strong>{$row['course_name']}</strong>
                <br>
                <a href='take_course_process.php?id={$row['course_id']}'>
                    Ambil Course Ini
                </a>
              </div>";
    }
}
?>
</body>
</html>
