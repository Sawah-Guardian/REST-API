<?php
date_default_timezone_set('Asia/Makassar');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

function json_response($data, $status = 200) {
  http_response_code($status);
  echo json_encode($data);
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
  $user_id = $_POST['user_id'];
  $detected_class = $_POST['detected_class'];
  $status_padi = $_POST['status_padi'];
  $info = $_POST['info'];
  $rekomendasi = $_POST['rekomendasi'];

// Validasi input
if (!$user_id || !$detected_class || !$status_padi || !$info || !$rekomendasi) {
  json_response(['error' => 'Data tidak lengkap'], 400);
}

// Auto generate nama file: hama001_YYYYMMDD.jpg
  $date = date('Ymd_His');
  $rand = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
  $new_filename = "padi{$rand}_{$date}.jpg";

  $upload_dir  = __DIR__ . '/padi/';
  $upload_path = $upload_dir . $new_filename;
  $base_url    = "https://tkj-3b.com/tkj-3b.com/opengate/padi/";
  $image_url   = $base_url . $new_filename;

  if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
    // Simpan ke tabel scan_padi
    $stmt = $koneksi->prepare("INSERT INTO scan_padi (user_id, image_url, detected_class, status_padi, info, rekomendasi) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $image_url, $detected_class, $status_padi, $info, $rekomendasi);

     if ($stmt->execute()) {
  $scan_id = $koneksi->insert_id;

  // Simpan ke riwayat
  $riwayat = $koneksi->prepare("INSERT INTO riwayat (user_id, reference_id, jenis) VALUES (?, ?, 'padi')");
  $riwayat->bind_param("ii", $user_id, $scan_id);
  $riwayat->execute();

  json_response([
    'message' => 'Scan padi berhasil disimpan',
    'scan_id' => $scan_id,
    'image_url' => $image_url
  ]);
  
} else {
  json_response(['error' => 'Gagal simpan ke database', 'details' => $stmt->error], 500);
}
  } else {
    json_response(['error' => 'Upload gambar gagal'], 500);
  }
} else {
  json_response(['error' => 'Metode tidak diizinkan atau file tidak ditemukan'], 405);
}
