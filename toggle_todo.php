<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit();
}

include 'db.php';

$todo_id = $_GET['id'];


$q = $conn->query("SELECT is_completed FROM todo_list WHERE todo_id = $todo_id");
$row = $q->fetch_assoc();

$new_status = $row['is_completed'] ? 0 : 1;


$conn->query("UPDATE todo_list SET is_completed = $new_status WHERE todo_id = $todo_id");

echo $new_status;
?>
