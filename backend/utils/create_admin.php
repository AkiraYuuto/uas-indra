<?php
require_once __DIR__ . '/../config/database.php';

// Data admin default
$admin = [
    'name' => 'Administrator',
    'email' => 'admin@admin.com',
    'password' => password_hash('admin123', PASSWORD_BCRYPT),
    'role' => 'admin'
];

// Cek apakah admin sudah ada
$existingAdmin = $database->users->findOne(['email' => $admin['email']]);

if (!$existingAdmin) {
    $database->users->insertOne($admin);
    echo "Admin berhasil dibuat!\n";
    echo "Email: admin@admin.com\n";
    echo "Password: admin123\n";
} else {
    echo "Admin sudah ada dalam database!\n";
}
?> 