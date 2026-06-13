<?php
session_start();

$serverName = "LAPTOP-H61L0EMI\SQLEXPRESS"; 
$connectionInfo = array("Database"=>"maru_bake_house", "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
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
        echo "<script>alert('Terima kasih! Review Anda sedang diproses oleh admin.'); window.location='customer.php#contactSec';</script>";
        exit();
    }
}

if (isset($_GET['approve_id'])) {
    $id = escape_string($_GET['approve_id']);
    sqlsrv_query($conn, "UPDATE review SET status = 'published' WHERE review_id = '$id'");
    header("Location: admin.php?status=success_update"); 
    exit();
}

if (isset($_POST['add_product'])) {
    $id_produk    = escape_string($_POST['product_id']); 
    $product_name = escape_string($_POST['product_name']);
    $category_id  = escape_string($_POST['category_id']); 
    $price        = intval($_POST['price']);
    $description  = escape_string($_POST['description']);

    $image_name = $_FILES['product_image']['name'];
    $image_tmp = $_FILES['product_image']['tmp_name'];
    $upload_dir = "img/";
    
    $final_image_name = time() . '_' . $image_name; 

    if (move_uploaded_file($image_tmp, $upload_dir . $final_image_name)) {
        $query = "INSERT INTO product (product_id, product_name, category_id, price, description, image_url) 
                  VALUES ('$id_produk', '$product_name', '$category_id', '$price', '$description', '$final_image_name')";
        $insert = sqlsrv_query($conn, $query);
        
        if ($insert) {
            header("Location: admin.php?status=success_add");
            exit();
        } else {
            die("Gagal menambah produk: " . print_r(sqlsrv_errors(), true));
        }
    } else {
        echo "<script>alert('Gagal mengupload gambar! Pastikan folder uploads/ sudah dibuat.');</script>";
    }
}

if (isset($_POST['edit_product'])) {
    $id           = escape_string($_POST['id']);
    $product_name = escape_string($_POST['product_name']);
    $category_id  = escape_string($_POST['category_id']);
    $price        = intval($_POST['price']);
    $description  = escape_string($_POST['description']);

    if (!empty($_FILES['product_image']['name'])) {
        $image_name       = $_FILES['product_image']['name'];
        $image_tmp        = $_FILES['product_image']['tmp_name'];
        $upload_dir       = "img/";
        $final_image_name = time() . '_' . $image_name;

        if (move_uploaded_file($image_tmp, $upload_dir . $final_image_name)) {
            $query = "UPDATE product 
                      SET product_name='$product_name', category_id='$category_id', 
                          price='$price', description='$description', image_url='$final_image_name' 
                      WHERE product_id='$id'";
        } else {
            echo "<script>alert('Gagal mengupload gambar baru! Pastikan folder uploads/ ada dan bisa ditulis.');</script>";
            exit();
        }
    } else {
        $query = "UPDATE product 
                  SET product_name='$product_name', category_id='$category_id', 
                      price='$price', description='$description' 
                  WHERE product_id='$id'";
    }

    $update = sqlsrv_query($conn, $query);
    if ($update) {
        header("Location: admin.php?status=success_update");
        exit();
    } else {
        die("Gagal mengedit produk: " . print_r(sqlsrv_errors(), true));
    }
}

if (isset($_GET['delete_product'])) {
    $id = escape_string($_GET['delete_product']);
    $delete = sqlsrv_query($conn, "DELETE FROM product WHERE product_id='$id'");
    
    if ($delete) {
        header("Location: admin.php?status=success_delete");
        exit();
    }
}

if (isset($_GET['delete_review'])) {
    $id = intval($_GET['delete_review']);
    $delete_rev = sqlsrv_query($conn, "DELETE FROM review WHERE review_id=$id");
    
    if ($delete_rev) { 
        header("Location: admin.php?status=success_delete_review"); 
        exit(); 
    } else {
        die("Gagal menghapus review: " . print_r(sqlsrv_errors(), true));
    }
}

if (isset($_GET['approve_review'])) {
    $id = intval($_GET['approve_review']);
    $res = sqlsrv_query($conn, "UPDATE review SET status='published' WHERE review_id=$id");
    if ($res) {
        header("Location: admin.php?status=success_update");
        exit();
    } else {
        die("Gagal mempublikasikan review: " . print_r(sqlsrv_errors(), true));
    }
}

