<?php
session_start();


$serverName = "LAPTOP-H61L0EMI\\SQLEXPRESS";
$connectionInfo = array("Database" => "maru_bake_house", "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Koneksi ke database gagal: " . print_r(sqlsrv_errors(), true));
}

function escape_string($str) {
    if ($str === null) return '';
    $str = str_replace("'", "''", $str);
    return $str;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $customer_name = escape_string($_POST['customer_name']);
    $review_text   = escape_string($_POST['review_text']);
    
    $query = "INSERT INTO review (customer_name, review_text, status, review_date) 
              VALUES ('$customer_name', '$review_text', 'pending', GETDATE())";
    
    $stmt = sqlsrv_query($conn, $query);
    
    if ($stmt) {
        header("Location: customer.php?status=success");
        exit();
    } else {
        die("Gagal menyimpan review: " . print_r(sqlsrv_errors(), true));
    }
}

$sql_menu = "SELECT p.*, c.category_name 
             FROM product p
             INNER JOIN category c ON p.category_id = c.category_id
             ORDER BY c.category_name ASC, p.product_name ASC";

$query_menu = sqlsrv_query($conn, $sql_menu);
$menu_dinamis = [];

if ($query_menu) {
    $has_data = false;
    while ($row = sqlsrv_fetch_array($query_menu, SQLSRV_FETCH_ASSOC)) {
        $has_data = true;
        $category_name = (!empty($row['category_name'])) ? $row['category_name'] : 'Melted Cheese Tart';
        $menu_dinamis[$category_name][] = [
            "product_name" => $row['product_name'],
            "price"        => $row['price'],
            "description"  => $row['description'],
            "image"        => $row['image_url']
        ];
    }
    if (!$has_data) {
        $menu_dinamis = [
            "Mochi Donut" => [
                ["product_name" => "Vanilla Mochi (Data Kosong)", "price" => 23000, "description" => "Koneksi aman, tapi tidak ada produk.", "image" => ""]
            ]
        ];
    }
} else {
    echo "<h3>Query Menu Bermasalah:</h3>";
    die(print_r(sqlsrv_errors(), true));
}

$query_reviews = sqlsrv_query($conn, "SELECT * FROM review WHERE status = 'published' ORDER BY review_id DESC");
$custom_reviews = [];

