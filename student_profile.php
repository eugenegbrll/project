<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
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
    $favorite_animal = $_POST['favorite_animal'];
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
                $sql_update = "UPDATE users SET full_name = ?, username = ?, favorite_animal = ?, password = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssi", $full_name, $username, $favorite_animal, $hashed_password, $user_id);
                
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
            $sql_update = "UPDATE users SET full_name = ?, username = ?, favorite_animal = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssi", $full_name, $username, $favorite_animal, $user_id);
            
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

$sql_courses = "SELECT COUNT(*) as total FROM student_courses WHERE user_id = ?";
$stmt_courses = $conn->prepare($sql_courses);
$stmt_courses->bind_param("i", $user_id);
$stmt_courses->execute();
$total_courses = $stmt_courses->get_result()->fetch_assoc()['total'];

$sql_completed = "SELECT COUNT(DISTINCT material_id) as total FROM material_completions WHERE user_id = ?";
$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$completed_materials = $stmt_completed->get_result()->fetch_assoc()['total'];

$sql_quizzes = "SELECT COUNT(*) as total FROM quiz_results WHERE user_id = ? AND is_correct = 1";
$stmt_quizzes = $conn->prepare($sql_quizzes);
$stmt_quizzes->bind_param("i", $user_id);
$stmt_quizzes->execute();
$correct_answers = $stmt_quizzes->get_result()->fetch_assoc()['total'];

$animal_emojis = [
    'cat' => '🐈',
    'dog' => '🐕',
    'chicken' => '🐔',
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profile Saya</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<header>
    <div class="container">
        <h1><a href="student_dashboard.php" style="color:white">EduQuest</a></h1>
        <nav>
            <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
            <p><a href="logout.php">Logout</a></p>
            <link rel="stylesheet" href="profile.css">
        </nav>
    </div>
</header>

<main>
    <a href="student_dashboard.php" class="back-link">⬅ Kembali ke Dashboard</a>
    
    <div class="profile-container">
        <div class="profile-header">
            <h2><?= htmlspecialchars($user['full_name']) ?></h2>
            <span class="role-badge">Student</span>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="profile-section">
            <h3>Statistik</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_courses ?></div>
                    <div class="stat-label">Course Diambil</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number"><?= $completed_materials ?></div>
                    <div class="stat-label">Materi Selesai</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-number"><?= $correct_answers ?></div>
                    <div class="stat-label">Jawaban Benar</div>
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
                        <div class="value">Student</div>
                    </div>
                    <div class="info-item">
                        <label>Pet Favorit</label>
                        <div class="value">
                            <?= $animal_emojis[$user['favorite_animal']] ?? '🐈' ?> 
                            <?= $animal_names[$user['favorite_animal']] ?? 'Kucing' ?>
                        </div>
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
                    <label>Pet Favorit</label>
                    <div class="animal-selector">
                        <?php
                        $animals = ['cat', 'dog', 'chicken', 'fish', 'rabbit', 'lizard'];
                        foreach ($animals as $animal) {
                            $selected = ($user['favorite_animal'] == $animal) ? 'selected' : '';
                            echo '<div class="animal-option ' . $selected . '" onclick="selectAnimal(\'' . $animal . '\')">
                                    <input type="radio" name="favorite_animal" value="' . $animal . '" id="' . $animal . '" ' . ($selected ? 'checked' : '') . '>
                                    <label for="' . $animal . '">' . $animal_emojis[$animal] . '</label>
                                    <div class="animal-name">' . $animal_names[$animal] . '</div>
                                </div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password Saat Ini (kosongkan jika tidak ingin mengubah password)</label>
                    <input type="password" name="current_password" id="current_password">
                </div>

                <div class="form-group">
                    <label>Password Baru</label>
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

function selectAnimal(animal) {
    document.querySelectorAll('.animal-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    const selectedOption = document.querySelector(`.animal-option input[value="${animal}"]`).parentElement;
    selectedOption.classList.add('selected');
    
    document.getElementById(animal).checked = true;
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