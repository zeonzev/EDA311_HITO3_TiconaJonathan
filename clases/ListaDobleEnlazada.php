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

    public function copiar(): ListaDobleEnlazada
    {
        $copia = new ListaDobleEnlazada();
        $actual = $this->cabeza;
        while ($actual !== null) {
            $copia->insertarFinal($actual->dato);
            $actual = $actual->siguiente;
        }
        return $copia;
    }

    public function ordenarBurbuja(callable $vaDespues): array
    {
        $pasadas = 0;
        $comparaciones = 0;
        $intercambios = 0;

        if ($this->tamanyo < 2) {
            return ['pasadas' => 0, 'comparaciones' => 0, 'intercambios' => 0];
        }

        $limite = null;
        do {
            $huboIntercambio = false;
            $pasadas++;
            $actual = $this->cabeza;

            while ($actual->siguiente !== $limite) {
                $comparaciones++;
                if ($vaDespues($actual->dato, $actual->siguiente->dato)) {
                    $temporal = $actual->dato;
                    $actual->dato = $actual->siguiente->dato;
                    $actual->siguiente->dato = $temporal;
                    $intercambios++;
                    $huboIntercambio = true;
                }
                $actual = $actual->siguiente;
            }
            $limite = $actual;
        } while ($huboIntercambio);

        return ['pasadas' => $pasadas, 'comparaciones' => $comparaciones, 'intercambios' => $intercambios];
    }
}
