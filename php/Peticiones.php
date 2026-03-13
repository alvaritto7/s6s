<?php
/**
 * Controlador Peticiones — Flujo de trabajo (Pendiente → En Revisión → Aprobado/Denegado → Entregado).
 * Muestra las solicitudes del usuario y, según rol, las pendientes de revisión.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class Peticiones
{
    public function ejecutar(): void
    {
        $bd = new BaseDeDatos();
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $rol = $_SESSION['usuario_rol'] ?? ROL_EMPLEADO;
        $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

        $misPeticiones = $bd->obtenerPedidosPorUsuario($usuarioId);
        $pendientesRevision = [];
        $pendientesPendientes = [];
        if ($rol === ROL_ADMINISTRADOR || $rol === ROL_STAFF) {
            $pendientesRevision = $bd->obtenerPedidosPorEstado(BaseDeDatos::ESTADO_EN_REVISION);
            $pendientesPendientes = $bd->obtenerPedidosPorEstado(BaseDeDatos::ESTADO_PENDIENTE);
        }

        $nombresProductos = [];
        foreach ($bd->obtenerProductos() as $prod) {
            $nombresProductos[(int) ($prod['id'] ?? 0)] = $prod['nombre'] ?? 'ID ' . ($prod['id'] ?? '');
        }

        $bloqueStaffPendientes = '';
        if (!empty($pendientesPendientes)) {
            $items = '';
            foreach ($pendientesPendientes as $p) {
                $pid = (int) ($p['id'] ?? 0);
                $nombreProd = $nombresProductos[(int) ($p['producto_id'] ?? 0)] ?? 'Producto #' . (int) ($p['producto_id'] ?? 0);
                $estadoSlug = htmlspecialchars(str_replace('_', '-', $p['estado'] ?? ''));
                $items .= '<li class="item-peticion" data-pedido-id="' . $pid . '"><strong>#' . $pid . '</strong> — ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($p['estado'] ?? '') . '</span><div class="peticion-acciones"><button type="button" class="boton boton-primario boton-estado" data-estado="en_revision">Pasar a revisión</button></div></li>';
            }
            $bloqueStaffPendientes = '<section class="bloque-peticiones" aria-labelledby="titulo-pendientes"><h2 id="titulo-pendientes">Pendientes (pasar a revisión)</h2><ul class="lista-peticiones" id="lista-pendientes">' . $items . '</ul></section>';
        }

        $bloqueStaffRevision = '';
        if (!empty($pendientesRevision)) {
            $items = '';
            foreach ($pendientesRevision as $p) {
                $pid = (int) ($p['id'] ?? 0);
                $nombreProd = $nombresProductos[(int) ($p['producto_id'] ?? 0)] ?? 'Producto #' . (int) ($p['producto_id'] ?? 0);
                $estadoSlug = htmlspecialchars(str_replace('_', '-', $p['estado'] ?? ''));
                $items .= '<li class="item-peticion" data-pedido-id="' . $pid . '"><strong>#' . $pid . '</strong> — ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · Prioridad: ' . htmlspecialchars($p['prioridad'] ?? '') . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($p['estado'] ?? '') . '</span><div class="peticion-acciones"><button type="button" class="boton boton-primario boton-estado boton-aprobar" data-estado="aprobado">Aprobar</button><button type="button" class="boton boton-secundario boton-estado boton-denegar" data-estado="denegado">Denegar</button><button type="button" class="boton boton-estado boton-entregado" data-estado="entregado">Marcar entregado</button></div></li>';
            }
            $bloqueStaffRevision = '<section class="bloque-peticiones" aria-labelledby="titulo-revision"><h2 id="titulo-revision">En revisión (staff)</h2><ul class="lista-peticiones" id="lista-revision">' . $items . '</ul></section>';
        }

        $listaMisPeticiones = '';
        if (empty($misPeticiones)) {
            $listaMisPeticiones = '<p class="color-gris">No tienes ninguna solicitud.</p>';
        } else {
            $items = '';
            foreach ($misPeticiones as $p) {
                $estadoRaw = $p['estado'] ?? '';
                $estadoSlug = htmlspecialchars(str_replace('_', '-', $estadoRaw));
                $nombreProd = $nombresProductos[(int) ($p['producto_id'] ?? 0)] ?? 'Producto #' . (int) ($p['producto_id'] ?? 0);
                $items .= '<li class="item-peticion" data-estado="' . htmlspecialchars($estadoRaw) . '"><strong>#' . (int) ($p['id'] ?? 0) . '</strong> — ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · Motivo: ' . htmlspecialchars($p['motivo'] ?? '') . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($estadoRaw) . '</span> · ' . htmlspecialchars($p['fecha_creacion'] ?? '') . '</li>';
            }
            $listaMisPeticiones = '<ul class="lista-peticiones">' . $items . '</ul>';
        }

        $puedeAdmin = ($rol === ROL_ADMINISTRADOR || $rol === ROL_STAFF);
        $enlaceAdmin = $puedeAdmin ? '<li><a href="index.php?accion=admin">Administración</a></li>' : '';
        $enlaceGestionUsuarios = ($rol === ROL_ADMINISTRADOR) ? '<li><a href="index.php?accion=admin_usuarios">Gestión de usuarios</a></li>' : '';
        $enlaceMiCuenta = '<li><a href="index.php?accion=mi_cuenta">Mi cuenta</a></li>';
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $html = cargarPlantilla('html/peticiones.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'ENLACE_ADMIN' => $enlaceAdmin,
            'ENLACE_GESTION_USUARIOS' => $enlaceGestionUsuarios,
            'ENLACE_MI_CUENTA' => $enlaceMiCuenta,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rol),
            'BLOQUE_STAFF_PENDIENTES' => $bloqueStaffPendientes,
            'BLOQUE_STAFF_REVISION' => $bloqueStaffRevision,
            'LISTA_MIS_PETICIONES' => $listaMisPeticiones,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
