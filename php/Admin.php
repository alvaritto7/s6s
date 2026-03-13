<?php
/**
 * Controlador Admin — Gráficos (Chart.js) y exportación PDF.
 * Solo accesible para rol administrador o staff según criterio del proyecto.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

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
        $categorias = $bd->obtenerCategorias();
        $pedidos = $bd->obtenerPedidos();
        $alertasStock = $bd->obtenerProductosBajoUmbral();
        $totalProductos = count(array_filter($productos, fn($p) => ($p['activo'] ?? true) !== false));
        $totalPedidos = count($pedidos);
        $totalAlertas = count($alertasStock);
        $pedidosPendientes = count(array_filter($pedidos, fn($p) => in_array($p['estado'] ?? '', ['pendiente', 'en_revision'], true)));
        $propuestasEnEstudio = $bd->contarPropuestasEnEstudio();

        $porCategoria = [];
        foreach ($categorias as $cat) {
            $idCat = (int) ($cat['id'] ?? 0);
            $porCategoria[$cat['nombre'] ?? 'Sin nombre'] = count(array_filter($productos, fn($p) => (int)($p['categoria_id'] ?? 0) === $idCat));
        }
        $porEstado = [];
        foreach ($pedidos as $p) {
            $est = $p['estado'] ?? 'sin_estado';
            $porEstado[$est] = ($porEstado[$est] ?? 0) + 1;
        }

        $datosGraficos = json_encode(['categorias' => $porCategoria, 'estados' => $porEstado]);
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
            ? '<section class="admin-informes" aria-labelledby="titulo-informes"><h2 id="titulo-informes">Informes</h2><p class="color-gris">Informes de inventario y pedidos listos para imprimir o guardar como PDF desde el navegador.</p><div class="admin-informes-botones"><a href="index.php?accion=api&amp;recurso=pdf_inventario" class="boton boton-primario" target="_blank" rel="noopener">Inventario (informe)</a><a href="index.php?accion=api&amp;recurso=pdf_pedidos" class="boton boton-primario" target="_blank" rel="noopener">Pedidos (informe)</a></div></section>'
            : '<section class="admin-informes" aria-labelledby="titulo-informes"><h2 id="titulo-informes">Informes</h2><p class="color-gris">Solo el administrador puede ver y generar los informes de inventario y pedidos.</p></section>';
        $bloqueGestionProductos = $esAdministrador
            ? '<section class="admin-productos" aria-labelledby="titulo-productos"><h2 id="titulo-productos">Gestión de productos</h2><button type="button" class="boton boton-primario" id="btn-abrir-form-producto">Añadir producto</button><div class="form-producto-wrapper" id="form-producto-wrapper" hidden><form id="form-producto" enctype="multipart/form-data"><input type="hidden" name="id" id="producto-id" value=""><label for="producto-nombre">Nombre</label><input type="text" id="producto-nombre" name="nombre" required maxlength="200"><label for="producto-descripcion">Descripción</label><input type="text" id="producto-descripcion" name="descripcion"><label for="producto-categoria_id">Categoría</label><select id="producto-categoria_id" name="categoria_id" required><option value="1">IT</option><option value="2">Mobiliario</option><option value="3">Consumibles</option></select><label for="producto-stock">Stock</label><input type="number" id="producto-stock" name="stock" min="0" value="0"><label for="producto-umbral_critico">Umbral crítico</label><input type="number" id="producto-umbral_critico" name="umbral_critico" min="0" value="0"><label class="upload-zone" for="producto-imagen" id="upload-zone-imagen"><input type="file" id="producto-imagen" name="imagen" accept="image/*" class="upload-input"><span class="upload-zone-inner"><span class="upload-icon" aria-hidden="true"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></span><span class="upload-text">Imagen del producto <em>(opcional)</em></span><span class="upload-hint">Arrastra aquí o haz clic para elegir</span><span class="upload-filename" id="upload-filename" aria-live="polite"></span></span></label><div class="form-producto-botones"><button type="button" class="boton boton-secundario" id="btn-cerrar-form-producto">Cancelar</button><button type="submit" class="boton boton-primario">Guardar</button></div></form></div><div id="lista-productos-admin" class="lista-productos-admin"></div></section>'
            : '<section class="admin-productos" aria-labelledby="titulo-productos"><h2 id="titulo-productos">Gestión de productos</h2><p class="color-gris">Solo el administrador puede añadir, editar o desactivar productos del catálogo.</p></section>';

        $bloqueGestionUsuarios = $esAdministrador
            ? '<section class="admin-gestion-usuarios" aria-labelledby="titulo-gestion-usuarios"><h2 id="titulo-gestion-usuarios">Gestión de usuarios</h2><p class="color-gris">Cambiar rol (empleado, staff, administrador), activar o desactivar cuentas y eliminar usuarios.</p><p><a href="index.php?accion=admin_usuarios" class="boton boton-primario">Ir a Gestión de usuarios</a></p></section>'
            : '';
        $enlaceGestionUsuarios = $esAdministrador ? '<li><a href="index.php?accion=admin_usuarios">Gestión de usuarios</a></li>' : '';
        $enlaceMiCuenta = '<li><a href="index.php?accion=mi_cuenta">Mi cuenta</a></li>';

        $avisos = [];
        if ($pedidosPendientes > 0) {
            $avisos[] = '<li class="aviso-item"><a href="index.php?accion=peticiones">' . $pedidosPendientes . ' petición' . ($pedidosPendientes !== 1 ? 'es' : '') . ' pendiente' . ($pedidosPendientes !== 1 ? 's' : '') . ' de revisión</a></li>';
        }
        if ($propuestasEnEstudio > 0) {
            $avisos[] = '<li class="aviso-item"><a href="index.php?accion=wishlist">' . $propuestasEnEstudio . ' propuesta' . ($propuestasEnEstudio !== 1 ? 's' : '') . ' en estudio</a></li>';
        }
        if ($totalAlertas > 0) {
            $avisos[] = '<li class="aviso-item aviso-alerta"><span class="aviso-icono" aria-hidden="true">⚠</span> ' . $totalAlertas . ' producto' . ($totalAlertas !== 1 ? 's' : '') . ' con stock bajo umbral (ver abajo)</li>';
        }
        $bloqueAvisosDashboard = empty($avisos)
            ? '<p class="color-gris admin-avisos-vacio">Nada pendiente de tu atención.</p>'
            : '<ul class="admin-avisos-lista">' . implode('', $avisos) . '</ul>';

        $html = cargarPlantilla('html/admin.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rolUsuario),
            'TOTAL_PRODUCTOS' => (string) $totalProductos,
            'TOTAL_PEDIDOS' => (string) $totalPedidos,
            'TOTAL_ALERTAS' => (string) $totalAlertas,
            'DATOS_GRAFICOS' => $datosGraficos,
            'BLOQUE_AVISOS_DASHBOARD' => $bloqueAvisosDashboard,
            'BLOQUE_ALERTAS_ADMIN' => $bloqueAlertasAdmin,
            'BLOQUE_INFORMES_PDF' => $bloqueInformesPdf,
            'BLOQUE_GESTION_PRODUCTOS' => $bloqueGestionProductos,
            'BLOQUE_GESTION_USUARIOS' => $bloqueGestionUsuarios,
            'ENLACE_GESTION_USUARIOS' => $enlaceGestionUsuarios,
            'ENLACE_MI_CUENTA' => $enlaceMiCuenta,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
