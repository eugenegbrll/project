<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$quiz_id = $_POST['quiz_id'];
$material_id = $_POST['material_id'];
$answer = $_POST['answer'];
$current_index = $_POST['current_index'];
$total_questions = $_POST['total_questions'];

$sql = "SELECT q.correct_answer, m.course_id 
        FROM quizzes q 
        JOIN materials m ON q.material_id = m.material_id
        WHERE q.quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$correct_answer = $data['correct_answer'];
$course_id = $data['course_id'];
$is_correct = ($answer == $correct_answer) ? 1 : 0;

$sql_insert = "INSERT INTO quiz_results (user_id, quiz_id, material_id, user_answer, is_correct) 
               VALUES (?, ?, ?, ?, ?)
               ON DUPLICATE KEY UPDATE user_answer = ?, is_correct = ?";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iiisisi", $user_id, $quiz_id, $material_id, $answer, $is_correct, $answer, $is_correct);
$stmt_insert->execute();

$sql_answered = "SELECT COUNT(*) as answered
                 FROM quiz_results qr
                 WHERE qr.user_id = ? AND qr.material_id = ?";
$stmt_answered = $conn->prepare($sql_answered);
$stmt_answered->bind_param("ii", $user_id, $material_id);
$stmt_answered->execute();
$answered_count = $stmt_answered->get_result()->fetch_assoc()['answered'];

if ($answered_count == $total_questions) {
    $sql_check_completion = "SELECT completed_at FROM material_completions 
                             WHERE user_id = ? AND material_id = ?";
    $stmt_check_comp = $conn->prepare($sql_check_completion);
    $stmt_check_comp->bind_param("ii", $user_id, $material_id);
    $stmt_check_comp->execute();
    $already_completed = $stmt_check_comp->get_result()->fetch_assoc();
    
    if (!$already_completed) {
        $sql_mark = "INSERT INTO material_completions (user_id, material_id) VALUES (?, ?)";
        $stmt_mark = $conn->prepare($sql_mark);
        $stmt_mark->bind_param("ii", $user_id, $material_id);
        $stmt_mark->execute();
        
        $sql_total = "SELECT COUNT(*) AS total FROM materials WHERE course_id = ?";
        $stmt_total = $conn->prepare($sql_total);
        $stmt_total->bind_param("i", $course_id);
        $stmt_total->execute();
        $total_materials = $stmt_total->get_result()->fetch_assoc()['total'];
        
        $progress_increment = 100 / $total_materials;

        $sql_update_progress = "UPDATE student_courses 
                                SET progress = LEAST(progress + ?, 100) 
                                WHERE user_id = ? AND course_id = ?";
        $stmt_update = $conn->prepare($sql_update_progress);
        $stmt_update->bind_param("dii", $progress_increment, $user_id, $course_id);
        $stmt_update->execute();
    }
    
    $sql_check_all = "SELECT COUNT(*) as total_correct 
                      FROM quiz_results 
                      WHERE user_id = ? AND material_id = ? AND is_correct = 1";
    $stmt_check_all = $conn->prepare($sql_check_all);
    $stmt_check_all->bind_param("ii", $user_id, $material_id);
    $stmt_check_all->execute();
    $total_correct = $stmt_check_all->get_result()->fetch_assoc()['total_correct'];
    

    if ($total_correct < $total_questions) {
        $_SESSION['pet_sad'] = true;
        $_SESSION['quiz_failed_material'] = $material_id;
    }
}

if (!$is_correct) {
    $_SESSION['pet_sad'] = true;
    $_SESSION['wrong_answer_count'] = ($_SESSION['wrong_answer_count'] ?? 0) + 1;
}

$next_question = $current_index + 1;

if ($next_question < $total_questions) {
    header("Location: quiz.php?material_id=$material_id&question=$next_question&answered=1");
} else {
    header("Location: quiz.php?material_id=$material_id&question=$current_index&completed=1");
}
exit();

setTimeout(() => {
    location.reload();
}, 5000);

?>