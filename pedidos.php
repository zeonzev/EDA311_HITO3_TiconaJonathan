<?php
require_once 'includes/funciones.php';
require_once 'config/conexion.php';
require_once 'repositorios/PedidoRepositorio.php';
require_once 'repositorios/ClienteRepositorio.php';

try {
    $conexion = conectar();
    $repositorio = new PedidoRepositorio($conexion);
    $clienteRepositorio = new ClienteRepositorio($conexion);
} catch (PDOException $error) {
    $errorConexion = $error->getMessage();
    require 'includes/error_conexion.php';
    exit;
}

function validarPedido(array $datos, array &$errores): Pedido
{
    $idCliente = filter_var($datos['id_cliente'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $fecha = trim($datos['fecha'] ?? '');
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
    $total = filter_var($datos['total'] ?? '', FILTER_VALIDATE_FLOAT);

    if ($idCliente === false) {
        $errores[] = "Debe seleccionar un cliente.";
    }
    if ($fechaValida === false || $fechaValida->format('Y-m-d') !== $fecha) {
        $errores[] = "La fecha no es válida.";
    }
    if ($total === false || $total < 0 || $total > 99999999.99) {
        $errores[] = "El total debe ser un número mayor o igual a 0.";
    }

    $idPedido = isset($datos['id_pedido']) && $datos['id_pedido'] !== '' ? (int) $datos['id_pedido'] : null;
    return new Pedido($idPedido, $idCliente === false ? 0 : $idCliente, $fecha, $total === false ? 0 : round($total, 2));
}

$errores = [];
$formulario = ['id_pedido' => '', 'id_cliente' => '', 'fecha' => date('Y-m-d'), 'total' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'registrar' || $accion === 'modificar') {
            $pedido = validarPedido($_POST, $errores);
            if (empty($errores) && $clienteRepositorio->buscarPorId($pedido->idCliente) === null) {
                $errores[] = "El cliente seleccionado no existe.";
            }
            if (empty($errores)) {
                if ($accion === 'registrar') {
                    $nuevoId = $repositorio->insertar($pedido);
                    guardarMensaje('ok', "Se registró el pedido #$nuevoId");
                    redirigir("pedidos.php?resaltar=$nuevoId");
                } else {
                    $repositorio->actualizar($pedido);
                    guardarMensaje('ok', "Se modificó el pedido #{$pedido->idPedido}: cliente #{$pedido->idCliente} | fecha {$pedido->fecha} | total Bs " . number_format($pedido->total, 2));
                    redirigir("pedidos.php?resaltar={$pedido->idPedido}");
                }
            }
            $formulario = array_merge($formulario, $_POST);
        } elseif ($accion === 'eliminar') {
            $idPedido = (int) ($_POST['id_pedido'] ?? 0);
            $repositorio->eliminar($idPedido);
            guardarMensaje('ok', "Se eliminó el pedido #$idPedido");
            redirigir('pedidos.php');
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
    $pedido = $repositorio->buscarPorId((int) $_GET['editar']);
    if ($pedido === null) {
        guardarMensaje('error', 'El pedido no existe.');
        redirigir('pedidos.php');
    }
    $formulario = [
        'id_pedido' => $pedido->idPedido, 'id_cliente' => $pedido->idCliente,
        'fecha' => $pedido->fecha, 'total' => number_format($pedido->total, 2, '.', '')
    ];
}
$idResaltado = (int) ($_GET['resaltar'] ?? ($_GET['editar'] ?? 0));

$clientes = $clienteRepositorio->listar()->recorrerAdelante();

$lista = $repositorio->listar();
$recorrido = ($_GET['recorrido'] ?? '') === 'atras' ? 'atras' : 'adelante';
$pedidos = $recorrido === 'atras' ? $lista->recorrerAtras() : $lista->recorrerAdelante();

$titulo = 'Pedidos';
require 'includes/encabezado.php';
?>

<h1>Gestión de Pedidos</h1>

<section class="caja">
    <h2><?= $editando ? 'Modificar pedido #' . e($formulario['id_pedido']) : 'Registrar pedido' ?></h2>
    <?php if ($errores): ?>
        <div class="mensaje error"><?= implode('<br>', array_map('e', $errores)) ?></div>
    <?php endif; ?>
    <?php if (!$clientes): ?>
        <div class="mensaje error">Primero registra al menos un cliente para poder crear pedidos.</div>
    <?php endif; ?>
    <form method="post" action="pedidos.php">
        <input type="hidden" name="accion" value="<?= $editando ? 'modificar' : 'registrar' ?>">
        <input type="hidden" name="id_pedido" value="<?= e($formulario['id_pedido']) ?>">
        <div class="fila">
            <label>Cliente
                <select name="id_cliente" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c->idCliente ?>" <?= (string) $c->idCliente === (string) $formulario['id_cliente'] ? 'selected' : '' ?>>
                            #<?= $c->idCliente ?> - <?= e($c->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Fecha <input name="fecha" type="date" required value="<?= e($formulario['fecha']) ?>"></label>
            <label>Total (Bs) <input name="total" type="number" step="0.01" min="0" required value="<?= e($formulario['total']) ?>"></label>
        </div>
        <button type="submit" class="boton verde"><?= $editando ? 'Guardar cambios' : 'Registrar' ?></button>
        <?php if ($editando): ?>
            <a href="pedidos.php" class="boton gris">Cancelar</a>
        <?php endif; ?>
    </form>
</section>

<section class="caja">
    <h2>Lista doblemente enlazada de pedidos</h2>
    <p>
        Registros cargados desde MySQL: <b><?= $lista->tamanyo() ?></b> |
        Recorrido:
        <a href="pedidos.php?recorrido=adelante" class="<?= $recorrido === 'adelante' ? 'activo' : '' ?>">adelante (cabeza → cola)</a> ·
        <a href="pedidos.php?recorrido=atras" class="<?= $recorrido === 'atras' ? 'activo' : '' ?>">atrás (cola → cabeza)</a>
    </p>
    <?= dibujarNodos(array_map(fn($p) => '#' . $p->idPedido, $pedidos)) ?>
    <table>
        <tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Total (Bs)</th><th>Acciones</th></tr>
        <?php if (!$pedidos): ?>
            <tr><td colspan="5" class="vacio">No hay pedidos registrados</td></tr>
        <?php endif; ?>
        <?php foreach ($pedidos as $p): ?>
            <tr class="<?= $p->idPedido === $idResaltado ? 'resaltado' : '' ?>">
                <td><?= $p->idPedido ?></td>
                <td>#<?= $p->idCliente ?> - <?= e($p->nombreCliente) ?></td>
                <td><?= date('d/m/Y', strtotime($p->fecha)) ?></td>
                <td><?= number_format($p->total, 2) ?></td>
                <td class="acciones">
                    <a href="pedidos.php?editar=<?= $p->idPedido ?>" class="boton azul">Editar</a>
                    <form method="post" action="pedidos.php" onsubmit="return confirm('¿Eliminar el pedido #<?= $p->idPedido ?>?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_pedido" value="<?= $p->idPedido ?>">
                        <button type="submit" class="boton rojo">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php require 'includes/pie.php'; ?>
