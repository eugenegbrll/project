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

$sql_all_courses = "SELECT COUNT(*) as total FROM courses";
$result_all = $conn->query($sql_all_courses);
$total_courses = $result_all->fetch_assoc()['total'];

$sql = "SELECT * FROM courses 
        WHERE course_id NOT IN (
            SELECT course_id FROM student_courses WHERE user_id = ?
        )";

if (!empty($search)) {
    $sql .= " AND (course_name LIKE ? OR description LIKE ? OR teacher_name LIKE ?)";
}

if (!empty($topic_filter)) {
    $sql .= " AND (course_name LIKE ? OR description LIKE ?)";
}

$stmt = $conn->prepare($sql);

if (!empty($search) && !empty($topic_filter)) {
    $search_param = "%$search%";
    $topic_param = "%$topic_filter%";
    $stmt->bind_param("isssss", $user_id, $search_param, $search_param, $search_param, $topic_param, $topic_param);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("isss", $user_id, $search_param, $search_param, $search_param);
} elseif (!empty($topic_filter)) {
    $topic_param = "%$topic_filter%";
    $stmt->bind_param("iss", $user_id, $topic_param, $topic_param);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$topics_sql = "SELECT DISTINCT 
                CASE 
                    WHEN course_name LIKE '%Math%' OR course_name LIKE '%Matematika%' THEN 'Matematika'
                    WHEN course_name LIKE '%Science%' OR course_name LIKE '%Sains%' OR course_name LIKE '%IPA%' THEN 'Sains'
                    WHEN course_name LIKE '%English%' OR course_name LIKE '%Bahasa Inggris%' THEN 'Bahasa Inggris'
                    WHEN course_name LIKE '%Indonesian%' OR course_name LIKE '%Bahasa Indonesia%' THEN 'Bahasa Indonesia'
                    WHEN course_name LIKE '%History%' OR course_name LIKE '%Sejarah%' THEN 'Sejarah'
                    WHEN course_name LIKE '%Physics%' OR course_name LIKE '%Fisika%' THEN 'Fisika'
                    WHEN course_name LIKE '%Chemistry%' OR course_name LIKE '%Kimia%' THEN 'Kimia'
                    WHEN course_name LIKE '%Biology%' OR course_name LIKE '%Biologi%' THEN 'Biologi'
                    WHEN course_name LIKE '%Programming%' OR course_name LIKE '%Coding%' OR course_name LIKE '%Computer%' THEN 'Komputer'
                    ELSE 'Lainnya'
                END as topic
                FROM courses";
$topics_result = $conn->query($topics_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ambil Course</title>
    <link rel="stylesheet" href="take_course.css">
</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
        }
    </script>
    <header>
        <div class="bar">
            <h1><a href="student_dashboard.php" style="color:white">EduQuest</a></h1>
            <nav style="display: flex; align-items: center; gap: 20px;">
                <button id="theme-toggle" style="background:none; border:none; cursor:pointer; font-size:20px; padding:0; margin:0; line-height:1; display:flex; align-items:center;">🌙</button>
                <p>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                <p><a href="student_profile.php" style="color: white; text-decoration: none;">Profile</a></p>
                <p><a href="logout.php">Logout</a></p>
            </nav>
        </div>
    </header>

    <main> 
        <a href="student_dashboard.php">⬅ Kembali</a>
        <br>
        <h2>Pilih Course Baru</h2>
       

        <div class="search-filter-container">
            <form method="GET" action="take_course.php" class="search-filter-form">
                <div class="search-box">
                    <input type="text" name="search" style="width:50%" placeholder="🔍 Cari course berdasarkan nama, deskripsi, atau guru" value="<?= htmlspecialchars($search) ?>" class="search-input">
                </div>

                <div class="filter-box" style="width:15%">
                    <select name="topic" class="topic-filter" >
                        <option value="">Semua Topik</option>
                        <option value="Matematika" <?= $topic_filter == 'Matematika' ? 'selected' : '' ?>>Matematika</option>
                        <option value="Sains" <?= $topic_filter == 'Sains' ? 'selected' : '' ?>>Sains</option>
                        <option value="Bahasa Inggris" <?= $topic_filter == 'Bahasa Inggris' ? 'selected' : '' ?>>Bahasa Inggris</option>
                        <option value="Bahasa Indonesia" <?= $topic_filter == 'Bahasa Indonesia' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                        <option value="Sejarah" <?= $topic_filter == 'Sejarah' ? 'selected' : '' ?>>Sejarah</option>
                        <option value="Fisika" <?= $topic_filter == 'Fisika' ? 'selected' : '' ?>>Fisika</option>
                        <option value="Kimia" <?= $topic_filter == 'Kimia' ? 'selected' : '' ?>>Kimia</option>
                        <option value="Biologi" <?= $topic_filter == 'Biologi' ? 'selected' : '' ?>>Biologi</option>
                        <option value="Komputer" <?= $topic_filter == 'Komputer' ? 'selected' : '' ?>>Komputer & Programming</option>
                        <option value="Lainnya" <?= $topic_filter == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div class="button-group" style="margin-top:10px;">
                    <button type="submit" class="btn-search">Cari</button>
                    <a href="take_course.php" class="btn-reset">Reset</a>
                </div>
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
        <?php endif; ?> <br>

        <div class="courses-list">
            <?php
            if ($total_courses == 0) {
                echo "<div class='empty-message'>
                        <h3>Belum Ada Course</h3>
                        <p>Saat ini belum ada course yang tersedia. Silakan hubungi admin untuk menambahkan course.</p>
                    </div>";
            } elseif ($result->num_rows == 0) {
                if (!empty($search) || !empty($topic_filter)) {
                    echo "<div class='empty-message'>
                            <h3>Tidak Ada Hasil</h3>
                            <p>Tidak ada course yang sesuai dengan pencarian atau filter Anda. Coba kata kunci lain atau reset filter.</p>
                        </div>";
                } else {
                    echo "<div class='empty-message'>
                            <h3>Maaf!</h3>
                            <p>Kamu sudah mengambil semua course yang tersedia!</p>
                        </div>";
                }
            } else {
                while ($row = $result->fetch_assoc()) {
                    $course_name = $row['course_name'];
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
                    
                    echo "<div class='course-box'>
                            <div class='course-header'>
                                <strong>" . htmlspecialchars($row['course_name']) . "</strong>
                                <span class='topic-badge' style='background-color: $badge_color;'>$topic_badge</span>
                            </div>";
                    
                    if (!empty($row['description'])) {
                        echo "<p style='color: #666; margin: 5px 0;'>" . htmlspecialchars($row['description']) . "</p>";
                    }
                    
                    if (!empty($row['teacher_name'])) {
                        echo "<p style='color: #888; font-size: 14px;'>👨‍🏫 Guru: " . htmlspecialchars($row['teacher_name']) . "</p>";
                    }
                    
                    echo "<br><a href='take_course_process.php?id={$row['course_id']}' class='btn-take-course'>
                            📖 Ambil Course Ini
                        </a>
                        </div>";
                }
            }
            ?>
        </div>
    </main>

    <footer>
        <?php include 'footer.html'; ?>
    </footer>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        if (localStorage.getItem('theme') === 'dark') {
            themeToggle.textContent = '☀️';
        }

        themeToggle.addEventListener('click', () => {
            if (document.body.getAttribute('data-theme') === 'dark') {
                document.body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙';
            } else {
                document.body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️';
            }
        });
    </script>

</body>
</html>
