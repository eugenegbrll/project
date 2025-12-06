<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "Course ID tidak ditemukan.";
    exit();
}

$course_id = $_GET['id'];
$user_id   = $_SESSION['user_id'];

/* ---------------------------------------------
   Ambil data course + progress siswa
--------------------------------------------- */
$sql = "
    SELECT 
        c.course_id,
        c.course_name,
        c.teacher_name,
        c.description,
        c.image_url,
        sc.progress
    FROM courses c
    JOIN student_courses sc 
         ON c.course_id = sc.course_id
    WHERE c.course_id = ? 
      AND sc.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* ---------------------------------------------
   Cek apakah course ditemukan
--------------------------------------------- */
if ($result->num_rows === 0) {
    echo "Course tidak ditemukan atau kamu belum terdaftar.";
    exit();
}

$course = $result->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Course</title>
    <style>
        .card {
            width: 400px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin: 20px auto;
        }
        img {
            max-width: 100%;
            border-radius: 8px;
        }
        .progress-bar {
            width: 100%;
            background: #eee;
            border-radius: 8px;
            overflow: hidden;
            height: 20px;
            margin-top: 5px;
        }
        .progress-fill {
            height: 100%;
            background: #4caf50;
        }
    </style>
</head>
<body>

<div class="card">
    <h2><?= htmlspecialchars($course['course_name']) ?></h2>

    <?php if (!empty($course['image_url'])): ?>
        <img src="<?= htmlspecialchars($course['image_url']) ?>" alt="Course Image">
    <?php endif; ?>

    <p><strong>Guru:</strong> <?= htmlspecialchars($course['teacher_name']) ?></p>
    <p><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($course['description'])) ?></p>

    <p><strong>Progress Kamu:</strong></p>
    <div class="progress-bar">
        <div class="progress-fill" style="width: <?= (int)$course['progress'] ?>%;"></div>
    </div>
    <p><?= (int)$course['progress'] ?>%</p>
</div>

</body>
</html>
