<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }
require 'db.php';

// Delete student
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: admin_students.php');
    exit;
}

// Reset all sessions
if (isset($_GET['reset_sessions'])) {
    $pdo->query("UPDATE students SET remaining_session = 30");
    header('Location: admin_students.php');
    exit;
}

// Edit student
$edit_student = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_student = $stmt->fetch();
}

// Save edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("UPDATE students SET first_name=?, last_name=?, middle_name=?, course=?, course_level=?, remaining_session=? WHERE id=?");
    $stmt->execute([
        $_POST['first_name'], $_POST['last_name'], $_POST['middle_name'],
        $_POST['course'], $_POST['course_level'], $_POST['remaining_session'],
        $_POST['edit_id']
    ]);
    header('Location: admin_students.php');
    exit;
}

$students = $pdo->query("SELECT * FROM students ORDER BY id_number ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCS Admin – Students</title>
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
main { flex: 1; padding: 2rem 1.5rem; position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; width: 100%; }
h1 { font-size: 1.4rem; font-weight: 600; color: var(--neon); text-align: center; margin-bottom: 1.5rem; letter-spacing: 1px; text-shadow: 0 0 20px rgba(0,212,255,.4); }
.top-bar { display: flex; gap: .8rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between; }
.btn-add { padding: .45rem 1.2rem; background: rgba(0,212,255,.15); color: var(--neon); border: 1px solid var(--neon); border-radius: 6px; font-size: .82rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-add:hover { background: rgba(0,212,255,.25); }
.btn-reset { padding: .45rem 1.2rem; background: rgba(192,57,43,.15); color: #ff6b6b; border: 1px solid #ff6b6b; border-radius: 6px; font-size: .82rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-reset:hover { background: rgba(192,57,43,.25); }
.search-bar { padding: .4rem .8rem; border: 1px solid var(--border); border-radius: 6px; font-size: .82rem; color: #c8e6f5; background: rgba(0,20,40,.6); width: 200px; }
.search-bar:focus { outline: none; border-color: var(--neon); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .82rem; }
thead tr { border-bottom: 1px solid rgba(0,212,255,.2); }
th { color: var(--muted); font-weight: 600; padding: .6rem .8rem; text-align: left; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
td { padding: .6rem .8rem; color: #c8e6f5; border-bottom: 1px solid rgba(0,212,255,.07); }
tr:hover td { background: rgba(0,212,255,.04); }
.btn-edit { padding: .3rem .8rem; background: rgba(0,212,255,.15); color: var(--neon); border: 1px solid var(--neon); border-radius: 5px; font-size: .75rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-del  { padding: .3rem .8rem; background: rgba(192,57,43,.15); color: #ff6b6b; border: 1px solid #ff6b6b; border-radius: 5px; font-size: .75rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-edit:hover { background: rgba(0,212,255,.25); }
.btn-del:hover  { background: rgba(192,57,43,.25); }
/* Modal */
.modal-bg { display:none; position:fixed; top:0;left:0;width:100%;height:100%; background:rgba(0,0,0,.7); z-index:200; align-items:center; justify-content:center; }
.modal-bg.open { display:flex; }
.modal { background: rgba(5,21,40,.98); border: 1px solid rgba(0,212,255,.3); border-radius: 12px; padding: 2rem; width: 100%; max-width: 460px; position: relative; }
.modal h3 { color: var(--neon); font-size: 1rem; margin-bottom: 1.2rem; letter-spacing: 1px; }
.modal-close { position: absolute; top: .8rem; right: 1rem; background: none; border: none; color: #ff6b6b; font-size: 1.2rem; cursor: pointer; }
.field { margin-bottom: .8rem; }
.field input, .field select { width: 100%; padding: .55rem .8rem; border: 1px solid var(--border); border-radius: 7px; font-size: .85rem; font-family: inherit; color: #c8e6f5; background: rgba(0,20,40,.6); }
.field input:focus, .field select:focus { outline: none; border-color: var(--neon); }
.field label { display: block; font-size: .7rem; color: var(--muted); margin-top: .2rem; }
.field select option { background: #051528; }
.btn-save { padding: .6rem 1.8rem; background: transparent; color: var(--neon); border: 1px solid var(--neon); border-radius: 7px; font-size: .88rem; font-family: inherit; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-save:hover { background: rgba(0,212,255,.1); }
</style>
</head>
<body>
<nav>
    <span class="brand">College of Computer Studies Admin</span>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="admin_search.php">Search</a></li>
        <li><a href="admin_students.php" class="active">Students</a></li>
        <li><a href="admin_sitin.php">Sit-in</a></li>
        <li><a href="admin_sitin_records.php">View Sit-in Records</a></li>
        <li><a href="admin_logout.php" class="logout">Log out</a></li>
    </ul>
</nav>
<main>
    <h1>Students Information</h1>
    <div class="top-bar">
        <div style="display:flex;gap:.8rem;">
            <a href="#" class="btn-add">Add Students</a>
            <a href="admin_students.php?reset_sessions=1"
               onclick="return confirm('Reset all sessions to 30?')"
               class="btn-reset">Reset All Session</a>
        </div>
        <input type="text" class="search-bar" id="searchInput" placeholder="Search...">
    </div>
    <div class="table-wrap">
        <table id="studentTable">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Year Level</th>
                    <th>Course</th>
                    <th>Remaining Session</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['id_number']) ?></td>
                    <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['middle_name'] . ' ' . $s['last_name']) ?></td>
                    <td><?= htmlspecialchars($s['course_level']) ?></td>
                    <td><?= htmlspecialchars($s['course']) ?></td>
                    <td><?= htmlspecialchars($s['remaining_session'] ?? 30) ?></td>
                    <td style="display:flex;gap:.4rem;">
                        <a href="#" class="btn-edit" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">Edit</a>
                        <a href="admin_students.php?delete=<?= $s['id'] ?>"
                           onclick="return confirm('Delete this student?')"
                           class="btn-del">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Edit Modal -->
<div class="modal-bg" id="editModal">
    <div class="modal">
        <button class="modal-close" onclick="closeEdit()">&#x2715;</button>
        <h3>Edit Student</h3>
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="field">
                <input type="text" name="first_name" id="edit_first" placeholder="First Name">
                <label>First Name</label>
            </div>
            <div class="field">
                <input type="text" name="last_name" id="edit_last" placeholder="Last Name">
                <label>Last Name</label>
            </div>
            <div class="field">
                <input type="text" name="middle_name" id="edit_middle" placeholder="Middle Name">
                <label>Middle Name</label>
            </div>
            <div class="field">
                <select name="course" id="edit_course">
                    <option value="BSIT">BSIT</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSEMC">BSEMC</option>
                </select>
                <label>Course</label>
            </div>
            <div class="field">
                <select name="course_level" id="edit_level">
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
                <label>Year Level</label>
            </div>
            <div class="field">
                <input type="number" name="remaining_session" id="edit_session" placeholder="Remaining Session">
                <label>Remaining Session</label>
            </div>
            <div style="text-align:right;margin-top:1rem;">
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(s) {
    document.getElementById('edit_id').value     = s.id;
    document.getElementById('edit_first').value  = s.first_name;
    document.getElementById('edit_last').value   = s.last_name;
    document.getElementById('edit_middle').value = s.middle_name;
    document.getElementById('edit_course').value = s.course;
    document.getElementById('edit_level').value  = s.course_level;
    document.getElementById('edit_session').value= s.remaining_session || 30;
    document.getElementById('editModal').classList.add('open');
}
function closeEdit() { document.getElementById('editModal').classList.remove('open'); }

document.getElementById('searchInput').addEventListener('keyup', function() {
    var val = this.value.toLowerCase();
    document.querySelectorAll('#studentTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
</body>
</html>