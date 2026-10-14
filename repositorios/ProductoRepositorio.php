<?php
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../clases/ListaDobleEnlazada.php';

class ProductoRepositorio
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listar(): ListaDobleEnlazada
    {
        $lista = new ListaDobleEnlazada();
        $consulta = $this->conexion->query("SELECT id_producto, nombre, categoria, precio, stock FROM productos ORDER BY id_producto");
        foreach ($consulta as $fila) {
            $lista->insertarFinal($this->crearProducto($fila));
        }
        return $lista;
    }

    public function buscarPorId(int $idProducto): ?Producto
    {
        $consulta = $this->conexion->prepare("SELECT id_producto, nombre, categoria, precio, stock FROM productos WHERE id_producto = ?");
        $consulta->execute([$idProducto]);
        $fila = $consulta->fetch();
        return $fila ? $this->crearProducto($fila) : null;
    }

    public function insertar(Producto $producto): int
    {
        $consulta = $this->conexion->prepare("INSERT INTO productos (nombre, categoria, precio, stock) VALUES (?, ?, ?, ?)");
        $consulta->execute([$producto->nombre, $producto->categoria, $producto->precio, $producto->stock]);
        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(Producto $producto): void
    {
        $consulta = $this->conexion->prepare("UPDATE productos SET nombre = ?, categoria = ?, precio = ?, stock = ? WHERE id_producto = ?");
        $consulta->execute([$producto->nombre, $producto->categoria, $producto->precio, $producto->stock, $producto->idProducto]);
    }

    public function eliminar(int $idProducto): void
    {
        $consulta = $this->conexion->prepare("DELETE FROM productos WHERE id_producto = ?");
        $consulta->execute([$idProducto]);
    }

    private function crearProducto(array $fila): Producto
    {
        return new Producto(
            (int) $fila['id_producto'], $fila['nombre'], $fila['categoria'],
            (float) $fila['precio'], (int) $fila['stock']
        );
    }
}
