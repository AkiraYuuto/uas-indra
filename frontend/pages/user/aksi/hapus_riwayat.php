<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    http_response_code(401);
    exit('Tidak memiliki akses');
}

$data = json_decode(file_get_contents('php://input'));
$idPesanan = $data->id_pesanan;

try {
    $hasil = $database->orders->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($idPesanan),
        'user_email' => $_SESSION['user']['email'],
        'status' => ['$ne' => 'pending']
    ]);

    if ($hasil->getDeletedCount() === 1) {
        http_response_code(200);
        echo json_encode(['sukses' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Riwayat pesanan tidak ditemukan atau tidak dapat dihapus']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 