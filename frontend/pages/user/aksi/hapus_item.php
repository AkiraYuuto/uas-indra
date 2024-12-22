<?php
session_start();
require_once __DIR__ . '/../../../../backend/config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    http_response_code(401);
    exit('Tidak memiliki akses');
}

$data = json_decode(file_get_contents('php://input'));
$idPesanan = $data->id_pesanan;
$indexItem = $data->index_item;

try {
    $pesanan = $database->orders->findOne([
        '_id' => new MongoDB\BSON\ObjectId($idPesanan),
        'user_email' => $_SESSION['user']['email']
    ]);

    if (!$pesanan || $pesanan->status !== 'pending') {
        http_response_code(400);
        exit('Pesanan tidak valid');
    }

    $items = $pesanan->items;
    array_splice($items, $indexItem, 1);

    // Jika tidak ada item tersisa, hapus pesanan
    if (empty($items)) {
        $database->orders->deleteOne(['_id' => new MongoDB\BSON\ObjectId($idPesanan)]);
    } else {
        // Hitung ulang total
        $total = 0;
        foreach ($items as $item) {
            $total += $item->price * $item->quantity;
        }

        $database->orders->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($idPesanan)],
            ['$set' => [
                'items' => $items,
                'total' => $total
            ]]
        );
    }

    http_response_code(200);
    echo json_encode(['sukses' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 