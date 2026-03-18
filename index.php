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
    --light:     #f4f1fb;
    --muted:     #888;
    --border:    #ccc;
}
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--light);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
nav {
    background: var(--purple);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    height: 48px;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
    position: sticky;
    top: 0;
    z-index: 99;
}
nav .brand { font-size: .85rem; font-weight: 600; color: #fff; }
nav ul { list-style: none; display: flex; gap: 1.2rem; }
nav ul li a { color: rgba(255,255,255,.82); text-decoration: none; font-size: .82rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--gold); }
main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
}
.login-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(75,40,130,.18);
    display: flex;
    align-items: center;
    gap: 2.5rem;
    padding: 2.5rem 3rem;
    max-width: 820px;
    width: 100%;
    animation: fadeUp .4s ease both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.vdiv { width: 1px; height: 240px; background: #e0e0e0; flex-shrink: 0; }
.form-col { flex: 1; }
h2 { font-size: 1.3rem; font-weight: 600; color: var(--purple); text-align: center; margin-bottom: 1.2rem; }
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: .9rem; }
.alert-err { background: #fde8e8; color: var(--danger); border-left: 3px solid var(--danger); }
.field { margin-bottom: .9rem; }
.field input {
    width: 100%;
    padding: .62rem .9rem;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    color: #333;
    background: #fafafa;
    transition: border-color .2s, box-shadow .2s;
}
.field input:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(75,40,130,.12);
    background: #fff;
}
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.row-check {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: .78rem;
    margin-bottom: 1rem;
}
.row-check label { display: flex; align-items: center; gap: .4rem; color: #555; cursor: pointer; }
.row-check input[type=checkbox] { accent-color: var(--purple); }
.row-check a { color: var(--purple); text-decoration: none; font-weight: 600; }
.row-check a:hover { text-decoration: underline; }
.btn-login {
    width: 100%;
    padding: .7rem;
    background: var(--purple);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, transform .15s;
    box-shadow: 0 4px 14px rgba(75,40,130,.28);
}
.btn-login:hover { background: var(--purple-dk); transform: translateY(-1px); }
.btn-login:active { transform: translateY(0); }
.reg-link { text-align: center; margin-top: .8rem; font-size: .78rem; color: #555; }
.reg-link a { color: var(--danger); font-weight: 600; text-decoration: none; }
.reg-link a:hover { text-decoration: underline; }
@media (max-width: 600px) {
    .login-card { flex-direction: column; gap: 1.5rem; padding: 2rem 1.4rem; }
    .vdiv { width: 80%; height: 1px; }
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
        <div class="logo-side">
            <svg viewBox="0 0 180 200" width="170" xmlns="http://www.w3.org/2000/svg">
                <path d="M90 8 L168 40 L168 110 Q168 165 90 192 Q12 165 12 110 L12 40 Z" fill="#4B2882" stroke="#D4A017" stroke-width="4"/>
                <path d="M90 18 L158 46 L158 112 Q158 158 90 182 Q22 158 22 112 L22 46 Z" fill="#D4A017"/>
                <path d="M90 28 L148 52 L148 114 Q148 152 90 172 Q32 152 32 114 L32 52 Z" fill="#4B2882"/>
                <text x="90" y="80"  font-size="11" font-weight="700" fill="#D4A017" text-anchor="middle" font-family="sans-serif">UC</text>
                <text x="90" y="95"  font-size="9" fill="#D4A017" text-anchor="middle" font-family="sans-serif">COLLEGE OF</text>
                <text x="90" y="128" font-size="34" font-weight="900" fill="#D4A017" text-anchor="middle" font-family="sans-serif">CCS</text>
                <text x="90" y="148" font-size="7.5" fill="#D4A017" text-anchor="middle" font-family="sans-serif">COMPUTER STUDIES</text>
                <text x="90" y="160" font-size="6" fill="#fff" text-anchor="middle" font-family="sans-serif" font-style="italic">INCEPTUM . INNOVATIO . MUNERIS</text>
                <rect x="55" y="165" width="70" height="16" rx="3" fill="#D4A017"/>
                <text x="90" y="176" font-size="9" font-weight="700" fill="#4B2882" text-anchor="middle" font-family="sans-serif">1983</text>
            </svg>
        </div>
        <div class="vdiv"></div>
        <div class="form-col">
            <h2>Sign In</h2>
            <?php if ($error): ?>
                <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php">
                <div class="field">
                    <input type="text" name="id_number"
                           placeholder="Enter a valid id number"
                           value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required>
                    <label>ID Number</label>
                </div>
                <div class="field">
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