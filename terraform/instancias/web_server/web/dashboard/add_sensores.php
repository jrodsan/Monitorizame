<?php 
    include('../includes/auth.php'); 
    include('../includes/db.php'); 
    include('../includes/header.php'); 

    // Obtener lista de Raspberrys
    $stmt = $pdo->query("SELECT id_raspberry, nombre FROM raspberry_pi");
    $raspberrys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $id_sensor_insertado = null; // Para mostrar el ID generado

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_raspberry = $_POST['id_raspberry'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if ($id_raspberry && $tipo) {
            $stmt = $pdo->prepare("INSERT INTO sensores (id_raspberry, tipo, descripcion) VALUES (:id_raspberry, :tipo, :descripcion)");
            $stmt->execute([
                ':id_raspberry' => $id_raspberry,
                ':tipo' => $tipo,
                ':descripcion' => $descripcion 
            ]);
            $id_sensor_insertado = $pdo->lastInsertId();

            echo '<div class="alert alert-success text-center">Sensor añadido correctamente. ID: ' . htmlspecialchars($id_sensor_insertado) . '</div>';
        } else {
            echo '<div class="alert alert-danger text-center">Por favor, completa los campos obligatorios.</div>';
        }
    }
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h3>Añadir sensor</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="id_raspberry" class="form-label">Raspberry Pi</label>
                            <select name="id_raspberry" id="id_raspberry" class="form-select" required>
                                <option value="">Selecciona una Raspberry Pi</option>
                                <?php foreach ($raspberrys as $rasp): ?>
                                    <option value="<?= htmlspecialchars($rasp['id_raspberry']) ?>">
                                        <?= htmlspecialchars($rasp['nombre']) ?> (ID: <?= $rasp['id_raspberry'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de sensor</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="">Selecciona un tipo de sensor</option>
                                <option value="temperatura">Temperatura</option>
                                <option value="humedad">Humedad</option>
                                <option value="luz">Luz</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Añadir sensor</button>
                    </form>

                    <?php if ($id_sensor_insertado !== null): ?>
                        <div class="alert alert-info text-center mt-3">
                            El ID del sensor añadido es: <?= htmlspecialchars($id_sensor_insertado) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>