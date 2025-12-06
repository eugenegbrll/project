<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$material_id = $_GET['material_id'] ?? 0;
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM quizzes WHERE material_id = ? ORDER BY quiz_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();
$quizzes = $result->fetch_all(MYSQLI_ASSOC);

if (empty($quizzes)) {
    echo "Quiz untuk materi ini belum tersedia.";
    exit();
}

$current_index = isset($_GET['question']) ? (int)$_GET['question'] : 0;

if ($current_index < 0 || $current_index >= count($quizzes)) {
    $current_index = 0;
}

$quiz = $quizzes[$current_index];
$total_questions = count($quizzes);

$sql_check = "SELECT * FROM quiz_results 
              WHERE user_id = ? AND quiz_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $quiz['quiz_id']);
$stmt_check->execute();
$already_answered = $stmt_check->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kuis</title>
    <style>
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 10px;
            transition: width 0.3s;
        }
    </style>
</head>
<body>

<a href="course_detail.php?id=<?php 
    $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
    echo $course_q->fetch_assoc()['course_id'];
?>">⬅ Kembali ke Course</a>

<h2>Kuis - Pertanyaan <?php echo ($current_index + 1); ?> dari <?php echo $total_questions; ?></h2>

<div class="progress-bar">
    <div class="progress-fill" style="width: <?php echo (($current_index + 1) / $total_questions) * 100; ?>%;"></div>
</div>

<?php if ($already_answered): ?>
    <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>✅ Kamu sudah menjawab pertanyaan ini!</strong></p>
        <p>Jawabanmu: <strong><?php echo $already_answered['user_answer']; ?></strong></p>
        <p>Jawaban benar: <strong><?php echo $quiz['correct_answer']; ?></strong></p>
        <?php if ($already_answered['user_answer'] == $quiz['correct_answer']): ?>
            <p style="color: green;">✓ Benar!</p>
        <?php else: ?>
            <p style="color: red;">✗ Salah</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<form action="quiz_submit.php" method="POST">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
    <input type="hidden" name="material_id" value="<?php echo $material_id; ?>">
    <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
    <input type="hidden" name="total_questions" value="<?php echo $total_questions; ?>">

    <p><strong><?php echo htmlspecialchars($quiz['question']); ?></strong></p>

    <?php
    $options = ['A' => $quiz['option_a'], 'B' => $quiz['option_b'], 'C' => $quiz['option_c'], 'D' => $quiz['option_d']];
    
    foreach ($options as $letter => $text) {
        if (!empty($text)) {
            $disabled = $already_answered ? 'disabled' : '';
            $checked = ($already_answered && $already_answered['user_answer'] == $letter) ? 'checked' : '';
            echo '<label>
                    <input type="radio" name="answer" value="' . $letter . '" required ' . $disabled . ' ' . $checked . '> 
                    ' . $letter . '. ' . htmlspecialchars($text) . '
                  </label><br>';
        }
    }
    ?>

    <br>
    
    <?php if (!$already_answered): ?>
        <button type="submit">Kirim Jawaban</button>
    <?php else: ?>
        <?php if ($current_index < $total_questions - 1): ?>
            <a href="quiz.php?material_id=<?php echo $material_id; ?>&question=<?php echo $current_index + 1; ?>">
                <button type="button">Pertanyaan Selanjutnya ➡</button>
            </a>
        <?php else: ?>
            <p style="color: green;"><strong>🎉 Kamu sudah menyelesaikan semua pertanyaan!</strong></p>
            <a href="course_detail.php?id=<?php 
                $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
                echo $course_q->fetch_assoc()['course_id'];
            ?>">
                <button type="button">Kembali ke Course</button>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</form>

<hr>

<div>
    <h4>Navigasi Pertanyaan:</h4>
    <?php for ($i = 0; $i < $total_questions; $i++): ?>
        <?php
        $check_q = $conn->query("SELECT * FROM quiz_results WHERE user_id = $user_id AND quiz_id = {$quizzes[$i]['quiz_id']}");
        $is_answered = $check_q->num_rows > 0;
        $style = $is_answered ? 'background: #4CAF50; color: white;' : 'background: #ddd;';
        $current_style = ($i == $current_index) ? 'border: 3px solid blue;' : '';
        ?>
        <a href="quiz.php?material_id=<?php echo $material_id; ?>&question=<?php echo $i; ?>">
            <button type="button" style="<?php echo $style . ' ' . $current_style; ?> padding: 10px; margin: 5px;">
                <?php echo ($i + 1); ?> <?php echo $is_answered ? '✓' : ''; ?>
            </button>
        </a>
    <?php endfor; ?>
</div>

</body>
</html>