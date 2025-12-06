<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];

    $sql = "INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $role, $full_name);

    if ($stmt->execute()) {
        echo "Registrasi berhasil!";
        header("Location: login.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="POST" action="">
    <input type="text" name="full_name" placeholder="Nama Lengkap" required><br>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <label>Role:</label>
    <select name="role">
        <option value="student">Student</option>
        <option value="admin">Admin</option>
    </select><br>
    <button type="submit">Register</button>
</form>