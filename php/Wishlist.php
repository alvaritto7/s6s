<?php
/**
 * Controlador Wishlist — Tablón de propuestas de compra con sistema de votos.
 * La lista se puede refrescar por AJAX; la vista incluye el contenedor para el JS.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class Wishlist
{
    public function ejecutar(): void
    {
        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $rolUsuario = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;
        $puedeAdmin = ($rolUsuario === ROL_ADMINISTRADOR || $rolUsuario === ROL_STAFF);
        $enlaceAdmin = $puedeAdmin ? '<li><a href="index.php?accion=admin">Administración</a></li>' : '';
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $html = cargarPlantilla('html/wishlist.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'ENLACE_ADMIN' => $enlaceAdmin,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
