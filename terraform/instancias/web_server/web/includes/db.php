<?php
$host = '10.0.1.4';     // IP del contenedor o servidor PostgreSQL
$port = '5432';
$dbname = 'sensordata';
$user = 'admin';
$password = 'admin';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión a PostgreSQL: " . $e->getMessage());
}
?>
