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

// Ambil data terakhir dari deteksi_hama
$result = $koneksi->query("SELECT * FROM deteksi_hama ORDER BY detected_at DESC LIMIT 1");

if ($result && $result->num_rows > 0) {
  $data = $result->fetch_assoc();

  json_response([
    'status_hama'  => $data['status_hama'],
    'image_url'    => $data['image_url'],
    'detected_at'  => $data['detected_at']
  ]);
} else {
  // Tidak ada data deteksi tersimpan
  json_response([
    'status_hama'  => 'Tidak Terdeteksi',
    'image_url'    => null,
    'detected_at'  => null
  ]);
}
