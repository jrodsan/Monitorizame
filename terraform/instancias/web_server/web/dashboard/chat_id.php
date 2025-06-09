<?php
require_once('../includes/auth.php');  // Asegurarnos de que el usuario esté autenticado
include('../includes/db.php');  // Conexión a la base de datos

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chat_id_telegram'])) {
    // Obtener el chat_id_telegram enviado
    $chat_id_telegram = $_POST['chat_id_telegram'];
    $usuario_id = $_SESSION['usuario_id'];

    // Validar que el chat_id_telegram sea un número entero válido
    if (filter_var($chat_id_telegram, FILTER_VALIDATE_INT)) {
        // Actualizar el chat_id_telegram en la base de datos
        $sql = "UPDATE usuarios SET chat_id_telegram = :chat_id_telegram WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':chat_id_telegram' => $chat_id_telegram,
            ':id_usuario' => $usuario_id
        ]);

        // Verificar si la actualización fue exitosa
        if ($stmt->rowCount() > 0) {
            // Redirigir con un mensaje de éxito
            header("Location: chat_id.php?message=Chat ID actualizado con éxito");
        } else {
            // Redirigir con un mensaje de error
            header("Location: chat_id.php?message=No se pudo actualizar el Chat ID");
        }
    } else {
        // Si el chat_id no es válido, redirigir con mensaje de error
        header("Location: chat_id.php?message=Chat ID no válido");
    }
    exit;
}
?>

<?php include('../includes/header.php'); ?>

<head>
    <meta charset="UTF-8">
    <title>Actualizar Chat ID - Telegram</title>
</head>

<body class="bg-light">
    <div class="container py-4">
        <h1 class="mb-4">Actualizar Chat ID de Telegram</h1>

        <!-- Mostrar mensaje de éxito o error si lo hay -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para ingresar el chat_id_telegram -->
        <form method="POST" action="chat_id.php">
            <div class="mb-3">
                <label for="chat_id_telegram" class="form-label">Chat ID de Telegram:</label>
                <input type="text" class="form-control" id="chat_id_telegram" name="chat_id_telegram" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>

        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary btn-sm">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
