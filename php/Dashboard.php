<?php
/**
 * Controlador Dashboard — Pantalla principal tras el login.
 * Muestra el logotipo en la esquina superior y el contenido central.
 * El acceso está protegido por index.php (solo usuarios con sesión).
 */

declare(strict_types=1);

class Dashboard
{
    /**
     * Carga la plantilla HTML del dashboard y reemplaza placeholders.
     */
    public function ejecutar(): void
    {
        $bd = new BaseDeDatos();
        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $rolUsuario    = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;
        $alertasStock  = $bd->obtenerProductosBajoUmbral();
        $puedeAdmin    = ($rolUsuario === ROL_ADMINISTRADOR || $rolUsuario === ROL_STAFF);

        $bloqueAlertas = '';
        if (!empty($alertasStock)) {
            $items = '';
            foreach ($alertasStock as $a) {
                $stock = (int) ($a['stock'] ?? 0);
                $umbral = (int) ($a['umbral_critico'] ?? 0);
                $extra = $stock === 0 ? ' <span class="texto-agotado">(Agotado)</span>' : '';
                $items .= '<li>' . htmlspecialchars($a['nombre'] ?? '') . ' — Stock: <strong>' . $stock . '</strong>' . $extra . ' (umbral: ' . $umbral . ')</li>';
            }
            $bloqueAlertas = '<section class="alertas-dashboard tarjeta" aria-label="Alertas de stock"><h2 class="titulo-alertas"><span class="titulo-alertas-icono" aria-hidden="true">⚠</span> Stock bajo umbral crítico</h2><ul class="lista-alertas">' . $items . '</ul></section>';
        }
        $enlaceAdmin = $puedeAdmin ? '<li><a href="index.php?accion=admin">Administración</a></li>' : '';
        $enlaceGestionUsuarios = ($rolUsuario === ROL_ADMINISTRADOR) ? '<li><a href="index.php?accion=admin_usuarios">Gestión de usuarios</a></li>' : '';
        $enlaceMiCuenta = '<li><a href="index.php?accion=mi_cuenta">Mi cuenta</a></li>';
        $enlaceAdminCard = $puedeAdmin ? '<a href="index.php?accion=admin" class="dashboard-card dashboard-card-admin"><span class="dashboard-card-icono" aria-hidden="true">⚙</span><span class="dashboard-card-titulo">Administración</span><span class="dashboard-card-desc">Informes, productos y pedidos</span></a>' : '';

        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);
        $html = cargarPlantilla('html/dashboard.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'ENLACE_ADMIN' => $enlaceAdmin,
            'ENLACE_GESTION_USUARIOS' => $enlaceGestionUsuarios,
            'ENLACE_MI_CUENTA' => $enlaceMiCuenta,
            'BLOQUE_ALERTAS' => $bloqueAlertas,
            'ENLACE_ADMIN_CARD' => $enlaceAdminCard,
            'ALERTAS_CANTIDAD' => (string) count($alertasStock),
            'PUEDE_ADMIN' => $puedeAdmin ? '1' : '0',
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
