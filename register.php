<?php
session_start();
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "student"; 

    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $error = "Username sudah dipakai!";
    } else {
      
        $sql = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $sql->bind_param("ssss", $username, $password, $full_name, $role);

        if ($sql->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = "Gagal melakukan registrasi!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>

<h2>Register</h2>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required><br><br>
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
</form>

</body>
</html>
