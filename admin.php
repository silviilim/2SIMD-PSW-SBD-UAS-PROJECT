<?php
session_start();

// 1. PENGATURAN KONEKSI DATABASE MARU
$host = "localhost";
$user = "root";         
$pass = "";           
$db   = "maru_bake_house";       

$conn = mysqli_connect('localhost', 'root', '', 'maru_bake_house');

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $review_text   = mysqli_real_escape_string($conn, $_POST['review_text']);
    
    // Status 'pending' agar perlu persetujuan Admin
        $query = "INSERT INTO reviews (customer_name, review_text, status, review_date) 
          VALUES ('$customer_name', '$review_text', 'pending', NOW())";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Terima kasih! Review Anda sedang diproses oleh admin.'); window.location='customer.php#contactSec';</script>";
        exit();
    }

    if (isset($_GET['approve_id'])) {
    $id = $_GET['approve_id'];
    mysqli_query($conn, "UPDATE reviews SET status = 'published' WHERE id = '$id'");
    header("Location: admin.php"); // Refresh halaman admin
}

}

// 2. PROSES TAMBAH PRODUK BARU
if (isset($_POST['add_product'])) {
    // Menangkap input ID dari form
    $id_produk    = mysqli_real_escape_string($conn, $_POST['product_id']); 
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category     = mysqli_real_escape_string($conn, $_POST['category']);
    $price        = intval($_POST['price']);
    $description  = mysqli_real_escape_string($conn, $_POST['description']);

    // Query menyertakan kolom 'id'
    $insert = mysqli_query($conn, "INSERT INTO products (id, product_name, category, price, description) VALUES ('$id_produk', '$product_name', '$category', '$price', '$description')");
    
    if ($insert) {
        header("Location: admin.php?status=success_add");
        exit();
    }
}

