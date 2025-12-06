<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

/* -----------------------------
   Aksi: Tambah Course
----------------------------- */
if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];

    $stmt = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

/* -----------------------------
   Aksi: Tambah Material
----------------------------- */
if (isset($_POST['add_material'])) {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $level = $_POST['level'];

    $stmt = $conn->prepare("
        INSERT INTO materials (course_id, material_title, material_content, level)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("issi", $course_id, $title, $content, $level);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

/* -----------------------------
   Aksi: Tambah Quiz
----------------------------- */
if (isset($_POST['add_quiz'])) {
    $material_id = $_POST['material_id'];
    $question = $_POST['question'];
    $a = $_POST['option_a'];
    $b = $_POST['option_b'];
    $correct = $_POST['correct_answer'];

    $stmt = $conn->prepare("
        INSERT INTO quizzes (material_id, question, option_a, option_b, correct_answer)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $material_id, $question, $a, $b, $correct);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

/* -----------------------------
   Aksi: Delete Material
----------------------------- */
if (isset($_GET['delete_material'])) {
    $id = $_GET['delete_material'];

    // Hapus juga quiz terkait
    $conn->query("DELETE FROM quizzes WHERE material_id = $id");

    $conn->query("DELETE FROM materials WHERE material_id = $id");

    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Selamat Datang, Admin</h1>

    <!-- ------------------------------------------------ -->
    <h2>➕ Tambah Course Baru</h2>
    <form method="POST">
        <input type="hidden" name="add_course">
        <input type="text" name="course_name" placeholder="Nama Course" required>
        <button type="submit">Tambah Course</button>
    </form>

    <hr>

    <!-- ------------------------------------------------ -->
    <h2>➕ Tambah Materi Pelajaran</h2>
    <form method="POST">
        <input type="hidden" name="add_material">
        
        <label>Pilih Course:</label>
        <select name="course_id" required>
            <option value="">-- Pilih Course --</option>
            <?php
            $q = $conn->query("SELECT * FROM courses");
            while ($c = $q->fetch_assoc()) {
                echo "<option value='{$c['course_id']}'>".
                     htmlspecialchars($c['course_name']) .
                     "</option>";
            }
            ?>
        </select><br><br>

        <input type="text" name="title" placeholder="Judul Materi" required><br><br>
        <textarea name="content" placeholder="Konten Materi"></textarea><br><br>
        <input type="number" name="level" placeholder="Level (misal: 1)" required><br><br>

        <button type="submit">Simpan Materi</button>
    </form>

    <hr>

    <!-- ------------------------------------------------ -->
    <h2>📝 Buat Quiz Terkait Materi</h2>
    <form method="POST">
        <input type="hidden" name="add_quiz">

        <label>Pilih Materi:</label>
        <select name="material_id" required>
            <option value="">-- Pilih Materi --</option>
            <?php
            $q = $conn->query("SELECT * FROM materials");
            while ($m = $q->fetch_assoc()) {
                echo "<option value='{$m['material_id']}'>".
                     htmlspecialchars($m['material_title']) .
                     "</option>";
            }
            ?>
        </select><br><br>

        <textarea name="question" placeholder="Pertanyaan Quiz" required></textarea><br><br>

        <input type="text" name="option_a" placeholder="Opsi A" required><br><br>
        <input type="text" name="option_b" placeholder="Opsi B" required><br><br>

        <label>Jawaban Benar:</label>
        <select name="correct_answer">
            <option value="A">A</option>
            <option value="B">B</option>
        </select><br><br>

        <button type="submit">Simpan Quiz</button>
    </form>

    <hr>

    <!-- ------------------------------------------------ -->
    <h2>🗑️ Kelola Materi</h2>
    <?php
    $sql_list = "
        SELECT m.material_id, m.material_title, c.course_name
        FROM materials m 
        JOIN courses c ON m.course_id = c.course_id
    ";
    $result_list = $conn->query($sql_list);

    while ($item = $result_list->fetch_assoc()) {
        echo "<p>" .
            htmlspecialchars($item['course_name']) .
            " - " .
            htmlspecialchars($item['material_title']) .
            " <a href='?delete_material=".$item['material_id']."' onclick='return confirm(\"Yakin?\");'>[Hapus]</a></p>";
    }
    ?>
</body>
</html>
