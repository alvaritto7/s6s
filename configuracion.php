<?php
/**
 * Configuración global s6s — Rutas, roles y constantes.
 * Se carga desde index.php en la raíz del proyecto.
 */

declare(strict_types=1);

// Ruta base del proyecto (este archivo está en la raíz s6s/)
define('RUTA_RAIZ', __DIR__);

// Base de datos MySQL (phpMyAdmin / XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 's6s');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Roles (control de acceso con sesiones PHP):
 * - administrador: acceso total. Panel Admin completo (resumen, gráficos, exportar PDFs, CRUD productos) y cambiar estados de pedidos en Peticiones.
 * - staff: revisión operativa. Puede entrar al panel Admin (solo ver resumen y gráficos) y puede cambiar estados de pedidos en Peticiones. No puede exportar PDFs ni crear/editar/desactivar productos.
 * - empleado: solo uso de la aplicación (Inventario, Peticiones, Wishlist); no accede a Administración.
 * El registro público crea siempre usuarios con rol empleado.
 */
define('ROL_ADMINISTRADOR', 'administrador');
define('ROL_STAFF', 'staff');
define('ROL_EMPLEADO', 'empleado');

// Acciones permitidas en el front controller
define('ACCION_LOGIN', 'login');
define('ACCION_REGISTRO', 'registro');
define('ACCION_LOGOUT', 'logout');
define('ACCION_DASHBOARD', 'dashboard');
define('ACCION_INVENTARIO', 'inventario');
define('ACCION_PETICIONES', 'peticiones');
define('ACCION_WISHLIST', 'wishlist');
define('ACCION_ADMIN', 'admin');
define('ACCION_ADMIN_USUARIOS', 'admin_usuarios');
define('ACCION_MI_CUENTA', 'mi_cuenta');
define('ACCION_API', 'api');

// Identidad visual: logotipo e isotipo
define('ASSET_LOGOTIPO', 'imagenes/logotipo_transparente.png');
define('ASSET_LOGOTIPO_OPACO', 'imagenes/logotipo_s6s.png');
define('ASSET_ISOTIPO', 'imagenes/isotipo_transparente.png');
// Favicon de pestaña: siempre favicon.png
define('ASSET_FAVICON', 'imagenes/favicon.png');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Plantillas.php';
