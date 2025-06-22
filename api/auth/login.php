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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents("php://input"), true);
  
  $email = $input['email'] ?? null;
  $password = $input['password'] ?? null;

  if (!$email || !$password) {
    json_response(['error' => 'Email dan password wajib diisi'], 400);
  }

  $stmt = $koneksi->prepare("SELECT id, name, email, password, created_at FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    unset($user['password']);
    json_response(['message' => 'Login berhasil', 'user' => $user]);
  } else {
    json_response(['error' => 'Email atau password salah'], 401);
  }
}
