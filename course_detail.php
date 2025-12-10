<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$sql = "SELECT c.* FROM courses c WHERE c.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo "Course tidak ditemukan";
    exit();
}

$sql_progress = "SELECT 
    COUNT(DISTINCT m.material_id) as total_materials,
    COUNT(DISTINCT mc.material_id) as completed_materials
    FROM materials m
    LEFT JOIN material_completions mc ON m.material_id = mc.material_id AND mc.user_id = ?
    WHERE m.course_id = ?";
$stmt_prog = $conn->prepare($sql_progress);
$stmt_prog->bind_param("ii", $user_id, $course_id);
$stmt_prog->execute();
$progress_data = $stmt_prog->get_result()->fetch_assoc();

$total = $progress_data['total_materials'];
$completed = $progress_data['completed_materials'];
$progress = $total > 0 ? round(($completed / $total) * 100) : 0;

$sql_m = "SELECT m.*, 
          EXISTS(SELECT 1 FROM material_completions mc 
                 WHERE mc.material_id = m.material_id 
                 AND mc.user_id = ?) as is_completed
          FROM materials m 
          WHERE m.course_id = ? 
          ORDER BY m.level ASC";
$stmt_m = $conn->prepare($sql_m);
$stmt_m->bind_param("ii", $user_id, $course_id);
$stmt_m->execute();
$materials = $stmt_m->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Course</title>
    <style>
        .material-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .material-card.completed {
            background-color: #e8f5e9;
            border-color: #4CAF50;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background-color: #ddd;
            border-radius: 15px;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 15px;
            text-align: center;
            line-height: 30px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<main> 
    <a href="student_dashboard.php">⬅ Kembali</a>

    <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
    <p><strong>Guru:</strong> <?php echo htmlspecialchars($course['teacher_name']); ?></p>
    <p><?php echo htmlspecialchars($course['description']); ?></p>

    <h3>Progress Kamu:</h3>
    <div class="progress-bar">
        <div class="progress-fill" style="width: <?php echo $progress; ?>%;">
            <?php echo $progress; ?>%
        </div>
    </div>
    <p><?php echo $completed; ?> dari <?php echo $total; ?> materi selesai</p>

    <hr>

    <h2>Daftar Materi</h2>

    <?php
    while ($m = $materials->fetch_assoc()) {
        $completed_class = $m['is_completed'] ? 'completed' : '';
        $completed_badge = $m['is_completed'] ? ' ✅' : '';
        
        echo "<div class='material-card $completed_class'>";
        echo "<h3>Level {$m['level']} - " . htmlspecialchars($m['material_title']) . $completed_badge . "</h3>";
        echo "<p>" . nl2br(htmlspecialchars($m['material_content'])) . "</p>";

        if ($m['is_completed']) {
            echo "<p style='color: green;'>✓ Materi selesai!</p>";
            echo "<a href='quiz.php?material_id={$m['material_id']}'>Review Quiz ➜</a>";
        } else {
            echo "<a href='quiz.php?material_id={$m['material_id']}'>Kerjakan Quiz ➜</a>";
        }

        echo "</div>";
    }
    ?>
</main>


<footer>
    <?php include 'footer.html'; ?>
</footer>

</body>
</html>