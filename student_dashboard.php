<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';
$user_id = $_SESSION['user_id'];

$sql_user = "SELECT favorite_animal FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$favorite_animal = $user_data['favorite_animal'] ?? 'cat';

$animal_emojis = [
    'cat' => '🐈',
    'dog' => '🐕',
    'chicken' => '🐓',
    'fish' => '🐠',
    'rabbit' => '🐇',
    'lizard' => '🦎'
];

$animal_names = [
    'cat' => 'Kucing',
    'dog' => 'Anjing',
    'chicken' => 'Ayam',
    'fish' => 'Ikan',
    'rabbit' => 'Kelinci',
    'lizard' => 'Kadal'
];

$animal_sounds = [
    'cat' => 'cat.mp3',
    'dog' => 'dog.mp3',
    'chicken' => 'chicken.mp3',
    'fish' => 'fish.mp3',
    'rabbit' => 'rabbit.mp3',
    'lizard' => 'lizard.mp3'
];

$pet_emoji = $animal_emojis[$favorite_animal] ?? '🐈';
$pet_name = $animal_names[$favorite_animal] ?? 'Kucing';
$pet_sound = $animal_sounds[$favorite_animal] ?? 'cat.mp3';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <script src="student_dashboard.js" defer></script>

</head>

<body>
<header>

</header>

<main>
    <div class="container">
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

        if ($result->num_rows == 0) {
            echo "<div class='course-card'><p>Kamu belum mengambil course apapun. Silakan ambil course baru!</p></div>";
        }

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
        <input type="text" name="task" placeholder="Tambah tugas baru" required style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
        <button type="submit">Tambah</button>
    </form>

    <ul id="todo-list" style="list-style: none; padding: 0;">
    <?php
    $sql_todo = "SELECT todo_id, task_description, is_completed 
                 FROM todo_list WHERE user_id = ?";
    $stmt_todo = $conn->prepare($sql_todo);
    $stmt_todo->bind_param("i", $user_id);
    $stmt_todo->execute();
    $result_todo = $stmt_todo->get_result();

    while ($todo = $result_todo->fetch_assoc()) {
        $checked = $todo['is_completed'] ? 'checked' : '';
        $style = $todo['is_completed'] ? 'text-decoration: line-through; color: #999;' : '';
        
        echo "<li id='todo-{$todo['todo_id']}' style='padding: 10px; margin: 5px 0; background: white; border-radius: 5px; $style'>
                <input type='checkbox' $checked onchange='toggleTodo({$todo['todo_id']})'>
                " . htmlspecialchars($todo['task_description']) . "
             </li>";
    }
    ?>
    </ul>
</div>

<div class="pet-container">
    <div class="pet-box">
        <div class="pat-counter" id="patCounter"></div>
        <div class="speech-bubble" id="speechBubble"></div>
        <div class="pet-emoji" 
            id="petEmoji" 
            data-sound="sounds/<?php echo $pet_sound; ?>"
            onclick="patPet()">
            <?php echo $pet_emoji; ?>
        </div>
        <div class="pet-name">My <?php echo $pet_name; ?></div>
        <div class="pet-mood" id="petMood">😊 Happy</div>
    </div>
</div>

</main>

<footer>

</footer>
</body>
</html>