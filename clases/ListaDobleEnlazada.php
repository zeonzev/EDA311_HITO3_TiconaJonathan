<?php
require_once __DIR__ . '/Nodo.php';

class ListaDobleEnlazada
{
    private ?Nodo $cabeza = null;
    private ?Nodo $cola = null;
    private int $tamanyo = 0;

    public function insertarInicio($dato): void
    {
        $nuevo = new Nodo($dato);
        if ($this->estaVacia()) {
            $this->cabeza = $nuevo;
            $this->cola = $nuevo;
        } else {
            $nuevo->siguiente = $this->cabeza;
            $this->cabeza->anterior = $nuevo;
            $this->cabeza = $nuevo;
        }
        $this->tamanyo++;
    }

    public function insertarFinal($dato): void
    {
        $nuevo = new Nodo($dato);
        if ($this->estaVacia()) {
            $this->cabeza = $nuevo;
            $this->cola = $nuevo;
        } else {
            $this->cola->siguiente = $nuevo;
            $nuevo->anterior = $this->cola;
            $this->cola = $nuevo;
        }
        $this->tamanyo++;
    }

    public function recorrerAdelante(): array
    {
        $datos = [];
        $actual = $this->cabeza;
        while ($actual !== null) {
            $datos[] = $actual->dato;
            $actual = $actual->siguiente;
        }
        return $datos;
    }

    public function recorrerAtras(): array
    {
        $datos = [];
        $actual = $this->cola;
        while ($actual !== null) {
            $datos[] = $actual->dato;
            $actual = $actual->anterior;
        }
        return $datos;
    }

    public function tamanyo(): int
    {
        return $this->tamanyo;
    }

    public function estaVacia(): bool
    {
        return $this->cabeza === null;
    }
}
