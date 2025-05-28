<?php 
    include('../includes/auth.php'); 
    include('../includes/db.php'); 
    include('../includes/header.php'); 

    $id_usuario = $_SESSION['usuario_id']; // Tomamos el ID del usuario desde la sesión
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h3>Añadir Raspberry Pi</h3>
                </div>
                <div class="card-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $identificador_unico = $_POST['identificador_unico'] ?? '';
                        $nombre = $_POST['nombre'] ?? '';

                        if ($identificador_unico) {
                            $stmt = $pdo->prepare("INSERT INTO raspberry_pi (id_usuario, identificador_unico, nombre) VALUES (:id_usuario, :identificador_unico, :nombre)");
                            try {
                                $stmt->execute([
                                    ':id_usuario' => $id_usuario,
                                    ':identificador_unico' => $identificador_unico,
                                    ':nombre' => $nombre
                                ]);
                                echo '<div class="alert alert-success text-center">Raspberry Pi registrada correctamente.</div>';
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning text-center">Por favor, completa el identificador único.</div>';
                        }
                    }
                    ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="identificador_unico" class="form-label">Identificador Único</label>
                            <input type="text" name="identificador_unico" id="identificador_unico" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar Raspberry Pi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>