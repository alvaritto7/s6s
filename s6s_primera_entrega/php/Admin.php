<?php
// Panel admin: totales, avisos y listado de alertas de stock.
declare(strict_types=1);

class Admin
{
    public function ejecutar(): void
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        if ($rol !== ROL_ADMINISTRADOR && $rol !== ROL_STAFF) {
            header('Location: index.php?accion=' . ACCION_DASHBOARD);
            exit;
        }
        $bd = new BaseDeDatos();
        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $rolUsuario = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;

        $productos = $bd->obtenerProductos();
        $pedidos = $bd->obtenerPedidos();
        $alertasStock = $bd->obtenerProductosBajoUmbral();
        $totalProductos = count(array_filter($productos, fn($p) => ($p['activo'] ?? true) !== false));
        $totalPedidos = count($pedidos);
        $totalAlertas = count($alertasStock);
        $pedidosPendientes = count(array_filter($pedidos, fn($p) => in_array($p['estado'] ?? '', ['pendiente', 'en_revision'], true)));
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $bloqueAlertasAdmin = '';
        if (!empty($alertasStock)) {
            $items = '';
            $limite = 12;
            $mostrados = array_slice($alertasStock, 0, $limite);
            foreach ($mostrados as $a) {
                $nombre = htmlspecialchars($a['nombre'] ?? '');
                $stock = (int) ($a['stock'] ?? 0);
                $umbral = (int) ($a['umbral_critico'] ?? 0);
                $clase = $stock === 0 ? 'alerta-item agotado' : 'alerta-item';
                $items .= '<li class="' . $clase . '"><span class="alerta-nombre">' . $nombre . '</span><span class="alerta-datos">' . $stock . ' / ' . $umbral . '</span></li>';
            }
            $resto = count($alertasStock) - $limite;
            $mas = $resto > 0 ? '<p class="admin-alertas-mas">y ' . $resto . ' más</p>' : '';
            $bloqueAlertasAdmin = '<ul class="lista-alertas-admin">' . $items . '</ul>' . $mas;
        } else {
            $bloqueAlertasAdmin = '<p class="color-gris admin-alertas-vacio">Ningún producto bajo umbral.</p>';
        }

        $esAdministrador = ($rol === ROL_ADMINISTRADOR);
        $bloqueInformesPdf = $esAdministrador
            ? '<section class="admin-informes" aria-labelledby="titulo-informes"><h2 id="titulo-informes">Informes</h2><p class="color-gris">Exportación a PDF o HTML: pendiente de implementar.</p></section>'
            : '<section class="admin-informes" aria-labelledby="titulo-informes"><h2 id="titulo-informes">Informes</h2><p class="color-gris">Solo el administrador puede generar informes.</p></section>';

        $bloqueGestionProductos = $esAdministrador
            ? '<section class="admin-productos" aria-labelledby="titulo-productos"><h2 id="titulo-productos">Gestión de productos</h2><p class="color-gris">Alta, edición y baja desde esta pantalla: pendiente. El catálogo actual viene de la base de datos.</p></section>'
            : '<section class="admin-productos" aria-labelledby="titulo-productos"><h2 id="titulo-productos">Gestión de productos</h2><p class="color-gris">Solo el administrador puede gestionar el catálogo.</p></section>';

        $avisos = [];
        if ($pedidosPendientes > 0) {
            $avisos[] = '<li class="aviso-item"><a href="index.php?accion=peticiones">' . $pedidosPendientes . ' petición' . ($pedidosPendientes !== 1 ? 'es' : '') . ' pendiente' . ($pedidosPendientes !== 1 ? 's' : '') . ' de revisión</a></li>';
        }
        if ($totalAlertas > 0) {
            $avisos[] = '<li class="aviso-item aviso-alerta"><span class="aviso-icono" aria-hidden="true">⚠</span> ' . $totalAlertas . ' producto' . ($totalAlertas !== 1 ? 's' : '') . ' con stock bajo umbral (ver abajo)</li>';
        }
        $bloqueAvisosDashboard = empty($avisos)
            ? '<p class="color-gris admin-avisos-vacio">Nada pendiente de tu atención.</p>'
            : '<ul class="admin-avisos-lista">' . implode('', $avisos) . '</ul>';

        $textoEstadisticas = 'Gráficos de estadísticas: pendiente de implementar.';

        $html = cargarPlantilla('html/admin.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'TOTAL_PRODUCTOS' => (string) $totalProductos,
            'TOTAL_PEDIDOS' => (string) $totalPedidos,
            'TOTAL_ALERTAS' => (string) $totalAlertas,
            'TEXTO_ESTADISTICAS_PLACEHOLDER' => $textoEstadisticas,
            'BLOQUE_AVISOS_DASHBOARD' => $bloqueAvisosDashboard,
            'BLOQUE_ALERTAS_ADMIN' => $bloqueAlertasAdmin,
            'BLOQUE_INFORMES_PDF' => $bloqueInformesPdf,
            'BLOQUE_GESTION_PRODUCTOS' => $bloqueGestionProductos,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
