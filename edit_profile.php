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
        // Check email not taken by another user
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
            // Refresh session
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
    --purple:    #4B2882;
    --purple-dk: #311a5e;
    --gold:      #D4A017;
    --danger:    #c0392b;
    --light:     #f4f1fb;
    --muted:     #888;
    --border:    #ccc;
}
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--light); min-height: 100vh; display: flex; flex-direction: column; }
nav { background: var(--purple); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; height: 48px; box-shadow: 0 2px 8px rgba(0,0,0,.25); position: sticky; top: 0; z-index: 99; }
nav .brand { font-size: .85rem; font-weight: 600; color: #fff; }
nav ul { list-style: none; display: flex; gap: 1.2rem; }
nav ul li a { color: rgba(255,255,255,.82); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--gold); }
main { flex: 1; display: flex; align-items: flex-start; justify-content: center; padding: 2.5rem 1rem; }
.edit-card { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(75,40,130,.18); padding: 2rem 2.5rem; max-width: 560px; width: 100%; animation: fadeUp .4s ease both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.back-btn { display: inline-block; background: var(--danger); color: #fff; font-size: .75rem; font-weight: 600; padding: .3rem .8rem; border-radius: 6px; text-decoration: none; margin-bottom: 1rem; transition: background .2s; }
.back-btn:hover { background: #a93226; }
h2 { font-size: 1.3rem; font-weight: 600; color: var(--purple); text-align: center; margin-bottom: 1.2rem; }
.section-label { font-size: .75rem; font-weight: 700; color: var(--purple); text-transform: uppercase; letter-spacing: .5px; margin: 1.2rem 0 .6rem; border-bottom: 1px solid #eee; padding-bottom: .3rem; }
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: .9rem; }
.alert-err { background: #fde8e8; color: var(--danger); border-left: 3px solid var(--danger); }
.alert-ok  { background: #e8f8f0; color: #1e7e46; border-left: 3px solid #1e7e46; }
.field { margin-bottom: .9rem; }
.field input, .field select { width: 100%; padding: .62rem .9rem; border: 1.5px solid var(--border); border-radius: 8px; font-size: .88rem; font-family: inherit; color: #333; background: #fafafa; transition: border-color .2s, box-shadow .2s; appearance: auto; }
.field input:focus, .field select:focus { outline: none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(75,40,130,.12); background: #fff; }
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.btn-group { display: flex; gap: 1rem; justify-content: center; margin-top: 1.4rem; flex-wrap: wrap; }
.btn-save { padding: .7rem 2rem; background: var(--purple); color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-family: inherit; font-weight: 600; cursor: pointer; transition: background .2s, transform .15s; box-shadow: 0 4px 14px rgba(75,40,130,.28); }
.btn-save:hover { background: var(--purple-dk); transform: translateY(-1px); }
.btn-cancel { padding: .7rem 2rem; background: #eee; color: #555; border: none; border-radius: 8px; font-size: .9rem; font-family: inherit; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: background .2s; }
.btn-cancel:hover { background: #ddd; }
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

            <div class="section-label">Change Password <span style="font-weight:400;color:var(--muted)">(leave blank to keep current)</span></div>

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