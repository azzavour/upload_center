<?php

try {
    // DSN (Data Source Name) untuk SQL Server
    $dsn = "sqlsrv:Server=127.0.0.1,1433;Database=upload_center";

    // Coba buat koneksi PDO
    $pdo = new PDO($dsn, 'sa', 'kucing1234!');

    echo "✅ PDO connection OK\n";
} catch (PDOException $e) {
    echo "❌ PDO error:\n";
    echo $e->getMessage() . "\n";
}
