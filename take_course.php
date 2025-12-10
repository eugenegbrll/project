<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

$sql_all_courses = "SELECT COUNT(*) as total FROM courses";
$result_all = $conn->query($sql_all_courses);
$total_courses = $result_all->fetch_assoc()['total'];

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
    <link rel="stylesheet" href="take_course.css">
</head>
<body>


<main>
    <h2>Pilih Course Baru</h2>
    <a href="student_dashboard.php">⬅ Kembali</a>
    <br><br>

    <?php
    if ($total_courses == 0) {
        echo "<div class='empty-message'>
                <h3>Belum Ada Course</h3>
                <p>Saat ini belum ada course yang tersedia. Silakan hubungi admin untuk menambahkan course.</p>
            </div>";
    } elseif ($result->num_rows == 0) {
        echo "<div class='empty-message'>
                <h3>Maaf!</h3>
                <p>Kamu sudah mengambil semua course yang tersedia!</p>
            </div>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='course-box'>
                    <strong>" . htmlspecialchars($row['course_name']) . "</strong>";
            
            if (!empty($row['description'])) {
                echo "<p style='color: #666; margin: 5px 0;'>" . htmlspecialchars($row['description']) . "</p>";
            }
            
            if (!empty($row['teacher_name'])) {
                echo "<p style='color: #888; font-size: 14px;'>Guru: " . htmlspecialchars($row['teacher_name']) . "</p>";
            }
            
            echo "<a href='take_course_process.php?id={$row['course_id']}'>
                    📖 Ambil Course Ini
                </a>
                </div>";
        }
    }
    ?>
</main>


<footer>
    <?php include 'footer.html'; ?>
</footer>

</body>
</html>
