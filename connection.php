<?php
$serverName = "localhost\SQLEXPRESS";
$database = "maru_bake_house";
$uid = "";
$password = "";

$connection = [
    "Database" => $database,
    "UID" => $uid,
    "PWD" => $password,
    "CharacterSet" => "UTF-8" 
];

$conn = sqlsrv_connect($serverName, $connection);

if ($conn === false) {
    die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
}
?>