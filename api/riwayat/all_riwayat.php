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

$filter_jenis = isset($_GET['jenis']) ? strtolower(trim($_GET['jenis'])) : null;

// Bangun query berdasarkan filter
$result = false;
if ($filter_jenis === 'padi' || $filter_jenis === 'hama') {
    $stmt = $koneksi->prepare("SELECT * FROM riwayat WHERE jenis = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("s", $filter_jenis);
        $stmt->execute();
        $result = $stmt->get_result();
    }
} else {
    $result = $koneksi->query("SELECT * FROM riwayat ORDER BY created_at DESC");
}

if (!$result) {
    json_response(['error' => 'Query gagal', 'details' => $koneksi->error], 500);
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $jenis = strtolower(trim($row['jenis']));
    $ref_id = (int)$row['reference_id'];
    $detail = null;

    if ($jenis === 'padi') {
        $stmt = $koneksi->prepare("SELECT image_url, detected_class, status_padi, created_at FROM scan_padi WHERE id = ?");
        $stmt->bind_param("i", $ref_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $detail = $res->fetch_assoc();
    } elseif ($jenis === 'hama') {
        $stmt = $koneksi->prepare("SELECT image_url, status_hama, detected_at FROM deteksi_hama WHERE id = ?");
        $stmt->bind_param("i", $ref_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $detail = $res->fetch_assoc();
    }

    $row['detail'] = $detail;
    $data[] = $row;
}

json_response($data);
