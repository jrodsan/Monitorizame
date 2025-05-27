
 <?php include('includes/header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido | Monitorizame</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Main content -->
<div class="container mt-5 ">
    <div class="text-center mb-4">
        <h1 class="display-4">Bienvenido a Monitorizame</h1>
        <p class="lead">Tu plataforma para controlar sensores y Raspberry Pi desde la nube.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title">Información sobre el servicio</h2>
                    <p class="card-text" >
                        Este sistema te permite monitorizar variables como temperatura, humedad o luz de tu huerto inteligente, gracias a una Raspberry Pi conectada a la nube.
                        Puedes registrar una cuenta, asociar tu dispositivo y visualizar tus datos en tiempo real.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
