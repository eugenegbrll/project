<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
include 'db.php';
$user_id = $_SESSION['user_id'];

$search = $_GET['search'] ?? '';
$topic_filter = $_GET['topic'] ?? '';

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
    <script>
        function deleteTodo(todoId) {
            if (!confirm("Yakin hapus tugas ini?")) return;

            fetch("delete_todo.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "todo_id=" + todoId
            })
            .then(res => {
                if (res.ok) {
                    document.getElementById("todo-" + todoId).remove();
                } else {
                    alert("Gagal menghapus todo");
                }
            });
        }
    </script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const petContainer = document.getElementById("petContainer");

    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;

    petContainer.addEventListener("mousedown", (e) => {
        isDragging = true;

        const rect = petContainer.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;

        petContainer.style.transition = "none";
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;

        let x = e.clientX - offsetX;
        let y = e.clientY - offsetY;

        const maxX = window.innerWidth - petContainer.offsetWidth;
        const maxY = window.innerHeight - petContainer.offsetHeight;

        x = Math.max(0, Math.min(x, maxX));
        y = Math.max(0, Math.min(y, maxY));

        petContainer.style.left = x + "px";
        petContainer.style.top = y + "px";
        petContainer.style.right = "auto";
        petContainer.style.bottom = "auto";
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
    });
});
</script>

</head>
<body>
<header>
    <div class="container">
        <h1><a href="student_dashboard.php" style="color:white">EduQuest</a></h1>
        <nav>
            <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
            <p><a href="student_profile.php" style="color: white; text-decoration: none;">Profile</a></p>
            <p><a href="logout.php">Logout</a></p>
        </nav>
    </div>
</header>

