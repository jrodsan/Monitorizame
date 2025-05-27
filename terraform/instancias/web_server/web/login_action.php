<?php
session_start();
include('includes/db.php'); // Aquí defines $pdo como conexión PDO a PostgreSQL

// Recoger datos del formulario
$username = $_POST['username'];
$password = $_POST['password'];

// Buscar usuario en la base de datos
$sql = "SELECT * FROM usuarios WHERE nombre_usuario = :username LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($password, $usuario['contrasena'])) {
    // Usuario autenticado
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
    
    header("Location: dashboard/index.php"); 
    exit;
} else {
    echo "Usuario o contraseña incorrectos.";
}
?>