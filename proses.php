<?php
// 1. Jalankan session di baris paling pertama untuk membaca status login & logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connection.php';

// =========================================================================
// LOGIKA LOGOUT
// =========================================================================
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    header("Location: login.php");
    exit();
}

// =========================================================================
// LOGIKA INSERT KATEGORI BARU
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aksi']) && $_POST['aksi'] == 'insert_category') {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    $sql_cek_cat = "SELECT category_id FROM maru_bake_house.dbo.category WHERE category_id = ?";
    $query_cek_cat = sqlsrv_query($conn, $sql_cek_cat, array($category_id));

    if ($query_cek_cat === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    if (sqlsrv_has_rows($query_cek_cat)) {
        echo "<script>
                alert('Gagal Tambah Kategori! Category ID ($category_id) sudah terdaftar.');
                window.location.href = 'admin.php';
              </script>";
        exit();
    }

    $sql_insert_cat = "INSERT INTO maru_bake_house.dbo.category (category_id, category_name) VALUES (?, ?)";
    $stmt = sqlsrv_query($conn, $sql_insert_cat, array($category_id, $category_name));

    if ($stmt === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    header("Location: admin.php");
    exit();
}

// =========================================================================
// LOGIKA INSERT PRODUK BARU + UPLOAD GAMBAR
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aksi']) && $_POST['aksi'] == 'insert') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    $nama_gambar_baru = null; // default value jika gambar kosong

    // Manajemen Upload File Gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp  = $_FILES['image']['tmp_name'];
        
        $ekstensi_boleh = array('jpg', 'jpeg', 'png');
        $x = explode('.', $file_name);
        $ekstensi = strtolower(end($x));

        if (in_array($ekstensi, $ekstensi_boleh) === true) {
            if ($file_size < 2097152) { // Batas 2 Megabyte
                $nama_gambar_baru = time() . '_' . uniqid() . '.' . $ekstensi;
                
                // Pastikan folder img ada sebelum memindahkan file
                if (!file_exists('img')) {
                    mkdir('img', 0777, true);
                }
                
                move_uploaded_file($file_tmp, 'img/' . $nama_gambar_baru);
            } else {
                echo "<script>alert('Gagal! Ukuran gambar terlalu besar (Maks 2MB).'); window.location.href = 'admin.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Gagal! Format file harus berupa JPG, JPEG, atau PNG.'); window.location.href = 'admin.php';</script>";
            exit();
        }
    }

    // Cek duplikasi ID Produk
    $sql_cek = "SELECT product_id FROM maru_bake_house.dbo.product WHERE product_id = ?";
    $query_cek = sqlsrv_query($conn, $sql_cek, array($product_id));

    if ($query_cek === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    if (sqlsrv_has_rows($query_cek)) {
        echo "<script>
                alert('Gagal Tambah Produk! Product ID ($product_id) sudah digunakan oleh produk lain.');
                window.location.href = 'admin.php';
              </script>";
        exit();
    }

    $sql_insert = "INSERT INTO maru_bake_house.dbo.product (product_id, category_id, product_name, description, price, image_url) VALUES (?, ?, ?, ?, ?, ?)";
    $params = array($product_id, $category_id, $product_name, $description, $price, $nama_gambar_baru);
    $stmt = sqlsrv_query($conn, $sql_insert, $params);

    if ($stmt === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    header("Location: admin.php");
    exit();
}

// =========================================================================
// LOGIKA UPDATE DATA PRODUK + PERBARUI GAMBAR
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aksi']) && $_POST['aksi'] == 'update') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    if (empty($product_id)) {
        echo "<script>
                alert('Gagal Update! Silakan pilih data produk dari tabel terlebih dahulu.');
                window.location.href = 'admin.php';
              </script>";
        exit();
    }

    // Cek jika user mengunggah berkas gambar baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp  = $_FILES['image']['tmp_name'];
        
        $ekstensi_boleh = array('jpg', 'jpeg', 'png');
        $x = explode('.', $file_name);
        $ekstensi = strtolower(end($x));

        if (in_array($ekstensi, $ekstensi_boleh) === true) {
            if ($file_size < 2097152) {
                $nama_gambar_baru = time() . '_' . uniqid() . '.' . $ekstensi;
                
                if (!file_exists('img')) {
                    mkdir('img', 0777, true);
                }
                
                move_uploaded_file($file_tmp, 'img/' . $nama_gambar_baru);

                // Hapus file gambar lama agar media penyimpanan tidak penuh
                $sql_lama = "SELECT image_url FROM maru_bake_house.dbo.product WHERE product_id = ?";
                $query_lama = sqlsrv_query($conn, $sql_lama, array($product_id));
                if ($row_lama = sqlsrv_fetch_array($query_lama, SQLSRV_FETCH_ASSOC)) {
                    if (!empty($row_lama['image_url']) && file_exists('img/' . $row_lama['image_url'])) {
                        unlink('img/' . $row_lama['image_url']);
                    }
                }

                // Jalankan kueri update yang menyertakan data gambar baru
                $sql_update = "UPDATE maru_bake_house.dbo.product 
                               SET category_id = ?, product_name = ?, description = ?, price = ?, image_url = ? 
                               WHERE product_id = ?";
                $params = array($category_id, $product_name, $description, $price, $nama_gambar_baru, $product_id);
            } else {
                echo "<script>alert('Gagal! Ukuran gambar maksimal 2MB.'); window.location.href = 'admin.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Gagal! Ekstensi file tidak valid.'); window.location.href = 'admin.php';</script>";
            exit();
        }
    } else {
        // Jalankan kueri update biasa tanpa merubah kolom gambar bawaan
        $sql_update = "UPDATE maru_bake_house.dbo.product 
                       SET category_id = ?, product_name = ?, description = ?, price = ? 
                       WHERE product_id = ?";
        $params = array($category_id, $product_name, $description, $price, $product_id);
    }

    $stmt = sqlsrv_query($conn, $sql_update, $params);

    if ($stmt === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    header("Location: admin.php");
    exit();
}

// =========================================================================
// LOGIKA HAPUS PRODUK
// =========================================================================
if (isset($_GET['hapus'])) {
    $product_id = $_GET['hapus'];

    // Hapus berkas fisik gambar di folder img sebelum record database dihapus
    $sql_img = "SELECT image_url FROM maru_bake_house.dbo.product WHERE product_id = ?";
    $query_img = sqlsrv_query($conn, $sql_img, array($product_id));
    
    if ($row_img = sqlsrv_fetch_array($query_img, SQLSRV_FETCH_ASSOC)) {
        if (!empty($row_img['image_url']) && file_exists('img/' . $row_img['image_url'])) {
            unlink('img/' . $row_img['image_url']);
        }
    }

    $sql_delete = "DELETE FROM maru_bake_house.dbo.product WHERE product_id = ?";
    $stmt = sqlsrv_query($conn, $sql_delete, array($product_id));

    if ($stmt === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    header("Location: admin.php");
    exit();
}

// =========================================================================
// LOGIKA HAPUS REVIEW PELANGGAN
// =========================================================================
if (isset($_GET['hapus_review'])) {
    $review_id = $_GET['hapus_review'];

    $sql_delete_review = "DELETE FROM maru_bake_house.dbo.review WHERE review_id = ?";
    $stmt = sqlsrv_query($conn, $sql_delete_review, array($review_id));

    if ($stmt === false) {
        die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }

    header("Location: admin.php");
    exit();
}
?>