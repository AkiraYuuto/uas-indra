<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Cek autentikasi
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Proses hapus menu
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    try {
        $menuId = $_GET['delete'];
        // Ambil data menu untuk mendapatkan path gambar
        $menu = $database->menu->findOne(['_id' => new MongoDB\BSON\ObjectId($menuId)]);
        
        if ($menu && isset($menu->gambar)) {
            // Hapus file gambar
            $gambarPath = __DIR__ . '/../../' . $menu->gambar;
            if (file_exists($gambarPath)) {
                unlink($gambarPath);
            }
        }
        
        // Hapus data menu dari database
        $result = $database->menu->deleteOne(['_id' => new MongoDB\BSON\ObjectId($menuId)]);
        
        if ($result->getDeletedCount() > 0) {
            echo "<script>alert('Menu berhasil dihapus!');</script>";
        } else {
            echo "<script>alert('Gagal menghapus menu!');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
    
    header('Location: menu.php');
    exit();
}

// Proses edit menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $menuId = $_POST['id'];
        $updateData = [
            'nama' => $_POST['nama'],
            'harga' => (int)$_POST['harga'],
            'deskripsi' => $_POST['deskripsi'],
            'kategori' => $_POST['kategori']
        ];
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            // Hapus gambar lama
            $oldMenu = $database->menu->findOne(['_id' => new MongoDB\BSON\ObjectId($menuId)]);
            if ($oldMenu && isset($oldMenu->gambar)) {
                $oldGambarPath = __DIR__ . '/../../' . $oldMenu->gambar;
                if (file_exists($oldGambarPath)) {
                    unlink($oldGambarPath);
                }
            }
            
            // Upload gambar baru
            $targetDir = "../../assets/images/menu/";
            $fileName = time() . '_' . basename($_FILES['gambar']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                $updateData['gambar'] = 'assets/images/menu/' . $fileName;
            }
        }
        
        $result = $database->menu->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($menuId)],
            ['$set' => $updateData]
        );
        
        if ($result->getModifiedCount() > 0) {
            echo "<script>alert('Menu berhasil diupdate!');</script>";
        } else {
            echo "<script>alert('Tidak ada perubahan pada menu!');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
    
    header('Location: menu.php');
    exit();
}

// Proses tambah menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $menu = [
            'nama' => $_POST['nama'],
            'harga' => (int)$_POST['harga'],
            'deskripsi' => $_POST['deskripsi'],
            'kategori' => $_POST['kategori']
        ];
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            $targetDir = "../../assets/images/menu/";
            $fileName = time() . '_' . basename($_FILES['gambar']['name']);
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                $menu['gambar'] = 'assets/images/menu/' . $fileName;
            }
        }
        
        $database->menu->insertOne($menu);
        header('Location: menu.php');
        exit();
    }
}

// Mengambil data menu
$menus = $database->menu->find();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Admin Dashboard</title>
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

        .badge {
            padding: 0.5em 1em;
            font-weight: 600;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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
                        <a class="nav-link active" href="menu.php">
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
            <h2 class="mb-0">Kelola Menu</h2>
            <p class="mb-0">Kelola menu restoran Anda dengan mudah</p>
        </div>

        <!-- Menu Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">
                    <i class='bx bx-food-menu me-2'></i>
                    Daftar Menu
                </h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahMenuModal">
                    <i class='bx bx-plus me-1'></i>
                    Tambah Menu
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menus as $menu): ?>
                            <tr>
                                <td><img src='../../<?php echo htmlspecialchars($menu->gambar); ?>' width='50' height='50' alt='Menu' class="rounded"></td>
                                <td><?php echo htmlspecialchars($menu->nama); ?></td>
                                <td>Rp <?php echo number_format($menu->harga, 0, ',', '.'); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($menu->kategori); ?></span></td>
                                <td>
                                    <button type="button" class='btn btn-sm btn-warning' onclick="editMenu('<?php echo $menu->_id; ?>', '<?php echo htmlspecialchars($menu->nama); ?>', <?php echo $menu->harga; ?>, '<?php echo htmlspecialchars($menu->deskripsi ?? ''); ?>', '<?php echo htmlspecialchars($menu->kategori); ?>')">
                                        <i class='bx bx-edit-alt'></i> Edit
                                    </button>
                                    <button type="button" class='btn btn-sm btn-danger' onclick="deleteMenu('<?php echo $menu->_id; ?>')">
                                        <i class='bx bx-trash'></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Menu -->
    <div class="modal fade" id="tambahMenuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class='bx bx-plus-circle me-1'></i>
                        Tambah Menu Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="menu.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="kategori" required>
                                <option value="Ayam Geprek">Ayam Geprek</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Tambahan">Tambahan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" class="form-control" name="gambar" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-1'></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Menu -->
    <div class="modal fade" id="editMenuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class='bx bx-edit-alt me-1'></i>
                        Edit Menu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="menu.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" id="edit_harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="kategori" id="edit_kategori" required>
                                <option value="Ayam Geprek">Ayam Geprek</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Tambahan">Tambahan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Baru (Opsional)</label>
                            <input type="file" class="form-control" name="gambar" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-1'></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editMenu(id, nama, harga, deskripsi, kategori) {
        // Set nilai ke form edit
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_harga').value = harga;
        document.getElementById('edit_deskripsi').value = deskripsi;
        document.getElementById('edit_kategori').value = kategori;
        
        // Tampilkan modal edit
        var editModal = new bootstrap.Modal(document.getElementById('editMenuModal'));
        editModal.show();
    }

    function deleteMenu(id) {
        if(confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
            window.location.href = 'menu.php?delete=' + id;
        }
    }
    </script>
</body>
</html>