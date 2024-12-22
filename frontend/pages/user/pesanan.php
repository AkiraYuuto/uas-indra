<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../../login.php');
    exit();
}

// Mengambil pesanan user dan mengkonversi ke array
$orders = $database->orders->find([
    'user_email' => $_SESSION['user']['email']
], [
    'sort' => ['created_at' => -1]
])->toArray();

// Hitung total pesanan pending
$totalSemuaPesanan = 0;
$adaPesananPending = false;
$pendingOrderIds = [];

foreach ($orders as $order) {
    if (isset($order['status']) && $order['status'] === 'pending') {
        $totalSemuaPesanan += isset($order['total']) ? $order['total'] : 0;
        $adaPesananPending = true;
        $pendingOrderIds[] = (string)$order['_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Ayam Geprek Said</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            padding: 1rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .order-card {
            margin-bottom: 1rem;
        }

        .order-items {
            padding: 1rem;
            background-color: var(--light-color);
            border-radius: 10px;
            margin-top: 1rem;
        }

        .status-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .table th {
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
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
                        <a class="nav-link active" href="pesanan.php">Pesanan Saya</a>
                    </li>
                </ul>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        N/A
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <h1>Pesanan Saya</h1>
                <p class="text-muted">Lihat status pesanan dan riwayat pembelian Anda</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary mb-3">Semua Pesanan</button>

                <!-- Daftar Pesanan -->
                <?php foreach ($orders as $order): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Pesanan #<?php echo substr((string)$order['_id'], -6); ?></h5>
                                <p class="text-muted mb-0">
                                    <?php echo date('d M Y H:i', isset($order['created_at']) ? $order['created_at'] : time()); ?>
                                </p>
                            </div>
                            <span class="badge bg-<?php 
                                echo match(isset($order['status']) ? $order['status'] : '') {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php 
                                echo match(isset($order['status']) ? $order['status'] : '') {
                                    'pending' => 'Menunggu Pembayaran',
                                    'processing' => 'Diproses',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                    default => 'N/A'
                                };
                                ?>
                            </span>
                        </div>

                        <!-- Detail Pesanan -->
                        <div class="mt-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Menu</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (isset($order['items']) ? $order['items'] : [] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(isset($item['menu_name']) ? $item['menu_name'] : 'N/A'); ?></td>
                                        <td class="text-center">
                                            <?php if (isset($order['status']) && $order['status'] === 'pending'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-secondary" onclick="ubahSemuaJumlah('<?php echo $order['_id']; ?>', 'kurang')">-</button>
                                                <span class="btn btn-outline-secondary"><?php echo isset($item['quantity']) ? $item['quantity'] : 0; ?></span>
                                                <button class="btn btn-outline-secondary" onclick="ubahSemuaJumlah('<?php echo $order['_id']; ?>', 'tambah')">+</button>
                                            </div>
                                            <?php else: ?>
                                                <?php echo isset($item['quantity']) ? $item['quantity'] : 0; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">Rp <?php echo number_format(isset($item['price']) ? $item['price'] : 0, 0, ',', '.'); ?></td>
                                        <td class="text-end">Rp <?php echo number_format((isset($item['price']) ? $item['price'] : 0) * (isset($item['quantity']) ? $item['quantity'] : 0), 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total</td>
                                        <td class="text-end fw-bold">Rp <?php echo number_format(isset($order['total']) ? $order['total'] : 0, 0, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="mt-3 d-flex justify-content-between">
                            <?php if (isset($order['status']) && $order['status'] === 'pending'): ?>
                            <button class="btn btn-danger" onclick="hapusPesanan('<?php echo $order['_id']; ?>')">
                                <i class='bx bx-trash me-1'></i>
                                Hapus Pesanan
                            </button>
                            <a href="transaksi.php?order_id=<?php echo $order['_id']; ?>" class="btn btn-primary">
                                <i class='bx bx-credit-card me-1'></i>
                                Bayar Sekarang
                            </a>
                            <?php else: ?>
                            <button class="btn btn-danger" onclick="hapusRiwayat('<?php echo $order['_id']; ?>')">
                                <i class='bx bx-trash me-1'></i>
                                Hapus Riwayat
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($adaPesananPending): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Total Semua Pesanan</h5>
                            <h5 class="mb-0">Rp <?php echo number_format($totalSemuaPesanan, 0, ',', '.'); ?></h5>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="transaksi.php?order_ids=<?php echo implode(',', $pendingOrderIds); ?>" class="btn btn-primary">
                                <i class='bx bx-credit-card me-1'></i>
                                Bayar Semua Pesanan
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tambahkan script JavaScript sebelum closing body tag -->
    <script>
    async function ubahSemuaJumlah(idPesanan, aksi) {
        try {
            const response = await fetch('aksi/ubah_semua_jumlah.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_pesanan: idPesanan,
                    aksi: aksi
                })
            });
            
            if (response.ok) {
                location.reload();
            } else {
                alert('Gagal mengubah jumlah pesanan');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        }
    }

    async function hapusPesanan(idPesanan) {
        if (confirm('Apakah Anda yakin ingin menghapus pesanan ini?')) {
            try {
                const response = await fetch('aksi/hapus_pesanan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_pesanan: idPesanan
                    })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Gagal menghapus pesanan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem');
            }
        }
    }

    async function hapusRiwayat(idPesanan) {
        if (confirm('Apakah Anda yakin ingin menghapus riwayat pesanan ini?')) {
            try {
                const response = await fetch('aksi/hapus_riwayat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_pesanan: idPesanan
                    })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Gagal menghapus riwayat pesanan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem');
            }
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 