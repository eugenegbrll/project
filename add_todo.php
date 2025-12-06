<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$task = $_POST['task'];

$stmt = $conn->prepare("INSERT INTO todo_list (user_id, task_description) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $task);
$stmt->execute();

header("Location: student_dashboard.php");
exit();
?>
