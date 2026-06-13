<?php
$serverName = "LAPTOP-H61L0EMI\\SQLEXPRESS";
$connection = [
    "Database"     => "maru_bake_house",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connection);

if ($conn === false) {
    die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
}

echo "";
?>