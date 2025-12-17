<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $sql_current = "SELECT password FROM users WHERE user_id = ?";
    $stmt_current = $conn->prepare($sql_current);
    $stmt_current->bind_param("i", $user_id);
    $stmt_current->execute();
    $current_user = $stmt_current->get_result()->fetch_assoc();
    
    $sql_check = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $username, $user_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $message = "Username sudah digunakan oleh user lain!";
        $message_type = "error";
    } else {
        if (!empty($new_password)) {
            if (!password_verify($current_password, $current_user['password'])) {
                $message = "Password saat ini salah!";
                $message_type = "error";
            } elseif ($new_password !== $confirm_password) {
                $message = "Password baru dan konfirmasi tidak cocok!";
                $message_type = "error";
            } elseif (strlen($new_password) < 6) {
                $message = "Password baru minimal 6 karakter!";
                $message_type = "error";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE users SET full_name = ?, username = ?, password = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssi", $full_name, $username, $hashed_password, $user_id);
                
                if ($stmt_update->execute()) {
                    $_SESSION['username'] = $username;
                    $_SESSION['full_name'] = $full_name;
                    $message = "Profile dan password berhasil diperbarui!";
                    $message_type = "success";
                } else {
                    $message = "Gagal memperbarui profile!";
                    $message_type = "error";
                }
            }
        } else {
            $sql_update = "UPDATE users SET full_name = ?, username = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $full_name, $username, $user_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $message = "Profile berhasil diperbarui!";
                $message_type = "success";
            } else {
                $message = "Gagal memperbarui profile!";
                $message_type = "error";
            }
        }
    }
}

$sql_user = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

$sql_courses = "SELECT COUNT(*) as total FROM courses";
$total_courses = $conn->query($sql_courses)->fetch_assoc()['total'];

$sql_students = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$total_students = $conn->query($sql_students)->fetch_assoc()['total'];

$sql_materials = "SELECT COUNT(*) as total FROM materials";
$total_materials = $conn->query($sql_materials)->fetch_assoc()['total'];

$sql_quizzes = "SELECT COUNT(*) as total FROM quizzes";
$total_quizzes = $conn->query($sql_quizzes)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profile Admin</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<header>
    <div class="container">
        <h1><a href="admin_dashboard.php" style="color:white;text-decoration:none;">EduQuest</a></h1>
        <nav>
            <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
            <p><a href="logout.php">Logout</a></p>
        </nav>
    </div>
</header>

<main>
    <a href="admin_dashboard.php" class="back-link">⬅ Kembali ke Dashboard</a>
    
    <div class="profile-container">
        <div class="profile-header">
            <h2><?= htmlspecialchars($user['full_name']) ?></h2>
            <span class="role-badge admin">Administrator</span>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="profile-section">
            <h3>📊 Statistik Platform</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_courses ?></div>
                    <div class="stat-label">Total Course</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number"><?= $total_students ?></div>
                    <div class="stat-label">Total Student</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-number"><?= $total_materials ?></div>
                    <div class="stat-label">Total Materi</div>
                </div>
            </div>
            <div class="stats-grid" style="margin-top: 15px; grid-template-columns: repeat(2, 1fr);">
                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="stat-number"><?= $total_quizzes ?></div>
                    <div class="stat-label">Total Quiz</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                    <div class="stat-number">
                        <?php 
                        $sql_enrollments = "SELECT COUNT(*) as total FROM student_courses";
                        echo $conn->query($sql_enrollments)->fetch_assoc()['total'];
                        ?>
                    </div>
                    <div class="stat-label">Total Enrollment</div>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h3>Informasi Profile</h3>
            <div id="profileView">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nama Lengkap</label>
                        <div class="value"><?= htmlspecialchars($user['full_name']) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Username</label>
                        <div class="value"><?= htmlspecialchars($user['username']) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Role</label>
                        <div class="value">Administrator</div>
                    </div>
                    <div class="info-item">
                        <label>User ID</label>
                        <div class="value">#<?= $user['user_id'] ?></div>
                    </div>
                </div>
                <div class="button-group">
                    <button class="btn btn-primary" onclick="toggleEdit()">Edit Profile</button>
                </div>
            </div>

            <form method="POST" id="editForm" class="edit-form">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Password Saat Ini (kosongkan jika tidak ingin mengubah password)</label>
                    <input type="password" name="current_password" id="current_password">
                </div>

                <div class="form-group">
                    <label>Password Baru (minimal 6 karakter)</label>
                    <input type="password" name="new_password" id="new_password" minlength="6">
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" id="confirm_password" minlength="6">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleEdit()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <?php include 'footer.html'; ?>
</footer>

<script>
function toggleEdit() {
    const profileView = document.getElementById('profileView');
    const editForm = document.getElementById('editForm');
    
    if (editForm.classList.contains('active')) {
        editForm.classList.remove('active');
        profileView.style.display = 'block';
    } else {
        editForm.classList.add('active');
        profileView.style.display = 'none';
    }
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword || confirmPassword) {
        if (!currentPassword) {
            e.preventDefault();
            alert('Masukkan password saat ini untuk mengubah password!');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Password baru dan konfirmasi tidak cocok!');
            return;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Password baru minimal 6 karakter!');
            return;
        }
    }
});
</script>

</body>
</html>