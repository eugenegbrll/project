<?php
session_start();
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $favorite_animal = $_POST['favorite_animal'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "student"; 

    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $error = "Username sudah dipakai!";
    } else {
      
        $sql = $conn->prepare("INSERT INTO users (username, password, full_name, favorite_animal, role) VALUES (?, ?, ?, ?, ?)");
        $sql->bind_param("sssss", $username, $password, $full_name, $favorite_animal, $role);

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
<head>
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
<div class="register-container">
    <h2>Register</h2>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Full Name:</label>
        <input type="text" name="full_name" placeholder="Full Name" required>
        
        <label>Username:</label>
        <input type="text" name="username" placeholder="Username" required>
        
        <label>Password:</label>
        <input type="password" name="password" placeholder="Password" required>
        
        <label>Pilih Hewan Favorit:</label>
        <div class="animal-selector">
            <div class="animal-option">
                <input type="radio" name="favorite_animal" value="cat" id="cat" required>
                <label for="cat">🐈<div class="animal-name">Kucing</div></label>
            </div>
            <div class="animal-option">
                <input type="radio" name="favorite_animal" value="dog" id="dog">
                <label for="dog">🐕<div class="animal-name">Anjing</div></label>
            </div>
            <div class="animal-option">
                <input type="radio" name="favorite_animal" value="chicken" id="chicken">
                <label for="chicken">🐓<div class="animal-name">Ayam</div></label>
            </div>
            <div class="animal-option">
                <input type="radio" name="favorite_animal" value="fish" id="fish">
                <label for="fish">🐠<div class="animal-name">Ikan</div></label>
            </div>
            <div class="animal-option">
                <input type="radio" name="favorite_animal" value="rabbit" id="rabbit">
                <label for="rabbit">🐇<div class="animal-name">Kelinci</div></label>
            </div>
            <div class="animal-option">
                <input type="radio" name="favorite_animal" value="lizard" id="lizard">
                <label for="lizard">🦎<div class="animal-name">Kadal</div></label>
            </div>
        </div>
        
        <button type="submit">Register</button>
        <a href="login.php">Sudah punya akun? Login</a>
    </form>
</div>


</body>
</html>