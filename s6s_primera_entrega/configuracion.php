<?php
// Rutas, conexión MySQL y roles. Incluido desde index.php.
declare(strict_types=1);

define('RUTA_RAIZ', __DIR__);

define('DB_HOST', 'localhost');
define('DB_NAME', 's6s_primera_entrega');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('ROL_ADMINISTRADOR', 'administrador');
define('ROL_STAFF', 'staff');
define('ROL_EMPLEADO', 'empleado');

define('ACCION_LOGIN', 'login');
define('ACCION_REGISTRO', 'registro');
define('ACCION_LOGOUT', 'logout');
define('ACCION_DASHBOARD', 'dashboard');
define('ACCION_INVENTARIO', 'inventario');
define('ACCION_PETICIONES', 'peticiones');
define('ACCION_ADMIN', 'admin');
define('ACCION_API', 'api');

define('ASSET_LOGOTIPO', 'imagenes/logotipo_transparente.png');
define('ASSET_LOGOTIPO_OPACO', 'imagenes/logotipo_s6s.png');
define('ASSET_ISOTIPO', 'imagenes/isotipo_transparente.png');
define('ASSET_FAVICON', 'imagenes/favicon.png');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Plantillas.php';
