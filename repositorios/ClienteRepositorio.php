<?php
require_once __DIR__ . '/../clases/Cliente.php';
require_once __DIR__ . '/../clases/ListaDobleEnlazada.php';

class ClienteRepositorio
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listar(): ListaDobleEnlazada
    {
        $lista = new ListaDobleEnlazada();
        $consulta = $this->conexion->query("SELECT id_cliente, nombre, email, edad FROM clientes ORDER BY id_cliente");
        foreach ($consulta as $fila) {
            $lista->insertarFinal(new Cliente(
                (int) $fila['id_cliente'], $fila['nombre'], $fila['email'], (int) $fila['edad']
            ));
        }
        return $lista;
    }

    public function buscarPorId(int $idCliente): ?Cliente
    {
        $consulta = $this->conexion->prepare("SELECT id_cliente, nombre, email, edad FROM clientes WHERE id_cliente = ?");
        $consulta->execute([$idCliente]);
        $fila = $consulta->fetch();
        if (!$fila) {
            return null;
        }
        return new Cliente((int) $fila['id_cliente'], $fila['nombre'], $fila['email'], (int) $fila['edad']);
    }

    public function insertar(Cliente $cliente): int
    {
        $consulta = $this->conexion->prepare("INSERT INTO clientes (nombre, email, edad) VALUES (?, ?, ?)");
        $consulta->execute([$cliente->nombre, $cliente->email, $cliente->edad]);
        return (int) $this->conexion->lastInsertId();
    }

    public function actualizar(Cliente $cliente): void
    {
        $consulta = $this->conexion->prepare("UPDATE clientes SET nombre = ?, email = ?, edad = ? WHERE id_cliente = ?");
        $consulta->execute([$cliente->nombre, $cliente->email, $cliente->edad, $cliente->idCliente]);
    }

    public function eliminar(int $idCliente): void
    {
        $consulta = $this->conexion->prepare("DELETE FROM clientes WHERE id_cliente = ?");
        $consulta->execute([$idCliente]);
    }
}
