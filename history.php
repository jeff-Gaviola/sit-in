<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require 'db.php';

$user = $_SESSION['user'];

// Get this student's sit-in history
$stmt = $pdo->prepare("SELECT * FROM sitin_records WHERE student_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS – History</title>
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
    padding: 2rem 1.5rem;
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
}
h1 {
    font-size: 1.6rem;
    font-weight: 600;
    color: #c8e6f5;
    text-align: center;
    margin-bottom: 1.5rem;
    letter-spacing: 1px;
}
/* TOOLBAR */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: .8rem;
}
.entries-group {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .82rem;
    color: #c8e6f5;
}
.entries-group select {
    padding: .3rem .5rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: .82rem;
    color: #c8e6f5;
    background: rgba(0,20,40,.6);
}
.entries-group select:focus { outline: none; border-color: var(--neon); }
.search-group {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .82rem;
    color: #c8e6f5;
}
.search-group input {
    padding: .35rem .7rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: .82rem;
    color: #c8e6f5;
    background: rgba(0,20,40,.6);
    width: 200px;
}
.search-group input:focus { outline: none; border-color: var(--neon); }
/* TABLE */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .82rem; }
thead tr {
    background: rgba(0,212,255,.15);
    border-bottom: 1px solid rgba(0,212,255,.3);
}
th {
    color: var(--neon);
    font-weight: 600;
    padding: .75rem .9rem;
    text-align: left;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    white-space: nowrap;
}
th .sort-arrows { color: rgba(0,212,255,.5); font-size: .6rem; margin-left: .3rem; }
td {
    padding: .65rem .9rem;
    color: #c8e6f5;
    border-bottom: 1px solid rgba(0,212,255,.07);
    white-space: nowrap;
}
tr:hover td { background: rgba(0,212,255,.04); }
.no-data { text-align: center; color: var(--muted); padding: 2rem; font-size: .88rem; }
/* BADGES */
.badge-active { padding: .2rem .7rem; background: rgba(0,212,255,.15); color: var(--neon); border-radius: 20px; font-size: .72rem; font-weight: 600; border: 1px solid rgba(0,212,255,.3); }
.badge-done   { padding: .2rem .7rem; background: rgba(126,200,126,.15); color: #7ec87e; border-radius: 20px; font-size: .72rem; font-weight: 600; border: 1px solid rgba(126,200,126,.3); }
/* PAGINATION */
.pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    flex-wrap: wrap;
    gap: .5rem;
}
.showing-text { font-size: .78rem; color: var(--muted); }
.pagination { display: flex; gap: .3rem; }
.page-btn {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-size: .78rem;
    color: var(--muted);
    cursor: pointer;
    background: transparent;
    transition: all .2s;
    text-decoration: none;
}
.page-btn:hover, .page-btn.active-page {
    border-color: var(--neon);
    color: var(--neon);
    background: rgba(0,212,255,.1);
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
        <li><a href="history.php" class="active">History</a></li>
        <li><a href="reservation.php">Reservation</a></li>
        <li><a href="logout.php" class="logout">Log out</a></li>
    </ul>
</nav>

<main>
    <h1>History Information</h1>

    <div class="toolbar">
        <div class="entries-group">
            <select id="entriesSelect" onchange="changeEntries(this.value)">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            entries per page
        </div>
        <div class="search-group">
            Search:
            <input type="text" id="searchInput" placeholder="Search...">
        </div>
    </div>

    <div class="table-wrap">
        <table id="historyTable">
            <thead>
                <tr>
                    <th>ID Number <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Name <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Sit Purpose <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Laboratory <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Login <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Logout <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Date <span class="sort-arrows">&#9650;&#9660;</span></th>
                    <th>Action <span class="sort-arrows">&#9650;&#9660;</span></th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="8" class="no-data">No data available</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['id_number']) ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['purpose']) ?></td>
                        <td><?= htmlspecialchars($r['lab']) ?></td>
                        <td><?= date('h:i A', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['status'] === 'Done'): ?>
                                <?= date('h:i A', strtotime($r['created_at'] . ' +1 hour')) ?>
                            <?php else: ?>
                                <span style="color:var(--muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['status'] === 'Active'): ?>
                                <span class="badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge-done">Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="pagination-wrap">
        <div class="showing-text" id="showingText">
            Showing 1 to <?= min(10, count($records)) ?> of <?= count($records) ?> entries
        </div>
        <div class="pagination" id="pagination">
            <a class="page-btn" id="btn-first">&#171;</a>
            <a class="page-btn" id="btn-prev">&#8249;</a>
            <a class="page-btn active-page" id="btn-page">1</a>
            <a class="page-btn" id="btn-next">&#8250;</a>
            <a class="page-btn" id="btn-last">&#187;</a>
        </div>
    </div>
</main>

<script>
var allRows      = Array.from(document.querySelectorAll('#tableBody tr'));
var entriesPerPage = 10;
var currentPage  = 1;

function getFilteredRows() {
    var val = document.getElementById('searchInput').value.toLowerCase();
    return allRows.filter(function(row) {
        return row.textContent.toLowerCase().includes(val);
    });
}

function renderTable() {
    var filtered = getFilteredRows();
    var total    = filtered.length;
    var start    = (currentPage - 1) * entriesPerPage;
    var end      = Math.min(start + entriesPerPage, total);

    allRows.forEach(function(r) { r.style.display = 'none'; });
    filtered.slice(start, end).forEach(function(r) { r.style.display = ''; });

    var showingText = total === 0
        ? 'Showing 0 entries'
        : 'Showing ' + (start + 1) + ' to ' + end + ' of ' + total + ' entries';
    document.getElementById('showingText').textContent = showingText;
    document.getElementById('btn-page').textContent = currentPage;
}

function changeEntries(val) {
    entriesPerPage = parseInt(val);
    currentPage    = 1;
    renderTable();
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    currentPage = 1;
    renderTable();
});

document.getElementById('btn-first').addEventListener('click', function() {
    currentPage = 1; renderTable();
});
document.getElementById('btn-prev').addEventListener('click', function() {
    if (currentPage > 1) { currentPage--; renderTable(); }
});
document.getElementById('btn-next').addEventListener('click', function() {
    var total = getFilteredRows().length;
    var maxPage = Math.ceil(total / entriesPerPage);
    if (currentPage < maxPage) { currentPage++; renderTable(); }
});
document.getElementById('btn-last').addEventListener('click', function() {
    var total = getFilteredRows().length;
    currentPage = Math.ceil(total / entriesPerPage) || 1;
    renderTable();
});

renderTable();
</script>

</body>
</html>