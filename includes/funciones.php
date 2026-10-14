<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function largo(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto);
}

function guardarMensaje(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

function obtenerMensaje(): ?array
{
    $mensaje = $_SESSION['mensaje'] ?? null;
    unset($_SESSION['mensaje']);
    return $mensaje;
}

function redirigir(string $url): void
{
    header("Location: $url");
    exit;
}

function mensajeErrorBD(PDOException $error): string
{
    $codigo = $error->errorInfo[1] ?? 0;
    return match ($codigo) {
        1062 => "Ya existe un registro con ese dato (no se puede repetir).",
        1451 => "No se puede eliminar porque tiene registros relacionados en otra tabla.",
        1452 => "El registro relacionado no existe.",
        3819, 4025 => "Los datos no cumplen las restricciones de la base de datos.",
        default => "Error de la base de datos: " . $error->getMessage(),
    };
}

function dibujarNodos(array $etiquetas): string
{
    $html = '<div class="nodos"><span class="flecha">null ⇄</span>';
    foreach ($etiquetas as $i => $etiqueta) {
        $html .= '<span class="nodo">' . e($etiqueta) . '</span>';
        $html .= '<span class="flecha">' . ($i < count($etiquetas) - 1 ? '⇄' : '⇄ null') . '</span>';
    }
    return $html . '</div>';
}
