<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Ayam Geprek Said</title>
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

        .menu-card {
            height: 100%;
        }

        .menu-card img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class='bx bxs-restaurant me-2'></i>
                Ayam Geprek Said
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
                            Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesanan.php">
                            <i class='bx bxs-cart me-1'></i>
                            Pesanan Saya
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class='bx bxs-user-circle me-1'></i>
                            <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class='bx bxs-user-detail me-2'></i>
                                    Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="../../logout.php">
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
            <p class="mb-0">Pesan makanan favoritmu sekarang</p>
        </div>

        <div class="row">
            <!-- Pesanan Aktif Card -->
            <div class="col-md-4">
                <div class="card card-stats h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stats-icon bg-gradient-primary text-white">
                                <i class='bx bx-cart'></i>
                            </div>
                            <div class="text-end">
                                <p class="stats-card-value">
                                    <?php 
                                    $activeOrders = $database->orders->countDocuments([
                                        'user_id' => $_SESSION['user']['id'],
                                        'status' => 'active'
                                    ]);
                                    echo $activeOrders;
                                    ?>
                                </p>
                                <p class="stats-card-label">Pesanan Aktif</p>
                            </div>
                        </div>
                        <a href="pesanan.php" class="btn btn-primary mt-3 w-100">
                            <i class='bx bx-show me-1'></i>
                            Lihat Pesanan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Menu Terbaru -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class='bx bx-food-menu me-2'></i>
                            Menu Terbaru
                        </h5>
                        <a href="menu.php" class="btn btn-primary btn-sm">
                            Lihat Semua
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $latestMenus = $database->menu->find([], [
                                'limit' => 3,
                                'sort' => ['_id' => -1]
                            ]);

                            foreach ($latestMenus as $menu) {
                                echo '<div class="col-md-4">';
                                echo '<div class="card menu-card">';
                                if (isset($menu->gambar)) {
                                    echo '<img src="../../' . htmlspecialchars($menu->gambar) . '" class="card-img-top" alt="' . htmlspecialchars($menu->nama) . '">';
                                }
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . htmlspecialchars($menu->nama) . '</h5>';
                                echo '<p class="card-text fw-bold">Rp ' . number_format($menu->harga, 0, ',', '.') . '</p>';
                                echo '<a href="menu.php" class="btn btn-primary btn-sm w-100"><i class="bx bx-cart-add me-1"></i>Pesan</a>';
                                echo '</div></div></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
