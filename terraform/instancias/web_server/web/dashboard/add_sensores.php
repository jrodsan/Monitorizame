<?php 
    include('../includes/auth.php'); 
    include('../includes/db.php'); 
    include('../includes/header.php'); 

    // Obtener lista de Raspberrys
    $stmt = $pdo->query("SELECT id_raspberry, nombre FROM raspberry_pi");
    $raspberrys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
?>
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
                        $nombre = $_POST['nombre'] ?? '';
                        $descripcion = $_POST['descripcion'] ?? '';

                        if ($id_raspberry && $tipo) {
                            $stmt = $pdo->prepare("INSERT INTO sensores (id_raspberry, tipo, descripcion, nombre ) VALUES (:id_raspberry, :tipo, :descripcion , :nombre)");
                            $stmt->execute([
                                ':id_raspberry' => $id_raspberry,
                                ':tipo' => $tipo,
                                ':descripcion' => $descripcion ,
                                ':nombre' => $nombre
                            ]);
                            echo '<div class="alert alert-success text-center">Sensor añadido correctamente.</div>';
                        } else {
                            echo '<div class="alert alert-danger text-center">Por favor, completa los campos obligatorios.</div>';
                        }
                    }
                    ?>

                    <form method="post">
                        <label for="id_raspberry" class="form-label">Raspberry Pi</label>
                            <div class="mb-3">
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
                            <input type="text" name="tipo" id="tipo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control" >
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" require>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Añadir sensor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>