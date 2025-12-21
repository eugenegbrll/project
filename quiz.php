<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$material_id = $_GET['material_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$completed = $_GET['completed'] ?? 0;

$sql_user = "SELECT favorite_animal FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$favorite_animal = $user_data['favorite_animal'] ?? 'cat';

$animal_emojis = [
    'cat' => '🐈',
    'dog' => '🐕',
    'chicken' => '🐔',
    'fish' => '🐠',
    'rabbit' => '🐇',
    'lizard' => '🦎'
];

$animal_names = [
    'cat' => 'Kucing',
    'dog' => 'Anjing',
    'chicken' => 'Ayam',
    'fish' => 'Ikan',
    'rabbit' => 'Kelinci',
    'lizard' => 'Kadal'
];

$animal_emoji = $animal_emojis[$favorite_animal] ?? '🐱';
$animal_name = $animal_names[$favorite_animal] ?? 'Kucing';

$sql = "SELECT * FROM quizzes WHERE material_id = ? ORDER BY quiz_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();
$quizzes = $result->fetch_all(MYSQLI_ASSOC);

if (empty($quizzes)) {
    echo "Quiz untuk materi ini belum tersedia.";
    exit();
}

$current_index = isset($_GET['question']) ? (int)$_GET['question'] : 0;

if ($current_index < 0 || $current_index >= count($quizzes)) {
    $current_index = 0;
}

$quiz = $quizzes[$current_index];
$total_questions = count($quizzes);

$sql_check = "SELECT * FROM quiz_results 
              WHERE user_id = ? AND quiz_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $quiz['quiz_id']);
$stmt_check->execute();
$already_answered = $stmt_check->get_result()->fetch_assoc();

// Check if all questions answered for score display
$sql_all_answered = "SELECT COUNT(*) as answered FROM quiz_results WHERE user_id = ? AND material_id = ?";
$stmt_all = $conn->prepare($sql_all_answered);
$stmt_all->bind_param("ii", $user_id, $material_id);
$stmt_all->execute();
$all_answered = $stmt_all->get_result()->fetch_assoc()['answered'] == $total_questions;

