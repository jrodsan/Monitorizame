<?php include('../includes/auth.php'); ?>
<?php include('../includes/db.php'); ?>
<?php include('../includes/header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h3>Añadir sensor</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Procesar el formulario al enviar
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
                            echo '<div class="alert alert-success text-center">Sensor añadido correctamente.</div>';
                        } else {
                            echo '<div class="alert alert-danger text-center">Por favor, completa los campos obligatorios.</div>';
                        }
                    }
                    ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="id_raspberry" class="form-label">ID Raspberry Pi</label>
                            <input type="number" name="id_raspberry" id="id_raspberry" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de sensor</label>
                            <input type="text" name="tipo" id="tipo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Añadir sensor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>