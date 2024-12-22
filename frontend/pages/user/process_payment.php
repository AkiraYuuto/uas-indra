<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Terima data JSON dari request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['order_ids']) || !isset($data['payment_method'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Update status untuk semua pesanan yang dipilih
    $orderIds = is_array($data['order_ids']) ? $data['order_ids'] : [$data['order_ids']];
    $successCount = 0;

    foreach ($orderIds as $orderId) {
        $result = $database->orders->updateOne(
            [
                '_id' => new MongoDB\BSON\ObjectId($orderId),
                'user_email' => $_SESSION['user']['email'],
                'status' => 'pending' // Pastikan hanya pesanan pending yang bisa diupdate
            ],
            [
                '$set' => [
                    'status' => 'processing',
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'paid',
                    'payment_date' => time()
                ]
            ]
        );

        if ($result->getModifiedCount() > 0) {
            $successCount++;
        }
    }

    if ($successCount > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully']);
    } else {
        throw new Exception('Failed to process payment');
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 