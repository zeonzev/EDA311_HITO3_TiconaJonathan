<?php
require_once 'includes/funciones.php';
require_once 'config/conexion.php';
require_once 'repositorios/ClienteRepositorio.php';

try {
    $repositorio = new ClienteRepositorio(conectar());
} catch (PDOException $error) {
    $errorConexion = $error->getMessage();
    require 'includes/error_conexion.php';
    exit;
}

function validarCliente(array $datos, array &$errores): Cliente
{
    $nombre = trim($datos['nombre'] ?? '');
    $email = trim($datos['email'] ?? '');
    $edad = filter_var($datos['edad'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 18, 'max_range' => 120]]);

    if ($nombre === '' || largo($nombre) > 100) {
        $errores[] = "El nombre es obligatorio (máximo 100 caracteres).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || largo($email) > 150) {
        $errores[] = "El email no es válido (máximo 150 caracteres).";
    }
    if ($edad === false) {
        $errores[] = "La edad debe ser un número entero entre 18 y 120 (la base de datos solo acepta mayores de edad).";
    }

    $idCliente = isset($datos['id_cliente']) && $datos['id_cliente'] !== '' ? (int) $datos['id_cliente'] : null;
    return new Cliente($idCliente, $nombre, $email, $edad === false ? 0 : $edad);
}

$errores = [];
$formulario = ['id_cliente' => '', 'nombre' => '', 'email' => '', 'edad' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'registrar' || $accion === 'modificar') {
            $cliente = validarCliente($_POST, $errores);
            if (empty($errores)) {
                if ($accion === 'registrar') {
                    $nuevoId = $repositorio->insertar($cliente);
                    guardarMensaje('ok', "Se registró el cliente #$nuevoId: {$cliente->nombre}");
                    redirigir("clientes.php?resaltar=$nuevoId");
                } else {
                    $repositorio->actualizar($cliente);
                    guardarMensaje('ok', "Se modificó el cliente #{$cliente->idCliente}: {$cliente->nombre} | {$cliente->email} | {$cliente->edad} años");
                    redirigir("clientes.php?resaltar={$cliente->idCliente}");
                }
            }
            $formulario = array_merge($formulario, $_POST);
        } elseif ($accion === 'eliminar') {
            $idCliente = (int) ($_POST['id_cliente'] ?? 0);
            $repositorio->eliminar($idCliente);
            guardarMensaje('ok', "Se eliminó el cliente #$idCliente");
            redirigir('clientes.php');
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
    $cliente = $repositorio->buscarPorId((int) $_GET['editar']);
    if ($cliente === null) {
        guardarMensaje('error', 'El cliente no existe.');
        redirigir('clientes.php');
    }
    $formulario = ['id_cliente' => $cliente->idCliente, 'nombre' => $cliente->nombre, 'email' => $cliente->email, 'edad' => $cliente->edad];
}
$idResaltado = (int) ($_GET['resaltar'] ?? ($_GET['editar'] ?? 0));

$lista = $repositorio->listar();
$recorrido = ($_GET['recorrido'] ?? '') === 'atras' ? 'atras' : 'adelante';
$clientes = $recorrido === 'atras' ? $lista->recorrerAtras() : $lista->recorrerAdelante();

$listaOrdenada = $lista->copiar();
$estadisticas = $listaOrdenada->ordenarBurbuja(
    fn(Cliente $a, Cliente $b) => strcasecmp($a->nombre, $b->nombre) > 0
);
$clientesOrdenados = $listaOrdenada->recorrerAdelante();

$titulo = 'Clientes';
require 'includes/encabezado.php';
?>

<h1>Gestión de Clientes</h1>

<section class="caja">
    <h2><?= $editando ? 'Modificar cliente #' . e($formulario['id_cliente']) : 'Registrar cliente' ?></h2>
    <?php if ($errores): ?>
        <div class="mensaje error"><?= implode('<br>', array_map('e', $errores)) ?></div>
    <?php endif; ?>
    <form method="post" action="clientes.php">
        <input type="hidden" name="accion" value="<?= $editando ? 'modificar' : 'registrar' ?>">
        <input type="hidden" name="id_cliente" value="<?= e($formulario['id_cliente']) ?>">
        <div class="fila">
            <label>Nombre <input name="nombre" maxlength="100" required value="<?= e($formulario['nombre']) ?>"></label>
            <label>Email <input name="email" type="email" maxlength="150" required value="<?= e($formulario['email']) ?>"></label>
            <label>Edad <input name="edad" type="number" min="18" max="120" required value="<?= e($formulario['edad']) ?>"></label>
        </div>
        <button type="submit" class="boton verde"><?= $editando ? 'Guardar cambios' : 'Registrar' ?></button>
        <?php if ($editando): ?>
            <a href="clientes.php" class="boton gris">Cancelar</a>
        <?php endif; ?>
    </form>
</section>

<section class="caja">
    <h2>Lista doblemente enlazada de clientes</h2>
    <p>
        Registros cargados desde MySQL: <b><?= $lista->tamanyo() ?></b> |
        Recorrido:
        <a href="clientes.php?recorrido=adelante" class="<?= $recorrido === 'adelante' ? 'activo' : '' ?>">adelante (cabeza → cola)</a> ·
        <a href="clientes.php?recorrido=atras" class="<?= $recorrido === 'atras' ? 'activo' : '' ?>">atrás (cola → cabeza)</a>
    </p>
    <?= dibujarNodos(array_map(fn($c) => '#' . $c->idCliente, $clientes)) ?>
    <table>
        <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Edad</th><th>Acciones</th></tr>
        <?php if (!$clientes): ?>
            <tr><td colspan="5" class="vacio">No hay clientes registrados</td></tr>
        <?php endif; ?>
        <?php foreach ($clientes as $c): ?>
            <tr class="<?= $c->idCliente === $idResaltado ? 'resaltado' : '' ?>">
                <td><?= $c->idCliente ?></td>
                <td><?= e($c->nombre) ?></td>
                <td><?= e($c->email) ?></td>
                <td><?= $c->edad ?></td>
                <td class="acciones">
                    <a href="clientes.php?editar=<?= $c->idCliente ?>" class="boton azul">Editar</a>
                    <form method="post" action="clientes.php" onsubmit="return confirm('¿Eliminar el cliente #<?= $c->idCliente ?>?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_cliente" value="<?= $c->idCliente ?>">
                        <button type="submit" class="boton rojo">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<section class="caja">
    <h2>Resultado de Bubble Sort</h2>
    <p class="criterio">Criterio: <b>nombre del cliente, de la A a la Z</b>. Facilita encontrar a un cliente por su nombre.</p>
    <p class="estadisticas">
        Pasadas: <b><?= $estadisticas['pasadas'] ?></b> |
        Comparaciones: <b><?= $estadisticas['comparaciones'] ?></b> |
        Intercambios: <b><?= $estadisticas['intercambios'] ?></b>
    </p>
    <?= dibujarNodos(array_map(fn($c) => $c->nombre, $clientesOrdenados)) ?>
    <table>
        <tr><th>#</th><th>Nombre</th><th>ID</th><th>Email</th><th>Edad</th></tr>
        <?php foreach ($clientesOrdenados as $i => $c): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><b><?= e($c->nombre) ?></b></td>
                <td><?= $c->idCliente ?></td>
                <td><?= e($c->email) ?></td>
                <td><?= $c->edad ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php require 'includes/pie.php'; ?>
