<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Student Dashboard</title>
    </head>
<body>
    <h2>Course yang Diambil</h2>
    <div class="courses-container">
        <?php
        $sql = "SELECT c.course_name, c.course_id, sc.progress FROM courses c JOIN student_courses sc ON c.course_id = sc.course_id WHERE sc.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($course = $result->fetch_assoc()) {
            echo "<div class='course-card'>";
            echo "<h3>" . htmlspecialchars($course['course_name']) . "</h3>";
            echo "<p>Progress: " . $course['progress'] . "%</p>";
            echo "<a href='course_detail.php?id=" . $course['course_id'] . "'>Lihat Detail</a>";
            echo "</div>";
        }
        ?>
    </div>

    <hr>

    <h2>To-Do List</h2>
    <form action="add_todo.php" method="POST">
        <input type="text" name="task" placeholder="Tambah tugas baru" required>
        <button type="submit">Tambah</button>
    </form>

    <ul>
        <?php
        $sql_todo = "SELECT todo_id, task_description, is_completed FROM todo_list WHERE user_id = ?";
        $stmt_todo = $conn->prepare($sql_todo);
        $stmt_todo->bind_param("i", $user_id);
        $stmt_todo->execute();
        $result_todo = $stmt_todo->get_result();

        while ($todo = $result_todo->fetch_assoc()) {
            $checked = $todo['is_completed'] ? 'checked' : '';
            $style = $todo['is_completed'] ? 'text-decoration: line-through;' : '';
            echo "<li style='$style'>";
            echo "<input type='checkbox' $checked onclick='toggleTodo(" . $todo['todo_id'] . ")'>";
            echo htmlspecialchars($todo['task_description']);
            echo "</li>";
        }
        ?>
    </ul>
</body>
</html>