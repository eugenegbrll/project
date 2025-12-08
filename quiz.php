<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$material_id = $_GET['material_id'] ?? 0;
$user_id = $_SESSION['user_id'];

$sql_user = "SELECT favorite_animal FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$favorite_animal = $user_data['favorite_animal'] ?? 'cat';

$animal_emojis = [
    'cat' => '🐈',
    'dog' => '🐕',
    'chicken' => '🐓',
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Selamatkan <?php echo $animal_name; ?>mu!</title>
    <link rel="stylesheet" href="quiz.css">
</head>
<body class="game-body">

<div class="game-container">
    <a href="course_detail.php?id=<?php 
        $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
        echo $course_q->fetch_assoc()['course_id'];
    ?>">⬅ Kembali ke Course</a>

    <h2>🚨 Selamatkan <?php echo $animal_name; ?>mu! 🚨</h2>
    <p style="text-align: center; color: #666;">Jawab pertanyaan dengan benar sebelum air naik!</p>

    <div class="progress-bar">
        <div class="progress-fill" style="width: <?php echo (($current_index + 1) / $total_questions) * 100; ?>%;"></div>
    </div>
    
    <p style="text-align: center;"><strong>Pertanyaan <?php echo ($current_index + 1); ?> dari <?php echo $total_questions; ?></strong></p>

    <?php if (!$already_answered): ?>
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
                <p style="color: green; font-size: 24px;">✓ Benar! <?php echo $animal_name; ?>mu selamat! 🎉</p>
            <?php else: ?>
                <p style="color: red; font-size: 24px;">✗ Salah! Tapi <?php echo $animal_name; ?>mu sudah diselamatkan.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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
                    <p style="color: green; font-size: 20px;"><strong>🎉 Kamu sudah menyelesaikan semua pertanyaan!</strong></p>
                    <a href="course_detail.php?id=<?php 
                        $course_q = $conn->query("SELECT course_id FROM materials WHERE material_id = $material_id");
                        echo $course_q->fetch_assoc()['course_id'];
                    ?>">
                        <button type="button">Kembali ke Course</button>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>

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

<?php if (!$already_answered): ?>
<script>
let timeLeft = 30;
let timerId;
const waterHeight = 300;

// Pinball physics variables
let animalX = 150;
let animalY = 50;
let velocityX = 3;
let velocityY = 2;
let gravity = 0.1;
let bounce = 0.8;
let animationId;

// Drag variables
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
    
    // Get container dimensions
    const containerWidth = timerContainer.offsetWidth;
    const containerHeight = timerContainer.offsetHeight;
    const animalSize = 80;
    
    // Initialize animal position
    animalX = containerWidth / 2 - animalSize / 2;
    animalY = 50;
    
    // Random initial velocity
    velocityX = (Math.random() - 0.5) * 6;
    velocityY = Math.random() * 2 + 1;
    
    // Drag event handlers
    function startDrag(e) {
        isDragging = true;
        animal.style.cursor = 'grabbing';
        
        // Get mouse position relative to container
        const rect = timerContainer.getBoundingClientRect();
        const mouseX = (e.clientX || e.touches[0].clientX) - rect.left;
        const mouseY = (e.clientY || e.touches[0].clientY) - rect.top;
        
        // Calculate offset from mouse to animal position
        dragOffsetX = mouseX - animalX;
        dragOffsetY = mouseY - animalY;
        
        lastMouseX = mouseX;
        lastMouseY = mouseY;
        
        // Stop the pinball animation while dragging
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
        
        // Update animal position
        animalX = mouseX - dragOffsetX;
        animalY = mouseY - dragOffsetY;
        
        // Keep within bounds
        animalX = Math.max(0, Math.min(animalX, containerWidth - animalSize));
        animalY = Math.max(0, Math.min(animalY, containerHeight - animalSize));
        
        // Calculate velocity based on mouse movement (for throw effect)
        velocityX = (mouseX - lastMouseX) * 0.5;
        velocityY = (mouseY - lastMouseY) * 0.5;
        
        lastMouseX = mouseX;
        lastMouseY = mouseY;
        
        // Update position
        animal.style.left = animalX + 'px';
        animal.style.top = animalY + 'px';
        
        e.preventDefault();
    }
    
    function endDrag(e) {
        if (!isDragging) return;
        
        isDragging = false;
        animal.style.cursor = 'grab';
        
        // Apply throw velocity (with some boost for fun!)
        velocityX *= 1.5;
        velocityY *= 1.5;
        
        // Restart the pinball animation
        animateAnimal();
        
        e.preventDefault();
    }
    
    // Add event listeners for mouse
    animal.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', endDrag);
    
    // Add event listeners for touch (mobile)
    animal.addEventListener('touchstart', startDrag);
    document.addEventListener('touchmove', drag, { passive: false });
    document.addEventListener('touchend', endDrag);
    
    // Pinball animation function
    function animateAnimal() {
        if (isDragging) return; // Don't animate while dragging
        
        // Apply gravity
        velocityY += gravity;
        
        // Update position
        animalX += velocityX;
        animalY += velocityY;
        
        // Get current water height percentage
        const currentWaterHeight = ((30 - timeLeft) / 30) * 100;
        const waterPixelHeight = (currentWaterHeight / 100) * containerHeight;
        const waterTop = containerHeight - waterPixelHeight;
        
        // Check boundaries and bounce
        
        // Left wall
        if (animalX <= 0) {
            animalX = 0;
            velocityX = Math.abs(velocityX) * bounce;
        }
        
        // Right wall
        if (animalX >= containerWidth - animalSize) {
            animalX = containerWidth - animalSize;
            velocityX = -Math.abs(velocityX) * bounce;
        }
        
        // Top (sky)
        if (animalY <= 0) {
            animalY = 0;
            velocityY = Math.abs(velocityY) * bounce;
        }
        
        // Bottom (water surface or floor)
        const bottomBoundary = Math.max(waterTop - animalSize, containerHeight - animalSize);
        if (animalY >= bottomBoundary) {
            animalY = bottomBoundary;
            velocityY = -Math.abs(velocityY) * bounce;
            
            // Add random horizontal velocity on bounce
            velocityX += (Math.random() - 0.5) * 2;
        }
        
        // If in water, add water resistance
        if (animalY + animalSize > waterTop) {
            velocityX *= 0.98; // Water drag
            velocityY *= 0.98;
            gravity = 0.05; // Less gravity in water
        } else {
            gravity = 0.1; // Normal gravity in air
        }
        
        // Apply rotation based on velocity
        const rotation = velocityX * 2;
        
        // Update animal position
        animal.style.left = animalX + 'px';
        animal.style.top = animalY + 'px';
        animal.style.transform = `rotate(${rotation}deg)`;
        
        // Continue animation
        animationId = requestAnimationFrame(animateAnimal);
    }
    
    // Start pinball animation
    animateAnimal();
    
    timerId = setInterval(() => {
        timeLeft--;
        timerText.textContent = timeLeft;
        document.getElementById('timeTaken').value = 30 - timeLeft;
        
        // Calculate water level (starts from 0, goes to 100%)
        const waterLevel = ((30 - timeLeft) / 30) * 100;
        water.style.height = waterLevel + '%';
        
        // Critical warning
        if (timeLeft <= 10) {
            timerText.classList.add('critical');
            animal.classList.add('drowning');
            warningMessage.classList.add('show');
            
            // Add panic movement (but not while dragging)
            if (!isDragging) {
                velocityX += (Math.random() - 0.5) * 4;
                velocityY += (Math.random() - 0.5) * 3;
            }
        }
        
        // Time's up
        if (timeLeft <= 0) {
            clearInterval(timerId);
            cancelAnimationFrame(animationId);
            submitBtn.disabled = true;
            gameOver.classList.add('show');
            
            // Remove drag functionality
            animal.removeEventListener('mousedown', startDrag);
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', endDrag);
            animal.removeEventListener('touchstart', startDrag);
            document.removeEventListener('touchmove', drag);
            document.removeEventListener('touchend', endDrag);
            
            // Sink animation
            animal.style.transition = 'all 1s ease-in';
            animal.style.transform = 'translateY(100px) rotate(180deg)';
            animal.style.opacity = '0.3';
            animal.style.cursor = 'default';
            
            // Auto redirect after 5 seconds
            setTimeout(() => {
                location.reload();
            }, 5000);
        }
    }, 1000);
}

// Start timer when page loads
window.onload = startTimer;

// Clear timer when form is submitted
document.getElementById('quizForm').addEventListener('submit', function() {
    clearInterval(timerId);
    cancelAnimationFrame(animationId);
    
    // Success animation - animal jumps for joy!
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

</body>
</html>