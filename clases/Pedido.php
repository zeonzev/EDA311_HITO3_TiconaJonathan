<?php

class Pedido
{
    public ?int $idPedido;
    public int $idCliente;
    public string $fecha;
    public float $total;
    public string $nombreCliente;

    public function __construct(?int $idPedido, int $idCliente, string $fecha, float $total, string $nombreCliente = '')
    {
        $this->idPedido = $idPedido;
        $this->idCliente = $idCliente;
        $this->fecha = $fecha;
        $this->total = $total;
        $this->nombreCliente = $nombreCliente;
    }
}
