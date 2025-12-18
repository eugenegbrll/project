<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

$sql_overall = "
    SELECT 
        COUNT(DISTINCT qr.material_id) as materials_completed,
        SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) as total_correct,
        COUNT(qr.result_id) as total_answered,
        ROUND(
            (SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / 
            NULLIF(COUNT(qr.result_id), 0)), 2
        ) as overall_percentage
    FROM quiz_results qr
    WHERE qr.user_id = ?
";

$stmt_overall = $conn->prepare($sql_overall);
$stmt_overall->bind_param("i", $user_id);
$stmt_overall->execute();
$overall_result = $stmt_overall->get_result()->fetch_assoc();

$sql_courses = "
    SELECT 
        c.course_id,
        c.course_name,
        COUNT(DISTINCT qr.material_id) as materials_completed,
        SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
        COUNT(qr.result_id) as total_questions,
        ROUND(
            (SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / 
            NULLIF(COUNT(qr.result_id), 0)), 2
        ) as score_percentage
    FROM courses c
    JOIN student_courses sc ON c.course_id = sc.course_id
    JOIN materials m ON c.course_id = m.course_id
    JOIN quiz_results qr ON m.material_id = qr.material_id
    WHERE sc.user_id = ? AND qr.user_id = ?
    GROUP BY c.course_id, c.course_name
    ORDER BY c.course_name
";

$stmt_courses = $conn->prepare($sql_courses);
$stmt_courses->bind_param("ii", $user_id, $user_id);
$stmt_courses->execute();
$courses_result = $stmt_courses->get_result();

$courses_data = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses_data[] = $row;
}

$sql_materials = "
    SELECT 
        m.material_id,
        m.material_title,
        c.course_name,
        SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
        COUNT(qr.result_id) as total_questions,
        ROUND(
            (SUM(CASE WHEN qr.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / 
            NULLIF(COUNT(qr.result_id), 0)), 2
        ) as score_percentage,
        MAX(qr.answered_at) as last_attempt
    FROM materials m
    JOIN courses c ON m.course_id = c.course_id
    JOIN student_courses sc ON c.course_id = sc.course_id
    JOIN quiz_results qr ON m.material_id = qr.material_id
    WHERE sc.user_id = ? AND qr.user_id = ?
    GROUP BY m.material_id, m.material_title, c.course_name
    ORDER BY last_attempt DESC
    LIMIT 10
";

$stmt_materials = $conn->prepare($sql_materials);
$stmt_materials->bind_param("ii", $user_id, $user_id);
$stmt_materials->execute();
$materials_result = $stmt_materials->get_result();

$materials_data = [];
while ($row = $materials_result->fetch_assoc()) {
    $materials_data[] = $row;
}

echo json_encode([
    'overall' => $overall_result,
    'courses' => $courses_data,
    'recent_materials' => $materials_data
]);
?>