<?php
session_start();

session_unset();
session_destroy();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <script>
        localStorage.removeItem('petLastGreeting');
        window.location.href = 'guest_dashboard.php';
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>