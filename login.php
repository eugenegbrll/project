<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $sql->bind_param("s", $username);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($user['role'] === 'student') {
                header("Location: student_dashboard.php");
                exit;
            } else if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            }

        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>

<h2>Login</h2>

<?php if (!empty($_GET['registered'])) echo "<p style='color:green;'>Registrasi berhasil! Silakan login.</p>"; ?>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
    <a href="register.php">register</a>
</form>

</body>
</html>
