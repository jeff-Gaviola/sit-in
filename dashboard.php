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

// Fetch announcements
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
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
    --purple: #4B2882;
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
/* NAV */
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
nav ul { list-style: none; display: flex; gap: 1.2rem; align-items: center; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
nav ul li a.logout {
    background: var(--gold);
    color: #000;
    padding: .28rem .9rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: .78rem;
}
nav ul li a.logout:hover { background: #e6b800; }
/* MAIN */
main {
    flex: 1;
    padding: 1.5rem;
    position: relative;
    z-index: 1;
}
/* 3 COLUMN GRID */
.dashboard-grid {
    display: grid;
    grid-template-columns: 280px 1fr 320px;
    gap: 1.2rem;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
}
/* PANELS */
.panel {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.2);
    border-radius: 10px;
    overflow: hidden;
}
.panel-header {
    background: rgba(0,212,255,.12);
    border-bottom: 1px solid rgba(0,212,255,.2);
    padding: .7rem 1rem;
    font-size: .82rem;
    font-weight: 700;
    color: var(--neon);
    text-transform: uppercase;
    letter-spacing: .5px;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.panel-body { padding: 1.2rem 1rem; }

/* STUDENT INFO PANEL */
.student-photo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
    position: relative;
    cursor: pointer;
}
.student-photo {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: rgba(0,212,255,.1);
    border: 2px solid rgba(0,212,255,.4);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,212,255,.2);
}
.student-photo img { width: 100%; height: 100%; object-fit: cover; }
.photo-plus {
    position: absolute;
    bottom: 4px;
    right: calc(50% - 62px);
    width: 26px; height: 26px;
    background: var(--neon);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    color: var(--dark);
    font-weight: 700;
    border: 2px solid var(--dark);
}
#photo-input { display: none; }
.divider { border: none; border-top: 1px solid rgba(0,212,255,.15); margin: .8rem 0; }
.info-row {
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    margin-bottom: .6rem;
    font-size: .83rem;
}
.info-row .icon { color: var(--neon); font-size: .9rem; flex-shrink: 0; margin-top: .1rem; }
.info-row .lbl { color: var(--muted); font-weight: 600; flex-shrink: 0; }
.info-row .val { color: #c8e6f5; }
.session-badge {
    display: inline-block;
    background: rgba(0,212,255,.12);
    border: 1px solid rgba(0,212,255,.3);
    color: var(--neon);
    border-radius: 20px;
    padding: .15rem .7rem;
    font-size: .8rem;
    font-weight: 700;
}

/* ANNOUNCEMENT PANEL */
.announce-item {
    padding: .8rem 0;
    border-bottom: 1px solid rgba(0,212,255,.08);
}
.announce-item:last-child { border-bottom: none; }
.announce-meta {
    font-size: .75rem;
    font-weight: 700;
    color: var(--neon);
    margin-bottom: .4rem;
}
.announce-text {
    font-size: .83rem;
    color: #a0c8e0;
    line-height: 1.6;
    background: rgba(0,212,255,.04);
    border: 1px solid rgba(0,212,255,.1);
    border-radius: 6px;
    padding: .6rem .8rem;
}
.no-announce { font-size: .85rem; color: var(--muted); text-align: center; padding: 1.5rem 0; }

/* RULES PANEL */
.rules-body {
    padding: 1rem;
    height: 500px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(0,212,255,.3) transparent;
}
.rules-body::-webkit-scrollbar { width: 4px; }
.rules-body::-webkit-scrollbar-thumb { background: rgba(0,212,255,.3); border-radius: 4px; }
.rules-title { text-align: center; margin-bottom: 1rem; }
.rules-title h3 { font-size: .95rem; font-weight: 700; color: #c8e6f5; }
.rules-title p  { font-size: .8rem; color: var(--neon); font-weight: 600; margin-top: .2rem; }
.rules-section-title {
    font-size: .82rem;
    font-weight: 700;
    color: #c8e6f5;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 1rem 0 .6rem;
}
.rules-body p {
    font-size: .8rem;
    color: #8ab0c8;
    line-height: 1.7;
    margin-bottom: .6rem;
}

/* BUTTONS */
.btn-group { display: flex; gap: .8rem; margin-top: 1rem; flex-wrap: wrap; }
.btn-edit {
    flex: 1;
    padding: .55rem 1rem;
    background: transparent;
    color: var(--neon);
    border: 1px solid var(--neon);
    border-radius: 7px;
    font-size: .82rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all .2s;
}
.btn-edit:hover { background: rgba(0,212,255,.1); box-shadow: 0 0 15px rgba(0,212,255,.2); }
.btn-logout {
    flex: 1;
    padding: .55rem 1rem;
    background: transparent;
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    border-radius: 7px;
    font-size: .82rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all .2s;
}
.btn-logout:hover { background: rgba(255,107,107,.1); }

@media (max-width: 900px) {
    .dashboard-grid { grid-template-columns: 1fr; }
    .rules-body { height: 300px; }
}
</style>
</head>
<body>

<nav>
    <span class="brand">CCS Sit-in Monitoring System</span>
    <ul>
        <li><a href="#">Notification ▾</a></li>
        <li><a href="dashboard.php" class="active">Home</a></li>
        <li><a href="edit_profile.php">Edit Profile</a></li>
        <li><a href="history.php">History</a></li>
        <li><a href="reservation.php">Reservation</a></li>
        <li><a href="logout.php" class="logout">Log out</a></li>
    </ul>
</nav>

<main>
    <div class="dashboard-grid">

        <!-- COLUMN 1: STUDENT INFO -->
        <div class="panel">
            <div class="panel-header">&#128100; Student Information</div>
            <div class="panel-body">

                <!-- Photo Upload -->
                <div style="display:flex;justify-content:center;">
                    <form action="upload_photo.php" method="POST"
                          enctype="multipart/form-data" id="photo-form">
                        <div class="student-photo-wrap"
                             onclick="document.getElementById('photo-input').click()">
                            <div class="student-photo" id="avatar-circle">
                                <?php if (!empty($user['photo'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($user['photo']) ?>"
                                         id="avatar-img">
                                <?php else: ?>
                                    <svg viewBox="0 0 60 60" width="50"
                                         xmlns="http://www.w3.org/2000/svg" id="avatar-svg">
                                        <circle cx="30" cy="22" r="12" fill="#00d4ff" opacity=".7"/>
                                        <ellipse cx="30" cy="50" rx="18" ry="10"
                                                 fill="#00d4ff" opacity=".5"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="photo-plus" title="Upload photo">+</div>
                        </div>
                        <input type="file" name="photo" id="photo-input" accept="image/*"
                               onchange="previewAndUpload(this)">
                    </form>
                </div>

                <hr class="divider">

                <div class="info-row">
                    <span class="icon">&#128100;</span>
                    <span class="lbl">Name:</span>
                    <span class="val">
                        <?= htmlspecialchars($user['first_name'] . ' ' .
                            ($user['middle_name'] ? $user['middle_name'].' ' : '') .
                            $user['last_name']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="icon">&#127891;</span>
                    <span class="lbl">Course:</span>
                    <span class="val"><?= htmlspecialchars($user['course']) ?></span>
                </div>
                <div class="info-row">
                    <span class="icon">&#8597;</span>
                    <span class="lbl">Year:</span>
                    <span class="val"><?= htmlspecialchars($user['course_level']) ?></span>
                </div>
                <div class="info-row">
                    <span class="icon">&#9993;</span>
                    <span class="lbl">Email:</span>
                    <span class="val"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div class="info-row">
                    <span class="icon">&#128204;</span>
                    <span class="lbl">Address:</span>
                    <span class="val"><?= htmlspecialchars($user['address'] ?: '—') ?></span>
                </div>
                <?php if (!empty($user['contact_number'])): ?>
                <div class="info-row">
                    <span class="icon">&#128222;</span>
                    <span class="lbl">Contact:</span>
                    <span class="val"><?= htmlspecialchars($user['contact_number']) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="icon">&#9201;</span>
                    <span class="lbl">Session:</span>
                    <span class="session-badge">
                        <?= htmlspecialchars($user['remaining_session'] ?? 30) ?>
                    </span>
                </div>

                
            </div>
        </div>

        <!-- COLUMN 2: ANNOUNCEMENTS -->
        <div class="panel">
            <div class="panel-header">&#128226; Announcement</div>
            <div class="panel-body">
                <?php if (empty($announcements)): ?>
                    <p class="no-announce">No announcements yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="announce-item">
                            <div class="announce-meta">
                                CCS Admin | <?= date('Y-M-d', strtotime($ann['created_at'])) ?>
                            </div>
                            <div class="announce-text">
                                <?= htmlspecialchars($ann['content']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLUMN 3: RULES AND REGULATIONS -->
        <div class="panel">
            <div class="panel-header">&#128218; Rules and Regulation</div>
            <div class="rules-body">
                <div class="rules-title">
                    <h3>University of Cebu</h3>
                    <p>COLLEGE OF INFORMATION & COMPUTER STUDIES</p>
                </div>

                <div class="rules-section-title">Laboratory Rules and Regulations</div>

                <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>

                <p>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</p>

                <p>2. Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.</p>

                <p>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>

                <p>4. Getting inside the laboratory requires the student to log in the logbook. No sitting-in without the permission of the instructor.</p>

                <p>5. Students are not allowed to transfer from one laboratory to another without permission.</p>

                <p>6. Deleting and changing of computer settings is strictly prohibited.</p>

                <p>7. Bringing of foods, drinks, and other forms of refreshments inside the laboratory is not allowed.</p>

                <p>8. Students must clean up after using the laboratory. Chair and tables should be arranged properly before leaving.</p>

                <p>9. Students who damage equipment will be held responsible for the repair or replacement of the damaged item.</p>

                <p>10. Only authorized personnel are allowed to use the printer and other peripherals.</p>

                <p>11. Any violation of the rules and regulations will be subject to disciplinary action in accordance with the University's student handbook.</p>
            </div>
        </div>

    </div>
</main>

<script>
function previewAndUpload(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var circle   = document.getElementById('avatar-circle');
            var svg      = document.getElementById('avatar-svg');
            var existing = document.getElementById('avatar-img');
            if (svg) svg.remove();
            if (existing) existing.remove();
            var img  = document.createElement('img');
            img.src  = e.target.result;
            img.id   = 'avatar-img';
            circle.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('photo-form').submit();
    }
}
</script>

</body>
</html>