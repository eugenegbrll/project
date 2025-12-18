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
$is_pet_proud = isset($_SESSION['pet_proud']) && $_SESSION['pet_proud'];
$wrong_count = $_SESSION['wrong_answer_count'] ?? 0;
$quiz_score = $_SESSION['quiz_score'] ?? null;
$quiz_correct = $_SESSION['quiz_correct'] ?? null;
$quiz_total = $_SESSION['quiz_total'] ?? null;

if ($quiz_score !== null) {
    unset($_SESSION['quiz_score']);
    unset($_SESSION['quiz_correct']);
    unset($_SESSION['quiz_total']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="student_dashboard.js" defer></script>
    <audio id="petSound" src="sounds/<?php echo $pet_sound; ?>"></audio>
    
    <?php if ($quiz_score !== null): ?>
    <script>
        window.quizScore = <?= $quiz_score ?>;
        window.quizCorrect = <?= $quiz_correct ?>;
        window.quizTotal = <?= $quiz_total ?>;
    </script>
    <?php endif; ?>
    
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
            (SELECT COUNT(*) 
            FROM materials m 
            WHERE m.course_id = c.course_id) AS total_materials,
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

    <hr>

    <div class="score-analytics-container">
        <h2>📊 Grafik Nilai</h2>
        <div id="scoreLoadingIndicator" class="loading-scores">
            <p>Memuat data score...</p>
        </div>
        <div id="scoreContent" style="display: none;">
            <div id="performanceBadge"></div>
            <div class="analytics-grid">
                <div class="stat-card">
                    <div class="stat-value" id="overallScore">0%</div>
                    <div class="stat-label">Overall Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="materialsCompleted">0</div>
                    <div class="stat-label">Materials Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="totalCorrect">0</div>
                    <div class="stat-label">Correct Answers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="totalAnswered">0</div>
                    <div class="stat-label">Total Questions</div>
                </div>
            </div>
            
            <div class="chart-wrapper">
                <canvas id="courseScoreChart"></canvas>
            </div>

            <h3 style="color: #2c5aa0; margin-top: 30px; margin-bottom: 15px;">📚 Material yang Telah Dikerjakan</h3>
            <div class="recent-scores" id="recentScores"></div>
        </div>
    </div>

    <div class="pet-container" id="petContainer">
        <div class="pet-box <?php echo $is_pet_sad ? 'sad' : ($is_pet_proud ? 'proud' : ''); ?>" id="petBox">
            <div class="pat-counter <?php echo $is_pet_sad ? 'healing' : ($is_pet_proud ? 'proud' : ''); ?>" id="patCounter">
                <?php echo $is_pet_sad ? ($wrong_count > 0 ? $wrong_count : '!') : '0'; ?>
            </div>
            <div class="speech-bubble" id="speechBubble"></div>
            <div class="pet-emoji <?php echo $is_pet_sad ? 'sad crying' : ($is_pet_proud ? 'proud' : ''); ?>" id="petEmoji" onclick="patPet()"><?php echo $pet_emoji; ?></div>
            <div class="pet-name">My <?php echo $pet_name; ?></div>
            <div class="pet-mood" id="petMood"><?php echo $is_pet_sad ? '😢 Sedih' : ($is_pet_proud ? '🌟 Proud!' : '😊 Happy'); ?></div>
        </div>
    </div>
    </main>

    <script>
    let scoreData = null;
    let scoreChart = null;

    const scoreBasedMessages = {
        excellent: [
            "🌟 Wow! Kamu luar biasa!",
            "🏆 Perfect! Aku sangat bangga padamu!",
            "⭐ Incredible! Kamu juara sejati!",
            "💯 Amazing work! Pertahankan ya!",
            "🎉 Outstanding! Kamu memang hebat!"
        ],
        good: [
            "👏 Great job! Score kamu bagus sekali!",
            "😊 Bagus! Kamu di jalur yang benar!",
            "💪 Good work! Sedikit lagi jadi sempurna!",
            "🎯 Nice! Progress yang solid!",
            "✨ Well done! Terus semangat!",
            "🥳 Widih, kerennn!"
        ],
        average: [
            "💙 Kamu bisa lebih baik! Aku percaya!",
            "🌱 Tetap belajar! Kamu pasti bisa!",
            "📚 Jangan menyerah! Practice makes perfect!",
            "🤗 Ayo semangat! Kamu mampu lebih!",
            "💫 Terus belajar! Aku akan menemanimu!"
        ],
        poor: [
            "🤗 Tidak apa-apa! Semua orang pernah struggle!",
            "💕 Ayo, kamu pasti bisa lebih baik lagi!",
            "🌈 Kesalahan bisa membuat kita lebih kuat!",
            "🫂 Jangan sedih, kamu tidak sendiri!",
            "💖 Aku tahu kamu bisa lebih baik! Yuk coba lagi!",
            "✨ Jangan stress ya, bisa kok"
        ]
    };

    async function loadScoreData() {
        try {
            const response = await fetch('get_scores.php');
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            scoreData = data;
            renderScoreAnalytics();
            observeScoreSection();
        } catch (error) {
            console.error('Error loading scores:', error);
            document.getElementById('scoreLoadingIndicator').innerHTML = 
                '<div class="no-score-data"><h3>❌ Error</h3><p>Gagal memuat data. Silakan refresh halaman.</p></div>';
        }
    }

    function renderScoreAnalytics() {
        const overall = scoreData.overall;
        
        if (!overall.total_answered || overall.total_answered === 0) {
            document.getElementById('scoreLoadingIndicator').innerHTML = 
                '<div class="no-score-data"><h3>📚 Belum Ada Data Quiz</h3><p>Mulai mengerjakan quiz untuk melihat statistik kamu!</p></div>';
            return;
        }

        document.getElementById('scoreLoadingIndicator').style.display = 'none';
        document.getElementById('scoreContent').style.display = 'block';

        document.getElementById('overallScore').textContent = 
            (overall.overall_percentage || 0) + '%';
        document.getElementById('materialsCompleted').textContent = 
            overall.materials_completed || 0;
        document.getElementById('totalCorrect').textContent = 
            overall.total_correct || 0;
        document.getElementById('totalAnswered').textContent = 
            overall.total_answered || 0;

        renderPerformanceBadge(overall.overall_percentage);
        renderCourseScoreChart();
        renderRecentScores();
    }

    function renderPerformanceBadge(score) {
        const badge = document.getElementById('performanceBadge');
        let className = '';
        let text = '';
        let icon = '';

        if (score >= 90) {
            className = 'badge-excellent';
            text = 'Excellent Performance!';
            icon = '🌟';
        } else if (score >= 75) {
            className = 'badge-good';
            text = 'Good Performance!';
            icon = '👍';
        } else if (score >= 60) {
            className = 'badge-average';
            text = 'Average Performance';
            icon = '🤌';
        } else {
            className = 'badge-poor';
            text = 'Needs Improvement';
            icon = '✍️';
        }

        badge.className = `performance-badge ${className}`;
        badge.innerHTML = `${icon} ${text}`;
    }

    function renderCourseScoreChart() {
        const ctx = document.getElementById('courseScoreChart').getContext('2d');
        
        if (scoreChart) {
            scoreChart.destroy();
        }

        const courses = scoreData.courses;
        
        if (courses.length === 0) {
            document.querySelector('.chart-wrapper').innerHTML = 
                '<p style="text-align:center;color:#999;padding:40px;">Belum ada data course</p>';
            return;
        }

        const labels = courses.map(c => c.course_name);
        const scores = courses.map(c => parseFloat(c.score_percentage) || 0);
        
        const backgroundColors = scores.map(score => {
            if (score >= 90) return 'rgba(76, 175, 80, 0.8)';
            if (score >= 75) return 'rgba(33, 150, 243, 0.8)';
            if (score >= 60) return 'rgba(255, 152, 0, 0.8)';
            return 'rgba(244, 67, 54, 0.8)';
        });

        scoreChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Score (%)',
                    data: scores,
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: scores.map(score => {
                        if (score >= 90) return 'rgba(76, 175, 80, 1)';
                        if (score >= 75) return 'rgba(33, 150, 243, 1)';
                        if (score >= 60) return 'rgba(255, 152, 0, 1)';
                        return 'rgba(244, 67, 54, 1)';
                    }),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: scores.map(score => {
                        if (score >= 90) return 'rgba(76, 175, 80, 1)';
                        if (score >= 75) return 'rgba(33, 150, 243, 1)';
                        if (score >= 60) return 'rgba(255, 152, 0, 1)';
                        return 'rgba(244, 67, 54, 1)';
                    }),
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Score: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    function renderRecentScores() {
        const container = document.getElementById('recentScores');
        const materials = scoreData.recent_materials;

        if (materials.length === 0) {
            container.innerHTML = '<p class="no-score-data">Belum ada material yang diselesaikan</p>';
            return;
        }

        container.innerHTML = materials.map(m => {
            const score = parseFloat(m.score_percentage) || 0;
            let scoreClass = 'score-poor';
            if (score >= 90) scoreClass = 'score-excellent';
            else if (score >= 75) scoreClass = 'score-good';
            else if (score >= 60) scoreClass = 'score-average';

            return `
                <div class="score-item">
                    <div class="score-item-title">${m.material_title}</div>
                    <div class="score-item-course">📚 ${m.course_name}</div>
                    <div class="score-item-value ${scoreClass}">
                        ${score.toFixed(1)}% <span style="font-size:14px;">(${m.correct_answers}/${m.total_questions})</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updatePetWithScoreData() {
        if (!scoreData || !scoreData.overall.total_answered || scoreData.overall.total_answered === 0) {
            return;
        }

        const score = parseFloat(scoreData.overall.overall_percentage) || 0;
        let messageCategory = '';

        if (score >= 90) messageCategory = 'excellent';
        else if (score >= 75) messageCategory = 'good';
        else if (score >= 60) messageCategory = 'average';
        else messageCategory = 'poor';

        const messages = scoreBasedMessages[messageCategory];
        const randomMessage = messages[Math.floor(Math.random() * messages.length)];

        setTimeout(() => {
            const bubble = document.getElementById('speechBubble');
            bubble.textContent = randomMessage;
            bubble.classList.add('show');
            
            setTimeout(() => {
                bubble.classList.remove('show');
            }, 5000);
        }, 2000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadScoreData();
    });

    function observeScoreSection() {
        if (!scoreAnalyticsContainer) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {

                if (entry.isIntersecting) {
                    if (!isWatchingGraph) {
                        isWatchingGraph = true;

                        petTalkAboutScore();

                        graphTalkInterval = setInterval(() => {
                            if (isWatchingGraph) {
                                petTalkAboutScore();
                            }
                        }, 7000);
                    }
                } else {
                    isWatchingGraph = false;
                    clearInterval(graphTalkInterval);
                    graphTalkInterval = null;
                }

            });
        }, {
            threshold: 0.4
        });

        observer.observe(scoreAnalyticsContainer);
    }

    let patCount = 0;
    let idleTimer;
    let lastPatTime = Date.now();
    let isSad = <?php echo $is_pet_sad ? 'true' : 'false'; ?>;
    let isProud = <?php echo $is_pet_proud ? 'true' : 'false'; ?>;
    let patsNeeded = <?php echo max($wrong_count, 5); ?>;
    let healingPats = 0;
    let isTrackingCursor = false;
    let hasReactedToGraph = false;
    let graphTalkInterval = null;
    let isWatchingGraph = false;


    const petEmoji = document.getElementById('petEmoji');
    const petContainer = document.getElementById('petContainer');
    const patCounter = document.getElementById('patCounter');
    const speechBubble = document.getElementById('speechBubble');
    const petMood = document.getElementById('petMood');
    const petBox = document.getElementById('petBox');
    const scoreAnalyticsContainer = document.querySelector('.score-analytics-container');

    const happyPhrases = [
        '🎵 <?php echo $pet_sound; ?> noises',
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
        '😭 Sangat mengecewakan',
        '🥺 Elus aku dong...',
        '💧 ...'
    ];

    const proudPhrases = [
        '🌟 Kamu hebat!',
        '🏆 Good job!',
        '👏 Amazing!',
        '💪 You did it!',
        '⭐ Sempurna!',
        '🎯 Excellent!',
        '🎉 Aku bangga!',
        '💖 You are the best!'
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

    const proudMoods = [
        '🌟 Proud!',
        '🏆 Bangga!',
        '👑 You rock!',
        '⭐ Amazing!',
        '💫 Terbaik!'
    ];

    const lastGreeting = localStorage.getItem('petLastGreeting');
    const now = Date.now();
    const fiveMinutes = 5 * 60 * 1000;
    
    const shouldGreet = !lastGreeting || (now - parseInt(lastGreeting)) > fiveMinutes;
    
    if (shouldGreet) {
        setTimeout(() => {
            const hour = new Date().getHours();
            let greeting = '';
            
            if (hour >= 5 && hour < 12) {
                greeting = '🌅 Selamat pagi! Semangat belajar!';
            } else if (hour >= 12 && hour < 15) {
                greeting = '☀️ Selamat siang! Waktunya makan!';
            } else if (hour >= 15 && hour < 18) {
                greeting = '🌤️ Selamat sore! Waktunya nyantai!';
            } else if (hour >= 18 && hour < 22) {
                greeting = '🌆 Selamat malam! Waktunya enak untuk tidur!';
            } else {
                greeting = '🌙 Selamat malam! Jangan begadang ya!';
            }
            
            speechBubble.textContent = greeting;
            speechBubble.classList.add('show');
            
            petEmoji.classList.add('happy');
            
            for (let i = 0; i < 5; i++) {
                setTimeout(() => createHeart(), i * 150);
            }
            
            setTimeout(() => {
                speechBubble.classList.remove('show');
                petEmoji.classList.remove('happy');
            }, 4000);
            
            localStorage.setItem('petLastGreeting', now.toString());
        }, 800);
    }
    
    if (typeof window.quizScore !== 'undefined' && isProud) {
        setTimeout(() => {
            const scoreMessage = `🎉 Skor kamu ${window.quizScore}%! Aku sangat bangga padamu! (${window.quizCorrect}/${window.quizTotal} benar)`;
            speechBubble.textContent = scoreMessage;
            speechBubble.classList.add('show');
            
            petEmoji.classList.add('happy');
            
            for (let i = 0; i < 15; i++) {
                setTimeout(() => createHeart(), i * 100);
            }
            
            setTimeout(() => {
                speechBubble.classList.remove('show');
                petEmoji.classList.remove('happy');
            }, 5000);
        }, shouldGreet ? 5000 : 500);
        
        setTimeout(() => {
            fetch('clear_pet_proud.php');
        }, shouldGreet ? 10500 : 6000);
    }

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
            
        } else if (isProud) {
            patCount++;
            patCounter.textContent = patCount;
            
            petEmoji.classList.remove('squish', 'happy', 'wiggle', 'proud');
            petEmoji.classList.add('happy');
            
            const randomPhrase = proudPhrases[Math.floor(Math.random() * proudPhrases.length)];
            speechBubble.textContent = randomPhrase;
            speechBubble.classList.add('show');
            
            const randomMood = proudMoods[Math.floor(Math.random() * proudMoods.length)];
            petMood.textContent = randomMood;
            
            for (let i = 0; i < 3; i++) {
                setTimeout(() => createHeart(), i * 100);
            }
            
            setTimeout(() => {
                petEmoji.classList.remove('happy');
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
        }
        
        resetIdleTimer();
        
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

    function petTalkAboutScore() {
        if (!scoreData || !scoreData.overall.total_answered) return;

        const score = parseFloat(scoreData.overall.overall_percentage) || 0;
        let category = 'average';

        if (score >= 90) category = 'excellent';
        else if (score >= 75) category = 'good';
        else if (score >= 60) category = 'average';
        else category = 'poor';

        const messages = scoreBasedMessages[category];
        const message = messages[Math.floor(Math.random() * messages.length)];

        speechBubble.textContent = message;
        speechBubble.classList.add('show');

        setTimeout(() => {
            speechBubble.classList.remove('show');
        }, 4000);
    }

    </script>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>

</body>
</html>