<?php
require_once('../includes/auth.php');
include('../includes/db.php');
include('../includes/header.php');

// Obtener las configuraciones del usuario autenticado
$sql = "
    SELECT 
        configuraciones.id_configuracion,
        configuraciones.nombre,
        configuraciones.umbral_min,
        configuraciones.umbral_max,
        configuraciones.alerta_activada,
        sensores.tipo AS tipo_sensor,
        raspberry_pi.nombre AS nombre_pi
    FROM configuraciones
    JOIN sensores ON configuraciones.id_sensor = sensores.id_sensor
    JOIN raspberry_pi ON sensores.id_raspberry = raspberry_pi.id_raspberry
    WHERE raspberry_pi.id_usuario = :id_usuario
    ORDER BY configuraciones.id_configuracion DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $_SESSION['usuario_id']]);
$configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h2>Configuraciones de Sensores</h2>

    <?php if (count($configuraciones) === 0): ?>
        <div class="alert alert-warning">⚠️ No tienes configuraciones registradas.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Sensor</th>
                    <th>Raspberry</th>
                    <th>Umbral Mínimo</th>
                    <th>Umbral Máximo</th>
                    <th>Alerta Activada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configuraciones as $config): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($config['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($config['tipo_sensor']); ?></td>
                        <td><?php echo htmlspecialchars($config['nombre_pi']); ?></td>
                        <td><?php echo is_null($config['umbral_min']) ? '-' : $config['umbral_min']; ?></td>
                        <td><?php echo is_null($config['umbral_max']) ? '-' : $config['umbral_max']; ?></td>
                        <td><?php echo $config['alerta_activada'] ? '✅ Sí' : '❌ No'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="index.php" class="btn btn-secondary">Volver al Dashboard</a>
    </div>
</div>
