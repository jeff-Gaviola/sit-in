<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require 'db.php';

$user    = $_SESSION['user'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve'])) {
    $purpose  = trim($_POST['purpose'] ?? '');
    $lab      = trim($_POST['lab']     ?? '');
    $time_in  = trim($_POST['time_in'] ?? '');
    $date     = trim($_POST['date']    ?? '');

    if (empty($purpose) || empty($lab) || empty($time_in) || empty($date)) {
        $error = 'Please fill in all fields.';
    } elseif (($user['remaining_session'] ?? 30) <= 0) {
        $error = 'You have no remaining sessions.';
    } else {
        $name = $user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'].' ' : '') . $user['last_name'];
        $stmt = $pdo->prepare("INSERT INTO sitin_records (student_id, id_number, name, purpose, lab, session, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $user['id'],
            $user['id_number'],
            $name,
            $purpose,
            $lab,
            $user['remaining_session'] ?? 30,
            'Active'
        ]);
        // Deduct session
        $pdo->prepare("UPDATE students SET remaining_session = remaining_session - 1 WHERE id = ?")->execute([$user['id']]);
        // Refresh session
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$user['id']]);
        $_SESSION['user'] = $stmt->fetch();
        $user = $_SESSION['user'];
        $success = 'Reservation submitted successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS – Reservation</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --neon:   #00d4ff;
    --dark:   #020b18;
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
nav ul { list-style: none; display: flex; gap: 1.2rem; align-items: center; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
nav ul li a.logout { background: var(--gold); color: #000; padding: .28rem .9rem; border-radius: 6px; font-weight: 700; font-size: .78rem; }
nav ul li a.logout:hover { background: #e6b800; }
main {
    flex: 1;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 2.5rem 1rem;
    position: relative;
    z-index: 1;
}
.card {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.25);
    border-radius: 16px;
    padding: 2.5rem 3rem;
    max-width: 700px;
    width: 100%;
    position: relative;
    animation: fadeUp .4s ease both;
    box-shadow: 0 0 40px rgba(0,212,255,.08);
}
.card::before { content: ''; position: absolute; top: 12px; left: 12px; width: 20px; height: 20px; border: 2px solid var(--neon); border-right: 0; border-bottom: 0; opacity: .7; }
.card::after  { content: ''; position: absolute; bottom: 12px; right: 12px; width: 20px; height: 20px; border: 2px solid var(--neon); border-left: 0; border-top: 0; opacity: .7; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--neon);
    text-align: center;
    margin-bottom: 2rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-shadow: 0 0 20px rgba(0,212,255,.5);
}
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: 1rem; }
.alert-err { background: rgba(192,57,43,.15); color: #ff6b6b; border-left: 3px solid #ff6b6b; }
.alert-ok  { background: rgba(0,212,255,.08); color: var(--neon); border-left: 3px solid var(--neon); }
/* FORM ROWS */
.form-row {
    display: grid;
    grid-template-columns: 180px 1fr;
    align-items: center;
    gap: .6rem;
    margin-bottom: 1.2rem;
}
.form-row label {
    font-size: .88rem;
    color: #c8e6f5;
    font-weight: 500;
}
.form-row input,
.form-row select {
    width: 100%;
    padding: .65rem .9rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    color: #c8e6f5;
    background: rgba(0,20,40,.6);
    transition: border-color .2s, box-shadow .2s;
    appearance: auto;
}
.form-row input::placeholder { color: rgba(120,180,210,.4); }
.form-row input:disabled { opacity: .55; cursor: not-allowed; }
.form-row input:focus,
.form-row select:focus {
    outline: none;
    border-color: var(--neon);
    box-shadow: 0 0 0 3px rgba(0,212,255,.1);
    background: rgba(0,25,50,.8);
}
.form-row select option { background: #051528; color: #c8e6f5; }
/* DIVIDER */
.section-div {
    border: none;
    border-top: 1px solid rgba(0,212,255,.12);
    margin: 1.4rem 0;
}
/* BUTTONS */
.btn-submit {
    padding: .6rem 2rem;
    background: rgba(0,100,255,.3);
    color: #fff;
    border: 1px solid rgba(0,150,255,.5);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    margin-left: 180px;
    margin-bottom: 1.2rem;
}
.btn-submit:hover { background: rgba(0,100,255,.5); }
.btn-reserve {
    padding: .6rem 2rem;
    background: rgba(0,100,255,.3);
    color: #fff;
    border: 1px solid rgba(0,150,255,.5);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.btn-reserve:hover { background: rgba(0,100,255,.5); }
@media (max-width: 560px) {
    .form-row { grid-template-columns: 1fr; }
    .btn-submit { margin-left: 0; }
    .card { padding: 2rem 1.2rem; }
}
</style>
</head>
<body>

<nav>
    <span class="brand">CCS Sit-in Monitoring System</span>
    <ul>
        <li><a href="#">Notification ▾</a></li>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="edit_profile.php">Edit Profile</a></li>
        <li><a href="#">History</a></li>
        <li><a href="reservation.php" class="active">Reservation</a></li>
        <li><a href="logout.php" class="logout">Log out</a></li>
    </ul>
</nav>

<main>
    <div class="card">
        <h1>Reservation</h1>

        <?php if ($error): ?>
            <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-ok"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="reservation.php">

            <!-- ID Number -->
            <div class="form-row">
                <label>ID Number:</label>
                <input type="text"
                       value="<?= htmlspecialchars($user['id_number']) ?>"
                       disabled>
            </div>

            <!-- Student Name -->
            <div class="form-row">
                <label>Student Name:</label>
                <input type="text"
                       value="<?= htmlspecialchars($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'].' ' : '') . $user['last_name']) ?>"
                       disabled>
            </div>

            <!-- Purpose -->
            <div class="form-row">
                <label>Purpose:</label>
                <select name="purpose">
                    <option value="C Programming">C Programming</option>
                    <option value="Java">Java</option>
                    <option value="C#">C#</option>
                    <option value="ASP.Net">ASP.Net</option>
                    <option value="PHP">PHP</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Lab -->
            <div class="form-row">
                <label>Lab:</label>
                <input type="text" name="lab" placeholder="e.g. 524">
            </div>

            <!-- Submit Button -->
            <button type="submit" name="submit_info" class="btn-submit">Submit</button>

            <hr class="section-div">

            <!-- Time In -->
            <div class="form-row">
                <label>Time In:</label>
                <input type="time" name="time_in">
            </div>

            <!-- Date -->
            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="date">
            </div>

            <!-- Remaining Session -->
            <div class="form-row">
                <label>Remaining Session:</label>
                <input type="text"
                       value="<?= htmlspecialchars($user['remaining_session'] ?? 30) ?>"
                       disabled>
            </div>

            <!-- Reserve Button -->
            <div style="margin-top:.5rem;">
                <button type="submit" name="reserve" class="btn-reserve">Reserve</button>
            </div>

        </form>
    </div>
</main>

</body>
</html>