if (isset($_GET['unpublish_review'])) {
    $id = intval($_GET['unpublish_review']);
    $res = sqlsrv_query($conn, "UPDATE review SET status='pending' WHERE review_id=$id");
    if ($res) {
        header("Location: admin.php?status=success_update");
        exit();
    } else {
        die("Gagal membatalkan review: " . print_r(sqlsrv_errors(), true));
    }
}

if (isset($_POST['submit_category'])) {
    $cat_id   = escape_string($_POST['category_id']);
    $cat_name = escape_string($_POST['category_name']);
    
    $insert = sqlsrv_query($conn, "INSERT INTO category (category_id, category_name) VALUES ('$cat_id', '$cat_name')");
    
    if ($insert) {
        header("Location: admin.php?status=success_category"); 
        exit();
    } else {
        die("Gagal menambah kategori. Pastikan ID unik: " . print_r(sqlsrv_errors(), true));
    }
}

if (isset($_POST['edit_category'])) {
    $old_cat_id = escape_string($_POST['old_category_id']);
    $new_cat_id = escape_string($_POST['category_id']);
    $cat_name   = escape_string($_POST['category_name']);
    
    $update = sqlsrv_query($conn, "UPDATE category SET category_id='$new_cat_id', category_name='$cat_name' WHERE category_id='$old_cat_id'");
    
    if ($update) {
        header("Location: admin.php?status=success_category_update");
        exit();
    } else {
        die("Gagal mengupdate kategori: " . print_r(sqlsrv_errors(), true));
    }
}


if (isset($_GET['delete_category'])) {
    $cat_id = escape_string($_GET['delete_category']);
    $delete = sqlsrv_query($conn, "DELETE FROM category WHERE category_id='$cat_id'");
    
    if ($delete) {
        header("Location: admin.php?status=success_category_delete");
        exit();
    } else {
        die("Gagal menghapus kategori. Pastikan tidak ada produk yang menggunakan kategori ini: " . print_r(sqlsrv_errors(), true));
    }
}


$products_query = sqlsrv_query($conn, "SELECT p.*, c.category_name FROM product p INNER JOIN category c ON p.category_id = c.category_id ORDER BY c.category_name ASC, p.product_name ASC");

$reviews_query = sqlsrv_query($conn, "SELECT review_id, customer_name, review_text, status, CONVERT(VARCHAR, review_date, 120) AS review_date_formatted FROM review ORDER BY status ASC, review_id DESC");

