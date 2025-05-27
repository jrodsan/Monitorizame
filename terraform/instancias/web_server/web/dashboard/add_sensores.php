<?php
include('../includes/db.php');

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
        echo "<p>Sensor añadido correctamente.</p>";
    } else {
        echo "<p>Por favor, completa los campos obligatorios.</p>";
    }
}
?>

<h2>Añadir sensor</h2>
<form method="post">
    <label>ID Raspberry Pi: <input type="number" name="id_raspberry" required></label><br>
    <label>Tipo de sensor: <input type="text" name="tipo" required></label><br>
    <label>Descripción: <input type="text" name="descripcion"></label><br>
    <button type="submit">Añadir sensor</button>
</form>