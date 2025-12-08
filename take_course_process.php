<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$course_id = $_GET['id'] ?? 0;


$sql_check = "SELECT * FROM student_courses WHERE user_id = ? AND course_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $course_id); 
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    header("Location: take_course.php?error=already_taken");
    exit();
}


$sql = "INSERT INTO student_courses (user_id, course_id, progress)
        VALUES (?, ?, 0)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);  

if ($stmt->execute()) {
    header("Location: student_dashboard.php?success=course_added");
} else {
    echo "Error: " . $conn->error;
}
?>