$categories_query = sqlsrv_query($conn, "SELECT * FROM category ORDER BY category_name ASC"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARU Bake House - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Poppins', sans-serif; }
        body { background:#F2EAD3; color:#581C14; display: flex; min-height: 100vh; }
        

        .sidebar { width: 260px; background: #7A1E13; color: #F2EAD3; padding: 30px 20px; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar h2 { font-size: 26px; font-weight: 800; text-align: center; margin-bottom: 40px; color: #FFFFFF; letter-spacing: 1px; }
        .sidebar a { color: #F2EAD3; text-decoration: none; padding: 12px 15px; border-radius: 10px; font-weight: 500; display: flex; align-items: center; gap: 15px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #581C14; color: #FFFFFF; }
        .sidebar .logout { margin-top: auto; background: rgba(0,0,0,0.2); }
        .sidebar .logout:hover { background: #581C14; }


        .main-content { margin-left: 260px; flex: 1; padding: 40px 5%; }
        .header-dash { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #7A1E13; padding-bottom: 15px; }
        .header-dash h1 { font-size: 28px; font-weight: 700; }
        

        .alert { background: #581C14; color: white; padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: 500; font-size: 14px; display: flex; justify-content: space-between; align-items: center; }
        .alert button { background: none; border: none; color: white; font-size: 18px; cursor: pointer; }


        .card { background: #FFFFFF; border-radius: 20px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .card h3 { font-size: 20px; margin-bottom: 20px; color: #7A1E13; display: flex; align-items: center; gap: 10px; }
        

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-grid-three { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full-width { grid-column: span 2; }
        .form-group.three-width { grid-column: span 3; }
        .form-group label { font-size: 13px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { padding: 12px 15px; border: 1px solid #DDD; border-radius: 10px; font-size: 14px; outline: none; background: #F9F9F9; }
        .form-group textarea { height: 80px; resize: none; }
        .btn-submit { background: #7A1E13; color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; width: fit-content; margin-top: 10px; }
        .btn-submit:hover { background: #581C14; }


        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 14px 18px; font-size: 14px; border-bottom: 1px solid #EEE; }
        th { background: #7A1E13; color: white; font-weight: 600; }
        tr:hover { background: #FDFBF7; }
        

        .badge-category { background: #F2EAD3; color: #581C14; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-edit { background: #AB826A; color: white; margin-right: 5px; }
        .btn-edit:hover { background: #8C6550; }
        .btn-delete { background: #7A1E13; color: white; }
        .btn-delete:hover { background: #581C14; }


        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 20px; width: 100%; max-width: 600px; position: relative; color: #581C14; }
        .close-modal { position: absolute; top: 20px; right: 25px; font-size: 24px; cursor: pointer; color: #AAA; }
        .close-modal:hover { color: #581C14; }

        .btn-publish { background: #28a745; color: white; }
        .btn-publish:hover { background: #218838; }
        .btn-unpublish { background: #ffc107; color: #000; }
        .btn-unpublish:hover { background: #e0a800; }

        .category-panel-white { background-color: #ffffff; padding: 30px; border-radius: 15px; border: 1px solid #e0e0e0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 40px; }
        .panel-title-dark { color: #6b2626; margin-top: 0; margin-bottom: 20px; }
        .form-group-dark { margin-bottom: 20px; }
        .form-group-dark label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .input-bordered { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 20px; background: #f9f9f9; color: #333; box-sizing: border-box; font-size: 14px; outline: none; }
        .btn-maroon { background: #6b2626; color: white; border: none; padding: 10px 25px; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-maroon:hover { background: #501d1d; }


        .image-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .current-image-label { font-size: 11px; color: #999; margin-top: 4px; display: block; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>MARU DASH</h2>
        <a href="#menu-management" class="active"><i class="fa-solid fa-cake-candles"></i> Menu Management</a>
        <a href="#customer-reviews"><i class="fa-solid fa-star"></i> Customer Reviews</a>
        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        <a href="customer.php" target="_blank" class="logout" style="margin-top: auto;"><i class="fa-solid fa-arrow-left"></i> View Live Website</a>
    </div>

    <div class="main-content">
        <div class="header-dash">
            <h1>Admin Dashboard</h1>
            <div style="font-weight: 600; font-size: 14px;"><i class="fa-solid fa-user-shield"></i> Welcome, Admin Maru</div>
        </div>

        <?php if(isset($_GET['status'])): ?>
            <div class="alert" id="alertBox">
                <span>
                    <?php
                    if($_GET['status'] == 'success_add') echo "🎉 New Product Has Been Added!";
                    if($_GET['status'] == 'success_update') echo "✏️ Data Has Been Updated!";
                    if($_GET['status'] == 'success_delete') echo "🗑️ Product Successfully Removed!";
                    if($_GET['status'] == 'success_delete_review') echo "🗑️ Customer Review Deleted!";
                    if($_GET['status'] == 'success_category') echo "📁 New Category Has Been Added!";
                    if($_GET['status'] == 'success_category_update') echo "✏️ Category Has Been Updated!";
                    if($_GET['status'] == 'success_category_delete') echo "🗑️ Category Successfully Removed!";
                    ?>
                </span>
                <button onclick="document.getElementById('alertBox').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <div id="menu-management">
            <div class="category-panel-white">
                <h3 class="panel-title-dark"><i class="fa-solid fa-list"></i> Active Menu List (Live)</h3>
                <table style="margin-bottom: 40px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($products_query):
                            while($prod = sqlsrv_fetch_array($products_query, SQLSRV_FETCH_ASSOC)): 
                        ?>
                                <tr>
                                    <td style="font-weight: 700; color: #7A1E13;"><?= htmlspecialchars($prod['product_id']) ?></td>
                                    <td>
                                        <?php if (!empty($prod['image_url'])): ?>
                                            <img src="img/<?= htmlspecialchars($prod['image_url']) ?>" class="image-preview" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                                        <?php else: ?>
                                            <span style="color:#ccc; font-size:12px;">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($prod['product_name']) ?></td>
                                    <td><span class="badge-category"><?= htmlspecialchars($prod['category_name']) ?></span></td>
                                    <td style="font-weight: 600;">Rp <?= number_format($prod['price'], 0, ',', '.') ?></td>
                                    <td style="color: #666; font-size: 13px; max-width: 250px;"><?= htmlspecialchars($prod['description']) ?></td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <button class="btn-action btn-edit" onclick="openEditModal('<?= $prod['product_id'] ?>', '<?= addslashes($prod['product_name']) ?>', '<?= $prod['category_id'] ?>', <?= $prod['price'] ?>, '<?= addslashes($prod['description']) ?>', '<?= htmlspecialchars($prod['image_url']) ?>')">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <a href="admin.php?delete_product=<?= $prod['product_id'] ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin-bottom: 30px;">

                <h3 class="panel-title-dark"><i class="fa-solid fa-plus-circle"></i> Add New Product Menu</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-grid" style="margin-bottom: 15px;">
                        <div class="form-group-dark">
                            <label>Product ID :</label>
                            <input type="text" name="product_id" class="input-bordered" placeholder="Ex: P001" required>
                        </div>
                        <div class="form-group-dark">
                            <label>Product Name :</label>
                            <input type="text" name="product_name" class="input-bordered" placeholder="Ex: Choco Pistachio" required>
                        </div>
                    </div>
                    <div class="form-grid" style="margin-bottom: 15px;">
                        <div class="form-group-dark">
                            <label>Category :</label>
                            <select name="category_id" class="input-bordered" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $cat_select_query = sqlsrv_query($conn, "SELECT * FROM category ORDER BY category_name ASC");
                                if($cat_select_query) {
                                    while($cat_opt = sqlsrv_fetch_array($cat_select_query, SQLSRV_FETCH_ASSOC)) {
                                        echo "<option value='".htmlspecialchars($cat_opt['category_id'])."'>".htmlspecialchars($cat_opt['category_name'])."</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group-dark">
                            <label>Price (Rupiah) :</label>
                            <input type="number" name="price" class="input-bordered" placeholder="Ex: 25000" required>
                        </div>
                    </div>
                    <div class="form-group-dark">
                        <label>Description :</label>
                        <textarea name="description" class="input-bordered" style="height: 100px; resize: none; border-radius: 15px;" placeholder="Provide attractive product details..." required></textarea>
                    </div>
                    <div class="form-group-dark" style="margin-bottom: 25px;">
                        <label>Product Image :</label>
                        <input type="file" name="product_image" class="input-bordered" accept="image/*" style="padding: 8px 15px;" required>
                    </div>
                    <button type="submit" name="add_product" class="btn-maroon">Add Product</button>
                </form>
            </div>

            <div class="category-panel-white">
                    <h3 class="panel-title-dark"><i class="fa-solid fa-folder"></i> Kelola Kategori</h3>
                    <table style="margin-bottom: 40px;">
                        <thead>
                            <tr>
                                <th>Category ID</th>
                                <th>Category Name</th>
                                <th style="text-align: center; width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($categories_query):
                                while($cat = sqlsrv_fetch_array($categories_query, SQLSRV_FETCH_ASSOC)):
                            ?>
                                <tr>
                                    <td style="font-weight: 700; color: #6b2626;"><?= htmlspecialchars($cat['category_id']) ?></td>
                                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                    <td style="text-align: center;">
                                            <button class="btn-action btn-edit" onclick="openCategoryModal('<?= $cat['category_id'] ?>', '<?= addslashes($cat['category_name']) ?>')">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <a href="admin.php?delete_category=<?= urlencode($cat['category_id']) ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin-bottom: 30px;">

                <h3 class="panel-title-dark"><i class="fa-solid fa-plus-circle"></i> Add New Category</h3>
                <form action="" method="post">
                    <div class="form-grid" style="margin-bottom: 15px;">
                        <div class="form-group-dark">
                            <label>Category ID :</label>
                            <input type="text" name="category_id" class="input-bordered" placeholder="Ex: CAT01" required>
                        </div>
                        <div class="form-group-dark">
                            <label>Category Name :</label>
                            <input type="text" name="category_name" class="input-bordered" placeholder="Ex: Pastry" required>
                        </div>
                    </div>
                    <button type="submit" name="submit_category" class="btn-maroon">Add Category</button>
                </form>
            </div>  
        </div>

        <div id="customer-reviews" class="card" style="margin-top: 50px;">
            <h3><i class="fa-solid fa-star"></i> Incoming Customer Reviews</h3>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Review Text</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th style="text-align: center; width: 200px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($reviews_query):
                        while($rev = sqlsrv_fetch_array($reviews_query, SQLSRV_FETCH_ASSOC)): 
                            $current_status = trim($rev['status']);
                    ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($rev['customer_name']) ?></td>
                                <td style="font-style: italic; color: #555;">"<?= htmlspecialchars($rev['review_text']) ?>"</td>
                                <td><?= htmlspecialchars($rev['review_date_formatted']) ?></td>
                                <td>
                                    <?php if($current_status == 'published'): ?>
                                        <span class="badge-category" style="background:#d4edda; color:#155724;">Published</span>
                                    <?php else: ?>
                                        <span class="badge-category" style="background:#fff3cd; color:#856404;">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($current_status == 'pending'): ?>
                                        <a href="admin.php?approve_review=<?= $rev['review_id'] ?>" class="btn-action btn-publish" style="margin-bottom:5px; display:inline-block;">
                                            <i class="fa-solid fa-check"></i> Publish
                                        </a>
                                    <?php else: ?>
                                        <a href="admin.php?unpublish_review=<?= $rev['review_id'] ?>" class="btn-action btn-unpublish" style="margin-bottom:5px; display:inline-block;">
                                            <i class="fa-solid fa-times"></i> Unpublish
                                        </a>
                                    <?php endif; ?>
                                    <a href="admin.php?delete_review=<?= $rev['review_id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus review dari customer ini?')">
                                        <i class="fa-solid fa-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center;">Tidak ada review dari customer atau terjadi kesalahan query.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <div class="modal" id="editModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 style="margin-bottom: 25px; color: #7A1E13;"><i class="fa-solid fa-edit"></i> Edit Product Details</h3>
            <form method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="product_name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" id="edit_category" required>
                            <?php
                            $cat_select_query2 = sqlsrv_query($conn, "SELECT * FROM category ORDER BY category_name ASC");
                            if($cat_select_query2) {
                                while($cat_opt2 = sqlsrv_fetch_array($cat_select_query2, SQLSRV_FETCH_ASSOC)) {
                                    echo "<option value='".htmlspecialchars($cat_opt2['category_id'])."'>".htmlspecialchars($cat_opt2['category_name'])."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price (Rupiah)</label>
                        <input type="number" name="price" id="edit_price" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            Product Image 
                            <span style="font-weight: 400; color: #999;">(Kosongkan jika tidak ingin mengubah gambar)</span>
                        </label>
                        <div id="current_image_wrap" style="margin-bottom: 10px; display:none;">
                            <img id="edit_image_preview" src="" class="image-preview" alt="Current Image">
                            <span class="current-image-label">Gambar saat ini</span>
                        </div>
                        <input type="file" name="product_image" id="edit_image_input" accept="image/*" style="padding: 8px 0; background: transparent; border: none;">
                    </div>
                    <button type="submit" name="edit_product" class="btn-submit" style="width: 100%;">Update & Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCategoryModal()">&times;</span>
            <h3 style="margin-bottom: 25px; color: #7A1E13;"><i class="fa-solid fa-folder-open"></i> Edit Category Details</h3>
            <form method="POST" action="admin.php">
                <input type="hidden" name="old_category_id" id="modal_old_cat_id">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label>Category ID</label>
                        <input type="text" name="category_id" id="modal_cat_id" required>
                    </div>
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" id="modal_cat_name" required>
                    </div>
                    <button type="submit" name="edit_category" class="btn-submit" style="width: 100%;">Update & Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, categoryId, price, desc, imageUrl) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_category').value = categoryId; 
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_description').value = desc;

            const previewWrap = document.getElementById('current_image_wrap');
            const previewImg  = document.getElementById('edit_image_preview');
            if (imageUrl && imageUrl.trim() !== '') {
                previewImg.src = 'uploads/' + imageUrl;
                previewWrap.style.display = 'block';
            } else {
                previewWrap.style.display = 'none';
                previewImg.src = '';
            }

            document.getElementById('edit_image_input').value = '';

            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openCategoryModal(id, name) {
            document.getElementById('modal_old_cat_id').value = id;
            document.getElementById('modal_cat_id').value = id;
            document.getElementById('modal_cat_name').value = name;
            document.getElementById('categoryModal').style.display = 'flex';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
            if (event.target == document.getElementById('categoryModal')) {
                closeCategoryModal();
            }
        }

        document.getElementById('edit_image_input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const previewWrap = document.getElementById('current_image_wrap');
                    const previewImg  = document.getElementById('edit_image_preview');
                    previewImg.src = ev.target.result;
                    previewWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>