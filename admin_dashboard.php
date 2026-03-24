<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }
require 'db.php';

// Post announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement'])) {
    $content = trim($_POST['announcement']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO announcements (content) VALUES (?)");
        $stmt->execute([$content]);
    }
    header('Location: admin_dashboard.php');
    exit;
}

// Stats
$total_students   = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$currently_sitin  = $pdo->query("SELECT COUNT(*) FROM sitin_records WHERE status='Active'")->fetchColumn();
$total_sitin      = $pdo->query("SELECT COUNT(*) FROM sitin_records")->fetchColumn();
$announcements    = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

// Course counts for chart
$course_counts = $pdo->query("SELECT course, COUNT(*) as cnt FROM students GROUP BY course")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Admin Dashboard</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root { --neon: #00d4ff; --dark: #020b18; --muted: #7a9bb5; --border: #0d3a5c; --danger: #c0392b; --gold: #D4A017; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--dark); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }
body::before { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(0,212,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,212,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 0; pointer-events: none; }
nav { background: rgba(2,11,24,.92); border-bottom: 1px solid rgba(0,212,255,.2); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; height: 48px; position: sticky; top: 0; z-index: 99; backdrop-filter: blur(8px); }
nav .brand { font-size: .85rem; font-weight: 600; color: var(--neon); letter-spacing: .5px; }
nav ul { list-style: none; display: flex; gap: 1rem; flex-wrap: wrap; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .78rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
nav ul li a.logout { color: var(--gold); border: 1px solid var(--gold); padding: .25rem .7rem; border-radius: 6px; }
nav ul li a.logout:hover { background: rgba(212,160,23,.15); }
main { flex: 1; padding: 2rem 1.5rem; position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; width: 100%; }
.grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.panel { background: rgba(5,21,40,.85); border: 1px solid rgba(0,212,255,.2); border-radius: 12px; padding: 1.5rem; position: relative; }
.panel-title { font-size: .8rem; font-weight: 700; color: var(--neon); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
.stat { font-size: .9rem; color: #c8e6f5; margin-bottom: .4rem; }
.stat strong { color: var(--neon); }
.announce-form textarea { width: 100%; padding: .6rem .9rem; border: 1px solid var(--border); border-radius: 8px; font-size: .85rem; font-family: inherit; color: #c8e6f5; background: rgba(0,20,40,.6); resize: vertical; min-height: 70px; }
.announce-form textarea:focus { outline: none; border-color: var(--neon); }
.btn-submit { margin-top: .5rem; padding: .5rem 1.4rem; background: rgba(0,212,255,.15); color: var(--neon); border: 1px solid var(--neon); border-radius: 6px; font-size: .82rem; font-family: inherit; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-submit:hover { background: rgba(0,212,255,.25); }
.posted-title { font-size: 1rem; font-weight: 700; color: #c8e6f5; margin: 1rem 0 .5rem; }
.announce-item { border-top: 1px solid rgba(0,212,255,.1); padding: .6rem 0; }
.announce-meta { font-size: .75rem; color: var(--muted); margin-bottom: .3rem; }
.announce-text { font-size: .85rem; color: #a0c8e0; }
canvas { max-width: 100%; }
@media (max-width: 640px) { .grid2 { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Admin</span>
    <ul>
        <li><a href="admin_dashboard.php" class="active">Home</a></li>
        <li><a href="admin_search.php">Search</a></li>
        <li><a href="admin_students.php">Students</a></li>
        <li><a href="admin_sitin.php">Sit-in</a></li>
        <li><a href="admin_sitin_records.php">View Sit-in Records</a></li>
        <li><a href="admin_logout.php" class="logout">Log out</a></li>
    </ul>
</nav>
<main>
    <div class="grid2">
        <!-- Statistics -->
        <div class="panel">
            <div class="panel-title">&#128200; Statistics</div>
            <p class="stat"><strong>Students Registered:</strong> <?= $total_students ?></p>
            <p class="stat"><strong>Currently Sit-in:</strong> <?= $currently_sitin ?></p>
            <p class="stat"><strong>Total Sit-in:</strong> <?= $total_sitin ?></p>
            <canvas id="courseChart" height="200"></canvas>
        </div>
        <!-- Announcements -->
        <div class="panel">
            <div class="panel-title">&#128226; Announcement</div>
            <form method="POST" class="announce-form">
                <textarea name="announcement" placeholder="New Announcement"></textarea>
                <button type="submit" class="btn-submit">Submit</button>
            </form>
            <p class="posted-title">Posted Announcement</p>
            <?php foreach ($announcements as $ann): ?>
                <div class="announce-item">
                    <div class="announce-meta">CCS Admin | <?= date('Y-M-d', strtotime($ann['created_at'])) ?></div>
                    <div class="announce-text"><?= htmlspecialchars($ann['content']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
var labels = <?= json_encode(array_column($course_counts, 'course')) ?>;
var data   = <?= json_encode(array_column($course_counts, 'cnt')) ?>;
new Chart(document.getElementById('courseChart'), {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: ['#00d4ff','#4B2882','#D4A017','#c0392b','#2ecc71'],
            borderWidth: 1,
            borderColor: '#020b18'
        }]
    },
    options: { plugins: { legend: { labels: { color: '#c8e6f5', font: { size: 11 } } } } }
});
</script>
</body>
</html>