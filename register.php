<?php
session_start();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number       = trim($_POST['id_number']       ?? '');
    $last_name       = trim($_POST['last_name']        ?? '');
    $first_name      = trim($_POST['first_name']       ?? '');
    $middle_name     = trim($_POST['middle_name']      ?? '');
    $course_level    = trim($_POST['course_level']     ?? '1');
    $password        = $_POST['password']              ?? '';
    $repeat_password = $_POST['repeat_password']       ?? '';
    $email           = trim($_POST['email']            ?? '');
    $course          = trim($_POST['course']           ?? '');
    $address         = trim($_POST['address']          ?? '');

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
        $users = [];
        if (file_exists('users.json')) {
            $users = json_decode(file_get_contents('users.json'), true) ?? [];
        }
        foreach ($users as $u) {
            if ($u['id_number'] === $id_number) {
                $error = 'ID Number is already registered.';
                break;
            }
        }
        if (empty($error)) {
            $users[] = [
                'id_number'    => $id_number,
                'last_name'    => $last_name,
                'first_name'   => $first_name,
                'middle_name'  => $middle_name,
                'course_level' => $course_level,
                'password'     => password_hash($password, PASSWORD_DEFAULT),
                'email'        => $email,
                'course'       => $course,
                'address'      => $address,
                'registered'   => date('Y-m-d H:i:s'),
            ];
            file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
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
    align-items: flex-start;
    justify-content: center;
    padding: 2.5rem 1rem;
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
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(75,40,130,.18);
    padding: 2rem 2.2rem;
}
.back-btn {
    display: inline-block;
    background: var(--danger);
    color: #fff;
    font-size: .75rem;
    font-weight: 600;
    padding: .3rem .8rem;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 1rem;
    transition: background .2s;
}
.back-btn:hover { background: #a93226; }
h2 { font-size: 1.3rem; font-weight: 600; color: var(--purple); text-align: center; margin-bottom: 1.2rem; }
.alert { padding: .6rem .9rem; border-radius: 8px; font-size: .82rem; font-weight: 600; margin-bottom: .9rem; }
.alert-err { background: #fde8e8; color: var(--danger); border-left: 3px solid var(--danger); }
.alert-ok  { background: #e8f8f0; color: #1e7e46; border-left: 3px solid #1e7e46; }
.alert-ok a { color: #1e7e46; font-weight: 700; margin-left: .4rem; }
.field { margin-bottom: .9rem; }
.field input,
.field select {
    width: 100%;
    padding: .62rem .9rem;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: .88rem;
    font-family: inherit;
    color: #333;
    background: #fafafa;
    transition: border-color .2s, box-shadow .2s;
    appearance: auto;
}
.field input:focus,
.field select:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(75,40,130,.12);
    background: #fff;
}
.field label { display: block; font-size: .72rem; color: var(--muted); margin-top: .25rem; }
.btn-register {
    display: block;
    width: 160px;
    margin: 1.2rem auto 0;
    padding: .7rem;
    background: var(--purple);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    transition: background .2s, transform .15s;
    box-shadow: 0 4px 14px rgba(75,40,130,.28);
}
.btn-register:hover { background: var(--purple-dk); transform: translateY(-1px); }
.btn-register:active { transform: translateY(0); }
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
    background: #f0ecfa;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    width: 100%;
}
.mock-avatar {
    width: 40px;
    height: 40px;
    background: var(--purple);
    border-radius: 50%;
    margin: 0 auto .5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1rem;
}
.mock-dots { color: #b0a0d0; font-size: 1rem; letter-spacing: 3px; margin-bottom: .3rem; }
.mock-su {
    background: var(--danger);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: .25rem .7rem;
    font-size: .72rem;
    font-weight: 600;
    cursor: default;
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
            <h2>Sign up</h2>

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
                    <input type="text" name="id_number"
                           placeholder="e.g. 2024-00001"
                           value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required>
                    <label>ID Number *</label>
                </div>
                <div class="field">
                    <input type="text" name="last_name"
                           placeholder="Last Name"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    <label>Last Name *</label>
                </div>
                <div class="field">
                    <input type="text" name="first_name"
                           placeholder="First Name"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    <label>First Name *</label>
                </div>
                <div class="field">
                    <input type="text" name="middle_name"
                           placeholder="Middle Name (optional)"
                           value="<?= htmlspecialchars($_POST['middle_name'] ?? '') ?>">
                    <label>Middle Name</label>
                </div>
                <div class="field">
                    <select name="course_level">
                        <?php
                        $levels = ['1'=>'1st Year','2'=>'2nd Year','3'=>'3rd Year','4'=>'4th Year'];
                        $sel    = $_POST['course_level'] ?? '1';
                        foreach ($levels as $val => $label):
                        ?>
                        <option value="<?= $val ?>" <?= $sel == $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Course Level *</label>
                </div>
                <div class="field">
                    <input type="password" name="password"
                           placeholder="Password (min 6 chars)" required>
                    <label>Password *</label>
                </div>
                <div class="field">
                    <input type="password" name="repeat_password"
                           placeholder="Repeat your password" required>
                    <label>Repeat your password *</label>
                </div>
                <div class="field">
                    <input type="email" name="email"
                           placeholder="Email address"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <label>Email *</label>
                </div>
                <div class="field">
                    <input type="text" name="course"
                           placeholder="e.g. BSIT, BSCS, BSEMC"
                           value="<?= htmlspecialchars($_POST['course'] ?? 'BSIT') ?>" required>
                    <label>Course *</label>
                </div>
                <div class="field">
                    <input type="text" name="address"
                           placeholder="Home address"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                    <label>Address</label>
                </div>
                <button type="submit" class="btn-register">Register</button>
            </form>
        </div>

        <div class="reg-illus">
            <svg viewBox="0 0 200 230" width="190" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="80" cy="210" rx="35" ry="10" fill="#ddd" opacity=".4"/>
                <rect x="65" y="162" width="13" height="46" rx="6" fill="#E8A020"/>
                <rect x="83" y="167" width="13" height="41" rx="6" fill="#E8A020"/>
                <ellipse cx="72" cy="208" rx="13" ry="5" fill="#c0392b"/>
                <ellipse cx="90" cy="208" rx="10" ry="4" fill="#c0392b"/>
                <rect x="54" y="108" width="52" height="60" rx="13" fill="#7B5EA7"/>
                <rect x="33" y="113" width="23" height="11" rx="5" fill="#7B5EA7" transform="rotate(12 33 113)"/>
                <rect x="102" y="116" width="26" height="11" rx="5" fill="#7B5EA7" transform="rotate(-10 102 116)"/>
                <rect x="122" y="96" width="58" height="72" rx="8" fill="#fff" stroke="#4B2882" stroke-width="1.5"/>
                <rect x="127" y="105" width="48" height="7" rx="3" fill="#4B2882" opacity=".12"/>
                <rect x="127" y="118" width="48" height="5" rx="2.5" fill="#4B2882" opacity=".09"/>
                <rect x="127" y="129" width="48" height="5" rx="2.5" fill="#4B2882" opacity=".09"/>
                <rect x="127" y="140" width="48" height="5" rx="2.5" fill="#4B2882" opacity=".09"/>
                <rect x="132" y="153" width="36" height="11" rx="4" fill="#c0392b"/>
                <text x="150" y="161.5" font-size="5.5" fill="#fff" text-anchor="middle"
                      font-family="sans-serif" font-weight="700">Sign Up</text>
                <circle cx="150" cy="108" r="9" fill="#4B2882" opacity=".18"/>
                <circle cx="150" cy="105.5" r="3.5" fill="#4B2882" opacity=".45"/>
                <ellipse cx="150" cy="114" rx="5.5" ry="2.5" fill="#4B2882" opacity=".35"/>
                <circle cx="80" cy="89" r="22" fill="#FFDCB2"/>
                <ellipse cx="80" cy="72" rx="22" ry="9" fill="#4B2882"/>
                <rect x="58" y="72" width="44" height="13" fill="#4B2882"/>
                <circle cx="73" cy="89" r="2.5" fill="#333"/>
                <circle cx="87" cy="89" r="2.5" fill="#333"/>
                <path d="M73 98 Q80 104 87 98" fill="none" stroke="#c0392b"
                      stroke-width="1.5" stroke-linecap="round"/>
                <line x1="102" y1="83" x2="126" y2="83" stroke="#4B2882"
                      stroke-width="1.8" stroke-dasharray="4,2"/>
                <polygon points="126,80 132,83 126,86" fill="#4B2882"/>
            </svg>

            <div class="mock-card">
                <div class="mock-avatar">&#128100;</div>
                <div class="mock-dots">&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</div>
                <div class="mock-dots" style="color:#d0c0f0;font-size:.85rem;">
                    &#9679;&#9679;&#9679;&#9679;&#9679;&#9679;
                </div>
                <button class="mock-su">Sign Up</button>
            </div>
        </div>

    </div>
</main>

</body>
</html>