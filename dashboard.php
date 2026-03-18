<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
// Refresh user data from DB
require 'db.php';
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;

$yr_map = ['1'=>'1st Year','2'=>'2nd Year','3'=>'3rd Year','4'=>'4th Year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Sit-in Monitoring – Dashboard</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --purple:    #4B2882;
    --purple-dk: #311a5e;
    --gold:      #D4A017;
    --danger:    #c0392b;
    --light:     #f4f1fb;
    --muted:     #888;
}
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--light); min-height: 100vh; display: flex; flex-direction: column; }
nav { background: var(--purple); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; height: 48px; box-shadow: 0 2px 8px rgba(0,0,0,.25); position: sticky; top: 0; z-index: 99; }
nav .brand { font-size: .85rem; font-weight: 600; color: #fff; }
nav ul { list-style: none; display: flex; gap: 1.2rem; }
nav ul li a { color: rgba(255,255,255,.82); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover { color: var(--gold); }
main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2.5rem 1rem; }
.dash-card { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(75,40,130,.18); padding: 2.5rem 3rem; max-width: 680px; width: 100%; text-align: center; animation: fadeUp .4s ease both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.avatar { width: 70px; height: 70px; background: var(--purple); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; }
h2 { font-size: 1.4rem; font-weight: 600; color: var(--purple); margin-bottom: .4rem; }
.sub { font-size: .85rem; color: var(--muted); margin-bottom: 1.5rem; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem 1.5rem; text-align: left; margin-bottom: 2rem; border-top: 1px solid #eee; padding-top: 1.2rem; }
.info-item label { font-size: .72rem; color: var(--muted); display: block; }
.info-item span { font-size: .92rem; font-weight: 600; color: #333; }
.btn-group { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.btn-edit { display: inline-block; padding: .65rem 2rem; background: var(--purple); color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-family: inherit; font-weight: 600; cursor: pointer; text-decoration: none; transition: background .2s, transform .15s; box-shadow: 0 4px 12px rgba(75,40,130,.28); }
.btn-edit:hover { background: var(--purple-dk); transform: translateY(-1px); }
.btn-logout { display: inline-block; padding: .65rem 2rem; background: var(--danger); color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-family: inherit; font-weight: 600; cursor: pointer; text-decoration: none; transition: background .2s, transform .15s; box-shadow: 0 4px 12px rgba(192,57,43,.28); }
.btn-logout:hover { background: #a93226; transform: translateY(-1px); }
@media (max-width: 500px) { .info-grid { grid-template-columns: 1fr; } .dash-card { padding: 2rem 1.4rem; } }
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Sit-in Monitoring System</span>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="edit_profile.php">Edit Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<main>
    <div class="dash-card">
        <div class="avatar">
            <svg viewBox="0 0 60 60" width="48" xmlns="http://www.w3.org/2000/svg">
                <circle cx="30" cy="22" r="12" fill="#fff"/>
                <ellipse cx="30" cy="50" rx="18" ry="10" fill="#fff"/>
            </svg>
        </div>
        <h2>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h2>
        <p class="sub">You are successfully logged in to the CCS Sit-in Monitoring System.</p>
        <div class="info-grid">
            <div class="info-item">
                <label>ID Number</label>
                <span><?= htmlspecialchars($user['id_number']) ?></span>
            </div>
            <div class="info-item">
                <label>Full Name</label>
                <span><?= htmlspecialchars($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'][0].'. ' : '') . $user['last_name']) ?></span>
            </div>
            <div class="info-item">
                <label>Course</label>
                <span><?= htmlspecialchars($user['course']) ?></span>
            </div>
            <div class="info-item">
                <label>Year Level</label>
                <span><?= htmlspecialchars($yr_map[$user['course_level']] ?? $user['course_level']) ?></span>
            </div>
            <div class="info-item">
                <label>Email</label>
                <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info-item">
                <label>Address</label>
                <span><?= htmlspecialchars($user['address'] ?: '—') ?></span>
            </div>
        </div>
        <div class="btn-group">
            <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</main>
</body>
</html>