// 3. PROSES EDIT PRODUK (UPDATE)
if (isset($_POST['edit_product'])) {
    $id           = mysqli_real_escape_string($conn, $_POST['id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category     = mysqli_real_escape_string($conn, $_POST['category']);
    $price        = intval($_POST['price']);
    $description  = mysqli_real_escape_string($conn, $_POST['description']);

    $update = mysqli_query($conn, "UPDATE products SET product_name='$product_name', category='$category', price='$price', description='$description' WHERE id='$id'");
    
    if ($update) {
        header("Location: admin.php?status=success_update");
        exit();
    }
}

// 4. PROSES HAPUS PRODUK (DELETE)
if (isset($_GET['delete_product'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_product']);
    $delete = mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    
    if ($delete) {
        header("Location: admin.php?status=success_delete");
        exit();
    }
}

// 5. PROSES HAPUS REVIEW (DELETE REVIEW)
if (isset($_GET['delete_review'])) {
    $id = intval($_GET['delete_review']);
    $delete_rev = mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");
    if ($delete_rev) { 
        header("Location: admin.php?status=success_delete_review"); 
        exit(); 
    }
}

// --- TAMBAHAN LOGIKA PUBLISH & UNPUBLISH ---
if (isset($_GET['approve_review'])) {
    $id = intval($_GET['approve_review']);
    mysqli_query($conn, "UPDATE reviews SET status='published' WHERE id=$id");
    header("Location: admin.php?status=success_update");
    exit();
}

if (isset($_GET['unpublish_review'])) {
    $id = intval($_GET['unpublish_review']);
    mysqli_query($conn, "UPDATE reviews SET status='pending' WHERE id=$id");
    header("Location: admin.php?status=success_update");
    exit();
}
// ------------------------------------------

// 6. AMBIL DATA PRODUK & REVIEW UNTUK DITAMPILKAN
$products_query = mysqli_query($conn, "SELECT * FROM products ORDER BY category ASC, product_name ASC");
$reviews_query = mysqli_query($conn, "SELECT * FROM reviews ORDER BY status ASC, id DESC");
?>

<h3>Daftar Kategori</h3>
<ul>
    <?php
    $cat_query = mysqli_query($conn, "SELECT * FROM categories");
    while($cat = mysqli_fetch_assoc($cat_query)) {
        echo "<li>" . $cat['category_name'] . "</li>";
    }
    ?>
</ul>
<?php
if (isset($_POST['submit_category'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    $insert = mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$cat_name')");
    
    if ($insert) {
        // Redirect ke halaman yang sama untuk membersihkan POST data
        header("Location: admin.php"); 
        exit(); // Sangat penting agar script berhenti setelah redirect
    }
}
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
        
        /* SIDEBAR STYLE */
        .sidebar { width: 260px; background: #7A1E13; color: #F2EAD3; padding: 30px 20px; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar h2 { font-size: 26px; font-weight: 800; text-align: center; margin-bottom: 40px; color: #FFFFFF; letter-spacing: 1px; }
        .sidebar a { color: #F2EAD3; text-decoration: none; padding: 12px 15px; border-radius: 10px; font-weight: 500; display: flex; align-items: center; gap: 15px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #581C14; color: #FFFFFF; }
        .sidebar .logout { margin-top: auto; background: rgba(0,0,0,0.2); }
        .sidebar .logout:hover { background: #581C14; }

        /* MAIN CONTENT STYLE */
        .main-content { margin-left: 260px; flex: 1; padding: 40px 5%; }
        .header-dash { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #7A1E13; padding-bottom: 15px; }
        .header-dash h1 { font-size: 28px; font-weight: 700; }
        
        /* ALERT NOTIFIKASI */
        .alert { background: #581C14; color: white; padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: 500; font-size: 14px; display: flex; justify-content: space-between; align-items: center; }
        .alert button { background: none; border: none; color: white; font-size: 18px; cursor: pointer; }

        /* KARTU FORM DAN TABEL */
        .card { background: #FFFFFF; border-radius: 20px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .card h3 { font-size: 20px; margin-bottom: 20px; color: #7A1E13; display: flex; align-items: center; gap: 10px; }
        
        /* FORM CONTROLS */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full-width { grid-column: span 2; }
        .form-group label { font-size: 13px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { padding: 12px 15px; border: 1px solid #DDD; border-radius: 10px; font-size: 14px; outline: none; background: #F9F9F9; }
        .form-group textarea { height: 80px; resize: none; }
        .btn-submit { background: #7A1E13; color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; width: fit-content; margin-top: 10px; }
        .btn-submit:hover { background: #581C14; }

        /* DATA TABLES */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 14px 18px; font-size: 14px; border-bottom: 1px solid #EEE; }
        th { background: #7A1E13; color: white; font-weight: 600; }
        tr:hover { background: #FDFBF7; }
        
        /* BADGES & ACTION BUTTONS */
        .badge-category { background: #F2EAD3; color: #581C14; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; }
        .btn-edit { background: #AB826A; color: white; margin-right: 5px; }
        .btn-edit:hover { background: #8C6550; }
        .btn-delete { background: #7A1E13; color: white; }
        .btn-delete:hover { background: #581C14; }

        /* MODAL UNTUK EDIT */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 20px; width: 100%; max-width: 600px; position: relative; }
        .close-modal { position: absolute; top: 20px; right: 25px; font-size: 24px; cursor: pointer; color: #AAA; }
        .close-modal:hover { color: #581C14; }

        /* TAMBAHAN STYLE PUBLISH */
        .btn-publish { background: #28a745; color: white; }
        .btn-publish:hover { background: #218838; }
        .btn-unpublish { background: #ffc107; color: #000; }
        .btn-unpublish:hover { background: #e0a800; }

        /* Styling untuk Form */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* Agar padding tidak merusak lebar */
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
}

.btn-primary {
    background-color: #28a745; /* Hijau agar beda dari tombol edit/delete */
    color: white;
}

.btn-primary:hover {
    background-color: #218838;
}

/* Membungkus form agar rapi */
#add-category {
    background: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 20px;
}

/* Background Putih dengan Border Radius */
.category-panel-white {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 15px;
    border: 1px solid #e0e0e0; /* Border halus untuk memisahkan dari background halaman */
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    margin-top: 20px;
    margin-bottom: 40px;
}

.panel-title-dark {
    color: #6b2626; /* Judul berwarna marun */
    margin-top: 0;
    margin-bottom: 20px;
}

/* Label & Input */
.form-group-dark {
    margin-bottom: 20px;
}

.form-group-dark label {
    display: block;
    margin-bottom: 8px;
    color: #333; /* Teks gelap */
    font-weight: 500;
}

.input-bordered {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    background: #f9f9f9; /* Background input sedikit abu-abu agar kontras */
    color: #333;
    box-sizing: border-box;
}

/* Tombol Marun */
.btn-maroon {
    background: #6b2626;
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.btn-maroon:hover {
    background: #501d1d;
}
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>MARU DASH</h2>
        <a href="#menu-management" class="active"><i class="fa-solid fa-cake-candles"></i> Menu Management</a>
        <a href="#customer-reviews"><i class="fa-solid fa-star"></i> Customer Reviews</a>
        
        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
        
        
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
                    if($_GET['status'] == 'success_update') echo "✏️ Review Has Been Updated!";
                    if($_GET['status'] == 'success_delete') echo "🗑️ Product Succesfully Removed!";
                    if($_GET['status'] == 'success_delete_review') echo "🗑️ Customer Review Deleted!";
                    ?>
                </span>
                <button onclick="document.getElementById('alertBox').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <div id="menu-management">
            <div class="card">
                <h3><i class="fa-solid fa-plus-circle"></i> Add New Product Menu</h3>
                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label>Product ID</label>
                        <input type="text" name="product_id" placeholder="e.g. P001" required>
                    </div>
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="product_name" placeholder="e.g. Choco Pistachio Dubai Kunafa" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Pumpkin Donut">Pumpkin Donut</option>
                            <option value="Mochi Donut">Mochi Donut</option>
                            <option value="Puffy Donut">Puffy Donut</option>
                            <option value="Melted Cheese Tart">Melted Cheese Tart</option>
                            <option value="Loaf Cheesecake">Loaf Cheesecake</option>
                            <option value="Tiramisu">Tiramisu</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Price (Rupiah)</label>
                        <input type="number" name="price" placeholder="e.g. 25000" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" placeholder="Provide attractive product details..." required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <button type="submit" name="add_product" class="btn-submit">Add Product</button>
                    </div>
                </form>
            </div>

<div class="category-panel-white">
    <h3 class="panel-title-dark">Add New Category</h3>
    <form action="" method="post">
        <div class="form-group-dark">
            <label>Category ID :</label>
            <input type="text" name="category_id" class="input-bordered" placeholder="Ex : 3" required>
        </div>

        <div class="form-group-dark">
            <label>Category Name :</label>
            <input type="text" name="category_name" class="input-bordered" placeholder="Ex : Pastry" required>
        </div>

        <button type="submit" name="submit_category" class="btn-maroon">Add Category</button>
    </form>
</div>
            <div class="card">
                <h3><i class="fa-solid fa-list"></i> Active Menu List (Live)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($products_query) > 0): ?>
                            <?php while($prod = mysqli_fetch_assoc($products_query)): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #7A1E13;"><?= htmlspecialchars($prod['id']) ?></td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($prod['product_name']) ?></td>
                                    <td><span class="badge-category"><?= htmlspecialchars($prod['category']) ?></span></td>
                                    <td style="font-weight: 600;">Rp <?= number_format($prod['price'], 0, ',', '.') ?></td>
                                    <td style="color: #666; font-size: 13px; max-width: 300px;"><?= htmlspecialchars($prod['description']) ?></td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <button class="btn-action btn-edit" onclick="openEditModal('<?= $prod['id'] ?>', '<?= addslashes($prod['product_name']) ?>', '<?= addslashes($prod['category']) ?>', <?= $prod['price'] ?>, '<?= addslashes($prod['description']) ?>')">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <a href="admin.php?delete_product=<?= $prod['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #999; padding: 30px;">Belum ada produk di database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($reviews_query) > 0): ?>
                        <?php while($rev = mysqli_fetch_assoc($reviews_query)): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($rev['customer_name']) ?></td>
                                <td style="font-style: italic; color: #555;">"<?= htmlspecialchars($rev['review_text']) ?>"</td>
                                <td><?= htmlspecialchars($rev['review_date']) ?></td>
                                <td>
                                    <?php if($rev['status'] == 'published'): ?>
                                        <span class="badge-category" style="background:#d4edda; color:#155724;">Published</span>
                                    <?php else: ?>
                                        <span class="badge-category" style="background:#fff3cd; color:#856404;">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($rev['status'] == 'pending'): ?>
                                        <a href="admin.php?approve_review=<?= $rev['id'] ?>" class="btn-action btn-publish" style="margin-bottom:5px; display:inline-block;">
                                            <i class="fa-solid fa-check"></i> Publish
                                        </a>
                                    <?php else: ?>
                                        <a href="admin.php?unpublish_review=<?= $rev['id'] ?>" class="btn-action btn-unpublish" style="margin-bottom:5px; display:inline-block;">
                                            <i class="fa-solid fa-times"></i> Unpublish
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="admin.php?delete_review=<?= $rev['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus review dari customer ini?')">
                                        <i class="fa-solid fa-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999; padding: 30px;">Belum ada review yang masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="modal" id="editModal">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h3 style="margin-bottom: 25px; color: #7A1E13;"><i class="fa-solid fa-edit"></i> Edit Product Details</h3>
                
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-grid" style="grid-template-columns: 1fr;">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="product_name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" id="edit_category" required>
                                <option value="Pumpkin Donut">Pumpkin Donut</option>
                                <option value="Mochi Donut">Mochi Donut</option>
                                <option value="Puffy Donut">Puffy Donut</option>
                                <option value="Melted Cheese Tart">Melted Cheese Tart</option>
                                <option value="Loaf Cheesecake">Loaf Cheesecake</option>
                                <option value="Tiramisu">Tiramisu</option>
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
                        <button type="submit" name="edit_product" class="btn-submit" style="width: 100%;">Update & Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openEditModal(id, name, category, price, desc) {
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_category').value = category;
                document.getElementById('edit_price').value = price;
                document.getElementById('edit_description').value = desc;
                
                document.getElementById('editModal').style.display = 'flex';
            }

            function closeModal() {
                document.getElementById('editModal').style.display = 'none';
            }

            window.onclick = function(event) {
                const modal = document.getElementById('editModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    </body>
</html>