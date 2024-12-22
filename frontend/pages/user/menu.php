<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../../login.php');
    exit();
}

// Mengambil semua menu
$menus = $database->menu->find();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Ayam Geprek Said</title>
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

        .menu-card {
            height: 100%;
        }

        .menu-card img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .menu-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .menu-category {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            background: rgba(78, 115, 223, 0.9);
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn-order {
            width: 100%;
            border-radius: 10px;
            font-weight: 600;
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
                        <a class="nav-link" href="dashboard.php">
                            <i class='bx bxs-home me-1'></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="menu.php">
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
            <h2 class="mb-0">Menu Kami</h2>
            <p class="mb-0">Pilih menu favoritmu dan pesan sekarang</p>
        </div>

        <!-- Menu Categories -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary active">Semua</button>
                    <button type="button" class="btn btn-primary">Ayam Geprek</button>
                    <button type="button" class="btn btn-primary">Minuman</button>
                    <button type="button" class="btn btn-primary">Tambahan</button>
                </div>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="row">
            <?php foreach ($menus as $menu): ?>
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <span class="menu-category"><?php echo htmlspecialchars($menu->kategori); ?></span>
                    <?php if (isset($menu->gambar)): ?>
                    <img src="../../<?php echo htmlspecialchars($menu->gambar); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($menu->nama); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($menu->nama); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($menu->deskripsi ?? ''); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="menu-price">Rp <?php echo number_format($menu->harga, 0, ',', '.'); ?></span>
                            <button class="btn btn-primary btn-sm" onclick="addToCart('<?php echo $menu->_id; ?>')">
                                <i class='bx bx-cart-add me-1'></i>
                                Pesan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(menuId) {
            // Ambil data menu dari elemen HTML
            const menuCard = event.target.closest('.card');
            const menuName = menuCard.querySelector('.card-title').textContent;
            const menuPrice = parseInt(menuCard.querySelector('.menu-price').textContent.replace(/\D/g, ''));

            // Kirim data pesanan ke server
            fetch('add_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    menu_id: menuId,
                    menu_name: menuName,
                    price: menuPrice,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Pesanan berhasil ditambahkan!');
                    window.location.href = 'pesanan.php';
                } else {
                    alert('Gagal menambahkan pesanan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menambahkan pesanan');
            });
        }
    </script>
</body>
</html> 