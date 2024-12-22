<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    // Pastikan extension MongoDB sudah terinstall
    if (!extension_loaded('mongodb')) {
        throw new Exception('MongoDB PHP extension belum terinstall');
    }

    // Koneksi ke MongoDB
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $database = $mongoClient->ayamgeprek_said;
    $users = $database->users;
    
} catch (Exception $e) {
    die("Error koneksi database: " . $e->getMessage());
}
?>
