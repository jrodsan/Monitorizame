<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include('../includes/db.php');
include('../includes/header.php');
require_once('../includes/auth.php');

// Obtener sensores del usuario a través de las Raspberry Pi asociadas
$sql = "
    SELECT sensores.*, raspberry_pi.nombre AS nombre_pi
    FROM sensores
    JOIN raspberry_pi ON sensores.id_raspberry = raspberry_pi.id_raspberry
    WHERE raspberry_pi.id_usuario = :id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$sensores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener Raspberry Pi asociadas al usuario
$sql_raspberry = "SELECT * FROM raspberry_pi WHERE id_usuario = :id_usuario";
$stmt_raspberry = $pdo->prepare($sql_raspberry);
$stmt_raspberry->execute([':id_usuario' => $_SESSION['usuario_id']]);
$raspberries = $stmt_raspberry->fetchAll(PDO::FETCH_ASSOC);

// Obtener lecturas de los sensores para cada Raspberry Pi
$raspberry_data = [];
foreach ($raspberries as $raspberry) {
    $id_raspberry = $raspberry['id_raspberry'];
    $sql_lecturas = "
        SELECT sensores.tipo, sensores.descripcion, lecturas.valor, lecturas.unidad, lecturas.fecha_hora 
        FROM lecturas 
        JOIN sensores ON lecturas.id_sensor = sensores.id_sensor 
        WHERE sensores.id_raspberry = :id_raspberry 
        ORDER BY lecturas.fecha_hora DESC
    ";
    $stmt_lecturas = $pdo->prepare($sql_lecturas);
    $stmt_lecturas->execute([':id_raspberry' => $id_raspberry]);
    $lecturas = $stmt_lecturas->fetchAll(PDO::FETCH_ASSOC);

    $raspberry_data[] = [
        'nombre' => $raspberry['nombre'],
        'lecturas' => $lecturas
    ];
}
?>

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sensores</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h1>

    <div class="mb-4">
        <h2>Tus sensores:</h2>
        <ul class="list-group mb-3">
            <?php foreach ($sensores as $sensor): ?>
                <li class="list-group-item">
                    <strong>Sensor:</strong> <?php echo htmlspecialchars($sensor['tipo']); ?>
                    <span class="text-muted">(Raspberry: <?php echo htmlspecialchars($sensor['nombre_pi']); ?>)</span>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="add_sensores.php" class="btn btn-primary btn-sm">Añadir nuevo sensor</a>
        <a href="configuraciones.php" class="btn btn-secondary btn-sm">Ver configuraciones</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
    </div>

    <h2>Raspberry Pi y sus lecturas:</h2>
    <?php if (!empty($raspberry_data)): ?>
        <?php foreach ($raspberry_data as $idx => $raspberry): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="h5 mb-0"><?php echo htmlspecialchars($raspberry['nombre']); ?></h3>
                </div>
                <div class="card-body">
                    <canvas id="chart-<?php echo $idx; ?>" height="100"></canvas>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No tienes Raspberry Pi registradas aún.</div>
    <?php endif; ?>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Pasa los datos de PHP a JS
    const raspberryData = <?php echo json_encode($raspberry_data); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        raspberryData.forEach(function(rasp, idx) {
            const ctx = document.getElementById('chart-' + idx);
            if (!ctx) return;
            // Agrupar lecturas por tipo
            const groupedByTipo = {};
            rasp.lecturas.forEach(l => {
                if (!groupedByTipo[l.tipo]) {
                    groupedByTipo[l.tipo] = { data: [], labels: [] };
                }
                groupedByTipo[l.tipo].data.push(l.valor);
                groupedByTipo[l.tipo].labels.push(l.fecha_hora);
            });

            // Usar la última serie de etiquetas como eje X
            // Reunir todas las fechas únicas y ordenarlas
            const allLabelsSet = new Set();
            Object.values(groupedByTipo).forEach(grupo => {
                grupo.labels.forEach(fecha => allLabelsSet.add(fecha));
            });
            const chartLabels = Array.from(allLabelsSet).sort();


            const datasets = Object.entries(groupedByTipo).map(([tipo, grupo]) => {
                const dataMap = {};
                grupo.labels.forEach((fecha, i) => {
                    dataMap[fecha] = parseFloat(grupo.data[i]);
                });

                const alignedData = chartLabels.map(fecha => dataMap[fecha] ?? null); // usa null para mantener la alineación

                return {
                    label: tipo,
                    data: alignedData,
                    borderColor: tipo === 'temperatura' ? 'rgb(255, 99, 132)' :
                                tipo === 'humedad' ? 'rgb(54, 162, 235)' :
                                tipo === 'luz' ? 'rgb(255, 206, 86)' :
                                'rgb(75, 192, 192)',
                    fill: false,
                    tension: 0.1
                };
            });

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        x: { display: true, title: { display: true, text: 'Fecha/Hora' } },
                        y: { display: true, title: { display: true, text: 'Valor' } }
                    }
                }
            });
        });
    });
</script>
</body>
</html>