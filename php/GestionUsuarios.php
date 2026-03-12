<?php
/**
 * Controlador Gestión de usuarios — Lista, cambiar rol, activar/desactivar, eliminar.
 * Solo accesible para rol administrador.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class GestionUsuarios
{
    public function ejecutar(): void
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        if ($rol !== ROL_ADMINISTRADOR) {
            header('Location: index.php?accion=' . ACCION_DASHBOARD);
            exit;
        }

        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $rolUsuario = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $html = cargarPlantilla('html/admin_usuarios.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'USUARIO_ID' => (string) $usuarioId,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
