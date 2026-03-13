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

        $esAdministrador = ($rol === ROL_ADMINISTRADOR);
        $bloqueInformesPdf = $esAdministrador
            ? '<section class="admin-informes" aria-labelledby="titulo-informes"><h2 id="titulo-informes">Informes</h2><p class="color-gris">Informes de inventario y pedidos listos para imprimir o guardar como PDF desde el navegador.</p><div class="admin-informes-botones"><a href="index.php?accion=api&amp;recurso=pdf_inventario" class="boton boton-primario" target="_blank" rel="noopener">Inventario (informe)</a><a href="index.php?accion=api&amp;recurso=pdf_pedidos" class="boton boton-primario" target="_blank" rel="noopener">Pedidos (informe)</a></div></section>'
            : '<section class="admin-informes" aria-labelledby="titulo-informes"><h2 id="titulo-informes">Informes</h2><p class="color-gris">Solo el administrador puede ver y generar los informes de inventario y pedidos.</p></section>';
        $bloqueGestionProductos = $esAdministrador
            ? '<section class="admin-productos" aria-labelledby="titulo-productos"><h2 id="titulo-productos">Gestión de productos</h2><button type="button" class="boton boton-primario" id="btn-abrir-form-producto">Añadir producto</button><div class="form-producto-wrapper" id="form-producto-wrapper" hidden><form id="form-producto" enctype="multipart/form-data"><input type="hidden" name="id" id="producto-id" value=""><label for="producto-nombre">Nombre</label><input type="text" id="producto-nombre" name="nombre" required maxlength="200"><label for="producto-descripcion">Descripción</label><input type="text" id="producto-descripcion" name="descripcion"><label for="producto-categoria_id">Categoría</label><select id="producto-categoria_id" name="categoria_id" required><option value="1">IT</option><option value="2">Mobiliario</option><option value="3">Consumibles</option></select><label for="producto-stock">Stock</label><input type="number" id="producto-stock" name="stock" min="0" value="0"><label for="producto-umbral_critico">Umbral crítico</label><input type="number" id="producto-umbral_critico" name="umbral_critico" min="0" value="0"><label for="producto-imagen">Imagen (opcional)</label><input type="file" id="producto-imagen" name="imagen" accept="image/*"><div class="form-producto-botones"><button type="button" class="boton boton-secundario" id="btn-cerrar-form-producto">Cancelar</button><button type="submit" class="boton boton-primario">Guardar</button></div></form></div><div id="lista-productos-admin" class="lista-productos-admin"></div></section>'
            : '<section class="admin-productos" aria-labelledby="titulo-productos"><h2 id="titulo-productos">Gestión de productos</h2><p class="color-gris">Solo el administrador puede añadir, editar o desactivar productos del catálogo.</p></section>';

        $bloqueGestionUsuarios = $esAdministrador
            ? '<section class="admin-gestion-usuarios" aria-labelledby="titulo-gestion-usuarios"><h2 id="titulo-gestion-usuarios">Gestión de usuarios</h2><p class="color-gris">Cambiar rol (empleado, staff, administrador), activar o desactivar cuentas y eliminar usuarios.</p><p><a href="index.php?accion=admin_usuarios" class="boton boton-primario">Ir a Gestión de usuarios</a></p></section>'
            : '';
        $enlaceGestionUsuarios = $esAdministrador ? '<li><a href="index.php?accion=admin_usuarios">Gestión de usuarios</a></li>' : '';

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
            'BLOQUE_INFORMES_PDF' => $bloqueInformesPdf,
            'BLOQUE_GESTION_PRODUCTOS' => $bloqueGestionProductos,
            'BLOQUE_GESTION_USUARIOS' => $bloqueGestionUsuarios,
            'ENLACE_GESTION_USUARIOS' => $enlaceGestionUsuarios,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
