<?php
/**
 * Controlador Inventario — Catálogo dinámico con tarjetas y filtros AJAX.
 * Carga la vista con categorías; el listado de productos se pide por JS vía API.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class Inventario
{
    public function ejecutar(): void
    {
        $bd = new BaseDeDatos();
        $categorias = $bd->obtenerCategorias();
        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $rolUsuario = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;
        $puedeAdmin = ($rolUsuario === ROL_ADMINISTRADOR || $rolUsuario === ROL_STAFF);
        $enlaceAdmin = $puedeAdmin ? '<li><a href="index.php?accion=admin">Administración</a></li>' : '';
        $enlaceGestionUsuarios = ($rolUsuario === ROL_ADMINISTRADOR) ? '<li><a href="index.php?accion=admin_usuarios">Gestión de usuarios</a></li>' : '';
        $enlaceMiCuenta = '<li><a href="index.php?accion=mi_cuenta">Mi cuenta</a></li>';

        $listaCategorias = '';
        foreach ($categorias as $cat) {
            $slug = htmlspecialchars($cat['slug'] ?? (string) ($cat['id'] ?? ''));
            $nombre = htmlspecialchars($cat['nombre'] ?? '');
            $listaCategorias .= '<button type="button" class="boton boton-filtro" data-categoria="' . $slug . '">' . $nombre . '</button>';
        }

        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);
        $html = cargarPlantilla('html/inventario.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'ENLACE_ADMIN' => $enlaceAdmin,
            'ENLACE_GESTION_USUARIOS' => $enlaceGestionUsuarios,
            'ENLACE_MI_CUENTA' => $enlaceMiCuenta,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'LISTA_CATEGORIAS' => $listaCategorias,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