// Calculate score if completed
$score_data = null;
$notif_sent = false;
if ($completed && $all_answered) {
    $sql_score = "SELECT 
                    COUNT(*) as total,
                    SUM(is_correct) as correct
                  FROM quiz_results 
                  WHERE user_id = ? AND material_id = ?";
    $stmt_score = $conn->prepare($sql_score);
    $stmt_score->bind_param("ii", $user_id, $material_id);
    $stmt_score->execute();
    $score_data = $stmt_score->get_result()->fetch_assoc();
    $score_data['percentage'] = round(($score_data['correct'] / $score_data['total']) * 100);
    $studentName = $_SESSION['full_name']; 
    $message = "Selamat! Kamu telah menyelesaikan quiz dengan skor " . $score_data['percentage'] . "%.";
    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt_notif->bind_param("is", $user_id, $message);
    $stmt_notif->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz</title>
    <link rel="stylesheet" href="quiz.css">
</head>
<body>
    <header>
        <div class="bar">
            <h1><a href="admin_dashboard.php" style="color:white;text-decoration:none">EduQuest - Admin</a></h1>
            <nav>
                <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="student_profile.php" style="color: white; text-decoration: none;">Profile</a></p>
                <p><a href="logout.php">Logout</a></p>
            </nav>
        </div>
    </header>

    <main class="game-body"> 
        <div class="game-container">
        <a href="course_detail.php?id=<?php 
            $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
            echo $course_q->fetch_assoc()['course_id'];
        ?>" style="color:#2c5aa0">⬅ Kembali ke Course</a>

        <?php if ($completed && $score_data): ?>
            <div class="score-display">
                <h2>🎉 Quiz Selesai! 🎉</h2>
                <div class="score-card">
                    <div class="score-number"><?= $score_data['percentage'] ?>%</div>
                    <p>Kamu menjawab <strong><?= $score_data['correct'] ?></strong> dari <strong><?= $score_data['total'] ?></strong> pertanyaan dengan benar!</p>
                    
                    <?php if ($score_data['percentage'] >= 90): ?>
                        <p class="score-message excellent">🌟 Sempurna! <?= $animal_name ?>mu sangat bangga! 🌟</p>
                    <?php elseif ($score_data['percentage'] >= 80): ?>
                        <p class="score-message great">👏 Bagus sekali! <?= $animal_name ?>mu senang! 👏</p>
                    <?php elseif ($score_data['percentage'] >= 70): ?>
                        <p class="score-message good">👍 Lumayan! <?= $animal_name ?>mu tersenyum! 👍</p>
                    <?php elseif ($score_data['percentage'] >= 60): ?>
                        <p class="score-message okay">😊 Cukup baik! Terus belajar ya! 😊</p>
                    <?php else: ?>
                        <p class="score-message need-improvement">😢 <?= $animal_name ?>mu sedih. Coba lagi ya! 😢</p>
                    <?php endif; ?>
                    <a href="course_detail.php?id=<?php 
                        $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
                        echo $course_q->fetch_assoc()['course_id'];
                    ?>">
                        <button type="button">Kembali ke Course</button>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <h2>🚨 Selamatkan <?php echo $animal_name; ?>mu! 🚨</h2>
            <p style="text-align: center; color: #666;">Jawab pertanyaan dengan benar sebelum air naik!</p>
        <?php endif; ?>

        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo (($current_index + 1) / $total_questions) * 100; ?>%;"></div>
        </div>
            
        <p style="text-align: center;"><strong>Pertanyaan <?php echo ($current_index + 1); ?> dari <?php echo $total_questions; ?></strong></p>

        <?php if (!$already_answered && !$completed): ?>
        <div class="timer-container" id="timerContainer">
            <div class="timer-text" id="timerText">30</div>
            <div class="animal" id="animal"><?php echo $animal_emoji; ?></div>
            <div class="water" id="water"></div>
        </div>
            
        <div class="warning-message" id="warningMessage">
            ⚠️ CEPAT! <?php echo strtoupper($animal_name); ?>MU HAMPIR TENGGELAM! ⚠️
        </div>
        <?php endif; ?>

        <?php if ($already_answered): ?>
            <div class="already-answered">
                <p><strong>✅ Kamu sudah menjawab pertanyaan ini!</strong></p>
                <p>Jawabanmu: <strong><?php echo $already_answered['user_answer']; ?></strong></p>
                <p>Jawaban benar: <strong><?php echo $quiz['correct_answer']; ?></strong></p>
                <?php if ($already_answered['user_answer'] == $quiz['correct_answer']): ?>
                    <p style="color: green; font-size: 24px;">✔ Benar! <?php echo $animal_name; ?>mu selamat! 🎉</p>
                <?php else: ?>
                    <p style="color: red; font-size: 24px;">✗ Salah! Tapi <?php echo $animal_name; ?>mu sudah diselamatkan.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$completed): ?>
        <div class="quiz-form">
            <form action="quiz_submit.php" method="POST" id="quizForm">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
                <input type="hidden" name="material_id" value="<?php echo $material_id; ?>">
                <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                <input type="hidden" name="total_questions" value="<?php echo $total_questions; ?>">
                <input type="hidden" name="time_taken" id="timeTaken" value="0">

                <div class="quiz-question"><?php echo htmlspecialchars($quiz['question']); ?></div>

                <?php
                $options = ['A' => $quiz['option_a'], 'B' => $quiz['option_b'], 'C' => $quiz['option_c'], 'D' => $quiz['option_d']];
                    
                foreach ($options as $letter => $text) {
                    if (!empty($text)) {
                        $disabled = $already_answered ? 'disabled' : '';
                        $checked = ($already_answered && $already_answered['user_answer'] == $letter) ? 'checked' : '';
                        echo '<label class="quiz-option">
                                <input type="radio" name="answer" value="' . $letter . '" required ' . $disabled . ' ' . $checked . '> 
                                ' . $letter . '. ' . htmlspecialchars($text) . '
                            </label>';
                    }
                }
                ?>

                <br>
                    
                <?php if (!$already_answered): ?>
                    <button type="submit" id="submitBtn">💾 Selamatkan <?php echo $animal_name; ?>!</button>
                <?php else: ?>
                    <?php if ($current_index < $total_questions - 1): ?>
                        <a href="quiz.php?material_id=<?php echo $material_id; ?>&question=<?php echo $current_index + 1; ?>">
                            <button type="button">Pertanyaan Selanjutnya ➡</button>
                        </a>
                    <?php else: ?>
                        <a href="quiz.php?material_id=<?php echo $material_id; ?>&question=<?php echo $current_index; ?>&completed=1">
                            <button type="button">Lihat Skor Akhir 🎯</button>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <div class="navigation">
            <h4>Navigasi Pertanyaan:</h4>
            <?php for ($i = 0; $i < $total_questions; $i++): ?>
                <?php
                $check_q = $conn->query("SELECT * FROM quiz_results WHERE user_id = $user_id AND quiz_id = {$quizzes[$i]['quiz_id']}");
                $is_answered = $check_q->num_rows > 0;
                $class = $is_answered ? 'answered' : 'unanswered';
                $current_class = ($i == $current_index) ? 'current' : '';
                ?>
                <a href="quiz.php?material_id=<?php echo $material_id; ?>&question=<?php echo $i; ?>" 
                class="nav-button <?php echo $class . ' ' . $current_class; ?>">
                    <?php echo ($i + 1); ?> <?php echo $is_answered ? '✓' : ''; ?>
                </a>
            <?php endfor; ?>
        </div>
        </div>

        <div class="game-over" id="gameOver">
            <div class="game-over-content">
                <h2>⏰ Waktu Habis!</h2>
                <div class="game-over-animal"><?php echo $animal_emoji; ?></div>
                <p style="font-size: 18px; color: #666;">
                    <?php echo $animal_name; ?>mu tenggelam karena kamu tidak menjawab tepat waktu!<br>
                    Tapi tenang, kamu bisa coba lagi.
                </p>
                <button onclick="location.reload()">🔄 Coba Lagi</button>
                <br><br>
                <a href="course_detail.php?id=<?php 
                    $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
                    echo $course_q->fetch_assoc()['course_id'];
                ?>">
                    <button>Kembali ke Course</button>
                </a>
            </div>
        </div>

        <?php if (!$already_answered && !$completed): ?>
        <script>
        let timeLeft = 30;
        let timerId;
        const waterHeight = 300;

        let animalX = 150;
        let animalY = 50;
        let velocityX = 3;
        let velocityY = 2;
        let gravity = 0.1;
        let bounce = 0.8;
        let animationId;

        let isDragging = false;
        let dragOffsetX = 0;
        let dragOffsetY = 0;
        let lastMouseX = 0;
        let lastMouseY = 0;

        function startTimer() {
            const timerText = document.getElementById('timerText');
            const water = document.getElementById('water');
            const animal = document.getElementById('animal');
            const warningMessage = document.getElementById('warningMessage');
            const gameOver = document.getElementById('gameOver');
            const submitBtn = document.getElementById('submitBtn');
            const timerContainer = document.querySelector('.timer-container');
            
            const containerWidth = timerContainer.offsetWidth;
            const containerHeight = timerContainer.offsetHeight;
            const animalSize = 80;
            
            animalX = containerWidth / 2 - animalSize / 2;
            animalY = 50;
            
            velocityX = (Math.random() - 0.5) * 6;
            velocityY = Math.random() * 2 + 1;
            
            function startDrag(e) {
                isDragging = true;
                animal.style.cursor = 'grabbing';

                const rect = timerContainer.getBoundingClientRect();
                const mouseX = (e.clientX || e.touches[0].clientX) - rect.left;
                const mouseY = (e.clientY || e.touches[0].clientY) - rect.top;
                
                dragOffsetX = mouseX - animalX;
                dragOffsetY = mouseY - animalY;
                
                lastMouseX = mouseX;
                lastMouseY = mouseY;
                
                if (animationId) {
                    cancelAnimationFrame(animationId);
                }
                
                e.preventDefault();
            }
            
            function drag(e) {
                if (!isDragging) return;
                
                const rect = timerContainer.getBoundingClientRect();
                const mouseX = (e.clientX || e.touches[0].clientX) - rect.left;
                const mouseY = (e.clientY || e.touches[0].clientY) - rect.top;
                
                animalX = mouseX - dragOffsetX;
                animalY = mouseY - dragOffsetY;
                
                animalX = Math.max(0, Math.min(animalX, containerWidth - animalSize));
                animalY = Math.max(0, Math.min(animalY, containerHeight - animalSize));
                
                velocityX = (mouseX - lastMouseX) * 0.5;
                velocityY = (mouseY - lastMouseY) * 0.5;
                
                lastMouseX = mouseX;
                lastMouseY = mouseY;

                animal.style.left = animalX + 'px';
                animal.style.top = animalY + 'px';
                
                e.preventDefault();
            }
            
            function endDrag(e) {
                if (!isDragging) return;
                
                isDragging = false;
                animal.style.cursor = 'grab';
                
                velocityX *= 1.5;
                velocityY *= 1.5;
                
                animateAnimal();
                
                e.preventDefault();
            }
            
            animal.addEventListener('mousedown', startDrag);
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', endDrag);
            
            animal.addEventListener('touchstart', startDrag);
            document.addEventListener('touchmove', drag, { passive: false });
            document.addEventListener('touchend', endDrag);
            
            function animateAnimal() {
                if (isDragging) return; 
                
                velocityY += gravity;
                
                animalX += velocityX;
                animalY += velocityY;
                
                const currentWaterHeight = ((30 - timeLeft) / 30) * 100;
                const waterPixelHeight = (currentWaterHeight / 100) * containerHeight;
                const waterTop = containerHeight - waterPixelHeight;
                
                if (animalX <= 0) {
                    animalX = 0;
                    velocityX = Math.abs(velocityX) * bounce;
                }
                
                if (animalX >= containerWidth - animalSize) {
                    animalX = containerWidth - animalSize;
                    velocityX = -Math.abs(velocityX) * bounce;
                }
                
                if (animalY <= 0) {
                    animalY = 0;
                    velocityY = Math.abs(velocityY) * bounce;
                }
                
                const bottomBoundary = Math.max(waterTop - animalSize, containerHeight - animalSize);
                if (animalY >= bottomBoundary) {
                    animalY = bottomBoundary;
                    velocityY = -Math.abs(velocityY) * bounce;
                    
                    velocityX += (Math.random() - 0.5) * 2;
                }
                
                if (animalY + animalSize > waterTop) {
                    velocityX *= 0.98; 
                    velocityY *= 0.98;
                    gravity = 0.05; 
                } else {
                    gravity = 0.1; 
                }
                
                const rotation = velocityX * 2;
                
                animal.style.left = animalX + 'px';
                animal.style.top = animalY + 'px';
                animal.style.transform = `rotate(${rotation}deg)`;
                
                animationId = requestAnimationFrame(animateAnimal);
            }
            
            animateAnimal();
            
            timerId = setInterval(() => {
                timeLeft--;
                timerText.textContent = timeLeft;
                document.getElementById('timeTaken').value = 30 - timeLeft;
                
                const waterLevel = ((30 - timeLeft) / 30) * 100;
                water.style.height = waterLevel + '%';
                
                if (timeLeft <= 10) {
                    timerText.classList.add('critical');
                    animal.classList.add('drowning');
                    warningMessage.classList.add('show');
                    
                    if (!isDragging) {
                        velocityX += (Math.random() - 0.5) * 4;
                        velocityY += (Math.random() - 0.5) * 3;
                    }
                }
                
                if (timeLeft <= 0) {
                    clearInterval(timerId);
                    cancelAnimationFrame(animationId);
                    submitBtn.disabled = true;
                    gameOver.classList.add('show');

                    fetch("quiz_failed.php", { method: "POST" });
                    
                    animal.removeEventListener('mousedown', startDrag);
                    document.removeEventListener('mousemove', drag);
                    document.removeEventListener('mouseup', endDrag);
                    animal.removeEventListener('touchstart', startDrag);
                    document.removeEventListener('touchmove', drag);
                    document.removeEventListener('touchend', endDrag);
                    
                    animal.style.transition = 'all 1s ease-in';
                    animal.style.transform = 'translateY(100px) rotate(180deg)';
                    animal.style.opacity = '0.3';
                    animal.style.cursor = 'default';
                    
                    setTimeout(() => {
                        location.reload();
                    }, 5000);
                }
            }, 1000);
        }

        window.onload = startTimer;

        document.getElementById('quizForm').addEventListener('submit', function() {
            clearInterval(timerId);
            cancelAnimationFrame(animationId);
            
            const animal = document.getElementById('animal');
            animal.style.transition = 'all 0.5s ease-out';
            animal.style.transform = 'translateY(-50px) scale(1.2) rotate(0deg)';
            animal.style.cursor = 'default';
            
            setTimeout(() => {
                animal.style.transform = 'translateY(0px) scale(1) rotate(0deg)';
            }, 500);
        });
        </script>
        <?php endif; ?>
    </main>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>
</body>
</html>
