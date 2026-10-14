<?php
require_once __DIR__ . '/../clases/Pedido.php';
require_once __DIR__ . '/../clases/ListaDobleEnlazada.php';

class PedidoRepositorio
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listar(): ListaDobleEnlazada
    {
        $lista = new ListaDobleEnlazada();
        $sql = "SELECT p.id_pedido, p.id_cliente, p.fecha, p.total, c.nombre AS nombre_cliente
                FROM pedidos p
                LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
                ORDER BY p.id_pedido";
        foreach ($this->conexion->query($sql) as $fila) {
            $lista->insertarFinal($this->crearPedido($fila));
        }
        return $lista;
    }

    public function buscarPorId(int $idPedido): ?Pedido
    {
        $sql = "SELECT p.id_pedido, p.id_cliente, p.fecha, p.total, c.nombre AS nombre_cliente
                FROM pedidos p
                LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
                WHERE p.id_pedido = ?";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([$idPedido]);
        $fila = $consulta->fetch();
        return $fila ? $this->crearPedido($fila) : null;
    }

    public function insertar(Pedido $pedido): int
    {
        $consulta = $this->conexion->prepare("INSERT INTO pedidos (id_cliente, fecha, total) VALUES (?, ?, ?)");
        $consulta->execute([$pedido->idCliente, $pedido->fecha, $pedido->total]);
        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(Pedido $pedido): void
    {
        $consulta = $this->conexion->prepare("UPDATE pedidos SET id_cliente = ?, fecha = ?, total = ? WHERE id_pedido = ?");
        $consulta->execute([$pedido->idCliente, $pedido->fecha, $pedido->total, $pedido->idPedido]);
    }

    public function eliminar(int $idPedido): void
    {
        $consulta = $this->conexion->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
        $consulta->execute([$idPedido]);
    }

    private function crearPedido(array $fila): Pedido
    {
        return new Pedido(
            (int) $fila['id_pedido'], (int) $fila['id_cliente'], $fila['fecha'],
            (float) $fila['total'], $fila['nombre_cliente'] ?? ''
        );
    }
}
