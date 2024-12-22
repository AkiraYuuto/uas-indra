<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Mengambil statistik
$totalMenu = $database->menu->countDocuments();
$totalUser = $database->users->countDocuments(['role' => 'user']);
$totalPendapatan = 0; // Ini bisa dihitung dari collection orders jika ada

// Mengambil pesanan terbaru
$latestOrders = $database->orders->find(
    [],
    [
        'limit' => 5,
        'sort' => ['_id' => -1]
    ]
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Ayam Geprek Said</title>
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
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-stats {
            overflow: hidden;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1.25rem;
        }

        .card-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 0;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .badge {
            padding: 0.5em 1em;
            font-weight: 600;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 10px;
        }

        .dropdown-item {
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: var(--light-color);
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .stats-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0;
            color: var(--dark-color);
        }

        .stats-card-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class='bx bxs-dashboard me-2'></i>
                Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class='bx bxs-home me-1'></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">
                            <i class='bx bxs-food-menu me-1'></i>
                            Kelola Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class='bx bxs-cart me-1'></i>
                            Pesanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class='bx bxs-user-detail me-1'></i>
                            Pengguna
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class='bx bxs-user-circle me-1'></i>
                            <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="../../../frontend/logout.php">
                                    <i class='bx bx-log-out me-2'></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2 class="mb-0">Selamat Datang, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h2>
            <p class="mb-0">Kelola bisnis Anda dengan mudah dan efisien</p>
        </div>

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stats-icon bg-gradient-primary text-white">
                                <i class='bx bx-food-menu'></i>
                            </div>
                            <div class="text-end">
                                <p class="stats-card-value"><?php echo $totalMenu; ?></p>
                                <p class="stats-card-label">Menu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stats-icon bg-gradient-success text-white">
                                <i class='bx bx-user'></i>
                            </div>
                            <div class="text-end">
                                <p class="stats-card-value"><?php echo $totalUser; ?></p>
                                <p class="stats-card-label">Pelanggan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stats-icon bg-gradient-info text-white">
                                <i class='bx bx-cart'></i>
                            </div>
                            <div class="text-end">
                                <p class="stats-card-value">0</p>
                                <p class="stats-card-label">Pesanan Hari Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stats-icon bg-gradient-warning text-white">
                                <i class='bx bx-money'></i>
                            </div>
                            <div class="text-end">
                                <p class="stats-card-value">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></p>
                                <p class="stats-card-label">Pendapatan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class='bx bx-list-ul me-2'></i>
                            Pesanan Terbaru
                        </h5>
                        <a href="orders.php" class="btn btn-primary btn-sm">
                            Lihat Semua
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#<?php echo substr($order->_id, -6); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order->customer_name ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="fw-bold">
                                                Rp <?php echo number_format($order->total ?? 0, 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($order->status ?? '') {
                                                    'pending' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($order->status ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', $order->created_at ?? time()); ?></td>
                                        <td>
                                            <a href="orders.php?id=<?php echo $order->_id; ?>" class="btn btn-sm btn-primary">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>