<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit();
}

$user_id = $_SESSION['user_id'];
$todo_id = $_POST['todo_id'] ?? 0;

$stmt = $conn->prepare(
    "DELETE FROM todo_list WHERE todo_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $todo_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "success";
} else {
    http_response_code(400);
}
