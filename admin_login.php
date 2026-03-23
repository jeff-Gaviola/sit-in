<?php
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: admin_dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Admin Login</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --neon: #00d4ff; --dark: #020b18; --muted: #7a9bb5; --border: #0d3a5c; --danger: #c0392b;
}
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--dark); min-height: 100vh; display: flex;
    align-items: center; justify-content: center; overflow-x: hidden;
}
body::before {
    content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background-image: linear-gradient(rgba(0,212,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,212,255,.04) 1px, transparent 1px);
    background-size: 40px 40px; z-index: 0; pointer-events: none;
}
.card {
    background: rgba(5,21,40,.85); border: 1px solid rgba(0,212,255,.25);
    border-radius: 16px; padding: 2.5rem 3rem; max-width: 400px; width: 100%;
    position: relative; z-index: 1; box-shadow: 0 0 40px rgba(0,212,255,.08);
}
.card::before { content: ''; position: absolute; top: 12px; left: 12px; width: 20px; height: 20px; border: 2px solid var(--neon); border-right: 0; border-bottom: 0; opacity: .7; }
.card::after  { content: ''; position: absolute; bottom: 12px; right: 12px; width: 20px; height: 20px; border: 2px solid var(--neon); border-left: 0; border-top: 0; opacity: .7; }
h2 { font-size: 1.3rem; font-weight: 600; color: var(--neon); text-align: center; margin-bottom: 1.5rem; letter-spacing: 2px; text-transform: uppercase; text-shadow: 0 0 20px rgba(0,212,255,.5); }
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: .9rem; background: rgba(192,57,43,.15); color: #ff6b6b; border-left: 3px solid #ff6b6b; }
.field { margin-bottom: .9rem; }
.field input { width: 100%; padding: .62rem .9rem; border: 1px solid var(--border); border-radius: 8px; font-size: .88rem; font-family: inherit; color: #c8e6f5; background: rgba(0,20,40,.6); transition: border-color .2s, box-shadow .2s; }
.field input::placeholder { color: rgba(120,180,210,.4); }
.field input:focus { outline: none; border-color: var(--neon); box-shadow: 0 0 0 3px rgba(0,212,255,.1); background: rgba(0,25,50,.8); }
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.btn { width: 100%; padding: .7rem; background: transparent; color: var(--neon); border: 1px solid var(--neon); border-radius: 8px; font-size: .9rem; font-family: inherit; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: all .2s; margin-top: .5rem; }
.btn:hover { background: rgba(0,212,255,.1); box-shadow: 0 0 20px rgba(0,212,255,.3); color: #fff; }
.back { display: block; text-align: center; margin-top: 1rem; font-size: .78rem; color: var(--muted); text-decoration: none; }
.back:hover { color: var(--neon); }
</style>
</head>
<body>
<div class="card">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="field">
            <input type="text" name="username" placeholder="Username" required>
            <label>Username</label>
        </div>
        <div class="field">
            <input type="password" name="password" placeholder="Password" required>
            <label>Password</label>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <a href="index.php" class="back">← Back to Student Login</a>
</div>
</body>
</html>