<?php
require_once 'includes/funciones.php';
require_once 'config/conexion.php';
require_once 'repositorios/ProductoRepositorio.php';

try {
    $repositorio = new ProductoRepositorio(conectar());
} catch (PDOException $error) {
    $errorConexion = $error->getMessage();
    require 'includes/error_conexion.php';
    exit;
}

function validarProducto(array $datos, array &$errores): Producto
{
    $nombre = trim($datos['nombre'] ?? '');
    $categoria = trim($datos['categoria'] ?? '');
    $precio = filter_var($datos['precio'] ?? '', FILTER_VALIDATE_FLOAT);
    $stock = filter_var($datos['stock'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($nombre === '' || largo($nombre) > 100) {
        $errores[] = "El nombre es obligatorio (máximo 100 caracteres).";
    }
    if ($categoria === '' || largo($categoria) > 50) {
        $errores[] = "La categoría es obligatoria (máximo 50 caracteres).";
    }
    if ($precio === false || $precio <= 0 || $precio > 99999999.99) {
        $errores[] = "El precio debe ser un número mayor a 0.";
    }
    if ($stock === false) {
        $errores[] = "El stock debe ser un número entero mayor o igual a 0.";
    }

    $idProducto = isset($datos['id_producto']) && $datos['id_producto'] !== '' ? (int) $datos['id_producto'] : null;
    return new Producto($idProducto, $nombre, $categoria, $precio === false ? 0 : round($precio, 2), $stock === false ? 0 : $stock);
}

$errores = [];
$formulario = ['id_producto' => '', 'nombre' => '', 'categoria' => '', 'precio' => '', 'stock' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'registrar' || $accion === 'modificar') {
            $producto = validarProducto($_POST, $errores);
            if (empty($errores)) {
                if ($accion === 'registrar') {
                    $nuevoId = $repositorio->insertar($producto);
                    guardarMensaje('ok', "Se registró el producto #$nuevoId: {$producto->nombre}");
                    redirigir("productos.php?resaltar=$nuevoId");
                } else {
                    $repositorio->actualizar($producto);
                    guardarMensaje('ok', "Se modificó el producto #{$producto->idProducto}: {$producto->nombre} | {$producto->categoria} | Bs " . number_format($producto->precio, 2) . " | stock {$producto->stock}");
                    redirigir("productos.php?resaltar={$producto->idProducto}");
                }
            }
            $formulario = array_merge($formulario, $_POST);
        } elseif ($accion === 'eliminar') {
            $idProducto = (int) ($_POST['id_producto'] ?? 0);
            $repositorio->eliminar($idProducto);
            guardarMensaje('ok', "Se eliminó el producto #$idProducto");
            redirigir('productos.php');
        }
    } catch (PDOException $error) {
        $errores[] = mensajeErrorBD($error);
        if ($accion !== 'eliminar') {
            $formulario = array_merge($formulario, $_POST);
        }
    }
}

$editando = isset($_GET['editar']) || (($_POST['accion'] ?? '') === 'modificar');
if (isset($_GET['editar']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $producto = $repositorio->buscarPorId((int) $_GET['editar']);
    if ($producto === null) {
        guardarMensaje('error', 'El producto no existe.');
        redirigir('productos.php');
    }
    $formulario = [
        'id_producto' => $producto->idProducto, 'nombre' => $producto->nombre, 'categoria' => $producto->categoria,
        'precio' => number_format($producto->precio, 2, '.', ''), 'stock' => $producto->stock
    ];
}
$idResaltado = (int) ($_GET['resaltar'] ?? ($_GET['editar'] ?? 0));

$lista = $repositorio->listar();
$recorrido = ($_GET['recorrido'] ?? '') === 'atras' ? 'atras' : 'adelante';
$productos = $recorrido === 'atras' ? $lista->recorrerAtras() : $lista->recorrerAdelante();

$titulo = 'Productos';
require 'includes/encabezado.php';
?>

<h1>Gestión de Productos</h1>

<section class="caja">
    <h2><?= $editando ? 'Modificar producto #' . e($formulario['id_producto']) : 'Registrar producto' ?></h2>
    <?php if ($errores): ?>
        <div class="mensaje error"><?= implode('<br>', array_map('e', $errores)) ?></div>
    <?php endif; ?>
    <form method="post" action="productos.php">
        <input type="hidden" name="accion" value="<?= $editando ? 'modificar' : 'registrar' ?>">
        <input type="hidden" name="id_producto" value="<?= e($formulario['id_producto']) ?>">
        <div class="fila">
            <label>Nombre <input name="nombre" maxlength="100" required value="<?= e($formulario['nombre']) ?>"></label>
            <label>Categoría <input name="categoria" maxlength="50" required value="<?= e($formulario['categoria']) ?>"></label>
            <label>Precio (Bs) <input name="precio" type="number" step="0.01" min="0.01" required value="<?= e($formulario['precio']) ?>"></label>
            <label>Stock <input name="stock" type="number" min="0" required value="<?= e($formulario['stock']) ?>"></label>
        </div>
        <button type="submit" class="boton verde"><?= $editando ? 'Guardar cambios' : 'Registrar' ?></button>
        <?php if ($editando): ?>
            <a href="productos.php" class="boton gris">Cancelar</a>
        <?php endif; ?>
    </form>
</section>

<section class="caja">
    <h2>Lista doblemente enlazada de productos</h2>
    <p>
        Registros cargados desde MySQL: <b><?= $lista->tamanyo() ?></b> |
        Recorrido:
        <a href="productos.php?recorrido=adelante" class="<?= $recorrido === 'adelante' ? 'activo' : '' ?>">adelante (cabeza → cola)</a> ·
        <a href="productos.php?recorrido=atras" class="<?= $recorrido === 'atras' ? 'activo' : '' ?>">atrás (cola → cabeza)</a>
    </p>
    <?= dibujarNodos(array_map(fn($p) => '#' . $p->idProducto, $productos)) ?>
    <table>
        <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio (Bs)</th><th>Stock</th><th>Acciones</th></tr>
        <?php if (!$productos): ?>
            <tr><td colspan="6" class="vacio">No hay productos registrados</td></tr>
        <?php endif; ?>
        <?php foreach ($productos as $p): ?>
            <tr class="<?= $p->idProducto === $idResaltado ? 'resaltado' : '' ?>">
                <td><?= $p->idProducto ?></td>
                <td><?= e($p->nombre) ?></td>
                <td><?= e($p->categoria) ?></td>
                <td><?= number_format($p->precio, 2) ?></td>
                <td><?= $p->stock ?></td>
                <td class="acciones">
                    <a href="productos.php?editar=<?= $p->idProducto ?>" class="boton azul">Editar</a>
                    <form method="post" action="productos.php" onsubmit="return confirm('¿Eliminar el producto #<?= $p->idProducto ?>?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_producto" value="<?= $p->idProducto ?>">
                        <button type="submit" class="boton rojo">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php require 'includes/pie.php'; ?>
