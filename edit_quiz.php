<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$teacher_id = $_SESSION['user_id'];
$material_id = $_GET['material_id'] ?? 0;

$verify = $conn->prepare("SELECT m.*, c.course_name, c.course_id 
                          FROM materials m 
                          JOIN courses c ON m.course_id = c.course_id 
                          WHERE m.material_id = ? AND c.teacher_id = ?");
$verify->bind_param("ii", $material_id, $teacher_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$material = $result->fetch_assoc();

if (isset($_POST['update_quiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = !empty($_POST['option_c']) ? $_POST['option_c'] : null;
    $option_d = !empty($_POST['option_d']) ? $_POST['option_d'] : null;
    $correct_answer = $_POST['correct_answer'];

    $stmt = $conn->prepare("UPDATE quizzes 
                           SET question = ?, option_a = ?, option_b = ?, option_c = ?, 
                               option_d = ?, correct_answer = ? 
                           WHERE quiz_id = ? AND material_id = ?");
    $stmt->bind_param("ssssssii", $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $quiz_id, $material_id);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_quiz.php?material_id=$material_id&success=quiz_updated");
    exit();
}

if (isset($_GET['delete_quiz'])) {
    $quiz_id = $_GET['delete_quiz'];
    
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ? AND material_id = ?");
    $stmt->bind_param("ii", $quiz_id, $material_id);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_quiz.php?material_id=$material_id&success=quiz_deleted");
    exit();
}

$quizzes = $conn->query("SELECT * FROM quizzes WHERE material_id = $material_id ORDER BY quiz_id");

$edit_quiz = null;
if (isset($_GET['edit_quiz_id'])) {
    $edit_quiz_id = $_GET['edit_quiz_id'];
    $edit_result = $conn->query("SELECT * FROM quizzes WHERE quiz_id = $edit_quiz_id AND material_id = $material_id");
    if ($edit_result->num_rows > 0) {
        $edit_quiz = $edit_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Quiz</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        .quiz-item {
            background: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .quiz-item h4 {
            margin-top: 0;
            color: #333;
        }
        .quiz-options {
            margin: 10px 0;
            padding-left: 20px;
        }
        .correct-answer {
            color: green;
            font-weight: bold;
        }
        .edit-form {
            background: #fff3cd;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 2px solid #ffc107;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="admin_dashboard.php" style="color:white;text-decoration:none;">EduQuest</a></h1>
            <nav>
                <a href="edit_course.php?course_id=<?= $material['course_id'] ?>">← Kembali ke Course</a>
                <p>Selamat Datang, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="logout.php" style="color:rgb(255, 62, 62);font-size:medium;">Logout</a></p>
            </nav>
        </div>
    </header>
    
    <div style="padding-left:8%;">
        <h1 style="text-align:center">Edit Quiz</h1>
        <h3>Course: <?= htmlspecialchars($material['course_name']) ?></h3>
        <h3>Materi: <?= htmlspecialchars($material['material_title']) ?></h3>
    </div>
    

    <?php if (isset($_GET['success']) && $_GET['success'] == 'quiz_updated'): ?>
        <p style="color: green;">✅ Quiz berhasil diupdate!</p>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'quiz_deleted'): ?>
        <p style="color: green;">✅ Quiz berhasil dihapus!</p>
    <?php endif; ?>

    <?php if ($edit_quiz): ?>
        <div class="edit-form">
            <h2>Edit Pertanyaan Quiz</h2>
            <form method="POST">
                <input type="hidden" name="update_quiz">
                <input type="hidden" name="quiz_id" value="<?= $edit_quiz['quiz_id'] ?>">

                <label>Pertanyaan:</label>
                <textarea name="question" required><?= htmlspecialchars($edit_quiz['question']) ?></textarea><br><br>

                <label>Pilihan Jawaban:</label><br>
                <input type="text" name="option_a" placeholder="Pilihan A" value="<?= htmlspecialchars($edit_quiz['option_a']) ?>" required><br>
                <input type="text" name="option_b" placeholder="Pilihan B" value="<?= htmlspecialchars($edit_quiz['option_b']) ?>" required><br>
                <input type="text" name="option_c" placeholder="Pilihan C (opsional)" value="<?= htmlspecialchars($edit_quiz['option_c'] ?? '') ?>"><br>
                <input type="text" name="option_d" placeholder="Pilihan D (opsional)" value="<?= htmlspecialchars($edit_quiz['option_d'] ?? '') ?>"><br><br>

                <label>Jawaban Benar:</label>
                <select name="correct_answer" required>
                    <option value="A" <?= $edit_quiz['correct_answer'] == 'A' ? 'selected' : '' ?>>A</option>
                    <option value="B" <?= $edit_quiz['correct_answer'] == 'B' ? 'selected' : '' ?>>B</option>
                    <option value="C" <?= $edit_quiz['correct_answer'] == 'C' ? 'selected' : '' ?>>C</option>
                    <option value="D" <?= $edit_quiz['correct_answer'] == 'D' ? 'selected' : '' ?>>D</option>
                </select><br><br>

                <button type="submit">Update Quiz</button>
                <a href="edit_quiz.php?material_id=<?= $material_id ?>"><button type="button">Batal</button></a>
            </form>
        </div>
    <?php endif; ?>

    <hr>

    <h2>Daftar Quiz (<?= $quizzes->num_rows ?> pertanyaan)</h2>
    <div class="material-list">
        <?php
        if ($quizzes->num_rows == 0) {
            echo "<p>Belum ada quiz untuk materi ini. <a href='admin_dashboard.php#buat-quiz'>Buat quiz baru</a></p>";
        } else {
            $no = 1;
            while ($quiz = $quizzes->fetch_assoc()) {
                echo "<div class='quiz-item'>";
                echo "<h4>Pertanyaan $no: " . htmlspecialchars($quiz['question']) . "</h4>";
                echo "<div class='quiz-options'>";
                
                $options = ['A' => $quiz['option_a'], 'B' => $quiz['option_b']];
                if (!empty($quiz['option_c'])) $options['C'] = $quiz['option_c'];
                if (!empty($quiz['option_d'])) $options['D'] = $quiz['option_d'];
                
                foreach ($options as $key => $value) {
                    $class = ($key == $quiz['correct_answer']) ? 'correct-answer' : '';
                    echo "<p class='$class'>$key. " . htmlspecialchars($value);
                    if ($key == $quiz['correct_answer']) echo " ✓ (Jawaban Benar)";
                    echo "</p>";
                }
                
                echo "</div>";
                echo "<a href='edit_quiz.php?material_id=$material_id&edit_quiz_id={$quiz['quiz_id']}'>[Edit]</a> ";
                echo "<a href='?material_id=$material_id&delete_quiz={$quiz['quiz_id']}' onclick='return confirm(\"Yakin hapus quiz ini?\");' style='color: red;'>[Hapus]</a>";
                echo "</div>";
                $no++;
            }
        }
        ?>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>