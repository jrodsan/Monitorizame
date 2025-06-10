<?php
require_once('../includes/auth.php');
include('../includes/db.php');
include('../includes/header.php');

// Obtener sensores del usuario actual
$sql = "
    SELECT sensores.id_sensor, sensores.tipo, raspberry_pi.nombre AS nombre_pi
    FROM sensores
    JOIN raspberry_pi ON sensores.id_raspberry = raspberry_pi.id_raspberry
    WHERE raspberry_pi.id_usuario = :id_usuario
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $_SESSION['usuario_id']]);
$sensores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sensor = $_POST['id_sensor'];
    $nombre = $_POST['nombre'];
    $umbral_min = $_POST['umbral_min'];
    $umbral_max = $_POST['umbral_max'];
    $alerta_activada = isset($_POST['alerta_activada']) ? true : false;

    $insert_sql = "
        INSERT INTO configuraciones (id_sensor, nombre, umbral_min, umbral_max, alerta_activada)
        VALUES (:id_sensor, :nombre, :umbral_min, :umbral_max, :alerta_activada)
    ";
    $stmt_insert = $pdo->prepare($insert_sql);
    $stmt_insert->execute([
        ':id_sensor' => $id_sensor,
        ':nombre' => $nombre,
        ':umbral_min' => $umbral_min,
        ':umbral_max' => $umbral_max,
        ':alerta_activada' => $alerta_activada
    ]);

    $mensaje = "✅ Configuración añadida correctamente.";
}
?>

<div class="container py-4">
    <h2>Añadir Configuración de Alerta</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="id_sensor" class="form-label">Sensor:</label>
            <select name="id_sensor" id="id_sensor" class="form-select" required>
                <?php foreach ($sensores as $sensor): ?>
                    <option value="<?php echo $sensor['id_sensor']; ?>">
                    <?php echo htmlspecialchars("ID {$sensor['id_sensor']} - {$sensor['tipo']} ({$sensor['nombre_pi']})"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la configuración:</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="umbral_min" class="form-label">Umbral mínimo:</label>
            <input type="number" step="0.01" name="umbral_min" id="umbral_min" class="form-control">
        </div>

        <div class="mb-3">
            <label for="umbral_max" class="form-label">Umbral máximo:</label>
            <input type="number" step="0.01" name="umbral_max" id="umbral_max" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="alerta_activada" id="alerta_activada" class="form-check-input" checked>
            <label for="alerta_activada" class="form-check-label">Activar alerta</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="index.php" class="btn btn-secondary">Volver al Dashboard</a>
        </div>
    </form>
</div>