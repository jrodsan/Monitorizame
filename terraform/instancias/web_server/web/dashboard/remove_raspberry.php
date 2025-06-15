<?php
    include('../includes/auth.php'); 

// Eliminar Raspberry si se ha enviado el ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_raspberry'])) {
    $id = $_POST['id_raspberry'];
    $stmt = $pdo->prepare("DELETE FROM raspberry_pi WHERE id_raspberry = :id");
    $stmt->execute([':id' => $id]);
    $mensaje = "Raspberry Pi eliminada correctamente (y sus sensores también).";
}

// Obtener lista de Raspberrys
$stmt = $pdo->query("SELECT id_raspberry, nombre FROM raspberry_pi");
$raspberrys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../includes/header.php'); ?>

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white text-center">
            <h3>Lista de Raspberry Pi</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <?php if (count($raspberrys) === 0): ?>
                <div class="alert alert-info text-center">No hay Raspberry Pi registradas.</div>
            <?php else: ?>
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($raspberrys as $rasp): ?>
                            <tr>
                                <td><?= htmlspecialchars($rasp['id_raspberry']) ?></td>
                                <td><?= htmlspecialchars($rasp['nombre']) ?></td>
                                <td class="text-center">
                                    <form method="post" class="d-inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta Raspberry Pi y todos sus sensores?');">
                                        <input type="hidden" name="id_raspberry" value="<?= $rasp['id_raspberry'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>