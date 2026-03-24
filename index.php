<?php
session_start();
require 'db.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = trim($_POST['id_number'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (empty($id_number) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id_number = ?");
        $stmt->execute([$id_number]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid ID number or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Sit-in Monitoring System</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --purple:    #4B2882;
    --purple-dk: #311a5e;
    --gold:      #D4A017;
    --danger:    #c0392b;
    --neon:      #00d4ff;
    --neon-dk:   #0099bb;
    --dark:      #020b18;
    --dark2:     #051528;
    --dark3:     #0a1f35;
    --muted:     #7a9bb5;
    --border:    #0d3a5c;
}
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--dark);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

/* animated grid background */
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
nav ul { list-style: none; display: flex; gap: 1.2rem; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }

/* MAIN */
main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    position: relative;
    z-index: 1;
}

/* LOGIN CARD */
.login-card {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.25);
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 2.5rem;
    padding: 2.5rem 3rem;
    max-width: 820px;
    width: 100%;
    position: relative;
    animation: fadeUp .4s ease both;
    box-shadow:
        0 0 40px rgba(0,212,255,.08),
        0 0 80px rgba(0,212,255,.04),
        inset 0 0 40px rgba(0,212,255,.02);
}

/* corner accents */
.login-card::before,
.login-card::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-color: var(--neon);
    border-style: solid;
    opacity: .7;
}
.login-card::before { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
.login-card::after  { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* LOGO glow */
.logo-side svg { display: block; filter: drop-shadow(0 0 12px rgba(0,212,255,.3)); }

/* DIVIDER */
.vdiv {
    width: 1px;
    height: 240px;
    background: linear-gradient(to bottom, transparent, rgba(0,212,255,.4), transparent);
    flex-shrink: 0;
}

/* FORM */
.form-col { flex: 1; }

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

/* FIELDS */
.field { margin-bottom: .9rem; position: relative; }
.field .icon {
    position: absolute;
    left: .85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--neon);
    font-size: .9rem;
    opacity: .7;
}
.field input {
    width: 100%;
    padding: .62rem .9rem .62rem 2.2rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    color: #c8e6f5;
    background: rgba(0,20,40,.6);
    transition: border-color .2s, box-shadow .2s;
}
.field input::placeholder { color: rgba(120,180,210,.4); }
.field input:focus {
    outline: none;
    border-color: var(--neon);
    box-shadow: 0 0 0 3px rgba(0,212,255,.1), 0 0 20px rgba(0,212,255,.1);
    background: rgba(0,25,50,.8);
}
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }

/* REMEMBER / FORGOT */
.row-check {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: .78rem;
    margin-bottom: 1rem;
}
.row-check label { display: flex; align-items: center; gap: .4rem; color: var(--muted); cursor: pointer; }
.row-check input[type=checkbox] { accent-color: var(--neon); }
.row-check a { color: var(--neon); text-decoration: none; font-weight: 600; }
.row-check a:hover { text-shadow: 0 0 8px var(--neon); }

/* LOGIN BUTTON */
.btn-login {
    width: 100%;
    padding: .75rem;
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
    transition: all .2s;
    position: relative;
    overflow: hidden;
}
.btn-login::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0,212,255,.15), transparent);
    transition: left .4s;
}
.btn-login:hover::before { left: 100%; }
.btn-login:hover {
    background: rgba(0,212,255,.1);
    box-shadow: 0 0 20px rgba(0,212,255,.3);
    color: #fff;
}

/* REGISTER LINK */
.reg-link { text-align: center; margin-top: .8rem; font-size: .78rem; color: var(--muted); }
.reg-link a { color: var(--neon); font-weight: 600; text-decoration: none; }
.reg-link a:hover { text-shadow: 0 0 8px var(--neon); }

@media (max-width: 600px) {
    .login-card { flex-direction: column; gap: 1.5rem; padding: 2rem 1.4rem; }
    .vdiv { width: 80%; height: 1px; background: linear-gradient(to right, transparent, rgba(0,212,255,.4), transparent); }
}
</style>
</head>
<body>

<nav>
    <span class="brand">College of Computer Studies Sit-in Monitoring System</span>
    <ul>
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="#">Community ▾</a></li>
        <li><a href="#">About</a></li>
        <li><a href="index.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    </ul>
</nav>

<main>
    <div class="login-card">

        <!-- CCS Logo -->
        <div class="logo-side">
            <svg viewBox="0 0 180 200" width="170" xmlns="http://www.w3.org/2000/svg">
                <path d="M90 8 L168 40 L168 110 Q168 165 90 192 Q12 165 12 110 L12 40 Z"
                      fill="#4B2882" stroke="#00d4ff" stroke-width="3"/>
                <path d="M90 18 L158 46 L158 112 Q158 158 90 182 Q22 158 22 112 L22 46 Z"
                      fill="#D4A017"/>
                <path d="M90 28 L148 52 L148 114 Q148 152 90 172 Q32 152 32 114 L32 52 Z"
                      fill="#4B2882"/>
                <text x="90" y="80"  font-size="11" font-weight="700" fill="#00d4ff"
                      text-anchor="middle" font-family="sans-serif">UC</text>
                <text x="90" y="95"  font-size="9" fill="#D4A017"
                      text-anchor="middle" font-family="sans-serif">COLLEGE OF</text>
                <text x="90" y="128" font-size="34" font-weight="900" fill="#D4A017"
                      text-anchor="middle" font-family="sans-serif">CCS</text>
                <text x="90" y="148" font-size="7.5" fill="#D4A017"
                      text-anchor="middle" font-family="sans-serif">COMPUTER STUDIES</text>
                <text x="90" y="160" font-size="6" fill="#00d4ff"
                      text-anchor="middle" font-family="sans-serif" font-style="italic">INCEPTUM . INNOVATIO . MUNERIS</text>
                <rect x="55" y="165" width="70" height="16" rx="3" fill="#D4A017"/>
                <text x="90" y="176" font-size="9" font-weight="700" fill="#4B2882"
                      text-anchor="middle" font-family="sans-serif">1983</text>
            </svg>
        </div>

        <div class="vdiv"></div>

        <!-- Form -->
        <div class="form-col">
            <h2>Login</h2>

            <?php if ($error): ?>
                <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="field">
                    <span class="icon">&#128100;</span>
                    <input type="text" name="id_number"
                           placeholder="Enter a valid id number"
                           value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required>
                    <label>ID Number</label>
                </div>
                <div class="field">
                    <span class="icon">&#128274;</span>
                    <input type="password" name="password"
                           placeholder="Enter password" required>
                    <label>Password</label>
                </div>
                <div class="row-check">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    <a href="#">Forgot password?</a>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>

            <p class="reg-link">Don't have an account? <a href="register.php">Register</a></p>
        </div>

    </div>
</main>

</body>
</html>