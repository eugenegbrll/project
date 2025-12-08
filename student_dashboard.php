<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h1>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
<h1><a href="logout.php">Logout</a></h1>

<h2>Course yang Diambil</h2>
<a href="take_course.php">Ambil Course Baru</a>

<div class="courses-container">
    <?php
    $sql = "SELECT c.course_name, c.course_id, sc.progress 
            FROM courses c 
            JOIN student_courses sc ON c.course_id = sc.course_id 
            WHERE sc.user_id = ?";
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

<ul id="todo-list">
<?php
$sql_todo = "SELECT todo_id, task_description, is_completed 
             FROM todo_list WHERE user_id = ?";
$stmt_todo = $conn->prepare($sql_todo);
$stmt_todo->bind_param("i", $user_id);
$stmt_todo->execute();
$result_todo = $stmt_todo->get_result();

while ($todo = $result_todo->fetch_assoc()) {
    $checked = $todo['is_completed'] ? 'checked' : '';
    $style = $todo['is_completed'] ? 'text-decoration: line-through;' : '';
    
    echo "<li id='todo-{$todo['todo_id']}' style='$style'>
            <input type='checkbox' $checked onchange='toggleTodo({$todo['todo_id']})'>
            " . htmlspecialchars($todo['task_description']) . "
         </li>";
}
?>
</ul>

<script>
function toggleTodo(id) {
    fetch("toggle_todo.php?id=" + id)
        .then(response => response.text())
        .then(result => {
            let li = document.getElementById("todo-" + id);
            if (result === "1") {
                li.style.textDecoration = "line-through";
            } else {
                li.style.textDecoration = "none";
            }
        });
}
</script>

</body>
</html>
