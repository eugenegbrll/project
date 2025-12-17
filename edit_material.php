<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$teacher_id = $_SESSION['user_id'];
$material_id = $_GET['material_id'] ?? 0;

$verify = $conn->prepare("SELECT m.*, c.course_name, c.course_id 
                          FROM materials m 
                          JOIN courses c ON m.course_id = c.course_id 
                          WHERE m.material_id = ? AND c.teacher_id = ?");
$verify->bind_param("ii", $material_id, $teacher_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$material = $result->fetch_assoc();

if (isset($_POST['update_material'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $level = $_POST['level'];
    $file_path = $material['file_path'];
    $file_name = $material['file_name'];
    $file_type = $material['file_type'];

    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $allowed_extensions = ['ppt', 'pptx', 'jpg', 'jpeg', 'png', 'docx', 'pdf', 'mov', 'mp4', 'mp3'];
        $file_info = pathinfo($_FILES['material_file']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        if (in_array($file_extension, $allowed_extensions)) {
            $upload_dir = __DIR__ . '/uploads/materials/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (!empty($material['file_path']) && file_exists($material['file_path'])) {
                unlink($material['file_path']);
            }
            
            $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $unique_name;
            
            if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_path)) {
                $file_path = 'uploads/materials/' . $unique_name;
                $file_name = $_FILES['material_file']['name'];
                $file_type = $file_extension;
            }
        }
    }

    if (isset($_POST['delete_file']) && $_POST['delete_file'] == '1') {
        if (!empty($material['file_path']) && file_exists($material['file_path'])) {
            unlink($material['file_path']);
        }
        $file_path = null;
        $file_name = null;
        $file_type = null;
    }

    $stmt = $conn->prepare("UPDATE materials 
                           SET material_title = ?, material_content = ?, level = ?, 
                               file_path = ?, file_name = ?, file_type = ? 
                           WHERE material_id = ?");
    $stmt->bind_param("ssisssi", $title, $content, $level, $file_path, $file_name, $file_type, $material_id);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_course.php?course_id=" . $material['course_id'] . "&success=material_updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Materi</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="admin_dashboard.php" style="color:white;text-decoration:none;">EduQuest</a></h1>
            <nav>
                <a href="edit_course.php?course_id=<?= $material['course_id'] ?>">← Kembali ke Course</a>
                <p>Selamat Datang, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="logout.php" style="color:rgb(255, 62, 62);font-size:medium;">Logout</a></p>
            </nav>
        </div>
    </header>

    <h1>Edit Materi</h1>
    <h3>Course: <?= htmlspecialchars($material['course_name']) ?></h3>
    
    <div class="material-list">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_material">
            
            <label>Judul Materi:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($material['material_title']) ?>" required><br><br>
            
            <label>Konten Materi:</label>
            <textarea name="content" placeholder="Konten Materi"><?= htmlspecialchars($material['material_content'] ?? '') ?></textarea><br><br>
            
            <label>Level:</label>
            <input type="number" name="level" value="<?= $material['level'] ?>" required><br><br>

            <?php if (!empty($material['file_path'])): ?>
                <div style="background:#f0f0f0; padding:10px; margin:10px 0; border-radius:5px;">
                    <p><strong>File saat ini:</strong></p>

                    <?php
                    $image_ext = ['jpg','jpeg','png','gif','webp'];
                    if (in_array(strtolower($material['file_type']), $image_ext)):
                    ?>
                        <img 
                            src="<?= htmlspecialchars($material['file_path']) ?>"
                            style="max-width:300px; height:auto; border-radius:5px;"
                        >
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($material['file_path']) ?>" target="_blank">
                            📎 Download <?= htmlspecialchars($material['file_name']) ?>
                        </a>
                    <?php endif; ?>

                    <p><?= htmlspecialchars($material['file_name']) ?></p>
                </div>
            <?php endif; ?>

            <label class="file-input-label">
                📎 Upload File Baru (akan mengganti file lama):
                <input type="file" name="material_file" accept=".ppt,.pptx,.jpg,.jpeg,.png,.docx,.pdf,.mov,.mp4,.mp3">
            </label><br><br>

            <button type="submit">Update Materi</button>
            <a href="edit_course.php?course_id=<?= $material['course_id'] ?>"><button type="button">Batal</button></a>
        </form>
    </div>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>