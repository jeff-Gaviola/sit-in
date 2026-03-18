<?php
session_start();
require 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number       = trim($_POST['id_number']    ?? '');
    $last_name       = trim($_POST['last_name']     ?? '');
    $first_name      = trim($_POST['first_name']    ?? '');
    $middle_name     = trim($_POST['middle_name']   ?? '');
    $course          = trim($_POST['course']        ?? '');
    $course_level    = trim($_POST['course_level']  ?? '1');
    $password        = $_POST['password']           ?? '';
    $repeat_password = $_POST['repeat_password']    ?? '';
    $email           = trim($_POST['email']         ?? '');
    $address         = trim($_POST['address']       ?? '');

    if (empty($id_number) || empty($last_name) || empty($first_name) ||
        empty($password)  || empty($email)     || empty($course)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $repeat_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE id_number = ? OR email = ?");
        $stmt->execute([$id_number, $email]);
        if ($stmt->fetch()) {
            $error = 'ID Number or Email is already registered.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO students
                (id_number, last_name, first_name, middle_name, course, course_level, password, email, address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_number, $last_name, $first_name, $middle_name,
                $course, $course_level,
                password_hash($password, PASSWORD_DEFAULT),
                $email, $address
            ]);
            $success = 'Registration successful! You can now log in.';
            $_POST   = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Sit-in Monitoring System – Register</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --purple:    #4B2882;
    --gold:      #D4A017;
    --danger:    #c0392b;
    --neon:      #00d4ff;
    --dark:      #020b18;
    --dark2:     #051528;
    --dark3:     #0a1f35;
    --muted:     #7a9bb5;
    --border:    #0d3a5c;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
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
.register-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 2rem;
    max-width: 820px;
    width: 100%;
    animation: fadeUp .4s ease both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.reg-form-col {
    flex: 1;
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.25);
    border-radius: 16px;
    padding: 2rem 2.2rem;
    position: relative;
    box-shadow: 0 0 40px rgba(0,212,255,.08);
}
.reg-form-col::before,
.reg-form-col::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    border-color: var(--neon);
    border-style: solid;
    opacity: .7;
}
.reg-form-col::before { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
.reg-form-col::after  { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }
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
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: .9rem; }
.alert-err { background: rgba(192,57,43,.15); color: #ff6b6b; border-left: 3px solid #ff6b6b; }
.alert-ok  { background: rgba(0,212,255,.08); color: var(--neon); border-left: 3px solid var(--neon); }
.alert-ok a { color: var(--neon); font-weight: 700; margin-left: .4rem; }
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
.field select option { background: #051528; color: #c8e6f5; }
.field input:focus,
.field select:focus {
    outline: none;
    border-color: var(--neon);
    box-shadow: 0 0 0 3px rgba(0,212,255,.1), 0 0 20px rgba(0,212,255,.1);
    background: rgba(0,25,50,.8);
}
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.btn-register {
    display: block;
    width: 160px;
    margin: 1.2rem auto 0;
    padding: .7rem;
    background: transparent;
    color: var(--neon);
    border: 1px solid var(--neon);
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    text-align: center;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}
.btn-register::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0,212,255,.15), transparent);
    transition: left .4s;
}
.btn-register:hover::before { left: 100%; }
.btn-register:hover { background: rgba(0,212,255,.1); box-shadow: 0 0 20px rgba(0,212,255,.3); color: #fff; }
.reg-illus {
    width: 200px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding-top: 3.5rem;
}
.mock-card {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.25);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    width: 100%;
    box-shadow: 0 0 20px rgba(0,212,255,.08);
}
.mock-avatar {
    width: 40px; height: 40px;
    background: rgba(0,212,255,.15);
    border: 1px solid var(--neon);
    border-radius: 50%;
    margin: 0 auto .5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--neon);
    font-size: 1rem;
    box-shadow: 0 0 10px rgba(0,212,255,.3);
}
.mock-dots { color: rgba(0,212,255,.4); font-size: 1rem; letter-spacing: 3px; margin-bottom: .3rem; }
.mock-su {
    background: transparent;
    color: var(--neon);
    border: 1px solid var(--neon);
    border-radius: 6px;
    padding: .25rem .7rem;
    font-size: .72rem;
    font-weight: 600;
    cursor: default;
    letter-spacing: 1px;
}
@media (max-width: 640px) {
    .register-wrapper { flex-direction: column; }
    .reg-illus { width: 100%; padding-top: 0; }
}
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Sit-in Monitoring System</span>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="#">Community ▾</a></li>
        <li><a href="#">About</a></li>
        <li><a href="index.php">Login</a></li>
        <li><a href="register.php" class="active">Register</a></li>
    </ul>
