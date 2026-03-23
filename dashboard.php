<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
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
    --neon:   #00d4ff;
    --dark:   #020b18;
    --dark2:  #051528;
    --muted:  #7a9bb5;
    --border: #0d3a5c;
    --danger: #c0392b;
    --gold:   #D4A017;
}
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--dark);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}
body::before {
    content: '';
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-image:
        linear-gradient(rgba(0,212,255,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,212,255,.04) 1px, transparent 1px);
    background-size: 40px 40px;
    z-index: 0;
    pointer-events: none;
}
nav {
    background: rgba(2,11,24,.92);
    border-bottom: 1px solid rgba(0,212,255,.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    height: 48px;
    position: sticky;
    top: 0;
    z-index: 99;
    backdrop-filter: blur(8px);
}
nav .brand { font-size: .85rem; font-weight: 600; color: var(--neon); letter-spacing: .5px; }
nav ul { list-style: none; display: flex; gap: 1.2rem; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    position: relative;
    z-index: 1;
}
.dash-card {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.25);
    border-radius: 16px;
    padding: 2.5rem 3rem;
    max-width: 680px;
    width: 100%;
    text-align: center;
    position: relative;
    animation: fadeUp .4s ease both;
    box-shadow: 0 0 40px rgba(0,212,255,.08);
}
.dash-card::before,
.dash-card::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    border-color: var(--neon);
    border-style: solid;
    opacity: .7;
}
.dash-card::before { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
.dash-card::after  { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.avatar-wrapper {
    position: relative;
    width: 90px;
    height: 90px;
    cursor: pointer;
}
.avatar {
    width: 90px; height: 90px;
    background: rgba(0,212,255,.1);
    border: 2px solid rgba(0,212,255,.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px rgba(0,212,255,.2);
    overflow: hidden;
}
.avatar img {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 50%;
}
.avatar-overlay {
    position: absolute;
    bottom: 0; right: 0;
    width: 28px; height: 28px;
    background: var(--neon);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .75rem;
    color: var(--dark);
    font-weight: 700;
    border: 2px solid var(--dark);
    cursor: pointer;
    transition: transform .2s;
}
.avatar-overlay:hover { transform: scale(1.1); }
#photo-input { display: none; }
h2 {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--neon);
    margin-bottom: .4rem;
    letter-spacing: 1px;
    text-shadow: 0 0 20px rgba(0,212,255,.5);
}
.sub { font-size: .85rem; color: var(--muted); margin-bottom: 1.5rem; }
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .6rem 1.5rem;
    text-align: left;
    margin-bottom: 2rem;
    border-top: 1px solid rgba(0,212,255,.15);
    padding-top: 1.2rem;
}
.info-item label { font-size: .72rem; color: var(--muted); display: block; text-transform: uppercase; letter-spacing: .5px; }
.info-item span  { font-size: .92rem; font-weight: 600; color: #c8e6f5; }
.btn-group { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.btn-edit {
    display: inline-block;
    padding: .65rem 2rem;
    background: transparent;
    color: var(--neon);
    border: 1px solid var(--neon);
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
}
.btn-edit:hover { background: rgba(0,212,255,.1); box-shadow: 0 0 20px rgba(0,212,255,.3); color: #fff; }
.btn-logout {
    display: inline-block;
    padding: .65rem 2rem;
    background: transparent;
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
}
.btn-logout:hover { background: rgba(255,107,107,.1); box-shadow: 0 0 20px rgba(255,107,107,.2); }
@media (max-width: 500px) {
    .info-grid { grid-template-columns: 1fr; }
    .dash-card { padding: 2rem 1.4rem; }
}
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Sit-in Monitoring System</span>
    <ul>
        <li><a href="dashboard.php" class="active">Dashboard</a></li>
        <li><a href="edit_profile.php">Edit Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<main>
    <div class="dash-card">

        <!-- PHOTO UPLOAD -->
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; margin-bottom:1rem;">
            <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="photo-form" style="display:flex; justify-content:center;">
                <div class="avatar-wrapper" onclick="document.getElementById('photo-input').click()">
                <div class="avatar" id="avatar-circle">
                    <?php if (!empty($user['photo'])): ?>
                     <img src="uploads/<?= htmlspecialchars($user['photo']) ?>" id="avatar-img">
                     <?php else: ?>
                         <svg viewBox="0 0 60 60" width="40" xmlns="http://www.w3.org/2000/svg" id="avatar-svg">
            <circle cx="30" cy="22" r="12" fill="#00d4ff" opacity=".7"/>
            <ellipse cx="30" cy="50" rx="18" ry="10" fill="#00d4ff" opacity=".5"/>
                        </svg>
                     <?php endif; ?>
                    </div>
                    <div class="avatar-overlay" title="Upload photo">+</div>
                </div>
                <input type="file" name="photo" id="photo-input" accept="image/*"
                       onchange="previewAndUpload(this)">
            </form>
        </div>

        <!-- WELCOME -->
        <h2>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h2>
        <p class="sub">You are successfully logged in to the CCS Sit-in Monitoring System.</p>

        <!-- INFO GRID -->
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
            <div class="info-item">
                <label>Contact Number</label>
                <span><?= htmlspecialchars($user['contact_number'] ?: '—') ?></span>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="btn-group">
            <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

    </div>
</main>

<script>
function previewAndUpload(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var circle = document.getElementById('avatar-circle');
            var svg = document.getElementById('avatar-svg');
            var existing = document.getElementById('avatar-img');
            if (svg) svg.remove();
            if (existing) existing.remove();
            var img = document.createElement('img');
            img.src = e.target.result;
            img.id = 'avatar-img';
            circle.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('photo-form').submit();
    }
}
</script>

</body>
</html>