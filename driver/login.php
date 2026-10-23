<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

$error = '';
if (isset($_POST['login'])) {
    $did = trim($_POST['did']);
    $password = md5($_POST['password']);
    $sql = "SELECT ID, DriverID FROM tbldriver WHERE DriverID=:did AND Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':did', $did, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        session_regenerate_id(true);
        $_SESSION['vamsid']  = $result->ID;
        $_SESSION['vamsdid'] = $result->DriverID;
        $_SESSION['login']   = $did;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid Driver ID or password. Please try again.';
    }
}
$oldDid = isset($_POST['did']) ? htmlspecialchars($_POST['did'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login | Smart Scrap Management System</title>
    <link rel="icon" href="../img1/trash.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root{--g-900:#0d3b1e;--g-800:#1b5e20;--g-700:#2e7d32;--g-600:#388e3c;--g-200:#a5d6a7;--g-100:#c8e6c9;--g-50:#eef7ef;--lime:#9ccc65;--ink:#1c2b22;--muted:#5f6f65}
        *{box-sizing:border-box}
        body{font-family:'Inter',sans-serif;color:var(--ink);background:#f4f8f4;min-height:100vh;margin:0}
        h1,h2,h3{font-family:'Poppins',sans-serif;font-weight:700}
        .auth-wrap{min-height:100vh;display:flex}

        /* Left visual panel */
        .auth-visual{flex:1 1 50%;position:relative;color:#fff;display:flex;flex-direction:column;justify-content:space-between;padding:48px;overflow:hidden;
            background:linear-gradient(160deg,rgba(13,59,30,.92),rgba(27,94,32,.80)),var(--bg) center/cover}
        .auth-visual .brand{display:flex;align-items:center;gap:10px;color:#fff;text-decoration:none;font:800 1.5rem 'Poppins'}
        .auth-visual .brand img{width:44px;height:44px;background:#fff;border-radius:12px;padding:4px}
        .auth-visual h2{font-size:clamp(1.8rem,3vw,2.6rem);line-height:1.2;margin:0 0 16px;color:#fff}
        .auth-visual p{color:#d6ead8;max-width:440px}
        .perk{display:flex;gap:14px;align-items:flex-start;margin-top:18px}
        .perk i{width:42px;height:42px;flex-shrink:0;border-radius:12px;display:grid;place-items:center;background:rgba(255,255,255,.12);color:var(--lime)}
        .perk b{display:block;font-family:'Poppins'}
        .perk small{color:#cfe8d2}
        .auth-visual .foot{font-size:.85rem;color:#b9d6bd}

        /* Right form panel */
        .auth-form{flex:1 1 50%;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:#fff}
        .form-box{width:100%;max-width:420px}
        .role-badge{display:inline-flex;align-items:center;gap:8px;background:var(--g-100);color:var(--g-800);font:600 .8rem 'Poppins';text-transform:uppercase;letter-spacing:.1em;padding:7px 14px;border-radius:50px}
        .form-box h1{font-size:2rem;margin:16px 0 6px}
        .form-box .sub{color:var(--muted);margin-bottom:28px}
        .field{position:relative;margin-bottom:18px}
        .field label{font-weight:600;font-size:.9rem;margin-bottom:6px;display:block}
        .field .ic{position:absolute;left:16px;top:43px;color:var(--g-600)}
        .field input{width:100%;height:52px;border:1.5px solid #dbe6dc;border-radius:14px;padding:0 48px 0 46px;font-size:1rem;transition:.2s;background:#fafdfa}
        .field input:focus{outline:none;border-color:var(--g-600);box-shadow:0 0 0 4px rgba(56,142,60,.15);background:#fff}
        .toggle-pw{position:absolute;right:8px;top:35px;border:none;background:none;color:var(--muted);width:38px;height:38px;border-radius:10px}
        .toggle-pw:hover{background:var(--g-50);color:var(--g-700)}
        .form-check-input:checked{background-color:var(--g-700);border-color:var(--g-700)}
        .form-check-input:focus{box-shadow:0 0 0 3px rgba(56,142,60,.2);border-color:var(--g-600)}
        .link{color:var(--g-700);font-weight:600;text-decoration:none}
        .link:hover{color:var(--g-900);text-decoration:underline}
        .btn-login{width:100%;height:52px;border:none;border-radius:14px;background:linear-gradient(135deg,var(--g-600),var(--g-800));color:#fff;font:600 1.05rem 'Poppins';transition:.25s}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(46,125,50,.35)}
        .alert-err{background:#fdecec;color:#b42318;border:1px solid #f8c9c5;border-radius:12px;padding:12px 14px;font-size:.92rem;display:flex;gap:10px;align-items:center;margin-bottom:20px}
        .divider{display:flex;align-items:center;gap:12px;color:var(--muted);font-size:.85rem;margin:26px 0 16px}
        .divider::before,.divider::after{content:"";flex:1;height:1px;background:#e3ece4}
        .other-roles{display:flex;gap:10px}
        .other-roles a{flex:1;text-align:center;padding:10px;border:1.5px solid #dbe6dc;border-radius:12px;color:var(--ink);text-decoration:none;font-weight:500;font-size:.92rem;transition:.2s}
        .other-roles a:hover{border-color:var(--g-600);color:var(--g-700);background:var(--g-50)}
        .back-home{display:inline-flex;align-items:center;gap:8px;color:var(--muted);text-decoration:none;font-size:.92rem;margin-top:24px}
        .back-home:hover{color:var(--g-700)}

        /* Mobile header (replaces the left panel on small screens) */
        .mobile-head{display:none}
        @media(max-width:991px){
            .auth-wrap{flex-direction:column}
            .auth-visual{display:none}
            .mobile-head{display:block;background:linear-gradient(160deg,rgba(13,59,30,.92),rgba(27,94,32,.85)),var(--bg) center/cover;color:#fff;padding:28px 20px 70px;text-align:center}
            .mobile-head .brand{display:inline-flex;align-items:center;gap:10px;color:#fff;text-decoration:none;font:800 1.35rem 'Poppins'}
            .mobile-head .brand img{width:38px;height:38px;background:#fff;border-radius:10px;padding:3px}
            .mobile-head p{color:#d6ead8;margin:8px 0 0;font-size:.92rem}
            .auth-form{background:transparent;padding:0 16px 40px;margin-top:-48px}
            .form-box{background:#fff;border-radius:22px;padding:30px 22px;box-shadow:0 14px 40px rgba(13,59,30,.12)}
            .form-box h1{font-size:1.6rem}
        }
    </style>
</head>
<body>
<?php $bg = "https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1400&q=70"; ?>
<div class="auth-wrap" style="--bg:url('<?php echo $bg; ?>')">

    <!-- Mobile header -->
    <div class="mobile-head">
        <a class="brand" href="../index.php"><img src="../img1/trash.png" alt="">SSMS</a>
        <p>Smart Scrap Management System</p>
    </div>

    <!-- Left panel (desktop) -->
    <div class="auth-visual">
        <a class="brand" href="../index.php"><img src="../img1/trash.png" alt="">SSMS</a>
        <div>
            <h2>Driver workspace</h2>
            <p>Everything you need for today's pickups, in one place.</p>
            <div class="perk"><i class="fa-solid fa-route"></i><div><b>Assigned pickups</b><small>See every pickup the admin has given you</small></div></div>
            <div class="perk"><i class="fa-solid fa-scale-balanced"></i><div><b>Collect and weigh</b><small>Segregate, weigh and pack the scrap</small></div></div>
            <div class="perk"><i class="fa-solid fa-indian-rupee-sign"></i><div><b>Update payment</b><small>Mark status and record payment details</small></div></div>
        </div>
        <div class="foot">&copy; <?php echo date('Y'); ?> Smart Scrap Management System</div>
    </div>

    <!-- Form -->
    <div class="auth-form">
        <div class="form-box">
            <span class="role-badge"><i class="fa-solid fa-truck"></i> Driver Area</span>
            <h1>Welcome back</h1>
            <p class="sub">Log in with the Driver ID given by the admin.</p>

            <?php if ($error): ?>
                <div class="alert-err"><i class="fa-solid fa-circle-exclamation"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="on" novalidate>
                <div class="field">
                    <label for="did">Driver ID</label>
                    <i class="fa-solid fa-id-card ic"></i>
                    <input type="text" id="did" name="did" placeholder="Enter your Driver ID" value="<?php echo $oldDid; ?>" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <i class="fa-solid fa-lock ic"></i>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <button type="button" class="toggle-pw" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                </div>
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <a class="link" href="forgot-password.php">Forgot password?</a>
                </div>
                <button type="submit" name="login" class="btn-login"><i class="fa-solid fa-right-to-bracket me-2"></i>Log In</button>
            </form>

            <div class="divider">Not a driver?</div>
            <div class="other-roles">
                <a href="../admin/login.php"><i class="fa-solid fa-user-shield me-1"></i> Admin</a>
                <a href="../user/login.php"><i class="fa-solid fa-user me-1"></i> User</a>
            </div>
            <a class="back-home" href="../index.php"><i class="fa-solid fa-arrow-left"></i> Back to home</a>
        </div>
    </div>
</div>

<script>
    // Show / hide password
    document.querySelectorAll('.toggle-pw').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
    }));
    // Simple required-field check
    document.querySelector('form').addEventListener('submit', e => {
        const empty = [...e.target.querySelectorAll('input[required]')].find(i => !i.value.trim());
        if (empty) { e.preventDefault(); empty.focus(); empty.style.borderColor = '#d92d20'; }
    });
</script>
</body>
</html>