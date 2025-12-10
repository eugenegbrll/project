<?php
session_start();

unset($_SESSION['pet_sad']);
unset($_SESSION['wrong_answer_count']);
unset($_SESSION['quiz_failed_material']);

echo "success";
?>