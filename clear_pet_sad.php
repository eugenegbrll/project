<?php
session_start();

// Clear the sad pet state
unset($_SESSION['pet_sad']);
unset($_SESSION['wrong_answer_count']);
unset($_SESSION['quiz_failed_material']);

echo "success";
?>