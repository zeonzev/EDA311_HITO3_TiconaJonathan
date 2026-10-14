<?php

class Producto
{
    public ?int $idProducto;
    public string $nombre;
    public string $categoria;
    public float $precio;
    public int $stock;

    public function __construct(?int $idProducto, string $nombre, string $categoria, float $precio, int $stock)
    {
        $this->idProducto = $idProducto;
        $this->nombre = $nombre;
        $this->categoria = $categoria;
        $this->precio = $precio;
        $this->stock = $stock;
    }
}