<main>
    <div class="subcontainer">
        <h2>Course yang Diambil</h2>
        <a href="take_course.php" style="text-decoration:underline;">Ambil Course Baru</a>
    </div>

    <hr>

    <div class="search-filter-container">
        <form method="GET" action="student_dashboard.php" class="search-filter-form">
            <input type="text" 
                   name="search" 
                   placeholder="🔍 Cari course..." 
                   value="<?= htmlspecialchars($search) ?>" 
                   class="search-input">

            <select name="topic" class="topic-filter">
                <option value="">Semua Topik</option>
                <option value="Matematika" <?= $topic_filter == 'Matematika' ? 'selected' : '' ?>>Matematika</option>
                <option value="Sains" <?= $topic_filter == 'Sains' ? 'selected' : '' ?>>Sains</option>
                <option value="Bahasa Inggris" <?= $topic_filter == 'Bahasa Inggris' ? 'selected' : '' ?>>Bahasa Inggris</option>
                <option value="Bahasa Indonesia" <?= $topic_filter == 'Bahasa Indonesia' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                <option value="Sejarah" <?= $topic_filter == 'Sejarah' ? 'selected' : '' ?>>Sejarah</option>
                <option value="Fisika" <?= $topic_filter == 'Fisika' ? 'selected' : '' ?>>Fisika</option>
                <option value="Kimia" <?= $topic_filter == 'Kimia' ? 'selected' : '' ?>>Kimia</option>
                <option value="Biologi" <?= $topic_filter == 'Biologi' ? 'selected' : '' ?>>Biologi</option>
                <option value="Komputer" <?= $topic_filter == 'Komputer' ? 'selected' : '' ?>>Komputer</option>
                <option value="Lainnya" <?= $topic_filter == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>

            <button type="submit" class="btn-search">Cari</button>
            <a href="student_dashboard.php" class="btn-reset">Reset</a>
        </form>
    </div>

    <?php if (!empty($search) || !empty($topic_filter)): ?>
        <div class="active-filters">
            <span class="filter-label">Filter aktif:</span>
            <?php if (!empty($search)): ?>
                <span class="filter-tag">Pencarian: "<?= htmlspecialchars($search) ?>"</span>
            <?php endif; ?>
            <?php if (!empty($topic_filter)): ?>
                <span class="filter-tag">Topik: <?= htmlspecialchars($topic_filter) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <div class="courses-container">
        <?php
        $sql = "
        SELECT 
            c.course_id,
            c.course_name,
            c.description,
            c.teacher_name,

            -- total materi per course
            (SELECT COUNT(*) 
            FROM materials m 
            WHERE m.course_id = c.course_id) AS total_materials,

            -- materi yang sudah diselesaikan oleh user
            (SELECT COUNT(*) 
            FROM material_completions mc
            JOIN materials m2 ON mc.material_id = m2.material_id
            WHERE mc.user_id = ? AND m2.course_id = c.course_id) AS completed_materials

        FROM courses c
        JOIN student_courses sc ON c.course_id = sc.course_id
        WHERE sc.user_id = ?
        ";
        
        if (!empty($search)) {
            $sql .= " AND (c.course_name LIKE ? OR c.description LIKE ? OR c.teacher_name LIKE ?)";
        }
        
        if (!empty($topic_filter)) {
            $sql .= " AND (c.course_name LIKE ? OR c.description LIKE ?)";
        }
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($search) && !empty($topic_filter)) {
            $search_param = "%$search%";
            $topic_param = "%$topic_filter%";
            $stmt->bind_param("iisssss", $user_id, $user_id, $search_param, $search_param, $search_param, $topic_param, $topic_param);
        } elseif (!empty($search)) {
            $search_param = "%$search%";
            $stmt->bind_param("iisss", $user_id, $user_id, $search_param, $search_param, $search_param);
        } elseif (!empty($topic_filter)) {
            $topic_param = "%$topic_filter%";
            $stmt->bind_param("iiss", $user_id, $user_id, $topic_param, $topic_param);
        } else {
            $stmt->bind_param("ii", $user_id, $user_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            if (!empty($search) || !empty($topic_filter)) {
                echo "<div class='empty-message'>
                        <h3>Tidak Ada Hasil</h3>
                        <p>Tidak ada course yang sesuai dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.</p>
                    </div>";
            } else {
                echo "<div class='course-card'><p>Kamu belum mengambil course apapun. Silakan ambil course baru!</p></div>";
            }
        }

        while ($course = $result->fetch_assoc()) {
            $total = $course['total_materials'];
            $completed = $course['completed_materials'];
            $progress = ($total > 0) ? round(($completed / $total) * 100) : 0;
            
            $course_name = $course['course_name'];
            $topic_badge = 'Lainnya';
            $badge_color = '#a5aeb7ff';
            
            if (stripos($course_name, 'Math') !== false || stripos($course_name, 'Matematika') !== false) {
                $topic_badge = 'Matematika';
                $badge_color = '#8a90ffff';
            } elseif (stripos($course_name, 'Science') !== false || stripos($course_name, 'Sains') !== false || stripos($course_name, 'IPA') !== false) {
                $topic_badge = 'Sains';
                $badge_color = '#81ff9eff';
            } elseif (stripos($course_name, 'English') !== false || stripos($course_name, 'Inggris') !== false) {
                $topic_badge = 'Bahasa Inggris';
                $badge_color = '#ef717eff';
            } elseif (stripos($course_name, 'Indonesian') !== false || stripos($course_name, 'Indonesia') !== false) {
                $topic_badge = 'Bahasa Indonesia';
                $badge_color = '#f3c55aff';
            } elseif (stripos($course_name, 'History') !== false || stripos($course_name, 'Sejarah') !== false) {
                $topic_badge = 'Sejarah';
                $badge_color = '#bc7f69ff';
            } elseif (stripos($course_name, 'Physics') !== false || stripos($course_name, 'Fisika') !== false) {
                $topic_badge = 'Fisika';
                $badge_color = '#d983e9ff';
            } elseif (stripos($course_name, 'Chemistry') !== false || stripos($course_name, 'Kimia') !== false) {
                $topic_badge = 'Kimia';
                $badge_color = '#faa676ff';
            } elseif (stripos($course_name, 'Biology') !== false || stripos($course_name, 'Biologi') !== false) {
                $topic_badge = 'Biologi';
                $badge_color = '#52ce56ff';
            } elseif (stripos($course_name, 'Programming') !== false || stripos($course_name, 'Coding') !== false || stripos($course_name, 'Computer') !== false) {
                $topic_badge = 'Komputer';
                $badge_color = '#39c9dcff';
            }

            echo "<div class='course-card'>";
            echo "<h3>" . htmlspecialchars($course['course_name']);
            echo "<span class='topic-badge' style='background-color: $badge_color;'>$topic_badge</span>";
            echo "</h3>";
            echo "<p>Progress: $progress%</p>";
            echo "<a href='course_detail.php?id=" . $course['course_id'] . "'>Lihat Detail</a>";
            echo "</div>";
        }
        ?>
    </div>

    <hr>

    <div class="todo-container">
        <h2>To-Do List</h2>

        <form action="add_todo.php" method="POST">
            <input type="text" name="task" placeholder="Tambah tugas baru" required style="padding: 10px; width: 500px; border: 1px solid #ddd; border-radius: 5px;">
            <button type="submit">Tambah</button>
        </form>

        <ul id="todo-list" style="list-style: none; padding:0; max-width: 850px; border: 1px solid #ddd; border-radius: 5px; margin-top: 20px; background: #f9f9f9;">
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
            
            echo "<li id='todo-{$todo['todo_id']}' 
                style='display:flex;align-items:center;justify-content:space-between;
                    padding:10px;margin:5px 0;background:white;border-radius:5px;$style'>
                
                <div>
                    <input type='checkbox' $checked onchange='toggleTodo({$todo['todo_id']})'>
                    " . htmlspecialchars($todo['task_description']) . "
                </div>

                <button onclick='deleteTodo({$todo['todo_id']})'
                        style='background:none;border:none;color:red;
                            font-size:18px;cursor:pointer;'>
                    ❌
                </button>
            </li>";
        }
        ?>
        </ul>
    </div>

    <div class="pet-container" id="petContainer">
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
        '✨ Yipeeeee!',
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
        '😄 Girang',
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

    <footer>
        <?php include 'footer.html'; ?>
    </footer>

</body>
</html>