</nav>
<main>
    <div class="register-wrapper">
        <div class="reg-form-col">
            <a href="index.php" class="back-btn">&#8592; Back</a>
            <h2>Sign Up</h2>
            <?php if ($error): ?>
                <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-ok">
                    <?= htmlspecialchars($success) ?>
                    <a href="index.php">Go to Login &#8594;</a>
                </div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <div class="field">
                    <input type="text" name="id_number" placeholder="e.g. 2024-00001"
                           value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required>
                    <label>ID Number *</label>
                </div>
                <div class="field">
                    <input type="text" name="last_name" placeholder="Last Name"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    <label>Last Name *</label>
                </div>
                <div class="field">
                    <input type="text" name="first_name" placeholder="First Name"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    <label>First Name *</label>
                </div>
                <div class="field">
                    <input type="text" name="middle_name" placeholder="Middle Name (optional)"
                           value="<?= htmlspecialchars($_POST['middle_name'] ?? '') ?>">
                    <label>Middle Name</label>
                </div>
                <div class="field">
                    <select name="course">
                        <?php
                        $courses    = ['BSIT', 'BSCS', 'BSEMC'];
                        $sel_course = $_POST['course'] ?? 'BSIT';
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
                        $sel    = $_POST['course_level'] ?? '1';
                        foreach ($levels as $val => $label):
                        ?>
                        <option value="<?= $val ?>" <?= $sel == $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Course Level *</label>
                </div>
                <div class="field">
                    <input type="password" name="password" placeholder="Password (min 6 chars)" required>
                    <label>Password *</label>
                </div>
                <div class="field">
                    <input type="password" name="repeat_password" placeholder="Repeat your password" required>
                    <label>Repeat your password *</label>
                </div>
                <div class="field">
                    <input type="email" name="email" placeholder="Email address"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <label>Email *</label>
                </div>
                <div class="field">
                    <input type="text" name="address" placeholder="Home address"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                    <label>Address</label>
                </div>
                <button type="submit" class="btn-register">Register</button>
            </form>
        </div>
        <div class="reg-illus">
            <svg viewBox="0 0 200 230" width="190" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="80" cy="210" rx="35" ry="10" fill="#00d4ff" opacity=".1"/>
                <rect x="65" y="162" width="13" height="46" rx="6" fill="#0d3a5c"/>
                <rect x="83" y="167" width="13" height="41" rx="6" fill="#0d3a5c"/>
                <ellipse cx="72" cy="208" rx="13" ry="5" fill="#00d4ff" opacity=".3"/>
                <ellipse cx="90" cy="208" rx="10" ry="4" fill="#00d4ff" opacity=".3"/>
                <rect x="54" y="108" width="52" height="60" rx="13" fill="#051528" stroke="#00d4ff" stroke-width="1" stroke-opacity=".4"/>
                <rect x="33" y="113" width="23" height="11" rx="5" fill="#051528" stroke="#00d4ff" stroke-width="1" stroke-opacity=".4" transform="rotate(12 33 113)"/>
                <rect x="102" y="116" width="26" height="11" rx="5" fill="#051528" stroke="#00d4ff" stroke-width="1" stroke-opacity=".4" transform="rotate(-10 102 116)"/>
                <rect x="122" y="96" width="58" height="72" rx="8" fill="#051528" stroke="#00d4ff" stroke-width="1.5" stroke-opacity=".6"/>
                <rect x="127" y="105" width="48" height="7" rx="3" fill="#00d4ff" opacity=".12"/>
                <rect x="127" y="118" width="48" height="5" rx="2.5" fill="#00d4ff" opacity=".09"/>
                <rect x="127" y="129" width="48" height="5" rx="2.5" fill="#00d4ff" opacity=".09"/>
                <rect x="127" y="140" width="48" height="5" rx="2.5" fill="#00d4ff" opacity=".09"/>
                <rect x="132" y="153" width="36" height="11" rx="4" fill="none" stroke="#00d4ff" stroke-width="1"/>
                <text x="150" y="161.5" font-size="5.5" fill="#00d4ff" text-anchor="middle" font-family="sans-serif" font-weight="700">Sign Up</text>
                <circle cx="150" cy="108" r="9" fill="#00d4ff" opacity=".1" stroke="#00d4ff" stroke-width="1" stroke-opacity=".4"/>
                <circle cx="150" cy="105.5" r="3.5" fill="#00d4ff" opacity=".45"/>
                <ellipse cx="150" cy="114" rx="5.5" ry="2.5" fill="#00d4ff" opacity=".35"/>
                <circle cx="80" cy="89" r="22" fill="#0a1f35" stroke="#00d4ff" stroke-width="1" stroke-opacity=".4"/>
                <ellipse cx="80" cy="72" rx="22" ry="9" fill="#051528"/>
                <rect x="58" y="72" width="44" height="13" fill="#051528"/>
                <circle cx="73" cy="89" r="2.5" fill="#00d4ff" opacity=".8"/>
                <circle cx="87" cy="89" r="2.5" fill="#00d4ff" opacity=".8"/>
                <path d="M73 98 Q80 104 87 98" fill="none" stroke="#00d4ff" stroke-width="1.5" stroke-linecap="round" opacity=".6"/>
                <line x1="102" y1="83" x2="126" y2="83" stroke="#00d4ff" stroke-width="1.8" stroke-dasharray="4,2" opacity=".5"/>
                <polygon points="126,80 132,83 126,86" fill="#00d4ff" opacity=".5"/>
            </svg>
            <div class="mock-card">
                <div class="mock-avatar">&#128100;</div>
                <div class="mock-dots">&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</div>
                <div class="mock-dots" style="opacity:.4;">&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</div>
                <button class="mock-su">Sign Up</button>
            </div>
        </div>
    </div>
</main>
</body>
</html>