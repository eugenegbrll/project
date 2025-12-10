<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';
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

$animal_sounds = [
    'cat' => 'cat.mp3',
    'dog' => 'dog.mp3',
    'chicken' => 'chicken.mp3',
    'fish' => 'fish.mp3',
    'rabbit' => 'rabbit.mp3',
    'lizard' => 'lizard.mp3'
];

$pet_emoji = $animal_emojis[$favorite_animal] ?? '🐈';
$pet_name = $animal_names[$favorite_animal] ?? 'Kucing';
$pet_sound = $animal_sounds[$favorite_animal] ?? 'cat.mp3';

$is_pet_sad = isset($_SESSION['pet_sad']) && $_SESSION['pet_sad'];
$wrong_count = $_SESSION['wrong_answer_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <script src="student_dashboard.js" defer></script>
    <audio id="petSound" src="sounds/<?php echo $pet_sound; ?>"></audio>


</head>

<body>
<header>

</header>

<main>
    <div class="container">
    <h1>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
    <h1><a href="logout.php">Logout</a></h1>

    <h2>Course yang Diambil</h2>
    <a href="take_course.php">Ambil Course Baru</a>

    <div class="courses-container">
        <?php
        $sql = "SELECT c.course_name, c.course_id, sc.progress 
                FROM courses c 
                JOIN student_courses sc ON c.course_id = sc.course_id 
                WHERE sc.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo "<div class='course-card'><p>Kamu belum mengambil course apapun. Silakan ambil course baru!</p></div>";
        }

        while ($course = $result->fetch_assoc()) {
            echo "<div class='course-card'>";
            echo "<h3>" . htmlspecialchars($course['course_name']) . "</h3>";
            echo "<p>Progress: " . $course['progress'] . "%</p>";
            echo "<a href='course_detail.php?id=" . $course['course_id'] . "'>Lihat Detail</a>";
            echo "</div>";
        }
        ?>
    </div>

    <hr>

    <h2>To-Do List</h2>

    <form action="add_todo.php" method="POST">
        <input type="text" name="task" placeholder="Tambah tugas baru" required style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
        <button type="submit">Tambah</button>
    </form>

    <ul id="todo-list" style="list-style: none; padding: 0;">
    <?php
    $sql_todo = "SELECT todo_id, task_description, is_completed 
                 FROM todo_list WHERE user_id = ?";
    $stmt_todo = $conn->prepare($sql_todo);
    $stmt_todo->bind_param("i", $user_id);
    $stmt_todo->execute();
    $result_todo = $stmt_todo->get_result();

    while ($todo = $result_todo->fetch_assoc()) {
        $checked = $todo['is_completed'] ? 'checked' : '';
        $style = $todo['is_completed'] ? 'text-decoration: line-through; color: #999;' : '';
        
        echo "<li id='todo-{$todo['todo_id']}' style='padding: 10px; margin: 5px 0; background: white; border-radius: 5px; $style'>
                <input type='checkbox' $checked onchange='toggleTodo({$todo['todo_id']})'>
                " . htmlspecialchars($todo['task_description']) . "
             </li>";
    }
    ?>
    </ul>
</div>

<div class="pet-container">
    <div class="pet-box <?php echo $is_pet_sad ? 'sad' : ''; ?>" id="petBox">
        <div class="pat-counter <?php echo $is_pet_sad ? 'healing' : ''; ?>" id="patCounter">
            <?php echo $is_pet_sad ? ($wrong_count > 0 ? $wrong_count : '!') : '0'; ?>
        </div>
        <div class="speech-bubble" id="speechBubble"></div>
        <div class="pet-emoji <?php echo $is_pet_sad ? 'sad crying' : ''; ?>" id="petEmoji" onclick="patPet()"><?php echo $pet_emoji; ?></div>
        <div class="pet-name">My <?php echo $pet_name; ?></div>
        <div class="pet-mood" id="petMood"><?php echo $is_pet_sad ? '😢 Sedih' : '😊 Happy'; ?></div>
    </div>
</div>

</main>

<footer>

</footer>

<script>
let patCount = 0;
let idleTimer;
let lastPatTime = Date.now();
let isSad = <?php echo $is_pet_sad ? 'true' : 'false'; ?>;
let patsNeeded = <?php echo max($wrong_count, 5); ?>;
let healingPats = 0;

const petEmoji = document.getElementById('petEmoji');
const patCounter = document.getElementById('patCounter');
const speechBubble = document.getElementById('speechBubble');
const petMood = document.getElementById('petMood');
const petBox = document.getElementById('petBox');

const happyPhrases = [
    '<?php echo $pet_sound; ?>',
    '❤️ Love you!',
    '😊 Yay!',
    '✨ Ahhh enak banget!',
    '🥰 Elus dong!',
    '💕 Thank you!',
    '🎉 Woohoo!'
];

const sadPhrases = [
    '😢 Aku sedih',
    '💔 Maafkan aku',
    '😭 Aku mengecewakanmu',
    '🥺 Elus aku dong...',
    '💧 Kenapa aku begini...'
];

const healingPhrases = [
    '🌟 Oh!',
    '💚 Aku merasa baikan',
    '😊 Thank you!',
    '✨ Aku merasa lebih senang!',
    '💖 Kamu peduli aku!',
    '🥰 Lagi plis!'
];

const happyMoods = [
    '😊 Senang',
    '🥰 Loved',
    '😍 Girang',
    '✨ Gembira',
    '💖 Blessed'
];

