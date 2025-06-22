<?php
date_default_timezone_set('Asia/Makassar');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

// Debug error sementara (boleh dihapus di production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Metode tidak diizinkan'], 405);
}

$user_id = $_GET['user_id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    json_response(['error' => 'Parameter user_id diperlukan dan harus berupa angka'], 400);
}

$stmt = $koneksi->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    json_response(['error' => 'User tidak ditemukan'], 404);
}

json_response($user);
