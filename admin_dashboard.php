<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$teacher_id = $_SESSION['user_id']; 

if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    $teacher_name = $_SESSION['full_name'];

    $stmt = $conn->prepare("INSERT INTO courses (course_name, description, teacher_id, teacher_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $course_name, $description, $teacher_id, $teacher_name);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}


if (isset($_POST['add_material'])) {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $level = $_POST['level'];

    $verify = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?");
    $verify->bind_param("ii", $course_id, $teacher_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("
            INSERT INTO materials (course_id, material_title, material_content, level)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("issi", $course_id, $title, $content, $level);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['add_quiz'])) {
    $material_id = $_POST['material_id'];
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = !empty($_POST['option_c']) ? $_POST['option_c'] : null;
    $option_d = !empty($_POST['option_d']) ? $_POST['option_d'] : null;
    $correct_answer = $_POST['correct_answer'];

    $verify = $conn->prepare("SELECT m.* FROM materials m 
                              JOIN courses c ON m.course_id = c.course_id 
                              WHERE m.material_id = ? AND c.teacher_id = ?");
    $verify->bind_param("ii", $material_id, $teacher_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("
            INSERT INTO quizzes (material_id, question, option_a, option_b, option_c, option_d, correct_answer)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssss", $material_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['add_another'])) {
        header("Location: admin_dashboard.php?material_id=" . $material_id . "&success=quiz_added");
    } else {
        header("Location: admin_dashboard.php?success=quiz_completed");
    }
    exit();
}

if (isset($_GET['delete_material'])) {
    $id = $_GET['delete_material'];

    $verify = $conn->prepare("SELECT m.* FROM materials m 
                              JOIN courses c ON m.course_id = c.course_id 
                              WHERE m.material_id = ? AND c.teacher_id = ?");
    $verify->bind_param("ii", $id, $teacher_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $conn->query("DELETE FROM quiz_results WHERE material_id = $id");
        $conn->query("DELETE FROM material_completions WHERE material_id = $id");
        $conn->query("DELETE FROM quizzes WHERE material_id = $id");
        $conn->query("DELETE FROM materials WHERE material_id = $id");
    }

    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['delete_course'])) {
    $course_id = $_GET['delete_course'];
    
    $verify = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?");
    $verify->bind_param("ii", $course_id, $teacher_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $materials = $conn->query("SELECT material_id FROM materials WHERE course_id = $course_id");
        while ($mat = $materials->fetch_assoc()) {
            $mat_id = $mat['material_id'];
            $conn->query("DELETE FROM quiz_results WHERE material_id = $mat_id");
            $conn->query("DELETE FROM material_completions WHERE material_id = $mat_id");
            $conn->query("DELETE FROM quizzes WHERE material_id = $mat_id");
        }
        $conn->query("DELETE FROM materials WHERE course_id = $course_id");
        $conn->query("DELETE FROM student_courses WHERE course_id = $course_id");
        $conn->query("DELETE FROM courses WHERE course_id = $course_id");
        
        header("Location: admin_dashboard.php?success=course_deleted");
    }
    exit();
}

$selected_material_id = $_GET['material_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard</title>
    <style>
        .course-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .course-box h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
    <h1><a href="logout.php">Logout</a></h1>

    <h1>Admin Dashboard</h1>
    <?php if (isset($_GET['success']) && $_GET['success'] == 'quiz_added'): ?>
        <p style="color: green;">✅ Quiz berhasil ditambahkan! Tambah pertanyaan lagi untuk materi yang sama.</p>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'quiz_completed'): ?>
        <p style="color: green;">✅ Semua quiz berhasil disimpan!</p>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'course_deleted'): ?>
        <p style="color: green;">✅ Course berhasil dihapus!</p>
    <?php endif; ?>

    <h2>Tambah Course Baru</h2>
    <form method="POST">
        <input type="hidden" name="add_course">
        <input type="text" name="course_name" placeholder="Nama Course" required><br><br>
        <textarea name="description" placeholder="Deskripsi Course (opsional)"></textarea><br><br>
        <p><em>Guru: <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Otomatis)</em></p>
        <button type="submit">Tambah Course</button>
    </form>

    <hr>

    <h2>Course yang Saya Buat</h2>
    <?php
    $sql_my_courses = "SELECT * FROM courses WHERE teacher_id = ?";
    $stmt_my = $conn->prepare($sql_my_courses);
    $stmt_my->bind_param("i", $teacher_id);
    $stmt_my->execute();
    $my_courses = $stmt_my->get_result();

    if ($my_courses->num_rows == 0) {
        echo "<p>Kamu belum membuat course apapun.</p>";
    } else {
        while ($course = $my_courses->fetch_assoc()) {
            echo "<div class='course-box'>
                    <h3>" . htmlspecialchars($course['course_name']) . "</h3>";
            
            if (!empty($course['description'])) {
                echo "<p style='color: #666;'>" . htmlspecialchars($course['description']) . "</p>";
            }
            
            $material_count = $conn->query("SELECT COUNT(*) as total FROM materials WHERE course_id = {$course['course_id']}")->fetch_assoc()['total'];
            $student_count = $conn->query("SELECT COUNT(*) as total FROM student_courses WHERE course_id = {$course['course_id']}")->fetch_assoc()['total'];
            
            echo "<p style='color: #888;'>📚 {$material_count} materi | 👥 {$student_count} siswa</p>";
            echo "<a href='?delete_course={$course['course_id']}' onclick='return confirm(\"Yakin hapus course ini? Semua materi dan quiz akan terhapus!\");' style='color: red;'>[Hapus Course]</a>";
            echo "</div>";
        }
    }
    ?>

    <hr>
    
    <h2>Tambah Materi Pelajaran</h2>
    <form method="POST">
        <input type="hidden" name="add_material">
        
        <label>Pilih Course:</label>
        <select name="course_id" required>
            <option value="">-- Pilih Course --</option>
            <?php
            $q = $conn->prepare("SELECT * FROM courses WHERE teacher_id = ?");
            $q->bind_param("i", $teacher_id);
            $q->execute();
            $courses_result = $q->get_result();
            
            if ($courses_result->num_rows == 0) {
                echo "<option value='' disabled>Buat course terlebih dahulu</option>";
            } else {
                while ($c = $courses_result->fetch_assoc()) {
                    echo "<option value='{$c['course_id']}'>".
                         htmlspecialchars($c['course_name']) .
                         "</option>";
                }
            }
            ?>
        </select><br><br>

        <input type="text" name="title" placeholder="Judul Materi" required><br><br>
        <textarea name="content" placeholder="Konten Materi"></textarea><br><br>
        <input type="number" name="level" placeholder="Level (misal: 1)" required><br><br>

        <button type="submit">Simpan Materi</button>
    </form>

    <hr>

    <h2>Buat Quiz Terkait Materi</h2>
    <form method="POST">
        <input type="hidden" name="add_quiz">

        <label>Pilih Materi:</label>
        <select name="material_id" required>
            <option value="">-- Pilih Materi --</option>
            <?php
            $q = $conn->prepare("SELECT m.*, c.course_name 
                                 FROM materials m 
                                 JOIN courses c ON m.course_id = c.course_id 
                                 WHERE c.teacher_id = ?");
            $q->bind_param("i", $teacher_id);
            $q->execute();
            $materials_result = $q->get_result();
            
            if ($materials_result->num_rows == 0) {
                echo "<option value='' disabled>Buat materi terlebih dahulu</option>";
            } else {
                while ($m = $materials_result->fetch_assoc()) {
                    $selected = ($m['material_id'] == $selected_material_id) ? 'selected' : '';
                    echo "<option value='{$m['material_id']}' $selected>".
                        htmlspecialchars($m['course_name']) . " - " .
                        htmlspecialchars($m['material_title']) .
                        "</option>";
                }
            }
            ?>
        </select><br><br>

        <?php
        if ($selected_material_id) {
            $count_query = $conn->query("SELECT COUNT(*) as total FROM quizzes WHERE material_id = $selected_material_id");
            $count = $count_query->fetch_assoc()['total'];
            echo "<p style='color: blue;'>📝 Sudah ada <strong>$count pertanyaan</strong> untuk materi ini.</p>";
        }
        ?>

        <textarea name="question" placeholder="Pertanyaan Quiz" required></textarea><br><br>

        <label>Pilihan Jawaban:</label><br>
        <input type="text" name="option_a" placeholder="Pilihan A" required><br>
        <input type="text" name="option_b" placeholder="Pilihan B" required><br>
        <input type="text" name="option_c" placeholder="Pilihan C (opsional)"><br>
        <input type="text" name="option_d" placeholder="Pilihan D (opsional)"><br><br>

        <label>Jawaban Benar:</label>
        <select name="correct_answer" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select><br><br>

        <button type="submit" name="add_another" value="1">➕ Simpan & Tambah Pertanyaan Lagi</button>
        <button type="submit">✅ Simpan & Selesai</button>
    </form>

    <hr>

    <h2>Kelola Materi Saya</h2>
    <?php
    $sql_list = "
        SELECT m.material_id, m.material_title, c.course_name,
               (SELECT COUNT(*) FROM quizzes WHERE material_id = m.material_id) as quiz_count
        FROM materials m 
        JOIN courses c ON m.course_id = c.course_id
        WHERE c.teacher_id = ?
    ";
    $stmt_list = $conn->prepare($sql_list);
    $stmt_list->bind_param("i", $teacher_id);
    $stmt_list->execute();
    $result_list = $stmt_list->get_result();

    if ($result_list->num_rows == 0) {
        echo "<p>Belum ada materi. Tambah materi untuk course yang kamu buat.</p>";
    } else {
        while ($item = $result_list->fetch_assoc()) {
            echo "<p>" .
                htmlspecialchars($item['course_name']) .
                " - " .
                htmlspecialchars($item['material_title']) .
                " <span style='color: gray;'>(" . $item['quiz_count'] . " quiz)</span> " .
                " <a href='?delete_material=".$item['material_id']."' onclick='return confirm(\"Yakin?\");'>[Hapus]</a></p>";
        }
    }
    ?>

<footer>
    <?php include 'footer.html'; ?>
</footer>
</body>
</html>