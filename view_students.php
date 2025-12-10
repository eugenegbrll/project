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
$course_result = $verify->get_result();

if ($course_result->num_rows == 0) {
    echo "Course tidak ditemukan atau bukan milik Anda.";
    exit();
}

$course = $course_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Student Viewing</title>
    <link rel="stylesheet" href="view_students.css">
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-link">⬅ Kembali ke Dashboard</a>
        
        <h1>📚 <?= htmlspecialchars($course['course_name']) ?></h1>
        
        <?php
        // Get statistics
        $total_students = $conn->query("SELECT COUNT(*) as total FROM student_courses WHERE course_id = $course_id")->fetch_assoc()['total'];
        $avg_progress = $conn->query("SELECT AVG(progress) as avg FROM student_courses WHERE course_id = $course_id")->fetch_assoc()['avg'] ?? 0;
        $completed_students = $conn->query("SELECT COUNT(*) as total FROM student_courses WHERE course_id = $course_id AND progress = 100")->fetch_assoc()['total'];
        ?>
        
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?= $total_students ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= number_format($avg_progress, 1) ?>%</div>
                <div class="stat-label">Rata-rata Progress</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $completed_students ?></div>
                <div class="stat-label">Siswa Selesai</div>
            </div>
        </div>

        <h2>Daftar Siswa</h2>
        
        <?php
        $sql_students = "
        SELECT u.user_id, u.full_name, sc.progress
        FROM student_courses sc
        JOIN users u ON sc.user_id = u.user_id
        WHERE sc.course_id = ?
        ORDER BY sc.progress DESC, u.full_name ASC
        ";

        $stmt_students = $conn->prepare($sql_students);
        $stmt_students->bind_param("i", $course_id);
        $stmt_students->execute();
        $students = $stmt_students->get_result();

        if ($students->num_rows == 0) {
            echo "<div class='no-students'>Belum ada siswa yang mendaftar di course ini.</div>";
        } else {
            echo "<table>";
            echo "<thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Progress</th>
                    </tr>
                  </thead>";
            echo "<tbody>";
            
            $no = 1;
            while ($student = $students->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . htmlspecialchars($student['full_name']) . "</td>";
                echo "<td>
                        <div class='progress-bar'>
                            <div class='progress-fill' style='width: " . $student['progress'] . "%;'></div>
                        </div>
                        <small style='color: #666;'>" . $student['progress'] . "%</small>
                      </td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
        }
        ?>
    </div>

    <footer style="margin-top: 40px;">
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>