const sadMoods = [
    '😢 Sedih',
    '💔 Terluka',
    '😭 Menangis',
    '🥺 Cemberut',
    '😿 Kecewa'
];

if (isSad) {
    setInterval(() => {
        createTear();
    }, 3000);
}

function patPet() {
    const sound = document.getElementById('petSound');
    sound.currentTime = 0;
    sound.play();
    lastPatTime = Date.now();
    
    if (isSad) {
        healingPats++;
        patCounter.textContent = patsNeeded - healingPats;
        
        petEmoji.classList.remove('sad', 'crying', 'squish', 'happy', 'wiggle');
        petEmoji.classList.add('squish');
        
        const randomPhrase = healingPhrases[Math.floor(Math.random() * healingPhrases.length)];
        speechBubble.textContent = randomPhrase;
        speechBubble.classList.add('show');
        
        createHeart();
        
        if (healingPats >= patsNeeded) {
            healPet();
        }
        
        setTimeout(() => {
            petEmoji.classList.remove('squish');
        }, 500);
        
    } else {
        patCount++;
        patCounter.textContent = patCount;
        
        petEmoji.classList.remove('squish', 'happy', 'wiggle', 'idle-blink', 'idle-bounce');
        
        const animations = ['squish', 'happy', 'wiggle'];
        const randomAnim = animations[Math.floor(Math.random() * animations.length)];
        petEmoji.classList.add(randomAnim);
        
        const randomPhrase = happyPhrases[Math.floor(Math.random() * happyPhrases.length)];
        speechBubble.textContent = randomPhrase;
        speechBubble.classList.add('show');
        
        const randomMood = happyMoods[Math.floor(Math.random() * happyMoods.length)];
        petMood.textContent = randomMood;
        
        createHeart();
        
        setTimeout(() => {
            petEmoji.classList.remove(randomAnim);
        }, 500);
        
        resetIdleTimer();
    }
    
    setTimeout(() => {
        speechBubble.classList.remove('show');
    }, 2000);
}

function healPet() {
    isSad = false;
    petBox.classList.remove('sad');
    petEmoji.classList.remove('sad', 'crying');
    patCounter.classList.remove('healing');
    patCounter.textContent = '0';
    patCount = 0;
    healingPats = 0;
    
    petMood.textContent = '🎉 Sembuh!';
    speechBubble.textContent = '💖 Terima kasih! Aku bahagia lagi!';
    speechBubble.classList.add('show');
    
    petEmoji.classList.add('happy');
    setTimeout(() => {
        petEmoji.classList.remove('happy');
    }, 1000);
    
    for (let i = 0; i < 10; i++) {
        setTimeout(() => createHeart(), i * 100);
    }
    
    setTimeout(() => {
        speechBubble.classList.remove('show');
        petMood.textContent = '😊 Happy';
    }, 3000);
    
    fetch('clear_pet_sad.php')
        .then(() => {
            resetIdleTimer();
        });
}

function createHeart() {
    const heart = document.createElement('div');
    heart.className = 'heart';
    heart.textContent = '❤️';
    
    const randomX = Math.random() * 100 - 50;
    heart.style.left = `calc(50% + ${randomX}px)`;
    heart.style.top = '50%';
    
    petBox.appendChild(heart);
    
    setTimeout(() => {
        heart.remove();
    }, 1500);
}

function createTear() {
    if (!isSad) return;
    
    const tear = document.createElement('div');
    tear.className = 'tear';
    tear.textContent = '💧';
    
    const randomX = Math.random() * 60 - 30;
    tear.style.left = `calc(50% + ${randomX}px)`;
    tear.style.top = '60%';
    
    petBox.appendChild(tear);
    
    setTimeout(() => {
        tear.remove();
    }, 2000);
}

function startIdleAnimation() {
    if (isSad) {
        const randomSad = sadPhrases[Math.floor(Math.random() * sadPhrases.length)];
        speechBubble.textContent = randomSad;
        speechBubble.classList.add('show');
        
        setTimeout(() => {
            speechBubble.classList.remove('show');
        }, 3000);
        
        const randomMood = sadMoods[Math.floor(Math.random() * sadMoods.length)];
        petMood.textContent = randomMood;
        
        return;
    }
    
    const idleAnimations = ['idle-blink', 'idle-bounce'];
    const randomIdle = idleAnimations[Math.floor(Math.random() * idleAnimations.length)];
    
    petEmoji.classList.add(randomIdle);
    
    setTimeout(() => {
        petEmoji.classList.remove(randomIdle);
    }, 3000);
}

function resetIdleTimer() {
    clearInterval(idleTimer);
    idleTimer = setInterval(() => {
        const timeSinceLastPat = Date.now() - lastPatTime;
        if (timeSinceLastPat > 8000) {
            startIdleAnimation();
        }
    }, 8000);
}

function toggleTodo(id) {
    fetch("toggle_todo.php?id=" + id)
        .then(response => response.text())
        .then(result => {
            let li = document.getElementById("todo-" + id);
            if (result === "1") {
                li.style.textDecoration = "line-through";
                li.style.color = "#999";
            } else {
                li.style.textDecoration = "none";
                li.style.color = "#000";
            }
        });
}

resetIdleTimer();
setTimeout(startIdleAnimation, 3000);

if (isSad) {
    setTimeout(() => {
        speechBubble.textContent = '😢 Aku butuh ' + patsNeeded + ' pelukan untuk ceria lagi...';
        speechBubble.classList.add('show');
        setTimeout(() => {
            speechBubble.classList.remove('show');
        }, 4000);
    }, 1000);
}
</script>

</body>
</html>