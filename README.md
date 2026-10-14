# EDA311_HITO3_TiconaJonathan

Proyecto del Hito 3 de Estructura de Datos (EDA-311).
Hice una aplicación web en PHP con MySQL para gestionar clientes, productos y pedidos (registrar, ver, modificar y eliminar). Los datos que se traen de la base se guardan en una lista doblemente enlazada que implementé yo, y esa lista se ordena con Bubble Sort.

Autor: Jonathan Emmanuel Ticona Pérez

## Qué se necesita

- XAMPP (con Apache y PHP 8 o superior)
- Acceso a la base de datos MySQL que nos dio el docente
- Git, para clonar el repositorio
- Un navegador

En el `php.ini` de XAMPP tienen que estar activadas las extensiones `pdo_mysql` y `openssl` (normalmente ya vienen activadas). `openssl` hace falta porque el servidor usa MySQL 8.

## Cómo instalarlo

1. Clonar el repositorio dentro de la carpeta `htdocs` de XAMPP:

```
cd C:\xampp\htdocs
git clone https://github.com/zeonzev/EDA311_HITO3_TiconaJonathan.git
```

2. Entrar a la carpeta `config`, hacer una copia de `config.ejemplo.php` y renombrarla como `config.php`.

3. Abrir `config.php` y poner los datos de conexión que da el docente:

```php
define('DB_HOST', 'IP_DEL_SERVIDOR');
define('DB_PUERTO', 3306);
define('DB_USUARIO', 'tu_usuario');
define('DB_CLAVE', 'tu_contraseña');
define('DB_NOMBRE', 'nombre_de_tu_base');
```

El archivo `config.php` está en el `.gitignore`, así que no se sube al repositorio y las contraseñas no quedan públicas.

## Cómo ejecutarlo

1. Abrir XAMPP y darle Start a **Apache** (no hace falta el MySQL de XAMPP porque nos conectamos al servidor del docente).
2. Abrir en el navegador: `http://localhost/EDA311_HITO3_TiconaJonathan/`
3. Desde el menú de arriba se entra a Clientes, Productos o Pedidos.

Si se quiere ver la estructura de la base de datos, está la página `explorar.php`.

## La base de datos

La revisé con MySQL Workbench y con la página `explorar.php`. Tiene tres tablas:

- **clientes:** `id_cliente`, `nombre`, `email`, `edad`
- **productos:** `id_producto`, `nombre`, `categoria`, `precio`, `stock`
- **pedidos:** `id_pedido`, `id_cliente`, `fecha`, `total`

Todas las tablas tienen su id como clave primaria con autoincremento.
La única relación es que `pedidos.id_cliente` apunta a `clientes.id_cliente`: un cliente puede tener varios pedidos. Por eso no se puede eliminar un cliente que tenga pedidos (la página muestra un aviso).

También encontré algunas restricciones que valido en los formularios:
- La edad del cliente tiene que ser 18 o más.
- El email del cliente y el nombre del producto no se pueden repetir.
- El precio tiene que ser mayor a 0, y el stock y el total no pueden ser negativos.

## Estructura del proyecto

```
clases/          -> Nodo, ListaDobleEnlazada, Cliente, Producto y Pedido
repositorios/    -> las consultas SQL de cada tabla
config/          -> la conexión a MySQL
includes/        -> el menú, el pie de página y funciones de ayuda
css/             -> los estilos
index.php        -> página de inicio
clientes.php     -> CRUD de clientes
productos.php    -> CRUD de productos
pedidos.php      -> CRUD de pedidos
explorar.php     -> muestra la estructura de la base de datos
```

Para las consultas usé consultas preparadas (`prepare` y `execute`) para evitar inyección SQL.

## Lista doblemente enlazada

Está en `clases/Nodo.php` y `clases/ListaDobleEnlazada.php`.

Cada nodo guarda un dato (un cliente, un producto o un pedido) y dos enlaces: uno al nodo anterior y otro al siguiente. La lista guarda la cabeza (primer nodo), la cola (último nodo) y el tamaño.

```
null <- [nodo 1] <-> [nodo 2] <-> [nodo 3] -> null
```

Tiene métodos para insertar al inicio y al final, y para recorrer la lista hacia adelante (desde la cabeza) o hacia atrás (desde la cola).

Para cargar los datos, el método `listar()` de cada repositorio hace un SELECT y va metiendo cada registro a la lista con `insertarFinal()`. En la página se puede ver la lista recorrida en los dos sentidos.

## Bubble Sort

Está en el método `ordenarBurbuja()` de `ListaDobleEnlazada.php` y trabaja directamente sobre los nodos, sin pasar la lista a un arreglo.

Lo que hace es recorrer la lista comparando cada nodo con el siguiente. Si están en desorden, intercambia sus datos. Después de cada pasada, el último elemento ya queda en su lugar, así que la siguiente pasada se detiene antes. Se repite hasta que en una pasada no haya ningún cambio.

Antes de ordenar hago una copia de la lista, para poder mostrar el orden original y el ordenado.

Los criterios que elegí son:
- **Clientes:** por nombre, de la A a la Z, porque así es más fácil encontrar a alguien.
- **Productos:** por precio, de menor a mayor, para comparar precios. Si dos cuestan lo mismo, se ordenan por nombre.
- **Pedidos:** por fecha, del más antiguo al más reciente, para verlos en el orden en que se hicieron.