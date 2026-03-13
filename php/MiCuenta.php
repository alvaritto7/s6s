<?php
/**
 * Controlador Mi cuenta — Datos del usuario, editar nombre y cambiar contraseña.
 * Accesible para cualquier usuario logueado.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class MiCuenta
{
    public function ejecutar(): void
    {
        $bd = new BaseDeDatos();
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $usuario = $usuarioId > 0 ? $bd->obtenerUsuarioPorId($usuarioId) : null;
        if (!$usuario) {
            header('Location: index.php?accion=' . ACCION_LOGIN);
            exit;
        }

        $nombreUsuario = $_SESSION['usuario_nombre'] ?? ($usuario['nombre'] ?? 'Usuario');
        $emailUsuario = $usuario['email'] ?? '';
        $rolUsuario = $_SESSION['usuario_rol'] ?? ($usuario['rol'] ?? ROL_EMPLEADO);
        $puedeAdmin = ($rolUsuario === ROL_ADMINISTRADOR || $rolUsuario === ROL_STAFF);
        $enlaceAdmin = $puedeAdmin ? '<li><a href="index.php?accion=admin">Administración</a></li>' : '';
        $enlaceGestionUsuarios = ($rolUsuario === ROL_ADMINISTRADOR) ? '<li><a href="index.php?accion=admin_usuarios">Gestión de usuarios</a></li>' : '';
        $enlaceMiCuenta = '<li><a href="index.php?accion=mi_cuenta" class="activo">Mi cuenta</a></li>';

        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);
        $html = cargarPlantilla('html/mi_cuenta.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'ENLACE_ADMIN' => $enlaceAdmin,
            'ENLACE_GESTION_USUARIOS' => $enlaceGestionUsuarios,
            'ENLACE_MI_CUENTA' => $enlaceMiCuenta,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'EMAIL_USUARIO' => htmlspecialchars($emailUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
