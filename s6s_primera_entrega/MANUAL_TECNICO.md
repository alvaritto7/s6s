# Manual técnico — s6s

## Requisitos

- PHP 8+ con PDO MySQL
- MySQL o MariaDB
- Servidor web (p. ej. Apache con XAMPP)

## Configuración

`configuracion.php`: `RUTA_RAIZ`, constantes `DB_*`, roles y acciones de la aplicación.

## Entrada

`index.php` enruta por `accion`: login, registro, logout, dashboard, inventario, peticiones, admin, api.

## API

`php/Api.php` — JSON según `?recurso=` (categorías, productos, alertas_stock, crear_pedido, cambiar_estado_pedido). Requiere sesión.

## Datos

`php/BaseDeDatos.php` — PDO, creación de tablas si no existen, datos iniciales si las tablas están vacías.

## Plantillas

HTML en `html/`; sustitución de `{{PLACEHOLDER}}` en `php/Plantillas.php`.

## Front-end

CSS en `css/`, scripts en `js/`. SweetAlert2 y fetch donde corresponda.
