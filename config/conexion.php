<?php
require_once __DIR__ . '/config.php';

function conectar(): PDO
{
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PUERTO . ";charset=utf8mb4";
    if (DB_NOMBRE !== '') {
        $dsn .= ";dbname=" . DB_NOMBRE;
    }
    $pdo = new PDO($dsn, DB_USUARIO, DB_CLAVE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}
