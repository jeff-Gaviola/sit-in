<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require 'db.php';

$user    = $_SESSION['user'];
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name    = trim($_POST['last_name']    ?? '');
    $first_name   = trim($_POST['first_name']   ?? '');
    $middle_name  = trim($_POST['middle_name']  ?? '');
    $course       = trim($_POST['course']       ?? '');
    $course_level = trim($_POST['course_level'] ?? '1');
    $email        = trim($_POST['email']        ?? '');
    $address      = trim($_POST['address']      ?? '');
    $new_password = $_POST['new_password']      ?? '';
    $confirm_pw   = $_POST['confirm_password']  ?? '';

    if (empty($last_name) || empty($first_name) || empty($email) || empty($course)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif (!empty($new_password) && $new_password !== $confirm_pw) {
        $error = 'New passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $error = 'That email is already used by another account.';
        } else {
            if (!empty($new_password)) {
                $stmt = $pdo->prepare("UPDATE students SET last_name=?, first_name=?, middle_name=?, course=?, course_level=?, email=?, address=?, password=? WHERE id=?");
                $stmt->execute([$last_name, $first_name, $middle_name, $course, $course_level, $email, $address, password_hash($new_password, PASSWORD_DEFAULT), $user['id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE students SET last_name=?, first_name=?, middle_name=?, course=?, course_level=?, email=?, address=? WHERE id=?");
                $stmt->execute([$last_name, $first_name, $middle_name, $course, $course_level, $email, $address, $user['id']]);
            }
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$user['id']]);
            $_SESSION['user'] = $stmt->fetch();
            $user = $_SESSION['user'];
            $success = 'Profile updated successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS – Edit Profile</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --neon:   #00d4ff;
    --dark:   #020b18;
    --dark2:  #051528;
    --muted:  #7a9bb5;
    --border: #0d3a5c;
    --danger: #c0392b;
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
    align-items: flex-start;
    justify-content: center;
    padding: 2.5rem 1rem;
    position: relative;
    z-index: 1;
}
.edit-card {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.25);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    max-width: 560px;
    width: 100%;
    position: relative;
    animation: fadeUp .4s ease both;
    box-shadow: 0 0 40px rgba(0,212,255,.08);
}
.edit-card::before,
.edit-card::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    border-color: var(--neon);
    border-style: solid;
    opacity: .7;
}
.edit-card::before { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
.edit-card::after  { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.back-btn {
    display: inline-block;
    background: transparent;
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    font-size: .75rem;
    font-weight: 600;
    padding: .3rem .8rem;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 1rem;
    transition: all .2s;
    letter-spacing: 1px;
}
.back-btn:hover { background: rgba(255,107,107,.1); box-shadow: 0 0 10px rgba(255,107,107,.3); }
h2 {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--neon);
    text-align: center;
    margin-bottom: 1.2rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-shadow: 0 0 20px rgba(0,212,255,.5);
}
.section-label {
    font-size: .72rem;
    font-weight: 700;
    color: var(--neon);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 1.2rem 0 .6rem;
    border-bottom: 1px solid rgba(0,212,255,.15);
    padding-bottom: .3rem;
    opacity: .8;
}
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: .9rem; }
.alert-err { background: rgba(192,57,43,.15); color: #ff6b6b; border-left: 3px solid #ff6b6b; }
.alert-ok  { background: rgba(0,212,255,.08); color: var(--neon); border-left: 3px solid var(--neon); }
.field { margin-bottom: .9rem; }
.field input,
.field select {
    width: 100%;
    padding: .62rem .9rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    color: #c8e6f5;
    background: rgba(0,20,40,.6);
    transition: border-color .2s, box-shadow .2s;
    appearance: auto;
}
.field input::placeholder { color: rgba(120,180,210,.4); }
.field input:disabled { opacity: .4; cursor: not-allowed; }
.field select option { background: #051528; color: #c8e6f5; }
.field input:focus,
.field select:focus {
    outline: none;
    border-color: var(--neon);
    box-shadow: 0 0 0 3px rgba(0,212,255,.1), 0 0 20px rgba(0,212,255,.1);
    background: rgba(0,25,50,.8);
}
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.btn-group { display: flex; gap: 1rem; justify-content: center; margin-top: 1.4rem; flex-wrap: wrap; }
.btn-save {
    padding: .7rem 2rem;
    background: transparent;
    color: var(--neon);
    border: 1px solid var(--neon);
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all .2s;
}
.btn-save:hover { background: rgba(0,212,255,.1); box-shadow: 0 0 20px rgba(0,212,255,.3); color: #fff; }
.btn-cancel {
    padding: .7rem 2rem;
    background: transparent;
    color: var(--muted);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all .2s;
}
.btn-cancel:hover { border-color: var(--muted); color: #fff; }
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Sit-in Monitoring System</span>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="edit_profile.php" class="active">Edit Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<main>
    <div class="edit-card">
        <a href="dashboard.php" class="back-btn">&#8592; Back</a>
        <h2>Edit Profile</h2>

        <?php if ($error): ?>
            <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-ok"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_profile.php">
            <div class="section-label">Personal Information</div>
            <div class="field">
                <input type="text" value="<?= htmlspecialchars($user['id_number']) ?>" disabled>
                <label>ID Number (cannot be changed)</label>
            </div>
            <div class="field">
                <input type="text" name="last_name" placeholder="Last Name"
                       value="<?= htmlspecialchars($_POST['last_name'] ?? $user['last_name']) ?>" required>
                <label>Last Name *</label>
            </div>
            <div class="field">
                <input type="text" name="first_name" placeholder="First Name"
                       value="<?= htmlspecialchars($_POST['first_name'] ?? $user['first_name']) ?>" required>
                <label>First Name *</label>
            </div>
            <div class="field">
                <input type="text" name="middle_name" placeholder="Middle Name"
                       value="<?= htmlspecialchars($_POST['middle_name'] ?? $user['middle_name']) ?>">
                <label>Middle Name</label>
            </div>
            <div class="field">
                <select name="course">
                    <?php
                    $courses    = ['BSIT', 'BSCS', 'BSEMC'];
                    $sel_course = $_POST['course'] ?? $user['course'];
                    foreach ($courses as $c):
                    ?>
                    <option value="<?= $c ?>" <?= $sel_course == $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Course *</label>
            </div>
            <div class="field">
                <select name="course_level">
                    <?php
                    $levels = ['1'=>'1st Year','2'=>'2nd Year','3'=>'3rd Year','4'=>'4th Year'];
                    $sel    = $_POST['course_level'] ?? $user['course_level'];
                    foreach ($levels as $val => $label):
                    ?>
                    <option value="<?= $val ?>" <?= $sel == $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Course Level *</label>
            </div>
            <div class="field">
                <input type="email" name="email" placeholder="Email"
                       value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
                <label>Email *</label>
            </div>
            <div class="field">
                <input type="text" name="address" placeholder="Home address"
                       value="<?= htmlspecialchars($_POST['address'] ?? $user['address']) ?>">
                <label>Address</label>
            </div>

            <div class="section-label">Change Password <span style="font-weight:400;opacity:.6">(leave blank to keep current)</span></div>
            <div class="field">
                <input type="password" name="new_password" placeholder="New password (min 6 chars)">
                <label>New Password</label>
            </div>
            <div class="field">
                <input type="password" name="confirm_password" placeholder="Confirm new password">
                <label>Confirm New Password</label>
            </div>

            <div class="btn-group">
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>