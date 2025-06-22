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

$id = $_GET['id'] ?? null;
if (!$id) {
    json_response(['error' => 'ID riwayat diperlukan'], 400);
}

// Ambil data dari tabel riwayat berdasarkan ID
$stmt = $koneksi->prepare("SELECT reference_id, jenis FROM riwayat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$riwayat = $result->fetch_assoc();

if (!$riwayat) {
    json_response(['error' => 'Riwayat tidak ditemukan'], 404);
}

$reference_id = $riwayat['reference_id'];
$jenis = strtolower(trim($riwayat['jenis']));

// Jika jenis adalah padi
if ($jenis === 'padi') {
    $stmt = $koneksi->prepare("SELECT image_url, detected_class, info, rekomendasi FROM scan_padi WHERE id = ?");
    $stmt->bind_param("i", $reference_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $padi = $result->fetch_assoc();

    if (!$padi) {
        json_response(['error' => 'Data scan padi tidak ditemukan'], 404);
    }

  // Mapping class ke nama tabel
    $class_raw = strtolower(trim($padi['detected_class']));
    $class_map = [
    'bacteria_leaf_blight' => 'bacterialeafblight',
    'brown_spot' => 'brownspot',
    'leaf_smut' => 'leafsmut'
];

    $table_name = $class_map[$class_raw] ?? null;

    if ($table_name) {
        $query = "SELECT id, name_product, isi, harga, image_url, product_url FROM `$table_name`";
        $produk_result = $koneksi->query($query);
        $produk_list = [];

        while ($row = $produk_result->fetch_assoc()) {
        $produk_list[] = $row;
        }

        $padi['produk_rekomendasi'] = $produk_list;
    } else {
        $padi['produk_rekomendasi'] = [];
    } 

        json_response($padi);
} else {
    json_response(['error' => "Jenis '$jenis' tidak dikenali atau belum didukung"], 400);
}
