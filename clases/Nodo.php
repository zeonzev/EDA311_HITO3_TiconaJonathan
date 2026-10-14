<?php

class Nodo
{
    public $dato;
    public ?Nodo $anterior = null;
    public ?Nodo $siguiente = null;

    public function __construct($dato)
    {
        $this->dato = $dato;
    }
}
