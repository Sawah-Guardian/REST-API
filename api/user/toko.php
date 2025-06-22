<?php
date_default_timezone_set('Asia/Makassar');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Metode tidak diizinkan'], 405);
}

$query = "SELECT id, name_toko, image_url, toko_url FROM toko ORDER BY id ASC";
$result = $koneksi->query($query);

$toko_list = [];

while ($row = $result->fetch_assoc()) {
    $toko_list[] = $row;
}

json_response($toko_list);
