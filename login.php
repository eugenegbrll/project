<?php
session_start();
include 'db.php';

// Jika koneksi gagal
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Ambil input
    $username = trim($_POST['username']);
    $input_password = $_POST['password'];

    // Cek input kosong
    if ($username === "" || $input_password === "") {
        $error = "Username dan password harus diisi!";
    } else {

        // Query cek user
        $sql = "SELECT user_id, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        // Jika username ditemukan
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($input_password, $user['password'])) {

                // Simpan session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan role
                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit();

            } else {
                $error = "Password salah!";
            }

        } else {
            $error = "Username tidak ditemukan!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input 
            type="text" 
            name="username" 
            placeholder="Username" 
            required
        ><br><br>

        <input 
            type="password" 
            name="password" 
            placeholder="Password" 
            required
        ><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Register di sini</a></p>

</body>
</html>
