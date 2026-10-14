<?php
require_once 'includes/funciones.php';
require_once 'config/conexion.php';
require_once 'repositorios/ClienteRepositorio.php';
require_once 'repositorios/ProductoRepositorio.php';

try {
    $conexion = conectar();
} catch (PDOException $error) {
    $errorConexion = $error->getMessage();
    require 'includes/error_conexion.php';
    exit;
}

$totalClientes = (new ClienteRepositorio($conexion))->listar()->tamanyo();
$totalProductos = (new ProductoRepositorio($conexion))->listar()->tamanyo();

$titulo = 'Inicio';
require 'includes/encabezado.php';
?>

<h1>Tienda Ticona</h1>
<p class="subtitulo">Gestión de clientes, productos y pedidos con PHP, MySQL, lista doblemente enlazada y Bubble Sort.</p>

<div class="tarjetas">
    <a class="tarjeta" href="clientes.php">
        <span class="numero"><?= $totalClientes ?></span>
        <span class="nombre">Clientes</span>
        <span class="detalle">Ordenados por nombre (A-Z)</span>
    </a>
    <a class="tarjeta" href="productos.php">
        <span class="numero"><?= $totalProductos ?></span>
        <span class="nombre">Productos</span>
        <span class="detalle">Ordenados por precio (menor a mayor)</span>
    </a>
</div>

<p class="nota">Base de datos: <b><?= e(DB_NOMBRE) ?></b> en <?= e(DB_HOST) ?> · <a href="explorar.php">Ver estructura de la base de datos</a></p>

<?php require 'includes/pie.php'; ?>
