<?php
$serverName = "LAPTOP-H61L0EMI\\SQLEXPRESS";
$connectionInfo = array("Database" => "maru_bake_house", "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
} else {
    echo "✅ Koneksi berhasil!";
    
    // Cek tabel product
    $q = sqlsrv_query($conn, "SELECT COUNT(*) as total FROM product");
    if ($q === false) {
        echo "<br>❌ Query gagal: <pre>" . print_r(sqlsrv_errors(), true) . "</pre>";
    } else {
        $row = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC);
        echo "<br>✅ Tabel product ditemukan, total data: " . $row['total'];
    }
}
?>