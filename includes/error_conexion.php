<?php
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Error de conexión</title><link rel="stylesheet" href="css/estilos.css"></head>
<body>
<main class="contenedor">
    <div class="mensaje error">
        <b>No se pudo conectar a la base de datos.</b><br>
        Revisa los datos de config/config.php y que el servidor MySQL esté disponible.<br><br>
        <small><?= htmlspecialchars($errorConexion ?? '', ENT_QUOTES, 'UTF-8') ?></small>
    </div>
</main>
</body>
</html>
