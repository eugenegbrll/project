<?php
session_start();
include 'db.php'; // pastikan sama dengan login.php

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $role      = $_POST['role'];

    // Validasi sederhana
    if ($full_name === "" || $username === "" || $password === "") {
        $error = "Semua form harus diisi!";
    } else {

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah username sudah dipakai
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {

            // Insert user baru
            $sql = "INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $full_name, $username, $hashed_password, $role);

            if ($stmt->execute()) {

                // Setelah berhasil daftar → langsung login otomatis
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['role'] = $role;

                // Redirect sesuai role
                if ($role === "admin") {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit();

            } else {
                $error = "Gagal registrasi: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>

<h2>Register</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <input type="text" name="full_name" placeholder="Nama Lengkap" required><br><br>

    <input type="text" name="username" placeholder="Username" required><br><br>

    <input type="password" name="password" placeholder="Password" required><br><br>

    <label>Role:</label>
    <select name="role" required>
        <option value="student">Student</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Register</button>
</form>

<p>Sudah punya akun? <a href="login.php">Login</a></p>

</body>
</html>
