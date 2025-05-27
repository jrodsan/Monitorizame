<?php
include('includes/db.php');

// Recibir datos del formulario
$nombre_usuario = $_POST['username'] ?? '';
$nombre_completo = $_POST['fullname'] ?? '';
$contrasena = $_POST['password'] ?? '';

// Validar campos básicos
if (empty($nombre_usuario) || empty($contrasena)) {
    die("Por favor, completa los campos requeridos.");
}

// Hashear la contraseña
$hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

try {
    // Insertar usuario en la base de datos
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_usuario, contrasena, nombre_completo) VALUES (:nombre_usuario, :contrasena, :nombre_completo)");
    $stmt->execute([
        ':nombre_usuario' => $nombre_usuario,
        ':contrasena' => $hashed_password,
        ':nombre_completo' => $nombre_completo
    ]);

    echo "Usuario registrado correctamente. <a href='login.php'>Iniciar sesión</a>";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'duplicate key value')) {
        echo "El nombre de usuario ya existe. Intenta con otro.";
    } else {
        echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
?>
