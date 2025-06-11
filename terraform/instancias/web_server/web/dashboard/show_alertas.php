<?php
require_once('../includes/auth.php');
include('../includes/db.php');
include('../includes/header.php');

// Obtener las alertas del usuario autenticado
$sql = "
    SELECT 
        alertas.id_alerta,
        sensores.tipo AS tipo_sensor,
        raspberry_pi.nombre AS nombre_pi,
        alertas.estado,
        alertas.fecha_inicio,
        alertas.fecha_fin
    FROM alertas
    JOIN sensores ON alertas.id_sensor = sensores.id_sensor
    JOIN raspberry_pi ON sensores.id_raspberry = raspberry_pi.id_raspberry
    WHERE raspberry_pi.id_usuario = :id_usuario
    ORDER BY alertas.fecha_inicio DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $_SESSION['usuario_id']]);
$alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h2>Historial de Alertas</h2>

    <?php if (count($alertas) === 0): ?>
        <div class="alert alert-info">ℹ️ No se han generado alertas aún.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Sensor</th>
                    <th>Raspberry</th>
                    <th>Estado</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alertas as $alerta): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($alerta['tipo_sensor']); ?></td>
                        <td><?php echo htmlspecialchars($alerta['nombre_pi']); ?></td>
                        <td>
                            <?php echo $alerta['estado'] ? '🔴 Activa' : '✅ Cerrada'; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($alerta['fecha_inicio'])); ?></td>
                        <td>
                            <?php echo $alerta['fecha_fin'] ? date('Y-m-d H:i:s', strtotime($alerta['fecha_fin'])) : '—'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-secondary">Volver al Dashboard</a>
    </div>
</div>
