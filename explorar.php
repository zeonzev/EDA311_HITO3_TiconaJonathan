<?php
require_once 'config/conexion.php';

try {
    $pdo = conectar();
} catch (PDOException $e) {
    die("<h2>Error de conexión</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
}

$sistema = ['information_schema', 'performance_schema', 'mysql', 'sys'];
$bases = array_diff($pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN), $sistema);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Exploración de la base de datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #999; padding: 4px 8px; font-size: 13px; }
        th { background: #ddd; }
        pre { background: #f4f4f4; padding: 8px; font-size: 12px; }
    </style>
</head>
<body>
<h1>Exploración de la base de datos</h1>
<p>Servidor: <?= DB_HOST ?> | Usuario: <?= DB_USUARIO ?></p>
<p>Bases de datos disponibles: <b><?= htmlspecialchars(implode(', ', $bases)) ?></b></p>

<?php foreach ($bases as $base): ?>
    <h2>Base de datos: <?= htmlspecialchars($base) ?></h2>
    <?php
    $pdo->exec("USE `" . str_replace('`', '', $base) . "`");
    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    ?>
    <p>Tablas: <?= htmlspecialchars(implode(', ', $tablas)) ?></p>

    <?php foreach ($tablas as $tabla): ?>
        <h3>Tabla: <?= htmlspecialchars($tabla) ?></h3>
        <?php
        $nombre = "`" . str_replace('`', '', $tabla) . "`";
        $columnas = $pdo->query("SHOW COLUMNS FROM $nombre")->fetchAll();
        $total = $pdo->query("SELECT COUNT(*) FROM $nombre")->fetchColumn();
        $crear = $pdo->query("SHOW CREATE TABLE $nombre")->fetch(PDO::FETCH_NUM)[1] ?? '';
        ?>
        <p>Registros: <?= $total ?></p>
        <table>
            <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Defecto</th><th>Extra</th></tr>
            <?php foreach ($columnas as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['Field']) ?></td>
                    <td><?= htmlspecialchars($c['Type']) ?></td>
                    <td><?= htmlspecialchars($c['Null']) ?></td>
                    <td><?= htmlspecialchars($c['Key']) ?></td>
                    <td><?= htmlspecialchars((string) $c['Default']) ?></td>
                    <td><?= htmlspecialchars($c['Extra']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <pre><?= htmlspecialchars($crear) ?></pre>
    <?php endforeach; ?>
<?php endforeach; ?>
</body>
</html>
