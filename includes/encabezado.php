<?php $paginaActual = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Tienda') ?> - EDA311 HITO 3</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="barra">
    <a href="index.php" class="marca">Tienda Ticona</a>
    <nav>
        <a href="index.php" class="<?= $paginaActual === 'index.php' ? 'activo' : '' ?>">Inicio</a>
        <a href="clientes.php" class="<?= $paginaActual === 'clientes.php' ? 'activo' : '' ?>">Clientes</a>
        <a href="productos.php" class="<?= $paginaActual === 'productos.php' ? 'activo' : '' ?>">Productos</a>
    </nav>
</header>
<main class="contenedor">
<?php if ($mensaje = obtenerMensaje()): ?>
    <div class="mensaje <?= e($mensaje['tipo']) ?>"><?= e($mensaje['texto']) ?></div>
<?php endif; ?>
