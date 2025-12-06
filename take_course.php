<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

// First check if there are ANY courses in the database
$sql_all_courses = "SELECT COUNT(*) as total FROM courses";
$result_all = $conn->query($sql_all_courses);
$total_courses = $result_all->fetch_assoc()['total'];

// Get courses the student hasn't taken yet
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
    <style>
        .course-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .course-box strong {
            font-size: 18px;
            color: #333;
        }
        .course-box a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .course-box a:hover {
            background-color: #45a049;
        }
        .empty-message {
            padding: 20px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
<h2>Pilih Course Baru</h2>
<a href="student_dashboard.php">⬅ Kembali</a>
<br><br>

<?php
if ($total_courses == 0) {
    // No courses exist in the database at all
    echo "<div class='empty-message'>
            <h3>Belum Ada Course</h3>
            <p>Saat ini belum ada course yang tersedia. Silakan hubungi admin untuk menambahkan course.</p>
          </div>";
} elseif ($result->num_rows == 0) {
    // Courses exist but student has taken all of them
    echo "<div class='empty-message'>
            <h3>Maaf!</h3>
            <p>Kamu sudah mengambil semua course yang tersedia!</p>
          </div>";
} else {
    // Show available courses
    while ($row = $result->fetch_assoc()) {
        echo "<div class='course-box'>
                <strong>" . htmlspecialchars($row['course_name']) . "</strong>";
        
        // Show description if available
        if (!empty($row['description'])) {
            echo "<p style='color: #666; margin: 5px 0;'>" . htmlspecialchars($row['description']) . "</p>";
        }
        
        // Show teacher if available
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
</body>
</html>
