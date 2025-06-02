<?php  
    include('../includes/auth.php'); 
    include('../includes/db.php'); 
    include('../includes/header.php'); 

    $id_usuario = $_SESSION['usuario_id']; // Tomamos el ID del usuario desde la sesión

    // Obtener las Raspberry Pi del usuario actual
    $stmt = $pdo->prepare("SELECT * FROM raspberry_pi WHERE id_usuario = :id_usuario");
    $stmt->execute([':id_usuario' => $id_usuario]);
    $raspberrys = $stmt->fetchAll();

    // Variable para almacenar el id de la Raspberry Pi recién insertada
    $id_raspberry_insertado = null;

    // Verificamos si el formulario se ha enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if ($nombre) {
            // Insertar la Raspberry Pi
            $stmt = $pdo->prepare("INSERT INTO raspberry_pi (id_usuario, nombre, descripcion) VALUES (:id_usuario, :nombre, :descripcion)");
            try {
                $stmt->execute([
                    ':id_usuario' => $id_usuario,
                    ':nombre' => $nombre,
                    ':descripcion' => $descripcion
                ]);
                
                // Obtener el ID de la Raspberry Pi recién insertada
                $id_raspberry_insertado = $pdo->lastInsertId();  // Esta es la forma de obtener el ID de la fila recién insertada

                echo '<div class="alert alert-success text-center">Raspberry Pi registrada correctamente.</div>';
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            echo '<div class="alert alert-warning text-center">Por favor, completa el nombre.</div>';
        }
    }
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h3>Añadir Raspberry Pi</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar Raspberry Pi</button>
                    </form>

                    <!-- Mostrar el ID de la Raspberry Pi recién registrada, si existe -->
                    <?php if ($id_raspberry_insertado !== null): ?>
                        <div class="alert alert-info text-center mt-4">
                            La Raspberry Pi ha sido registrada con éxito. El ID es: <?= htmlspecialchars($id_raspberry_insertado) ?>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
