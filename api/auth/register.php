<?php
date_default_timezone_set('Asia/Makassar');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

function response($data, $status = 200) {
  http_response_code($status);
  echo json_encode($data);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents("php://input"), true);

  $name     = $input['name'] ?? null;
  $email    = $input['email'] ?? null;
  $password = $input['password'] ?? null;

  if (!$name || !$email || !$password) {
    response(['error' => 'Semua field wajib diisi'], 400);
  }

  // Cek apakah email sudah ada
  $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->fetch_assoc()) {
    response(['error' => 'Email sudah terdaftar'], 409);
  }

  // Simpan user baru
  $hashed = password_hash($password, PASSWORD_BCRYPT);
  $stmt = $koneksi->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $email, $hashed);

  if ($stmt->execute()) {
    response(['message' => 'Registrasi berhasil']);
  } else {
    response(['error' => 'Gagal menyimpan data pengguna', 'details' => $stmt->error], 500);
  }
}
?>
