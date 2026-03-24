<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }
require 'db.php';

// Mark as Done
if (isset($_GET['done'])) {
    $stmt = $pdo->prepare("UPDATE sitin_records SET status='Done' WHERE id=?");
    $stmt->execute([$_GET['done']]);
    header('Location: admin_sitin_records.php');
    exit;
}

// Delete record
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM sitin_records WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header('Location: admin_sitin_records.php');
    exit;
}

// Filter
$filter = $_GET['filter'] ?? 'All';
if ($filter === 'Active') {
    $records = $pdo->query("SELECT * FROM sitin_records WHERE status='Active' ORDER BY created_at DESC")->fetchAll();
} elseif ($filter === 'Done') {
    $records = $pdo->query("SELECT * FROM sitin_records WHERE status='Done' ORDER BY created_at DESC")->fetchAll();
} else {
    $records = $pdo->query("SELECT * FROM sitin_records ORDER BY created_at DESC")->fetchAll();
}

$total_active = $pdo->query("SELECT COUNT(*) FROM sitin_records WHERE status='Active'")->fetchColumn();
$total_done   = $pdo->query("SELECT COUNT(*) FROM sitin_records WHERE status='Done'")->fetchColumn();
$total_all    = $pdo->query("SELECT COUNT(*) FROM sitin_records")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Admin – Sit-in Records</title>
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
nav .brand { font-size: .85rem; font-weight: 600; color: var(--neon); }
nav ul { list-style: none; display: flex; gap: 1rem; }
nav ul li a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .78rem; transition: color .2s; }
nav ul li a:hover, nav ul li a.active { color: var(--neon); }
nav ul li a.logout { color: var(--gold); border: 1px solid var(--gold); padding: .25rem .7rem; border-radius: 6px; }
nav ul li a.logout:hover { background: rgba(212,160,23,.15); }
main {
    flex: 1;
    padding: 2rem 1.5rem;
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
}
h1 {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--neon);
    text-align: center;
    margin-bottom: 1.5rem;
    letter-spacing: 1px;
    text-shadow: 0 0 20px rgba(0,212,255,.4);
}
/* STAT CARDS */
.stat-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: rgba(5,21,40,.85);
    border: 1px solid rgba(0,212,255,.2);
    border-radius: 10px;
    padding: 1rem 1.2rem;
    text-align: center;
}
.stat-card .num { font-size: 1.8rem; font-weight: 700; color: var(--neon); text-shadow: 0 0 15px rgba(0,212,255,.4); }
.stat-card .lbl { font-size: .72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; margin-top: .2rem; }
.stat-card.active .num { color: var(--neon); }
.stat-card.done .num   { color: #7ec87e; }
.stat-card.total .num  { color: var(--gold); }
/* TOOLBAR */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: .8rem;
}
.filter-group { display: flex; gap: .5rem; }
.btn-filter {
    padding: .35rem .9rem;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid var(--border);
    color: var(--muted);
    transition: all .2s;
}
.btn-filter:hover { border-color: var(--neon); color: var(--neon); }
.btn-filter.active-filter { border-color: var(--neon); color: var(--neon); background: rgba(0,212,255,.1); }
.search-bar {
    padding: .4rem .8rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: .82rem;
    color: #c8e6f5;
    background: rgba(0,20,40,.6);
    width: 220px;
}
.search-bar:focus { outline: none; border-color: var(--neon); }
/* TABLE */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .82rem; }
thead tr { border-bottom: 1px solid rgba(0,212,255,.2); }
th {
    color: var(--muted);
    font-weight: 600;
    padding: .6rem .8rem;
    text-align: left;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .5px;
}
td { padding: .65rem .8rem; color: #c8e6f5; border-bottom: 1px solid rgba(0,212,255,.07); }
tr:hover td { background: rgba(0,212,255,.04); }
/* BADGES */
.badge-active {
    padding: .2rem .7rem;
    background: rgba(0,212,255,.15);
    color: var(--neon);
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 600;
    border: 1px solid rgba(0,212,255,.3);
}
.badge-done {
    padding: .2rem .7rem;
    background: rgba(126,200,126,.15);
    color: #7ec87e;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 600;
    border: 1px solid rgba(126,200,126,.3);
}
/* ACTION BUTTONS */
.action-group { display: flex; gap: .4rem; }
.btn-done {
    padding: .28rem .7rem;
    background: rgba(0,212,255,.1);
    color: var(--neon);
    border: 1px solid rgba(0,212,255,.4);
    border-radius: 5px;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    white-space: nowrap;
}
.btn-done:hover { background: rgba(0,212,255,.2); }
.btn-del {
    padding: .28rem .7rem;
    background: rgba(192,57,43,.1);
    color: #ff6b6b;
    border: 1px solid rgba(255,107,107,.4);
    border-radius: 5px;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    white-space: nowrap;
}
.btn-del:hover { background: rgba(192,57,43,.2); }
.no-data { text-align: center; color: var(--muted); padding: 3rem; font-size: .9rem; }
@media (max-width: 600px) {
    .stat-cards { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Admin</span>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="admin_search.php">Search</a></li>
        <li><a href="admin_students.php">Students</a></li>
        <li><a href="admin_sitin.php">Sit-in</a></li>
        <li><a href="admin_sitin_records.php" class="active">View Sit-in Records</a></li>
        <li><a href="admin_logout.php" class="logout">Log out</a></li>
    </ul>
</nav>

<main>
    <h1>Sit-in Records</h1>

    <!-- STAT CARDS -->
    <div class="stat-cards">
        <div class="stat-card total">
            <div class="num"><?= $total_all ?></div>
            <div class="lbl">Total Records</div>
        </div>
        <div class="stat-card active">
            <div class="num"><?= $total_active ?></div>
            <div class="lbl">Currently Active</div>
        </div>
        <div class="stat-card done">
            <div class="num"><?= $total_done ?></div>
            <div class="lbl">Completed</div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="filter-group">
            <a href="admin_sitin_records.php?filter=All"
               class="btn-filter <?= $filter === 'All'    ? 'active-filter' : '' ?>">All</a>
            <a href="admin_sitin_records.php?filter=Active"
               class="btn-filter <?= $filter === 'Active' ? 'active-filter' : '' ?>">Active</a>
            <a href="admin_sitin_records.php?filter=Done"
               class="btn-filter <?= $filter === 'Done'   ? 'active-filter' : '' ?>">Done</a>
        </div>
        <input type="text" class="search-bar" id="searchInput" placeholder="Search records...">
    </div>

    <!-- TABLE -->
    <div class="table-wrap">
        <table id="recordTable">
            <thead>
                <tr>
                    <th>Sit ID</th>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Session</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="9" class="no-data">No records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['id_number']) ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['purpose']) ?></td>
                        <td><?= htmlspecialchars($r['lab']) ?></td>
                        <td><?= htmlspecialchars($r['session']) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['status'] === 'Active'): ?>
                                <span class="badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge-done">Done</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-group">
                                <?php if ($r['status'] === 'Active'): ?>
                                    <a href="admin_sitin_records.php?done=<?= $r['id'] ?>&filter=<?= $filter ?>"
                                       class="btn-done"
                                       onclick="return confirm('Mark this sit-in as done?')">
                                       ✓ Done
                                    </a>
                                <?php endif; ?>
                                <a href="admin_sitin_records.php?delete=<?= $r['id'] ?>&filter=<?= $filter ?>"
                                   class="btn-del"
                                   onclick="return confirm('Delete this record?')">
                                   ✕ Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    var val = this.value.toLowerCase();
    document.querySelectorAll('#recordTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
</body>
</html>