if ($query_reviews) {
    while ($rev_row = sqlsrv_fetch_array($query_reviews, SQLSRV_FETCH_ASSOC)) {
        $r_date = ($rev_row['review_date'] instanceof DateTime) ? $rev_row['review_date']->format('d M Y') : $rev_row['review_date'];
        $custom_reviews[] = [
            "customer_name" => $rev_row['customer_name'],
            "review_text"   => $rev_row['review_text'],
            "date"          => $r_date
        ];
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARU Bake House</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; font-family: 'Poppins', sans-serif; }
        html{ scroll-behavior:smooth; }
        body{ background:#F2EAD3; color:#581C14; }

        nav{ width:100%; background:#F2EAD3; padding:25px 8%; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:1000; }
        .logo-text{ color:#581C14; font-size:32px; font-weight:800; display:flex; align-items:center; }
        nav ul{ display:flex; gap:40px; list-style:none; align-items:center; }
        nav ul li a{ color:#AB826A; text-decoration:none; font-weight:600; font-size:20px; text-transform:uppercase; transition:0.3s; }
        nav ul li a:hover, nav ul li a.active{ color:#581C14; }
        .profile-icon{ color:#AB826A; font-size:28px; cursor:pointer; }

        .hero{ min-height:85vh; background:#7A1E13; display:flex; justify-content:space-between; align-items:center; padding:40px 8%; gap:40px; overflow:hidden; }
        .hero-text{ flex:1; color:#F2EAD3; max-width:600px; }
        .hero-text h1{ font-size:62px; font-weight:700; line-height:1.2; margin-bottom:5px; color:#F2EAD3; }
        .hero-text h2{ font-size:48px; font-weight:700; margin-bottom:20px; color:#F2EAD3; }
        .hero-text .sub-title{ font-size:22px; font-weight:600; margin-bottom:20px; color:#F2EAD3; }
        .hero-text p{ font-size:15px; line-height:1.6; color:#F2EAD3; margin-bottom:35px; font-weight:300; }
        .hero-btn{ display:inline-block; padding:12px 35px; background:#F2EAD3; color:#581C14; text-decoration:none; border-radius:30px; font-weight:600; font-size:15px; transition:0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.15); margin-right:15px; }
        .hero-btn:hover{ background:#E4DAC2; }
        .hero-image{ flex:1.2; display:grid; grid-template-columns: repeat(2, 1fr); gap:15px; position:relative; }
        .hero-image img{ width:100%; border-radius:15px; object-fit:cover; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .hero-image img:nth-child(1){ transform: translateY(30px); }
        .hero-image img:nth-child(2){ transform: translateY(-30px); }

        .section{ padding:80px 8%; }

        .menu-category-block { margin-bottom: 50px; background: #F2EAD3; padding: 20px 0; }
        .menu-category-header { text-align: center; margin-bottom: 35px; position: relative; }
        .menu-category-header h2 { font-size: 18px; color: #581C14; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: inline-block; background: #F2EAD3; padding: 0 20px; position: relative; z-index: 2; }
        .menu-category-header::after { content: ''; position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: #AB826A; z-index: 1; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; justify-content: center; padding: 20px 0; }
        .flip-container { perspective: 1000px; height: 360px; cursor: pointer; width: 100%; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; }
        .flip-container.flipped .flip-card-inner { transform: rotateY(180deg); }
        .menu-card-front, .menu-card-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #7A1E13; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .menu-card-back { transform: rotateY(180deg); background: #581C14; border: 4px solid #7A1E13; }
        .flip-card-inner h4 { font-size: 18px; font-weight: 600; margin-bottom: 12px; color: #FFFFFF; }
        .price-badge { background: rgba(255, 255, 255, 0.2); padding: 6px 20px; border-radius: 15px; font-size: 14px; font-weight: 600; color: #FFFFFF; }
        .description-text { font-size: 14px; opacity: 0.9; line-height: 1.5; padding: 0 10px; overflow-y: auto; }

        .review-section { text-align: center; background: #F2EAD3; padding: 80px 5%; }
        .review-sub { font-size: 14px; font-weight: 700; color: #581C14; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .review-main-title { font-size: 48px; font-weight: 700; color: #581C14; margin-bottom: 15px; position: relative; display: inline-block; }
        .review-main-title::before { content: '\201C'; position: absolute; left: -40px; top: -10px; font-size: 70px; color: #581C14; font-family: serif; }
        .review-main-title::after { content: '\201D'; position: absolute; right: -40px; top: -10px; font-size: 70px; color: #581C14; font-family: serif; }
        .review-desc { font-size: 16px; color: #581C14; margin-bottom: 60px; font-weight: 400; }

        .review-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; max-width: 1200px; margin: 50px auto 0; text-align: left; }
        .review-card { background: #FFF8E7; border-top: 4px solid #7A1E13; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(88, 28, 20, 0.08); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.3s, box-shadow 0.3s; }
        .review-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(88, 28, 20, 0.15); }
        .review-card .review-body-text { font-size: 14px; line-height: 1.7; color: #444; font-weight: 400; margin-bottom: 25px; flex-grow: 1; font-style: italic; }
        .review-card .reviewer-footer { display: flex; align-items: center; gap: 12px; border-top: 1px solid #F2EAD3; padding-top: 18px; }
        .reviewer-avatar { width: 45px; height: 45px; border-radius: 50%; background: #7A1E13; display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 18px; font-weight: 700; flex-shrink: 0; }
        .reviewer-info h4 { font-size: 14px; font-weight: 700; color: #581C14; margin-bottom: 2px; }
        .reviewer-info p { font-size: 12px; color: #AB826A; font-weight: 500; }

        .contact-section { background: #7A1E13; padding: 60px 8%; min-height: 85vh; display: flex; align-items: center; justify-content: center; }
        .contact-box { width: 100%; max-width: 1150px; display: flex; gap: 50px; align-items: stretch; }
        .contact-left { flex: 1.1; color: #FFFFFF; display: flex; flex-direction: column; justify-content: center; }
        .contact-left .tagline { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; color: #F2EAD3; }
        .contact-left h1 { font-size: 46px; font-weight: 700; margin-bottom: 20px; color: #FFFFFF; }
        .contact-left p { font-size: 14px; line-height: 1.6; color: #F2EAD3; margin-bottom: 35px; font-weight: 300; }
        .contact-form-custom { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group.full-width { grid-column: span 2; }
        .input-group label { font-size: 13px; font-weight: 500; color: #FFFFFF; }
        .input-group input { background: #F2EAD3; border: none; padding: 14px 20px; border-radius: 25px; font-size: 13px; font-style: italic; color: #581C14; outline: none; }
        .textarea-container { background: #F2EAD3; border-radius: 25px; padding: 15px 20px; display: flex; flex-direction: column; position: relative; height: 160px; }
        .textarea-container textarea { background: transparent; border: none; width: 100%; height: 100%; resize: none; font-size: 14px; outline: none; color: #581C14; }
        .send-btn-circle { position: absolute; bottom: 15px; right: 20px; background: transparent; border: none; font-size: 20px; color: #581C14; cursor: pointer; transition: 0.3s; }
        .send-btn-circle:hover { transform: scale(1.1); }
        .contact-right { flex: 0.9; background: #F2EAD3; border-radius: 30px; padding: 40px 45px; color: #581C14; display: flex; flex-direction: column; justify-content: space-between; }
        .info-block { text-align: center; margin-bottom: 25px; }
        .info-block:last-child { margin-bottom: 0; }
        .info-block .icon-wrapper { font-size: 22px; color: #581C14; margin-bottom: 8px; }
        .info-block h3 { font-size: 18px; font-weight: 700; margin-bottom: 6px; text-transform: capitalize; }
        .info-block p { font-size: 13px; line-height: 1.5; color: #AB826A; font-weight: 500; }

        .about-section { display: flex; min-height: 85vh; background: #7A1E13; width: 100%; }
        .about-left { flex: 1; background: #7A1E13; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 40px; color: #F2EAD3; }
        .about-logo-big { font-size: 90px; font-weight: 800; line-height: 1; letter-spacing: -2px; color: #F2EAD3; margin-bottom: 5px; }
        .about-logo-sub { font-size: 24px; font-weight: 700; letter-spacing: 6px; color: #F2EAD3; text-transform: uppercase; }
        .about-right { flex: 1; background: #F2EAD3; padding: 60px 8%; color: #581C14; display: flex; flex-direction: column; justify-content: center; }
        .about-right h1 { font-size: 44px; font-weight: 700; color: #581C14; margin-bottom: 30px; }
        .about-right p { font-size: 13px; line-height: 1.7; color: #581C14; margin-bottom: 25px; font-weight: 400; text-align: justify; }
        .about-right p:last-child { margin-bottom: 0; }

        .filter-container { background: #7A1E13; text-align: center; padding: 30px 20px; }
        .filter-container h3 { color: #F2EAD3; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .filter-container h1 { color: #FFFFFF; font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .filter-container p { color: #F2EAD3; font-size: 13px; margin-bottom: 25px; opacity: 0.8; }
        .filter-buttons { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; max-width: 900px; margin: 0 auto; }
        .filter-btn { background: #F2EAD3; color: #581C14; border: none; padding: 10px 25px; border-radius: 25px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; min-width: 140px; }
        .filter-btn:hover, .filter-btn.active { background: #FFFFFF; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }

        #splash-screen { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background-color: #F2EAD3; display: flex; justify-content: center; align-items: center; z-index: 99999; cursor: pointer; transition: opacity 0.5s ease, visibility 0.5s ease; }
        .splash-content { text-align: center; }
        .splash-content img { max-width: 80%; height: auto; display: block; margin: 0 auto; }
        .splash-sub { font-family: 'Poppins', sans-serif; font-size: 16px; letter-spacing: 4px; color: #7A1E13; margin-top: 20px; text-transform: uppercase; font-weight: 500; }

        @media(max-width:1024px){
            .card-grid { grid-template-columns: repeat(2, 1fr); }
            .review-grid { grid-template-columns: repeat(2, 1fr); }
            .contact-box { flex-direction: column; }
            .about-section { flex-direction: column; }
            .about-left { padding: 60px 20px; }
        }
        @media(max-width:768px){
            nav{ flex-direction:column; gap:15px; }
            nav ul{ gap: 20px; }
            .hero{ flex-direction:column; padding-top: 60px; }
            .hero-text h1 { font-size: 42px; }
            .hero-text h2 { font-size: 32px; }
            .hero-image { grid-template-columns: 1fr 1fr; }
            .card-grid { grid-template-columns: 1fr; }
            .review-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div id="splash-screen">
    <div class="splash-content">
        <img src="img/maru-logo.png" alt="Maru Bake House">
        <p class="splash-sub">MOCHI PUMPKIN DONUT</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const splash = document.getElementById('splash-screen');
    if (!sessionStorage.getItem('splash_shown')) {
        splash.style.display = 'flex';
        splash.addEventListener('click', function() {
            splash.style.opacity = '0';
            sessionStorage.setItem('splash_shown', 'true');
            setTimeout(() => {
                splash.style.visibility = 'hidden';
                splash.style.display = 'none';
            }, 500);
        });
    } else {
        splash.style.display = 'none';
    }
});
</script>

<nav>
    <div class="logo-text">M</div>
    <ul>
        <li><a href="javascript:void(0)" onclick="scrollToSec('storySec')">About</a></li>
        <li><a href="javascript:void(0)" onclick="scrollToSec('menuSec')">Menu</a></li>
        <li><a href="javascript:void(0)" onclick="scrollToSec('reviewSec')">Reviews</a></li>
        <li><a href="javascript:void(0)" onclick="scrollToSec('contactSec')">Contact</a></li>
    </ul>
    <div class="profile-icon">
        <a href="login.php" style="color: inherit; text-decoration: none;">
            <i class="fa-solid fa-user-large"></i>
        </a>
    </div>
</nav>

<section class="hero" id="homeSec">
    <div class="hero-text">
        <h1>First Pumpkin</h1>
        <h2>Donut</h2>
        <div class="sub-title">in Batam</div>
        <p>Where every donuts is made fresh with love. Mochi donuts in Batam made with high-quality ingredients. Every bite melts in your mouth, and every bite tells a story of passion and quality</p>
        <a href="javascript:void(0)" onclick="scrollToSec('menuSec')" class="hero-btn">View Our Menu</a>
        <a href="javascript:void(0)" onclick="scrollToSec('storySec')" class="hero-btn">Our Story</a>
    </div>
    <div class="hero-image">
        <img src="img/Donat3.png" alt="Donat 3">
        <img src="img/Donat4.png" alt="Donat 4">
    </div>
</section>

<section class="about-section" id="storySec" style="scroll-margin-top: 90px; padding-top: 40px; padding-bottom: 40px;">
    <div class="about-left">
        <div class="about-logo-big">M A R U</div>
        <div class="about-logo-sub">B a k e H o u s e</div>
    </div>
    <div class="about-right">
        <h1>Freshly Baked Delights</h1>
        <p>Founded in August 2025, Maru Bake House began with a simple dream to bring a homemade pumpkin cake to our community — something that had never been seen before. The name "Maru" means "circle" in Japanese, symbolising the unity and perfection we strive to bring to every creation.</p>
        <p>Our shop will continue to innovate, ensuring every cake that comes out of our oven is just as extraordinary. Every doughnut is made with love. We use the finest fresh ingredients sourced locally, so that our food remains fresh every day, baked fresh every morning, with no added preservatives.</p>
        <p>Although we are still new, we have received positive and constructive feedback that will help Maru Bake House grow in the future.</p>
    </div>
</section>

<div class="filter-container" id="menuSec" style="scroll-margin-top: 90px; padding-top: 40px;">
    <h3>Our Menu</h3>
    <h1>Are They All Your Favourites?</h1>
    <p>From artisan cheesetarts to delicious mochi donuts, discover our handcrafted selection</p>
    <div class="filter-buttons">
        <button class="filter-btn active" onclick="filterMenu('all', this)">All</button>
        <?php foreach(array_keys($menu_dinamis) as $category_name): ?>
            <button class="filter-btn" onclick="filterMenu('<?= md5($category_name) ?>', this)"><?= htmlspecialchars($category_name) ?></button>
        <?php endforeach; ?>
    </div>
    <div style="margin-top: 25px; text-align: center;">
        <input type="text" id="menuSearchInput" onkeyup="searchMenu()" placeholder="Find Donut Or Favorite Menu..." style="padding: 12px 20px; width: 100%; max-width: 400px; border-radius: 25px; border: 2px solid #F2EAD3; background: transparent; color: #FFFFFF; font-size: 15px; outline: none;">
    </div>
</div>

<section class="section" style="background:#F2EAD3; padding-top:40px;">
    <?php foreach($menu_dinamis as $category_name => $items): ?>
        <div class="menu-category-block" id="cat-<?= md5($category_name) ?>">
            <div class="menu-category-header">
                <h2><?= htmlspecialchars($category_name) ?></h2>
            </div>
            <div class="card-grid">
                <?php foreach($items as $item): ?>
                    <div class="flip-container" onclick="toggleFlip(this)">
                        <div class="flip-card-inner">
                            <div class="menu-card-front">
                                <img src="<?= !empty($item['image']) ? 'img/' . $item['image'] : 'img/maru-logo.png'; ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px;" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                <span class="price-badge"><?= number_format($item['price'] / 1000, 0) ?>K</span>
                            </div>
                            <div class="menu-card-back">
                                <h4 style="margin-bottom: 10px; font-weight:700; color:#FFFFFF;"><?= htmlspecialchars($item['product_name']) ?></h4>
                                <p class="description-text"><?= htmlspecialchars($item['description']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="review-section" id="reviewSec" style="scroll-margin-top: 90px; padding-top: 40px;">
    <div class="review-sub">Reviews</div>
    <div class="review-main-title">What Our Customers Say</div>
    <div class="review-desc">Don't just take our word for it. Let's hear from our happy customers</div>

    <?php if(!empty($custom_reviews)): ?>
        <div class="review-grid">
            <?php foreach($custom_reviews as $rev): ?>
                <div class="review-card">
                    <div>
                        <p class="review-body-text">"<?= htmlspecialchars($rev['review_text']); ?>"</p>
                    </div>
                    <div class="reviewer-footer">
                        <div class="reviewer-avatar">
                            <?= strtoupper(substr($rev['customer_name'], 0, 1)); ?>
                        </div>
                        <div class="reviewer-info">
                            <h4><?= htmlspecialchars($rev['customer_name']); ?></h4>
                            <p>Customer &bull; <?= htmlspecialchars($rev['date']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:#AB826A; margin-top: 30px; font-style: italic;">Belum ada review yang dipublish.</p>
    <?php endif; ?>

    <div style="margin-top: 50px;">
        <a href="javascript:void(0)" onclick="scrollToSec('contactSec')" style="
            display: inline-block;
            background-color: #7A1E13;
            color: #FFFFFF;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.3s;
        " onmouseover="this.style.backgroundColor='#581C14'; this.style.transform='translateY(-2px)'"
           onmouseout="this.style.backgroundColor='#7A1E13'; this.style.transform='translateY(0)'">
            Leave a Review
        </a>
    </div>
</section>

<section class="contact-section" id="contactSec" style="scroll-margin-top: 90px; padding-top: 40px; padding-bottom: 80px;">
    <div class="contact-box" style="display: flex; gap: 50px; flex-wrap: wrap;">
        <div class="contact-left" style="flex: 1; min-width: 300px;">
            <span class="tagline">Get In Touch</span>
            <h1>Say Hello !</h1>
            <p>Have questions about our products or want give some suggestions? We'd love to hear from you!</p>
            
            <form method="POST" class="contact-form-custom">
                <div class="input-group">
                    <label>Your Name</label>
                    <input type="text" name="customer_name" placeholder="Tulis nama Anda..." required>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="customer_email" placeholder="Tulis email Anda...">
                </div>
                <div class="input-group full-width">
                    <label>Message / Review</label>
                    <div class="textarea-container">
                        <textarea name="review_text" placeholder="Tulis review atau pesan Anda di sini..." required></textarea>
                        <button type="submit" name="submit_review" class="send-btn-circle">
                            <i class="fa-regular fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="contact-right" style="flex: 0 0 300px;">
            <div class="info-block">
                <div class="icon-wrapper"><i class="fa-solid fa-location-dot"></i></div>
                <h3>Visit us</h3>
                <p>Komp. Superblock, Sydney Hotel<br>No.6, Sungai Panas</p>
            </div>
            <div class="info-block">
                <div class="icon-wrapper"><i class="fa-solid fa-phone"></i></div>
                <h3>Call Us</h3>
                <p>+ 1987654321<br>+ 1987654321</p>
            </div>
            <div class="info-block">
                <div class="icon-wrapper"><i class="fa-solid fa-envelope"></i></div>
                <h3>Opening Hours</h3>
                <p>Mon - Fri : 7am - 8 pm<br>Sat - Sun : 8 am - 6pm</p>
            </div>
            <div class="info-block">
                <div class="icon-wrapper"><i class="fa-solid fa-clock"></i></div>
                <h3>Email us</h3>
                <p style="text-transform: none;">hello@marubakehouse.com</p>
            </div>
        </div>
    </div>
</section>

<footer style="background-color: #F2EAD3; padding: 50px 8% 30px 8%; font-family: 'Poppins', sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 40px; margin-bottom: 40px;">
        <div style="max-width: 400px;">
            <img src="img/maru-logo.png" alt="Maru Bake House Logo" style="width: 180px; height: auto; margin-bottom: 20px;">
            <p style="color: #7A1E13; font-size: 14px; line-height: 1.6; margin: 0;">
                Crafting artisan baked goods with love since 2026. Every bite tells a story of passion, quality, and tradition.
            </p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 12px; color: #7A1E13;">
                <i class="bx bxl-instagram" style="font-size: 24px;"></i>
                <span style="font-size: 14px; font-weight: 500;">marubake.house</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; color: #7A1E13;">
                <i class="bx bxl-facebook" style="font-size: 24px;"></i>
                <span style="font-size: 14px; font-weight: 500;">maru_bakehouse</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; color: #7A1E13;">
                <i class="bx bxl-twitter" style="font-size: 24px;"></i>
                <span style="font-size: 14px; font-weight: 500;">marubakehouse</span>
            </div>
        </div>
    </div>
    <hr style="border: 0; height: 1px; background-color: #D1C7BD; margin-bottom: 25px;">
    <div>
        <p style="color: #7A1E13; font-size: 13px; margin: 0; font-weight: 500;">
            @2026 Maru Bake House. All rights reserved
        </p>
    </div>
</footer>

<script>
    function scrollToSec(id) {
        const targetSection = document.getElementById(id);
        if (targetSection) {
            targetSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function toggleFlip(container) {
        container.classList.toggle("flipped");
    }

    function searchMenu() {
        const input = document.getElementById('menuSearchInput');
        const filter = input.value.toLowerCase().trim();
        const categoryBlocks = document.querySelectorAll('.menu-category-block');

        categoryBlocks.forEach(block => {
            const cards = block.querySelectorAll('.flip-container');
            let hasVisibleCard = false;
            cards.forEach(card => {
                const titleElement = card.querySelector('.menu-card-front h4');
                if (titleElement) {
                    const txtValue = titleElement.textContent || titleElement.innerText;
                    if (filter === "" || txtValue.toLowerCase().indexOf(filter) > -1) {
                        card.style.display = "";
                        hasVisibleCard = true;
                    } else {
                        card.style.display = "none";
                    }
                }
            });
            block.style.display = hasVisibleCard ? "" : "none";
        });
    }

    function filterMenu(category, button) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        document.querySelectorAll('.menu-category-block').forEach(block => {
            if (category === 'all') {
                block.style.display = 'block';
            } else {
                if (block.id === 'cat-' + category) {
                    block.style.display = 'block';
                } else {
                    block.style.display = 'none'; 
                }
            }
        });
    }
</script>
</body>
</html>