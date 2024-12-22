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

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Buat order baru
    $order = [
        'user_email' => $_SESSION['user']['email'],
        'user_name' => $_SESSION['user']['name'],
        'status' => 'pending',
        'created_at' => time(),
        'items' => [
            [
                'menu_id' => $data['menu_id'],
                'menu_name' => $data['menu_name'],
                'price' => $data['price'],
                'quantity' => $data['quantity']
            ]
        ],
        'total' => $data['price'] * $data['quantity']
    ];

    // Simpan ke database
    $result = $database->orders->insertOne($order);

    if ($result->getInsertedCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Order created successfully']);
    } else {
        throw new Exception('Failed to create order');
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 