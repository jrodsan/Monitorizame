<?php
require_once('../includes/auth.php');  // Asegurarnos de que el usuario esté autenticado
include('../includes/db.php');        // Conexión a la base de datos

$usuario_id = $_SESSION['usuario_id'];
$chat_id_actual = null;

// Consultar el chat_id_telegram actual
$sql = "SELECT chat_id_telegram FROM usuarios WHERE id_usuario = :id_usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $usuario_id]);
$row = $stmt->fetch();

if ($row) {
    $chat_id_actual = $row['chat_id_telegram'];
}

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chat_id_telegram'])) {
    $nuevo_chat_id = $_POST['chat_id_telegram'];

    if (filter_var($nuevo_chat_id, FILTER_VALIDATE_INT)) {
        $update_sql = "UPDATE usuarios SET chat_id_telegram = :chat_id WHERE id_usuario = :id_usuario";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            ':chat_id' => $nuevo_chat_id,
            ':id_usuario' => $usuario_id
        ]);

        if ($update_stmt->rowCount() > 0) {
            header("Location: chat_id.php?message=Chat ID actualizado con éxito");
        } else {
            header("Location: chat_id.php?message=No se pudo actualizar el Chat ID");
        }
    } else {
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

        <!-- Mensaje de éxito o error -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para actualizar el Chat ID -->
        <form method="POST" action="chat_id.php">
            <div class="mb-3">
                <label for="chat_id_telegram" class="form-label">Chat ID de Telegram:</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="chat_id_telegram" 
                    name="chat_id_telegram" 
                    required 
                    value="<?= htmlspecialchars($chat_id_actual ?? '') ?>"
                >
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
