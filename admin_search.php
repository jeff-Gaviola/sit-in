<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }
require 'db.php';

$results = [];
$searched = false;
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $searched = true;
    $q = '%' . trim($_GET['q']) . '%';
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?");
    $stmt->execute([$q, $q, $q, $q]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Admin – Search</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root { --neon: #00d4ff; --dark: #020b18; --muted: #7a9bb5; --border: #0d3a5c; --gold: #D4A017; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--dark); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }
body::before { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(0,212,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,212,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 0; pointer-events: none; }
nav { background: rgba(2,11,24,.92); border-bottom: 1px solid rgba(0,212,255,.2); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; height: 48px; position: sticky; top: 0; z-index: 99; backdrop-filter: blur(8px); }
nav .brand { font-size: .85rem; font-weight: 600; color: var(--neon); }
nav ul { list-style: none; display: flex; gap: 1rem; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .78rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
nav ul li a.logout { color: var(--gold); border: 1px solid var(--gold); padding: .25rem .7rem; border-radius: 6px; }
main { flex: 1; padding: 2rem 1.5rem; position: relative; z-index: 1; max-width: 800px; margin: 0 auto; width: 100%; }
h1 { font-size: 1.4rem; font-weight: 600; color: var(--neon); text-align: center; margin-bottom: 1.5rem; letter-spacing: 1px; }
.search-row { display: flex; gap: .6rem; margin-bottom: 1.5rem; }
.search-row input { flex: 1; padding: .65rem .9rem; border: 1px solid var(--border); border-radius: 8px; font-size: .88rem; font-family: inherit; color: #c8e6f5; background: rgba(0,20,40,.6); }
.search-row input:focus { outline: none; border-color: var(--neon); }
.search-row button { padding: .65rem 1.4rem; background: rgba(0,212,255,.15); color: var(--neon); border: 1px solid var(--neon); border-radius: 8px; font-size: .85rem; font-family: inherit; font-weight: 600; cursor: pointer; transition: all .2s; }
.search-row button:hover { background: rgba(0,212,255,.25); }
table { width: 100%; border-collapse: collapse; font-size: .82rem; }
thead tr { border-bottom: 1px solid rgba(0,212,255,.2); }
th { color: var(--muted); font-weight: 600; padding: .6rem .8rem; text-align: left; font-size: .75rem; text-transform: uppercase; }
td { padding: .6rem .8rem; color: #c8e6f5; border-bottom: 1px solid rgba(0,212,255,.07); }
tr:hover td { background: rgba(0,212,255,.04); }
.no-result { text-align: center; color: var(--muted); padding: 2rem; }
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Admin</span>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="admin_search.php" class="active">Search</a></li>
        <li><a href="admin_students.php">Students</a></li>
        <li><a href="admin_sitin.php">Sit-in</a></li>
        <li><a href="admin_sitin_records.php">View Sit-in Records</a></li>
        <li><a href="admin_logout.php" class="logout">Log out</a></li>
    </ul>
</nav>
<main>
    <h1>Search Student</h1>
    <form method="GET" class="search-row">
        <input type="text" name="q" placeholder="Search by ID, name, or email..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($searched): ?>
        <?php if (empty($results)): ?>
            <p class="no-result">No students found.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Email</th>
                    <th>Remaining Session</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['id_number']) ?></td>
                    <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['middle_name'] . ' ' . $s['last_name']) ?></td>
                    <td><?= htmlspecialchars($s['course']) ?></td>
                    <td><?= htmlspecialchars($s['course_level']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= htmlspecialchars($s['remaining_session'] ?? 30) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>