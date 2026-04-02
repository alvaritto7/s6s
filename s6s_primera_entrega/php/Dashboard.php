<?php
// Dashboard tras login: enlaces a secciones y alertas para roles con permiso de administración.
declare(strict_types=1);

class Dashboard
{
    public function ejecutar(): void
    {
        $bd = new BaseDeDatos();
        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $rolUsuario    = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;
        $puedeAdmin    = ($rolUsuario === ROL_ADMINISTRADOR || $rolUsuario === ROL_STAFF);
        $alertasStock  = $puedeAdmin ? $bd->obtenerProductosBajoUmbral() : [];

        $bloqueAlertas = '';
        if ($puedeAdmin && !empty($alertasStock)) {
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
        $enlaceAdminCard = $puedeAdmin ? '<a href="index.php?accion=admin" class="dashboard-card dashboard-card-admin"><span class="dashboard-card-icono" aria-hidden="true">⚙</span><span class="dashboard-card-titulo">Administración</span><span class="dashboard-card-desc">Resumen y alertas</span></a>' : '';

        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);
        $html = cargarPlantilla('html/dashboard.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'ENLACE_ADMIN' => $enlaceAdmin,
            'BLOQUE_ALERTAS' => $bloqueAlertas,
            'ENLACE_ADMIN_CARD' => $enlaceAdminCard,
            'ALERTAS_CANTIDAD' => (string) count($alertasStock),
            'PUEDE_ADMIN' => $puedeAdmin ? '1' : '0',
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
