<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $database->orders->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($_POST['order_id'])],
        ['$set' => ['status' => $_POST['status']]]
    );
    header('Location: orders.php');
    exit();
}

// Mengambil semua pesanan
$orders = $database->orders->find([], ['sort' => ['created_at' => -1]]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Dashboard</title>
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
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .table th {
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .badge-pending {
            background-color: var(--warning-color);
            color: white;
            padding: 0.5em 1em;
            border-radius: 4px;
        }

        .badge-processing {
            background-color: var(--primary-color);
            color: white;
            padding: 0.5em 1em;
            border-radius: 4px;
        }

        .badge-completed {
            background-color: var(--success-color);
            color: white;
            padding: 0.5em 1em;
            border-radius: 4px;
        }

        .badge-cancelled {
            background-color: var(--danger-color);
            color: white;
            padding: 0.5em 1em;
            border-radius: 4px;
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
                        <a class="nav-link" href="dashboard.php">
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
                        <a class="nav-link active" href="orders.php">
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
        <div class="welcome-section">
            <h2 class="mb-0">Kelola Pesanan</h2>
            <p class="mb-0">Kelola pesanan pelanggan dengan mudah</p>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <i class='bx bxs-cart me-2'></i>
                    <span>Daftar Pesanan</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID PESANAN</th>
                                <th>PELANGGAN</th>
                                <th>MENU</th>
                                <th>TOTAL</th>
                                <th>STATUS</th>
                                <th>TANGGAL</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo substr($order->_id, -6); ?></td>
                                <td><?php echo htmlspecialchars($order->user_name ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    if (isset($order->items)) {
                                        foreach ($order->items as $item) {
                                            echo htmlspecialchars($item->menu_name) . " (x" . $item->quantity . ")<br>";
                                        }
                                    }
                                    ?>
                                </td>
                                <td>Rp <?php echo number_format($order->total ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge-<?php echo strtolower($order->status ?? 'pending'); ?>">
                                        <?php echo ucfirst($order->status ?? 'Pending'); ?>
                                    </span>
                                </td>
                                <td><?php echo isset($order->created_at) ? date('d/m/Y H:i', $order->created_at) : 'N/A'; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Update Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form action="orders.php" method="POST">
                                                    <input type="hidden" name="order_id" value="<?php echo $order->_id; ?>">
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="dropdown-item">Pending</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="orders.php" method="POST">
                                                    <input type="hidden" name="order_id" value="<?php echo $order->_id; ?>">
                                                    <input type="hidden" name="status" value="processing">
                                                    <button type="submit" class="dropdown-item">Processing</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="orders.php" method="POST">
                                                    <input type="hidden" name="order_id" value="<?php echo $order->_id; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="dropdown-item">Completed</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="orders.php" method="POST">
                                                    <input type="hidden" name="order_id" value="<?php echo $order->_id; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="dropdown-item">Cancelled</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 