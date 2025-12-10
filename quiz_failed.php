<?php
session_start();
$_SESSION['pet_sad'] = true;
$_SESSION['wrong_answer_count'] = ($_SESSION['wrong_answer_count'] ?? 0) + 1;
echo "ok";
