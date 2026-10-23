<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

/* ---------- Fetch Contact Us details once ---------- */
$contactEmail = 'info@SSMS.in';
$contactPhone = '';
$contactAddr  = 'Chennai, Tamil Nadu';
try {
    $q = $dbh->prepare("SELECT * FROM tblpage WHERE PageType='contactus' LIMIT 1");
    $q->execute();
    if ($c = $q->fetch(PDO::FETCH_OBJ)) {
        $contactEmail = $c->Email;
        $contactPhone = $c->MobileNumber;
        // Address may contain editor HTML – keep it plain text
        $contactAddr  = trim(strip_tags(html_entity_decode($c->PageDescription))) ?: $contactAddr;
    }
} catch (Exception $e) {}

/* ---------- Fetch About Us (managed from admin) ---------- */
$aboutTitle = 'About Us';
$aboutDesc  = '';
try {
    $q1 = $dbh->prepare("SELECT * FROM tblpage WHERE PageType='aboutus' LIMIT 1");
    $q1->execute();
    if ($a = $q1->fetch(PDO::FETCH_OBJ)) {
        $aboutTitle = $a->PageTitle;
        /* The admin rich-text editor saves <font color="#000000"> and inline styles.
           That black text became invisible on the dark footer. We keep only safe
           structural tags so the content always follows this page's styling. */
        $aboutDesc = strip_tags($a->PageDescription, '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><h4><div>');
        $aboutDesc = preg_replace('/\s(style|class|color|face|size)=("[^"]*"|\'[^\']*\')/i', '', $aboutDesc);
    }
} catch (Exception $e) {}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$phoneDisplay = $contactPhone ? '+' . ltrim($contactPhone, '+') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSMS | Smart Scrap Management System – Doorstep Scrap Pickup in Chennai</title>
    <meta name="description" content="Door-to-door scrap collection service in Chennai. Sell paper, plastic, metal, e-waste and more with digital weighing, fixed prices and instant payment.">
    <meta name="keywords" content="scrap collection, recycling, waste management, Chennai, sell scrap online, kabadiwala">
    <meta name="theme-color" content="#1b5e20">
    <link href="img1/trash.png" rel="icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5, Font Awesome 6, AOS (scroll animations) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root{
            --g-900:#0d3b1e;  --g-800:#1b5e20; --g-700:#2e7d32; --g-600:#388e3c;
            --g-500:#43a047;  --g-400:#66bb6a; --g-200:#a5d6a7; --g-100:#c8e6c9; --g-50:#eef7ef;
            --lime:#9ccc65;   --ink:#1c2b22;   --muted:#5f6f65; --sand:#fbfcf8;
            --radius:18px;    --shadow:0 10px 30px rgba(13,59,30,.10);
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{font-family:'Inter',sans-serif;color:var(--ink);background:var(--sand);overflow-x:hidden}
        h1,h2,h3,h4,h5,h6,.font-head{font-family:'Poppins',sans-serif;font-weight:700;color:var(--ink)}
        a{text-decoration:none}
        img{max-width:100%}
        section{position:relative}
        .py-section{padding:96px 0}
        @media(max-width:768px){.py-section{padding:64px 0}}

        /* Helpers */
        .text-green{color:var(--g-700)!important}
        .bg-green-50{background:var(--g-50)}
        .eyebrow{display:inline-flex;align-items:center;gap:8px;font:600 .8rem 'Poppins';letter-spacing:.12em;text-transform:uppercase;color:var(--g-700);background:var(--g-100);padding:6px 14px;border-radius:50px;margin-bottom:14px}
        .section-title{font-size:clamp(1.8rem,3.4vw,2.6rem);line-height:1.2;margin-bottom:14px}
        .section-sub{color:var(--muted);max-width:640px;margin:0 auto}
        .btn-green{background:var(--g-700);color:#fff;border:2px solid var(--g-700);border-radius:50px;padding:13px 30px;font-weight:600;transition:.3s}
        .btn-green:hover{background:var(--g-800);border-color:var(--g-800);color:#fff;transform:translateY(-2px);box-shadow:0 8px 20px rgba(46,125,50,.35)}
        .btn-outline-green{border:2px solid var(--g-700);color:var(--g-700);border-radius:50px;padding:13px 30px;font-weight:600;transition:.3s;background:transparent}
        .btn-outline-green:hover{background:var(--g-700);color:#fff}
        .btn-white{background:#fff;color:var(--g-800);border-radius:50px;padding:13px 30px;font-weight:600;transition:.3s}
        .btn-white:hover{background:var(--g-50);color:var(--g-900);transform:translateY(-2px)}

        /* Notice bar */
        .notice-bar{background:linear-gradient(90deg,var(--g-900),var(--g-700),var(--g-900));color:#fff;font-size:.88rem;overflow:hidden;white-space:nowrap}
        .notice-track{display:inline-block;padding:8px 0;animation:ticker 28s linear infinite}
        .notice-track span{margin:0 40px}
        .notice-track i{color:var(--lime);margin-right:8px}
        .notice-bar:hover .notice-track{animation-play-state:paused}
        @keyframes ticker{from{transform:translateX(0)}to{transform:translateX(-50%)}}

        /* Top bar */
        .topbar{background:var(--g-900);color:#cfe8d2;font-size:.85rem}
        .topbar a{color:#cfe8d2}
        .topbar i{color:var(--lime)}
        .social a{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:rgba(255,255,255,.08);color:#fff;margin-left:6px;transition:.3s}
        .social a:hover{background:var(--lime);color:var(--g-900)}

        /* Navbar */
        .navbar{background:rgba(255,255,255,.96);backdrop-filter:blur(10px);transition:.3s;padding:12px 0}
        .navbar.scrolled{box-shadow:0 6px 24px rgba(0,0,0,.08);padding:6px 0}
        .navbar-brand{font:800 1.5rem 'Poppins';color:var(--g-800)!important;display:flex;align-items:center;gap:8px}
        .navbar-brand img{width:44px;height:44px}
        .navbar-brand small{display:block;font:500 .65rem 'Inter';color:var(--muted);letter-spacing:.08em;text-transform:uppercase;margin-top:-4px}
        .nav-link{font-weight:500;color:var(--ink)!important;padding:8px 14px!important;border-radius:50px;transition:.25s}
        .nav-link:hover,.nav-link.active{color:var(--g-700)!important;background:var(--g-50)}
        .navbar-toggler{border:none;box-shadow:none!important}

        /* Hero */
        .hero{background:radial-gradient(1200px 600px at 85% 10%,var(--g-100),transparent 60%),linear-gradient(180deg,#f3faf3,var(--sand));padding:70px 0 90px;overflow:hidden}
        .hero h1{font-size:clamp(2.2rem,5vw,3.8rem);font-weight:800;line-height:1.1;letter-spacing:-.02em}
        .hero h1 .hl{color:var(--g-700);position:relative;white-space:nowrap}
        .hero h1 .hl::after{content:"";position:absolute;left:0;right:0;bottom:6px;height:12px;background:var(--g-200);z-index:-1;border-radius:6px}
        .hero p.lead{color:var(--muted);font-size:1.12rem;max-width:540px}
        .hero-badges{display:flex;flex-wrap:wrap;gap:18px;margin-top:30px}
        .hero-badges div{display:flex;align-items:center;gap:8px;font-weight:500;font-size:.95rem}
        .hero-badges i{color:var(--g-600);font-size:1.1rem}
        .hero-visual{position:relative;max-width:520px;margin-left:auto}
        .hero-visual .main-img{width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:200px 200px 24px 24px;box-shadow:0 30px 60px rgba(13,59,30,.25);border:8px solid #fff}
        .hero-visual .blob{position:absolute;width:260px;height:260px;background:var(--g-400);opacity:.18;border-radius:50%;top:-30px;left:-50px;z-index:-1}
        .float-card{position:absolute;background:#fff;border-radius:16px;padding:14px 18px;box-shadow:var(--shadow);display:flex;align-items:center;gap:12px;animation:floaty 5s ease-in-out infinite}
        .float-card .ic{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:var(--g-100);color:var(--g-700);font-size:1.2rem}
        .float-card b{display:block;font-family:'Poppins';font-size:1.05rem}
        .float-card small{color:var(--muted)}
        .fc-1{bottom:60px;left:-40px}
        .fc-2{top:50px;right:-30px;animation-delay:1.5s}
        @keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
        @media(max-width:991px){.hero-visual{margin:50px auto 0}.fc-1{left:0}.fc-2{right:0}}

        /* Stats band */
        .stats-band{background:var(--g-800);color:#fff;border-radius:24px;padding:40px 20px;margin-top:-50px;position:relative;z-index:3;box-shadow:0 20px 40px rgba(13,59,30,.25)}
        .stat h3{color:#fff;font-size:2.4rem;font-weight:800;margin:0}
        .stat p{margin:0;color:var(--g-200);font-size:.95rem}
        .stat + .stat{border-left:1px solid rgba(255,255,255,.15)}
        @media(max-width:767px){.stat + .stat{border-left:none}.stat{padding:12px 0}}

        /* Feature cards */
        .feature-card{background:#fff;border-radius:var(--radius);padding:34px 28px;height:100%;box-shadow:var(--shadow);border:1px solid #e6efe7;transition:.35s;position:relative;overflow:hidden}
        .feature-card::before{content:"";position:absolute;inset:auto 0 0 0;height:4px;background:linear-gradient(90deg,var(--g-600),var(--lime));transform:scaleX(0);transform-origin:left;transition:.4s}
        .feature-card:hover{transform:translateY(-8px)}
        .feature-card:hover::before{transform:scaleX(1)}
        .feature-icon{width:72px;height:72px;border-radius:20px;background:var(--g-50);display:grid;place-items:center;margin-bottom:22px}
        .feature-icon img{width:44px;height:44px;object-fit:contain}
        .feature-icon i{font-size:1.8rem;color:var(--g-700)}
        .feature-card .num{position:absolute;top:18px;right:24px;font:800 3rem 'Poppins';color:var(--g-50)}
        .feature-card p{color:var(--muted);margin:0}

        /* How it works – timeline */
        .how{background:var(--g-900);color:#fff;overflow:hidden}
        .how h2,.how h4{color:#fff}
        .how .eyebrow{background:rgba(156,204,101,.15);color:var(--lime)}
        .how .section-sub{color:#b9d6bd}
        .timeline{position:relative;margin-top:60px}
        .timeline::before{content:"";position:absolute;left:50%;top:0;bottom:0;width:3px;background:linear-gradient(var(--lime),var(--g-500));transform:translateX(-50%)}
        .t-item{display:flex;align-items:center;margin-bottom:60px;position:relative}
        .t-item:last-child{margin-bottom:0}
        .t-item .t-media,.t-item .t-body{width:50%}
        .t-item .t-media{padding-right:60px}
        .t-item .t-body{padding-left:60px}
        .t-item.reverse{flex-direction:row-reverse}
        .t-item.reverse .t-media{padding:0 0 0 60px}
        .t-item.reverse .t-body{padding:0 60px 0 0;text-align:right}
        .t-item.reverse .t-list{justify-content:flex-end}
        .t-media img{width:100%;height:260px;object-fit:cover;border-radius:var(--radius);box-shadow:0 20px 40px rgba(0,0,0,.35)}
        .t-dot{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:58px;height:58px;border-radius:50%;background:var(--lime);color:var(--g-900);display:grid;place-items:center;font:800 1.3rem 'Poppins';border:6px solid var(--g-900);box-shadow:0 0 0 3px var(--lime);z-index:2}
        .t-body .tag{color:var(--lime);font-weight:600;font-size:.85rem;text-transform:uppercase;letter-spacing:.1em}
        .t-body p{color:#c8dfcb}
        .t-list{display:flex;flex-wrap:wrap;gap:8px;list-style:none;padding:0;margin:0}
        .t-list li{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);padding:6px 14px;border-radius:50px;font-size:.85rem}
        .t-list i{color:var(--lime);margin-right:6px}
        @media(max-width:991px){
            .timeline::before{left:28px}
            .t-item,.t-item.reverse{flex-direction:column;align-items:stretch;padding-left:70px}
            .t-item .t-media,.t-item .t-body,.t-item.reverse .t-media,.t-item.reverse .t-body{width:100%;padding:0;text-align:left}
            .t-item.reverse .t-list{justify-content:flex-start}
            .t-media{margin-bottom:20px}
            .t-media img{height:200px}
            .t-dot{left:28px;top:30px}
        }

        /* Materials */
        .material{position:relative;border-radius:var(--radius);overflow:hidden;height:340px;box-shadow:var(--shadow);cursor:default}
        .material img{width:100%;height:100%;object-fit:cover;transition:transform .6s}
        .material:hover img{transform:scale(1.08)}
        .material .ov{position:absolute;inset:0;background:linear-gradient(180deg,transparent 35%,rgba(13,59,30,.92));display:flex;flex-direction:column;justify-content:flex-end;padding:24px;color:#fff}
        .material .ov h4{color:#fff;margin-bottom:6px}
        .material .ov p{font-size:.9rem;color:#d8ecd9;margin:0;max-height:0;opacity:0;overflow:hidden;transition:.45s}
        .material:hover .ov p,.material:focus-within .ov p{max-height:120px;opacity:1;margin-top:6px}
        .material .chip{position:absolute;top:16px;left:16px;background:#fff;color:var(--g-800);font-weight:600;font-size:.78rem;padding:5px 12px;border-radius:50px}
        @media(hover:none){.material .ov p{max-height:120px;opacity:1}}

        /* Impact parallax */
        .impact{background:linear-gradient(rgba(13,59,30,.82),rgba(13,59,30,.88)),var(--impact-bg) center/cover fixed;color:#fff}
        @media(max-width:991px){.impact{background-attachment:scroll}}
        .impact h2{color:#fff}
        .impact .section-sub{color:#cfe8d2}
        .impact-card{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);backdrop-filter:blur(6px);border-radius:var(--radius);padding:30px;text-align:center;height:100%;transition:.3s}
        .impact-card:hover{background:rgba(255,255,255,.14);transform:translateY(-6px)}
        .impact-card i{font-size:2.2rem;color:var(--lime);margin-bottom:14px}
        .impact-card h3{color:#fff;font-size:2.2rem;margin:0}
        .impact-card p{color:#cfe8d2;margin:0}

        /* Portals (User / Driver / Admin) */
        .portal{background:#fff;border-radius:24px;padding:36px 30px;height:100%;box-shadow:var(--shadow);border:1px solid #e6efe7;transition:.35s;display:flex;flex-direction:column}
        .portal:hover{transform:translateY(-8px);box-shadow:0 24px 50px rgba(13,59,30,.16)}
        .portal .p-ic{width:70px;height:70px;border-radius:50%;display:grid;place-items:center;font-size:1.7rem;margin-bottom:20px;color:#fff}
        .portal.user .p-ic{background:linear-gradient(135deg,var(--g-500),var(--g-700))}
        .portal.driver .p-ic{background:linear-gradient(135deg,#7cb342,#558b2f)}
        .portal.admin .p-ic{background:linear-gradient(135deg,var(--g-800),var(--g-900))}
        .portal ul{list-style:none;padding:0;margin:16px 0 26px;flex-grow:1}
        .portal li{padding:7px 0;color:var(--muted);border-bottom:1px dashed #e3ece4}
        .portal li i{color:var(--g-600);margin-right:10px}
        .portal.featured{background:var(--g-800);color:#fff;border-color:var(--g-800)}
        .portal.featured h4{color:#fff}
        .portal.featured li{color:#d6ead8;border-color:rgba(255,255,255,.15)}
        .portal.featured li i{color:var(--lime)}
        .portal.featured .p-ic{background:var(--lime);color:var(--g-900)}

        /* Green reward split */
        .reward{border-radius:28px;overflow:hidden;background:var(--g-100)}
        .reward img{width:100%;height:100%;min-height:340px;object-fit:cover}
        .reward .content{padding:50px}
        @media(max-width:767px){.reward .content{padding:32px 24px}}
        .check-list{list-style:none;padding:0}
        .check-list li{padding:6px 0;display:flex;gap:12px}
        .check-list i{color:var(--g-700);margin-top:4px}

        /* Rules / pickup info cards */
        .info-card{border-radius:var(--radius);padding:28px;background:#fff;border-left:5px solid var(--g-600);box-shadow:var(--shadow);height:100%}
        .info-card i{font-size:1.5rem;color:var(--g-700);margin-bottom:12px}
        .info-card p{color:var(--muted);margin:0}

        /* FAQ */
        .accordion-item{border:none;border-radius:14px!important;margin-bottom:14px;overflow:hidden;box-shadow:0 4px 16px rgba(13,59,30,.06)}
        .accordion-button{font-weight:600;font-family:'Poppins';padding:20px 24px}
        .accordion-button:not(.collapsed){background:var(--g-700);color:#fff;box-shadow:none}
        .accordion-button:not(.collapsed)::after{filter:brightness(0) invert(1)}
        .accordion-button:focus{box-shadow:0 0 0 3px var(--g-200)}
        .accordion-body{color:var(--muted)}
        .faq-side{background:var(--g-800);color:#fff;border-radius:24px;padding:40px;height:100%;position:relative;overflow:hidden}
        .faq-side h3{color:#fff}
        .faq-side p{color:#cfe8d2}
        .faq-side .big-ic{position:absolute;right:-20px;bottom:-30px;font-size:10rem;color:rgba(255,255,255,.06)}

        /* About */
        .about-img-wrap{position:relative}
        .about-img-wrap .a1{width:80%;border-radius:24px;height:420px;object-fit:cover;box-shadow:var(--shadow)}
        .about-img-wrap .a2{position:absolute;width:52%;height:240px;object-fit:cover;right:0;bottom:-40px;border-radius:20px;border:8px solid var(--sand);box-shadow:var(--shadow)}
        .about-img-wrap .exp{position:absolute;left:20px;bottom:20px;background:var(--g-700);color:#fff;border-radius:18px;padding:18px 22px;text-align:center}
        .about-img-wrap .exp b{display:block;font:800 2rem 'Poppins'}
        @media(max-width:991px){.about-img-wrap{margin-bottom:70px}.about-img-wrap .a1{height:320px}.about-img-wrap .a2{height:180px}}
        .about-content{color:var(--muted);font-size:1.02rem;line-height:1.75}
        .about-content h2,.about-content h3,.about-content h4{font-size:1.3rem;color:var(--g-800);margin:0 0 12px}
        .about-content *{color:inherit;font-family:inherit!important;font-size:inherit}
        .about-content h2,.about-content h3,.about-content h4{color:var(--g-800)!important;font-size:1.3rem!important}

        /* CTA */
        .cta{background:linear-gradient(120deg,var(--g-700),var(--g-900));border-radius:28px;padding:60px 40px;color:#fff;position:relative;overflow:hidden}
        .cta h2{color:#fff}
        .cta p{color:#d6ead8}
        .cta::before,.cta::after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.06)}
        .cta::before{width:300px;height:300px;top:-120px;right:-80px}
        .cta::after{width:200px;height:200px;bottom:-90px;left:-60px}
        @media(max-width:767px){.cta{padding:44px 24px}}

        /* Footer */
        footer{background:var(--g-900);color:#b9d6bd;padding-top:80px}
        footer h5{color:#fff;margin-bottom:22px;font-size:1.1rem}
        footer a{color:#b9d6bd;transition:.25s}
        footer a:hover{color:var(--lime);padding-left:4px}
        footer .f-links a{display:block;padding:5px 0}
        footer .f-links i{font-size:.7rem;margin-right:8px;color:var(--lime)}
        footer .contact-line{display:flex;gap:12px;margin-bottom:14px}
        footer .contact-line i{color:var(--lime);margin-top:4px}
        footer .copy{border-top:1px solid rgba(255,255,255,.1);padding:22px 0;margin-top:50px;font-size:.9rem}

        /* Back to top */
        .back-top{position:fixed;right:22px;bottom:22px;width:48px;height:48px;border-radius:50%;background:var(--g-700);color:#fff;display:grid;place-items:center;box-shadow:0 8px 20px rgba(46,125,50,.4);opacity:0;visibility:hidden;transition:.3s;z-index:99}
        .back-top.show{opacity:1;visibility:visible}
        .back-top:hover{background:var(--g-900);color:#fff}

        /* Page loader */
        #loader{position:fixed;inset:0;background:#fff;display:grid;place-items:center;z-index:9999;transition:opacity .4s}
        #loader.hide{opacity:0;pointer-events:none}
        /* Scroll animations slide items in from the side. Clip each section so the
           sliding items never make the page wider than the phone screen. */
        section,footer{overflow-x:hidden;overflow-x:clip}
        /* phones: smaller column gap so nothing sticks out sideways */
        @media(max-width:575px){.row.g-5{--bs-gutter-x:1.5rem}}
        .leaf-spin{width:56px;height:56px;border:5px solid var(--g-100);border-top-color:var(--g-700);border-radius:50%;animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
    </style>
</head>

<body>
<!-- Loader -->
<div id="loader"><div class="leaf-spin"></div></div>

<!-- Notice bar -->
<div class="notice-bar" aria-label="Pickup notice">
    <div class="notice-track">
        <?php for($i=0;$i<2;$i++): ?>
        <span><i class="fa-solid fa-circle-info"></i>Minimum pickup: 8 kg and above / order value ₹500</span>
        <span><i class="fa-solid fa-scale-balanced"></i>Digital weighing at your doorstep</span>
        <span><i class="fa-solid fa-seedling"></i>Recycle with us and get a free plant</span>
        <span><i class="fa-solid fa-indian-rupee-sign"></i>Instant payment, no bargaining</span>
        <?php endfor; ?>
    </div>
</div>

<!-- Top bar -->
<div class="topbar d-none d-lg-block">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <div class="d-flex gap-4">
            <span><i class="fa-solid fa-location-dot me-2"></i>Chennai, Tamil Nadu</span>
            <a href="mailto:<?php echo e($contactEmail); ?>"><i class="fa-regular fa-envelope me-2"></i><?php echo e($contactEmail); ?></a>
            <?php if($phoneDisplay): ?>
            <a href="tel:<?php echo e($phoneDisplay); ?>"><i class="fa-solid fa-phone me-2"></i><?php echo e($phoneDisplay); ?></a>
            <?php endif; ?>
        </div>
        <div class="social">
            <a href="#" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="img1/trash.png" alt="SSMS logo">
            <span>SSMS<small>Smart Scrap System</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Toggle menu">
            <i class="fa-solid fa-bars fs-3 text-green"></i>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 py-3 py-lg-0">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#materials">What We Buy</a></li>
                <li class="nav-item"><a class="nav-link" href="price_list.php">Price List</a></li>
                <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Login</a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-4 p-2">
                        <li><a class="dropdown-item rounded-3 py-2" href="user/login.php"><i class="fa-solid fa-user text-green me-2"></i>User</a></li>
                        <li><a class="dropdown-item rounded-3 py-2" href="driver/login.php"><i class="fa-solid fa-truck text-green me-2"></i>Driver</a></li>
                        <li><a class="dropdown-item rounded-3 py-2" href="admin/login.php"><i class="fa-solid fa-user-shield text-green me-2"></i>Admin</a></li>
                    </ul>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-green py-2 px-4" href="user/login.php"><i class="fa-solid fa-truck-fast me-2"></i>Book Pickup</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="eyebrow"><i class="fa-solid fa-leaf"></i> Chennai's doorstep scrap service</span>
                <h1 class="mb-4">Turn your <span class="hl">scrap</span> into cash, the green way.</h1>
                <p class="lead mb-4">Book a pickup online, and our team comes to your door, weighs your scrap on a digital scale and pays you instantly at fixed prices. No bargaining, no mess.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="user/login.php" class="btn btn-green"><i class="fa-solid fa-calendar-check me-2"></i>Schedule Pickup</a>
                    <a href="price_list.php" class="btn btn-outline-green"><i class="fa-solid fa-tags me-2"></i>Today's Prices</a>
                </div>
                <div class="hero-badges">
                    <div><i class="fa-solid fa-circle-check"></i>Digital weighing</div>
                    <div><i class="fa-solid fa-circle-check"></i>Instant payment</div>
                    <div><i class="fa-solid fa-circle-check"></i>Free plants</div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="150">
                <div class="hero-visual">
                    <div class="blob"></div>
                    <img class="main-img api-img" data-kw="recycling,plastic"
                         src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=900&q=80"
                         alt="Sorted recyclable waste">
                    <div class="float-card fc-1">
                        <div class="ic"><i class="fa-solid fa-truck-fast"></i></div>
                        <div><b>Doorstep Pickup</b><small>At your chosen time</small></div>
                    </div>
                    <div class="float-card fc-2">
                        <div class="ic"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                        <div><b>Paid Instantly</b><small>Fixed, fair rates</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats band -->
<div class="container">
    <div class="stats-band" data-aos="zoom-in">
        <div class="row text-center g-0">
            <div class="col-6 col-md-3 stat"><h3><span class="counter" data-target="1200">0</span>+</h3><p>Happy customers</p></div>
            <div class="col-6 col-md-3 stat"><h3><span class="counter" data-target="1500">0</span>+</h3><p>Pickups completed</p></div>
            <div class="col-6 col-md-3 stat"><h3><span class="counter" data-target="10">0</span>+</h3><p>Material types</p></div>
            <div class="col-6 col-md-3 stat"><h3><span class="counter" data-target="24">0</span>/7</h3><p>Customer support</p></div>
        </div>
    </div>
</div>

<!-- ================= WHY CHOOSE US ================= -->
<section class="py-section">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="eyebrow"><i class="fa-solid fa-star"></i> Why SSMS</span>
            <h2 class="section-title">Recycling made simple and honest</h2>
            <p class="section-sub">We built a system that is transparent for customers, easy for drivers and fully manageable by the admin.</p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-card"><span class="num">01</span>
                    <div class="feature-icon"><img src="img1/weighingmachine.png" alt=""></div>
                    <h5>Accurate Weighing</h5><p>Calibrated digital scales, so every gram is counted in front of you.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card"><span class="num">02</span>
                    <div class="feature-icon"><img src="img1/trashtruck.png" alt=""></div>
                    <h5>Doorstep Recycling</h5><p>We come to your home or office at the date and time you choose.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card"><span class="num">03</span>
                    <div class="feature-icon"><img src="img1/plant.png" alt=""></div>
                    <h5>Plants for Trash</h5><p>Get a free sapling when you recycle with us and help green the city.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card"><span class="num">04</span>
                    <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
                    <h5>24/7 Support</h5><p>Questions about a pickup or payment? Our support team is always available.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= HOW IT WORKS (timeline) ================= -->
<section class="how py-section" id="how">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <span class="eyebrow"><i class="fa-solid fa-route"></i> Process</span>
            <h2 class="section-title">How it works in 4 easy steps</h2>
            <p class="section-sub">From booking to payment, the whole journey is tracked inside the system.</p>
        </div>

        <div class="timeline">
            <div class="t-item" data-aos="fade-up">
                <div class="t-media"><img class="api-img" data-kw="smartphone,booking" src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=900&q=80" alt="Booking a pickup on a phone"></div>
                <div class="t-dot">1</div>
                <div class="t-body">
                    <span class="tag">Just click and order</span>
                    <h4 class="my-2">Schedule a Pickup</h4>
                    <p>Log in, fill in your pickup details and submit the request. That's it. We take care of the rest.</p>
                    <ul class="t-list"><li><i class="fa-solid fa-check"></i>Open pickup request</li><li><i class="fa-solid fa-check"></i>Fill details</li><li><i class="fa-solid fa-check"></i>Submit</li></ul>
                </div>
            </div>
            <div class="t-item reverse" data-aos="fade-up">
                <div class="t-media"><img class="api-img" data-kw="truck,delivery" src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=900&q=80" alt="Pickup truck on the way"></div>
                <div class="t-dot">2</div>
                <div class="t-body">
                    <span class="tag">Here comes SSMS</span>
                    <h4 class="my-2">Driver Assigned</h4>
                    <p>The admin assigns a driver to your request, and they arrive at your scheduled time and location.</p>
                    <ul class="t-list"><li><i class="fa-solid fa-check"></i>Admin assigns driver</li><li><i class="fa-solid fa-check"></i>Relax, we're coming</li></ul>
                </div>
            </div>
            <div class="t-item" data-aos="fade-up">
                <div class="t-media"><img class="api-img" data-kw="cardboard,recycling" src="https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?auto=format&fit=crop&w=900&q=80" alt="Segregating scrap"></div>
                <div class="t-dot">3</div>
                <div class="t-body">
                    <span class="tag">Segregate and weigh</span>
                    <h4 class="my-2">We Sort and Weigh</h4>
                    <p>Show our driver your scrap. They segregate it, weigh it on a digital scale, then pack and pick it up.</p>
                    <ul class="t-list"><li><i class="fa-solid fa-check"></i>Segregate</li><li><i class="fa-solid fa-check"></i>Weigh</li><li><i class="fa-solid fa-check"></i>Pack and pick</li></ul>
                </div>
            </div>
            <div class="t-item reverse" data-aos="fade-up">
                <div class="t-media"><img class="api-img" data-kw="money,payment" src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=900&q=80" alt="Instant payment"></div>
                <div class="t-dot">4</div>
                <div class="t-body">
                    <span class="tag">Get paid instantly</span>
                    <h4 class="my-2">Instant Payment</h4>
                    <p>Prices are fixed when you place the order, so there's no bargaining. The driver updates the status and payment in the system.</p>
                    <ul class="t-list"><li><i class="fa-solid fa-check"></i>Fixed rates</li><li><i class="fa-solid fa-check"></i>Paid on the spot</li></ul>
                </div>
            </div>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="user/login.php" class="btn btn-white"><i class="fa-solid fa-truck-fast me-2"></i>Schedule Your Pickup Now</a>
        </div>
    </div>
</section>

<!-- ================= MATERIALS ================= -->
<section class="py-section" id="materials">
    <div class="container">
        <div class="row align-items-end mb-5 g-3">
            <div class="col-lg-7" data-aos="fade-right">
                <span class="eyebrow"><i class="fa-solid fa-recycle"></i> What we buy</span>
                <h2 class="section-title mb-0">Materials we recycle</h2>
            </div>
            <div class="col-lg-5 text-lg-end" data-aos="fade-left">
                <p class="text-muted mb-2">We accept a wide range of recyclables to keep waste out of landfills.</p>
                <a href="price_list.php" class="fw-semibold text-green">See full price list <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
        </div>
        <div class="row g-4">
            <?php
            $materials = [
                ['Paper',       'Newspapers, books, office paper',  'Recycled into new paper goods, saving trees and reducing landfill waste.', '1503596476-1c12a8ba09a9', 'newspaper,paper'],
                ['Plastic',     'Bottles, containers, packaging',   'Turned into new plastic products, cutting pollution and saving petroleum.', '1604187351574-c75ca79f5807', 'plastic,bottles'],
                ['Metal',       'Iron, steel, aluminium, copper',   'Melted and reused in manufacturing, which saves huge amounts of energy.', '1558618666-fcd25c85cd64', 'scrap,metal'],
                ['Cardboard',   'Cartons, boxes, packing sheets',   'Pulped and remade into new boxes, again and again.', '1607166452427-7e4477079cb9', 'cardboard,boxes'],
                ['E-Waste',     'Phones, laptops, cables, boards',  'Safely dismantled so precious metals are recovered and toxins stay out of soil.', '1550009158-9ebf69173e03', 'electronics,circuit'],
                ['Appliances',  'Fridges, washing machines, ACs',   'Large appliances collected and broken down into reusable parts.', '1574269909862-7e1d70bb8078', 'appliance,washing-machine'],
            ];
            $d = 0;
            foreach ($materials as $m): ?>
            <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $d; $d = ($d + 100) % 300; ?>">
                <div class="material" tabindex="0">
                    <img class="api-img" data-kw="<?php echo $m[4]; ?>" loading="lazy"
                         src="https://images.unsplash.com/photo-<?php echo $m[3]; ?>?auto=format&fit=crop&w=800&q=75" alt="<?php echo $m[0]; ?> scrap">
                    <span class="chip"><i class="fa-solid fa-recycle me-1"></i>Accepted</span>
                    <div class="ov">
                        <h4><?php echo $m[0]; ?></h4>
                        <small class="text-white-50"><?php echo $m[1]; ?></small>
                        <p><?php echo $m[2]; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= IMPACT (parallax) ================= -->
<section class="impact py-section" style="--impact-bg:url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1800&q=70')">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="eyebrow" style="background:rgba(156,204,101,.15);color:var(--lime)"><i class="fa-solid fa-earth-asia"></i> Our impact</span>
            <h2 class="section-title">Every kilo you recycle makes a difference</h2>
            <p class="section-sub">Small actions at home add up to a cleaner Chennai.</p>
        </div>
        <div class="row g-4">
            <div class="col-6 col-lg-3" data-aos="flip-left"><div class="impact-card"><i class="fa-solid fa-tree"></i><h3>17</h3><p>trees saved per tonne of paper recycled</p></div></div>
            <div class="col-6 col-lg-3" data-aos="flip-left" data-aos-delay="100"><div class="impact-card"><i class="fa-solid fa-bolt"></i><h3>95%</h3><p>less energy to recycle aluminium than make it new</p></div></div>
            <div class="col-6 col-lg-3" data-aos="flip-left" data-aos-delay="200"><div class="impact-card"><i class="fa-solid fa-dumpster"></i><h3>0</h3><p>scrap from our pickups is sent to landfill</p></div></div>
            <div class="col-6 col-lg-3" data-aos="flip-left" data-aos-delay="300"><div class="impact-card"><i class="fa-solid fa-seedling"></i><h3>1</h3><p>free plant for every eligible pickup</p></div></div>
        </div>
    </div>
</section>

<!-- ================= PORTALS ================= -->
<section class="py-section bg-green-50">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="eyebrow"><i class="fa-solid fa-layer-group"></i> One platform, three roles</span>
            <h2 class="section-title">Built for everyone in the recycling chain</h2>
            <p class="section-sub">Users request pickups, the admin manages and assigns, and drivers collect and update payment, all in one system.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="portal user">
                    <div class="p-ic"><i class="fa-solid fa-user"></i></div>
                    <h4>User</h4>
                    <p class="text-muted mb-0">For households and offices selling scrap.</p>
                    <ul>
                        <li><i class="fa-solid fa-circle-check"></i>Register and log in</li>
                        <li><i class="fa-solid fa-circle-check"></i>Raise pickup requests</li>
                        <li><i class="fa-solid fa-circle-check"></i>Track request status</li>
                        <li><i class="fa-solid fa-circle-check"></i>View payment details</li>
                    </ul>
                    <a href="user/login.php" class="btn btn-green w-100">User Login <i class="fa-solid fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="portal admin featured">
                    <div class="p-ic"><i class="fa-solid fa-user-shield"></i></div>
                    <h4>Admin</h4>
                    <p class="mb-0" style="color:#cfe8d2">The control centre of the system.</p>
                    <ul>
                        <li><i class="fa-solid fa-circle-check"></i>Manage all pickup requests</li>
                        <li><i class="fa-solid fa-circle-check"></i>Assign drivers</li>
                        <li><i class="fa-solid fa-circle-check"></i>Manage price list and pages</li>
                        <li><i class="fa-solid fa-circle-check"></i>Reports and driver management</li>
                    </ul>
                    <a href="admin/login.php" class="btn btn-white w-100">Admin Login <i class="fa-solid fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="portal driver">
                    <div class="p-ic"><i class="fa-solid fa-truck"></i></div>
                    <h4>Driver</h4>
                    <p class="text-muted mb-0">For our on-ground pickup team.</p>
                    <ul>
                        <li><i class="fa-solid fa-circle-check"></i>See assigned pickups</li>
                        <li><i class="fa-solid fa-circle-check"></i>Collect and weigh scrap</li>
                        <li><i class="fa-solid fa-circle-check"></i>Update pickup status</li>
                        <li><i class="fa-solid fa-circle-check"></i>Record payment details</li>
                    </ul>
                    <a href="driver/login.php" class="btn btn-green w-100">Driver Login <i class="fa-solid fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PLANTS FOR TRASH ================= -->
<section class="py-section">
    <div class="container">
        <div class="reward row g-0 align-items-stretch" data-aos="fade-up">
            <div class="col-lg-6">
                <img class="api-img" data-kw="plant,sapling" loading="lazy"
                     src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1000&q=75" alt="Hands holding a young plant">
            </div>
            <div class="col-lg-6 content d-flex flex-column justify-content-center">
                <span class="eyebrow align-self-start"><i class="fa-solid fa-seedling"></i> Plants for Trash</span>
                <h2 class="section-title">Give us your scrap, take home a plant</h2>
                <p class="text-muted">We believe recycling should feel rewarding. Along with fair payment, eligible pickups come with a free sapling for your home.</p>
                <ul class="check-list mb-4">
                    <li><i class="fa-solid fa-circle-check"></i>Free indoor or outdoor sapling</li>
                    <li><i class="fa-solid fa-circle-check"></i>Delivered by your pickup driver</li>
                    <li><i class="fa-solid fa-circle-check"></i>A greener home and a cleaner city</li>
                </ul>
                <div><a href="user/login.php" class="btn btn-green">Start Recycling</a></div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PICKUP GUIDELINES ================= -->
<section class="pb-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="eyebrow"><i class="fa-solid fa-clipboard-list"></i> Before you book</span>
            <h2 class="section-title">Pickup guidelines</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up"><div class="info-card"><i class="fa-solid fa-weight-hanging"></i><h5>Minimum 8 kg</h5><p>Pickups start at 8 kg or an order value of ₹500 and above.</p></div></div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100"><div class="info-card"><i class="fa-solid fa-layer-group"></i><h5>Keep it sorted</h5><p>Separating paper, plastic and metal speeds up your pickup.</p></div></div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200"><div class="info-card"><i class="fa-solid fa-droplet-slash"></i><h5>Dry scrap only</h5><p>Wet or food-soiled material can't be recycled, so keep it dry.</p></div></div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300"><div class="info-card"><i class="fa-solid fa-map-location-dot"></i><h5>Chennai service</h5><p>We currently serve locations across Chennai.</p></div></div>
        </div>
    </div>
</section>

<!-- ================= FAQ ================= -->
<section class="py-section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4" data-aos="fade-right">
                <div class="faq-side">
                    <span class="eyebrow" style="background:rgba(156,204,101,.15);color:var(--lime)">FAQ</span>
                    <h3 class="mb-3">Got questions? We've got answers.</h3>
                    <p>Can't find what you're looking for? Reach our team directly.</p>
                    <a href="contact_us.php" class="btn btn-white mt-2">Contact Us</a>
                    <i class="fa-solid fa-circle-question big-ic"></i>
                </div>
            </div>
            <div class="col-lg-8" data-aos="fade-left">
                <div class="accordion" id="faq">
                    <?php
                    $faqs = [
                        ['How do I book a scrap pickup?', 'Create an account or log in as a User, open the pickup request page, fill in your address, preferred date and scrap details, and submit. You can track the status from your dashboard.'],
                        ['Is there a minimum quantity?', 'Yes. The minimum pickup is 8 kg or an order value of ₹500 and above.'],
                        ['How are prices decided?', 'Prices for each material are set by the admin and shown on the Price List page. The price is fixed when you place the order, so there is no bargaining.'],
                        ['When do I get paid?', 'Immediately after your scrap is weighed. The driver records the payment in the system, so you can see it in your account.'],
                        ['Which areas do you cover?', 'We currently provide doorstep pickup across Chennai.'],
                    ];
                    foreach ($faqs as $i => $f): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $i ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#f<?php echo $i; ?>"><?php echo $f[0]; ?></button>
                        </h2>
                        <div id="f<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i ? '' : 'show'; ?>" data-bs-parent="#faq">
                            <div class="accordion-body"><?php echo $f[1]; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= ABOUT (managed from Admin > Pages > About Us) ================= -->
<section class="py-section bg-green-50" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-img-wrap">
                    <img class="a1 api-img" data-kw="recycling,team" loading="lazy" src="img1/aboutus.jpg" alt="About SSMS">
                    <img class="a2 api-img" data-kw="recycle,bins" loading="lazy" src="https://images.unsplash.com/photo-1530587191325-3db32d826c18?auto=format&fit=crop&w=700&q=75" alt="Recycling bins">
                    <div class="exp"><b>2021</b>Since</div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="eyebrow"><i class="fa-solid fa-building"></i> Who we are</span>
                <h2 class="section-title"><?php echo e($aboutTitle); ?></h2>
                <div class="about-content mb-4">
                    <?php if ($aboutDesc): echo $aboutDesc; else: ?>
                    <p>The Smart Scrap Management System is a web-based application that helps users request scrap pickups easily. Admins manage these requests by assigning drivers, while drivers update the status and payment details after collecting scrap.</p>
                    <?php endif; ?>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6"><div class="d-flex gap-3 align-items-center"><div class="feature-icon m-0" style="width:56px;height:56px"><i class="fa-solid fa-users"></i></div><div><h4 class="mb-0 text-green"><span class="counter" data-target="1200">0</span>+</h4><small class="text-muted">Happy clients</small></div></div></div>
                    <div class="col-sm-6"><div class="d-flex gap-3 align-items-center"><div class="feature-icon m-0" style="width:56px;height:56px"><i class="fa-solid fa-check-double"></i></div><div><h4 class="mb-0 text-green"><span class="counter" data-target="1500">0</span>+</h4><small class="text-muted">Pickups done</small></div></div></div>
                </div>
                <a href="contact_us.php" class="btn btn-green">Get in touch</a>
            </div>
        </div>
    </div>
</section>

<!-- ================= CTA ================= -->
<section class="py-section">
    <div class="container">
        <div class="cta text-center" data-aos="zoom-in">
            <div class="row justify-content-center position-relative" style="z-index:1">
                <div class="col-lg-8">
                    <h2 class="section-title">Ready to recycle your scrap?</h2>
                    <p class="mb-4">Join thousands of Chennai residents who are turning waste into money while protecting the environment.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="user/login.php" class="btn btn-white"><i class="fa-solid fa-truck-fast me-2"></i>Schedule Pickup</a>
                        <a href="contact_us.php" class="btn btn-outline-light rounded-pill px-4 py-3 fw-semibold">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand text-white mb-3" href="index.php" style="color:#fff!important"><img src="img1/trash.png" alt="" style="width:44px">SSMS</a>
                <p>A smart, transparent way to sell your scrap in Chennai. Door-to-door pickup, digital weighing and instant payment.</p>
                <div class="social mt-3" style="margin-left:-6px">
                    <a href="#" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-6 f-links">
                <h5>Explore</h5>
                <a href="index.php"><i class="fa-solid fa-chevron-right"></i>Home</a>
                <a href="#how"><i class="fa-solid fa-chevron-right"></i>How It Works</a>
                <a href="#materials"><i class="fa-solid fa-chevron-right"></i>What We Buy</a>
                <a href="price_list.php"><i class="fa-solid fa-chevron-right"></i>Price List</a>
                <a href="contact_us.php"><i class="fa-solid fa-chevron-right"></i>Contact Us</a>
            </div>
            <div class="col-lg-2 col-md-6 col-6 f-links">
                <h5>Login</h5>
                <a href="user/login.php"><i class="fa-solid fa-chevron-right"></i>User</a>
                <a href="driver/login.php"><i class="fa-solid fa-chevron-right"></i>Driver</a>
                <a href="admin/login.php"><i class="fa-solid fa-chevron-right"></i>Admin</a>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5>Get in Touch</h5>
                <div class="contact-line"><i class="fa-solid fa-location-dot"></i><span><?php echo e($contactAddr); ?></span></div>
                <?php if($phoneDisplay): ?>
                <div class="contact-line"><i class="fa-solid fa-phone"></i><a href="tel:<?php echo e($phoneDisplay); ?>"><?php echo e($phoneDisplay); ?></a></div>
                <?php endif; ?>
                <div class="contact-line"><i class="fa-solid fa-envelope"></i><a href="mailto:<?php echo e($contactEmail); ?>"><?php echo e($contactEmail); ?></a></div>
            </div>
        </div>
        <div class="copy d-flex flex-column flex-md-row justify-content-between gap-2 text-center">
            <span>&copy; <?php echo date('Y'); ?> Smart Scrap Management System. All rights reserved.</span>
            <span>Designed by SSMS</span>
        </div>
    </div>
</footer>

<a href="#" class="back-top" id="backTop" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    // Loader (also hides after 3s so it can never get stuck)
    const hideLoader = () => document.getElementById('loader').classList.add('hide');
    window.addEventListener('load', hideLoader);
    setTimeout(hideLoader, 3000);

    // Image API fallback: if a photo fails, load a keyword-matched image instead
    document.querySelectorAll('img.api-img').forEach((img, i) => {
        img.addEventListener('error', function handler() {
            const kw = img.dataset.kw || 'recycling';
            if (!img.dataset.tried) {
                img.dataset.tried = '1';
                const w = img.clientWidth > 600 ? 1000 : 800;
                img.src = `https://loremflickr.com/${w}/700/${kw}?lock=${i + 7}`;
            } else {
                img.removeEventListener('error', handler);
                img.src = `https://picsum.photos/seed/SSMS${i}/900/700`;
            }
        });
    });

    // Navbar shadow + back-to-top
    const nav = document.getElementById('mainNav'), topBtn = document.getElementById('backTop');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', scrollY > 60);
        topBtn.classList.toggle('show', scrollY > 500);
    });

    // Close mobile menu after clicking an anchor link
    document.querySelectorAll('#nav a.nav-link:not(.dropdown-toggle)').forEach(a => a.addEventListener('click', () => {
        const c = bootstrap.Collapse.getInstance(document.getElementById('nav'));
        if (c) c.hide();
    }));

    // Counters
    const io = new IntersectionObserver(entries => entries.forEach(en => {
        if (!en.isIntersecting) return;
        const el = en.target, target = +el.dataset.target, dur = 1600, t0 = performance.now();
        const step = t => { const p = Math.min((t - t0) / dur, 1); el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3))).toLocaleString('en-IN'); if (p < 1) requestAnimationFrame(step); };
        requestAnimationFrame(step); io.unobserve(el);
    }), { threshold: .5 });
    document.querySelectorAll('.counter').forEach(c => io.observe(c));

    AOS.init({ duration: 800, once: true, offset: 60 });
</script>
</body>
</html>