<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../../login.php');
    exit();
}

// Ambil order_id atau order_ids dari URL
$orderIds = [];
if (isset($_GET['order_id'])) {
    $orderIds[] = $_GET['order_id'];
} elseif (isset($_GET['order_ids'])) {
    $orderIds = explode(',', $_GET['order_ids']);
}

if (empty($orderIds)) {
    header('Location: pesanan.php');
    exit();
}

// Ambil data pesanan
$orders = [];
$totalPembayaran = 0;

foreach ($orderIds as $orderId) {
    $order = $database->orders->findOne([
        '_id' => new MongoDB\BSON\ObjectId($orderId),
        'user_email' => $_SESSION['user']['email'],
        'status' => 'pending'
    ]);

    if ($order) {
        $orders[] = $order;
        $totalPembayaran += $order->total;
    }
}

if (empty($orders)) {
    header('Location: pesanan.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Ayam Geprek Said</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fc;
        }
        .payment-method {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #4e73df;
            background-color: #f8f9fc;
        }
        .payment-method.selected {
            border-color: #4e73df;
            background-color: #eef2ff;
        }
        .payment-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Ayam Geprek Said</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesanan.php">Pesanan Saya</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>Metode Pembayaran</h4>
                        
                        <!-- QRIS -->
                        <div class="payment-method" onclick="selectPayment(this, 'qris')">
                            <div class="d-flex align-items-center">
                                <img src="../../assets/images/qris.png" alt="QRIS" class="payment-logo me-3">
                                <div>
                                    <h5 class="mb-1">QRIS</h5>
                                    <p class="mb-0 text-muted">Bayar dengan QRIS</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer -->
                        <div class="payment-method" onclick="selectPayment(this, 'transfer')">
                            <div class="d-flex align-items-center">
                                <img src="../../assets/images/bank.png" alt="Bank Transfer" class="payment-logo me-3">
                                <div>
                                    <h5 class="mb-1">Transfer Bank</h5>
                                    <p class="mb-0 text-muted">Transfer manual ke rekening</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bayar di Tempat -->
                        <div class="payment-method" onclick="selectPayment(this, 'cod')">
                            <div class="d-flex align-items-center">
                                <img src="../../assets/images/cod.png" alt="COD" class="payment-logo me-3">
                                <div>
                                    <h5 class="mb-1">Bayar di Tempat</h5>
                                    <p class="mb-0 text-muted">Bayar saat pesanan tiba</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4>Ringkasan Pembayaran</h4>
                        <?php foreach ($orders as $order): ?>
                            <div class="mb-3">
                                <h6>Pesanan #<?php echo substr($order['_id'], -6); ?></h6>
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="d-flex justify-content-between">
                                        <small><?php echo $item['menu_name']; ?> (x<?php echo $item['quantity']; ?>)</small>
                                        <small>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Total</h5>
                            <h5>Rp <?php echo number_format($totalPembayaran, 0, ',', '.'); ?></h5>
                        </div>
                        
                        <button id="btnBayar" class="btn btn-primary w-100" onclick="prosesPembayaran()" disabled>
                            Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let selectedPaymentMethod = '';

    function selectPayment(element, method) {
        // Hapus kelas selected dari semua metode pembayaran
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Tambah kelas selected ke metode yang dipilih
        element.classList.add('selected');
        selectedPaymentMethod = method;
        
        // Enable tombol bayar
        document.getElementById('btnBayar').disabled = false;
    }

    async function prosesPembayaran() {
        if (!selectedPaymentMethod) {
            alert('Silakan pilih metode pembayaran');
            return;
        }

        try {
            const orderIds = <?php echo json_encode($orderIds); ?>;
            const response = await fetch('process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_ids: orderIds,
                    payment_method: selectedPaymentMethod
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'pesanan.php';
                } else {
                    alert(data.message || 'Gagal memproses pembayaran');
                }
            } else {
                throw new Error('Network response was not ok');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        }
    }
    </script>
</body>
</html>