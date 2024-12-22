<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Proses tambah atau edit pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'role' => $_POST['role']
    ];

    if ($_POST['action'] === 'create') {
        $user['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $database->users->insertOne($user);
    } elseif ($_POST['action'] === 'update' && isset($_POST['id'])) {
        if (!empty($_POST['password'])) {
            $user['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
        $database->users->updateOne(['_id' => new MongoDB\BSON\ObjectId($_POST['id'])], ['$set' => $user]);
    }

    header('Location: users.php');
    exit();
}

// Proses hapus pengguna
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $database->users->deleteOne(['_id' => new MongoDB\BSON\ObjectId($_GET['delete'])]);
    header('Location: users.php');
    exit();
}

$users = $database->users->find();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
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

        .card {
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
        }

        .btn-edit {
            background-color: #ffc107;
            color: white;
            border: none;
            padding: 0.25rem 1rem;
            border-radius: 4px;
        }

        .btn-hapus {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.25rem 1rem;
            border-radius: 4px;
        }

        .badge-admin {
            background-color: #4e73df;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
        }

        .badge-user {
            background-color: #858796;
            color: white;
            padding: 0.25rem 0.75rem;
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
                            Beranda
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
                        <a class="nav-link active" href="users.php">
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
                                    Keluar
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
            <h2>Kelola Pengguna</h2>
            <p>Kelola data pengguna sistem dengan mudah</p>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <i class='bx bxs-user me-2'></i>
                        Daftar Pengguna
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
                        + Tambah Pengguna
                    </button>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>NAMA</th>
                            <th>EMAIL</th>
                            <th>ROLE</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user->name); ?></td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td>
                                <span class="<?php echo $user->role === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo ucfirst($user->role); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-edit" onclick="editUser('<?php echo $user->_id; ?>')">Edit</button>
                                <button class="btn-hapus" onclick="deleteUser('<?php echo $user->_id; ?>')">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal akan ditambahkan di sini -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(id) {
            // Implementasi edit user
        }

        function deleteUser(id) {
            if(confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
                window.location.href = 'users.php?delete=' + id;
            }
        }
    </script>
</body>
</html>