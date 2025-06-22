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
  $status_hama = $_POST['status_hama'] ?? null;
  $user_id     = $_POST['user_id'] ?? null;

  if (!$status_hama || !$user_id) {
    json_response(['error' => 'Data tidak lengkap'], 400);
  }

  // Auto generate nama file: hama001_YYYYMMDD.jpg
  $date = date('Ymd_His');
  $rand = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
  $new_filename = "hama{$rand}_{$date}.jpg";

  $upload_dir  = __DIR__ . '/hama/';
  $upload_path = $upload_dir . $new_filename;
  $base_url    = "https://tkj-3b.com/tkj-3b.com/opengate/hama/";
  $image_url   = $base_url . $new_filename;

  if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
    // Simpan ke tabel deteksi_hama
    $stmt = $koneksi->prepare("INSERT INTO deteksi_hama (image_url, status_hama) VALUES (?, ?)");
    $stmt->bind_param("ss", $image_url, $status_hama);

    if ($stmt->execute()) {
      $hama_id = $koneksi->insert_id;

      // Simpan ke riwayat
      $riwayat = $koneksi->prepare("INSERT INTO riwayat (user_id, reference_id, jenis) VALUES (?, ?, 'hama')");
      $riwayat->bind_param("ii", $user_id, $hama_id);
      $riwayat->execute();

      json_response([
        'message' => 'Data deteksi hama berhasil disimpan',
        'hama_id' => $hama_id,
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
