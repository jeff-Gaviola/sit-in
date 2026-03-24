<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }
require 'db.php';

// Process sit-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sitin_id'])) {
    $student_id = $_POST['sitin_id'];
    $purpose    = $_POST['purpose'];
    $lab        = $_POST['lab'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $s = $stmt->fetch();

    if ($s && $s['remaining_session'] > 0) {
        $name = $s['first_name'] . ' ' . $s['middle_name'] . ' ' . $s['last_name'];
        $ins  = $pdo->prepare("INSERT INTO sitin_records (student_id, id_number, name, purpose, lab, session) VALUES (?,?,?,?,?,?)");
        $ins->execute([$s['id'], $s['id_number'], $name, $purpose, $lab, $s['remaining_session']]);
        $upd = $pdo->prepare("UPDATE students SET remaining_session = remaining_session - 1 WHERE id = ?");
        $upd->execute([$s['id']]);
    }
    header('Location: admin_sitin.php');
    exit;
}

// Search student
$search_result = null;
if (isset($_GET['search_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id_number = ?");
    $stmt->execute([$_GET['search_id']]);
    $search_result = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Admin – Sit-in</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root { --neon: #00d4ff; --dark: #020b18; --muted: #7a9bb5; --border: #0d3a5c; --danger: #c0392b; --gold: #D4A017; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--dark); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }
body::before { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(0,212,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,212,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 0; pointer-events: none; }
nav { background: rgba(2,11,24,.92); border-bottom: 1px solid rgba(0,212,255,.2); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; height: 48px; position: sticky; top: 0; z-index: 99; backdrop-filter: blur(8px); }
nav .brand { font-size: .85rem; font-weight: 600; color: var(--neon); }
nav ul { list-style: none; display: flex; gap: 1rem; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .78rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
nav ul li a.logout { color: var(--gold); border: 1px solid var(--gold); padding: .25rem .7rem; border-radius: 6px; }
main { flex: 1; padding: 2rem 1.5rem; position: relative; z-index: 1; max-width: 600px; margin: 0 auto; width: 100%; }
h1 { font-size: 1.4rem; font-weight: 600; color: var(--neon); text-align: center; margin-bottom: 1.5rem; letter-spacing: 1px; }
.panel { background: rgba(5,21,40,.85); border: 1px solid rgba(0,212,255,.2); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
.search-row { display: flex; gap: .6rem; }
.search-row input { flex: 1; padding: .6rem .9rem; border: 1px solid var(--border); border-radius: 8px; font-size: .88rem; font-family: inherit; color: #c8e6f5; background: rgba(0,20,40,.6); }
.search-row input:focus { outline: none; border-color: var(--neon); }
.btn-search { padding: .6rem 1.4rem; background: rgba(0,212,255,.15); color: var(--neon); border: 1px solid var(--neon); border-radius: 8px; font-size: .85rem; font-family: inherit; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-search:hover { background: rgba(0,212,255,.25); }
.field { margin-bottom: .9rem; }
.field input, .field select { width: 100%; padding: .6rem .9rem; border: 1px solid var(--border); border-radius: 8px; font-size: .88rem; font-family: inherit; color: #c8e6f5; background: rgba(0,20,40,.6); }
.field input:focus, .field select:focus { outline: none; border-color: var(--neon); }
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.field select option { background: #051528; }
.field input[disabled] { opacity: .5; cursor: not-allowed; }
.btn-row { display: flex; gap: .8rem; justify-content: flex-end; margin-top: .5rem; }
.btn-close { padding: .6rem 1.4rem; background: transparent; color: var(--muted); border: 1px solid var(--border); border-radius: 8px; font-size: .85rem; font-family: inherit; font-weight: 600; cursor: pointer; text-decoration: none; }
.btn-sitin { padding: .6rem 1.4rem; background: rgba(0,212,255,.15); color: var(--neon); border: 1px solid var(--neon); border-radius: 8px; font-size: .85rem; font-family: inherit; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-sitin:hover { background: rgba(0,212,255,.25); }
.panel-title { font-size: .8rem; font-weight: 700; color: var(--neon); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; }
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Admin</span>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="admin_search.php">Search</a></li>
        <li><a href="admin_students.php">Students</a></li>
        <li><a href="admin_sitin.php" class="active">Sit-in</a></li>
        <li><a href="admin_sitin_records.php">View Sit-in Records</a></li>
        <li><a href="admin_logout.php" class="logout">Log out</a></li>
    </ul>
</nav>
<main>
    <h1>Sit-in</h1>

    <!-- Search -->
    <div class="panel">
        <div class="panel-title">Search Student</div>
        <form method="GET" class="search-row">
            <input type="text" name="search_id" placeholder="Enter ID Number..."
                   value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    <!-- Sit-in Form -->
    <?php if ($search_result): ?>
    <div class="panel">
        <div class="panel-title">Sit In Form</div>
        <form method="POST">
            <input type="hidden" name="sitin_id" value="<?= $search_result['id'] ?>">
            <div class="field">
                <input type="text" value="<?= htmlspecialchars($search_result['id_number']) ?>" disabled>
                <label>ID Number</label>
            </div>
            <div class="field">
                <input type="text" value="<?= htmlspecialchars($search_result['first_name'] . ' ' . $search_result['middle_name'] . ' ' . $search_result['last_name']) ?>" disabled>
                <label>Student Name</label>
            </div>
            <div class="field">
                <select name="purpose">
                    <option value="C Programming">C Programming</option>
                    <option value="Java">Java</option>
                    <option value="C#">C#</option>
                    <option value="ASP.Net">ASP.Net</option>
                    <option value="PHP">PHP</option>
                    <option value="Other">Other</option>
                </select>
                <label>Purpose</label>
            </div>
            <div class="field">
                <input type="text" name="lab" placeholder="e.g. 524">
                <label>Lab</label>
            </div>
            <div class="field">
                <input type="text" value="<?= htmlspecialchars($search_result['remaining_session'] ?? 30) ?>" disabled>
                <label>Remaining Session</label>
            </div>
            <div class="btn-row">
                <a href="admin_sitin.php" class="btn-close">Close</a>
                <button type="submit" class="btn-sitin">Sit In</button>
            </div>
        </form>
    </div>
    <?php elseif (isset($_GET['search_id'])): ?>
    <div class="panel" style="color:#ff6b6b;">Student not found.</div>
    <?php endif; ?>
</main>
</body>
</html>