<?php
/**
 * Front controller s6s — Punto de entrada único.
 * Todas las peticiones pasan por aquí; la acción determina qué archivo PHP ejecutar.
 * Estructura: js/, css/, imagenes/, html/ (plantillas), php/ (lógica). BD MySQL creada desde php/BaseDeDatos.php.
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'configuracion.php';
require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'BaseDeDatos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

if ($accion === ACCION_LOGOUT) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?accion=' . ACCION_DASHBOARD);
        exit;
    }
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Login.php';
    (new Login())->cerrarSesion();
    exit;
}

$logueado = !empty($_SESSION['usuario_id']);
if ($logueado && ($accion === ACCION_LOGIN || $accion === ACCION_REGISTRO || $accion === '')) {
    header('Location: index.php?accion=' . ACCION_DASHBOARD);
    exit;
}

if ($accion === ACCION_API) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Api.php';
    (new Api())->ejecutar();
    exit;
}

if (!$logueado && $accion !== ACCION_LOGIN && $accion !== ACCION_REGISTRO) {
    header('Location: index.php?accion=' . ACCION_LOGIN);
    exit;
}

if ($accion === ACCION_REGISTRO) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Registro.php';
    (new Registro())->ejecutar();
    exit;
}

if ($accion === ACCION_LOGIN) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Login.php';
    (new Login())->ejecutar();
    exit;
}

if ($accion === ACCION_DASHBOARD) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Dashboard.php';
    (new Dashboard())->ejecutar();
    exit;
}

if ($accion === ACCION_INVENTARIO) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Inventario.php';
    (new Inventario())->ejecutar();
    exit;
}

if ($accion === ACCION_PETICIONES) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Peticiones.php';
    (new Peticiones())->ejecutar();
    exit;
}

if ($accion === ACCION_WISHLIST) {
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Wishlist.php';
    (new Wishlist())->ejecutar();
    exit;
}

if ($accion === ACCION_ADMIN) {
    $rol = $_SESSION['usuario_rol'] ?? '';
    if ($rol !== ROL_ADMINISTRADOR && $rol !== ROL_STAFF) {
        header('Location: index.php?accion=' . ACCION_DASHBOARD);
        exit;
    }
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Admin.php';
    (new Admin())->ejecutar();
    exit;
}

if ($accion === ACCION_ADMIN_USUARIOS) {
    $rol = $_SESSION['usuario_rol'] ?? '';
    if ($rol !== ROL_ADMINISTRADOR) {
        header('Location: index.php?accion=' . ACCION_DASHBOARD);
        exit;
    }
    require_once RUTA_RAIZ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'GestionUsuarios.php';
    (new GestionUsuarios())->ejecutar();
    exit;
}

header('Location: index.php?accion=' . ACCION_LOGIN);
exit;
