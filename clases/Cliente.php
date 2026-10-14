<?php

class Cliente
{
    public ?int $idCliente;
    public string $nombre;
    public string $email;
    public int $edad;

    public function __construct(?int $idCliente, string $nombre, string $email, int $edad)
    {
        $this->idCliente = $idCliente;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->edad = $edad;
    }
}
