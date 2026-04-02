<?php
// Listado de solicitudes del usuario y, para staff/admin, colas de revisión y entrega.
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
        $aprobados = [];
        $pendientesPendientes = [];
        if ($rol === ROL_ADMINISTRADOR || $rol === ROL_STAFF) {
            $pendientesRevision = $bd->obtenerPedidosPorEstado(BaseDeDatos::ESTADO_EN_REVISION);
            $pendientesPendientes = $bd->obtenerPedidosPorEstado(BaseDeDatos::ESTADO_PENDIENTE);
            $aprobados = $bd->obtenerPedidosPorEstado(BaseDeDatos::ESTADO_APROBADO);
        }

        $nombresProductos = [];
        foreach ($bd->obtenerProductos() as $prod) {
            $nombresProductos[(int) ($prod['id'] ?? 0)] = $prod['nombre'] ?? 'ID ' . ($prod['id'] ?? '');
        }
        $nombresUsuarios = [];
        foreach ($bd->obtenerUsuarios() as $u) {
            $nombresUsuarios[(int) ($u['id'] ?? 0)] = $u['nombre'] ?? $u['email'] ?? 'Usuario #' . ($u['id'] ?? '');
        }

        $prioridadLabels = ['alta' => 'Alta', 'normal' => 'Normal', 'baja' => 'Baja'];
        $prioridadClass = function ($p) {
            $pr = strtolower(trim((string) ($p['prioridad'] ?? 'normal')));
            if (!in_array($pr, ['alta', 'normal', 'baja'], true)) {
                $pr = 'normal';
            }
            return 'badge-prioridad badge-prioridad-' . $pr;
        };
        $prioridadHtml = function ($p) use ($prioridadLabels, $prioridadClass) {
            $pr = strtolower(trim((string) ($p['prioridad'] ?? 'normal')));
            $label = $prioridadLabels[$pr] ?? $prioridadLabels['normal'];
            return '<span class="' . $prioridadClass($p) . '">' . htmlspecialchars($label) . '</span>';
        };

        $bloqueStaffPendientes = '';
        if (!empty($pendientesPendientes)) {
            $items = '';
            foreach ($pendientesPendientes as $p) {
                $pid = (int) ($p['id'] ?? 0);
                $uid = (int) ($p['usuario_id'] ?? 0);
                $solicitante = $nombresUsuarios[$uid] ?? 'Usuario #' . $uid;
                $nombreProd = $nombresProductos[(int) ($p['producto_id'] ?? 0)] ?? 'Producto #' . (int) ($p['producto_id'] ?? 0);
                $estadoSlug = htmlspecialchars(str_replace('_', '-', $p['estado'] ?? ''));
                $motivo = trim((string) ($p['motivo'] ?? ''));
                $textoMotivo = $motivo !== '' ? ' · Motivo: ' . htmlspecialchars($motivo) : '';
                $items .= '<li class="item-peticion" data-pedido-id="' . $pid . '"><span class="peticion-solicitante">' . htmlspecialchars($solicitante) . '</span> · ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · ' . $prioridadHtml($p) . $textoMotivo . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($p['estado'] ?? '') . '</span><div class="peticion-acciones"><button type="button" class="boton boton-primario boton-estado" data-estado="en_revision">Pasar a revisión</button></div></li>';
            }
            $bloqueStaffPendientes = '<section class="bloque-peticiones" aria-labelledby="titulo-pendientes"><h2 id="titulo-pendientes">Pendientes (pasar a revisión)</h2><ul class="lista-peticiones" id="lista-pendientes">' . $items . '</ul></section>';
        }

        $bloqueStaffRevision = '';
        if (!empty($pendientesRevision)) {
            $items = '';
            foreach ($pendientesRevision as $p) {
                $pid = (int) ($p['id'] ?? 0);
                $uid = (int) ($p['usuario_id'] ?? 0);
                $solicitante = $nombresUsuarios[$uid] ?? 'Usuario #' . $uid;
                $nombreProd = $nombresProductos[(int) ($p['producto_id'] ?? 0)] ?? 'Producto #' . (int) ($p['producto_id'] ?? 0);
                $estadoSlug = htmlspecialchars(str_replace('_', '-', $p['estado'] ?? ''));
                $motivo = trim((string) ($p['motivo'] ?? ''));
                $textoMotivo = $motivo !== '' ? ' · Motivo: ' . htmlspecialchars($motivo) : '';
                $items .= '<li class="item-peticion" data-pedido-id="' . $pid . '"><span class="peticion-solicitante">' . htmlspecialchars($solicitante) . '</span> · ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · ' . $prioridadHtml($p) . $textoMotivo . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($p['estado'] ?? '') . '</span><div class="peticion-acciones"><button type="button" class="boton boton-primario boton-estado boton-aprobar" data-estado="aprobado">Aprobar</button><button type="button" class="boton boton-secundario boton-estado boton-denegar" data-estado="denegado">Denegar</button></div></li>';
            }
            $bloqueStaffRevision = '<section class="bloque-peticiones" aria-labelledby="titulo-revision"><h2 id="titulo-revision">En revisión (staff)</h2><ul class="lista-peticiones" id="lista-revision">' . $items . '</ul></section>';
        }

        $bloqueStaffAprobados = '';
        if (!empty($aprobados)) {
            $items = '';
            foreach ($aprobados as $p) {
                $pid = (int) ($p['id'] ?? 0);
                $uid = (int) ($p['usuario_id'] ?? 0);
                $solicitante = $nombresUsuarios[$uid] ?? 'Usuario #' . $uid;
                $nombreProd = $nombresProductos[(int) ($p['producto_id'] ?? 0)] ?? 'Producto #' . (int) ($p['producto_id'] ?? 0);
                $estadoSlug = htmlspecialchars(str_replace('_', '-', $p['estado'] ?? ''));
                $motivo = trim((string) ($p['motivo'] ?? ''));
                $textoMotivo = $motivo !== '' ? ' · Motivo: ' . htmlspecialchars($motivo) : '';
                $items .= '<li class="item-peticion" data-pedido-id="' . $pid . '"><span class="peticion-solicitante">' . htmlspecialchars($solicitante) . '</span> · ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · ' . $prioridadHtml($p) . $textoMotivo . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($p['estado'] ?? '') . '</span><div class="peticion-acciones"><button type="button" class="boton boton-estado boton-entregado" data-estado="entregado">Marcar entregado</button></div></li>';
            }
            $bloqueStaffAprobados = '<section class="bloque-peticiones" aria-labelledby="titulo-aprobados"><h2 id="titulo-aprobados">Aprobados (marcar entregado)</h2><ul class="lista-peticiones" id="lista-aprobados">' . $items . '</ul></section>';
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
                $items .= '<li class="item-peticion" data-estado="' . htmlspecialchars($estadoRaw) . '" data-pedido-id="' . (int) ($p['id'] ?? 0) . '"><span class="peticion-solicitante">Tú</span> · ' . htmlspecialchars($nombreProd) . ' · Unidades: ' . (int) ($p['unidades'] ?? 0) . ' · ' . $prioridadHtml($p) . ' · Motivo: ' . htmlspecialchars($p['motivo'] ?? '') . ' · <span class="estado badge badge-' . $estadoSlug . '">' . htmlspecialchars($estadoRaw) . '</span> · ' . htmlspecialchars($p['fecha_creacion'] ?? '') . '</li>';
            }
            $listaMisPeticiones = '<ul class="lista-peticiones">' . $items . '</ul>';
        }

        $puedeAdmin = ($rol === ROL_ADMINISTRADOR || $rol === ROL_STAFF);
        $enlaceAdmin = $puedeAdmin ? '<li><a href="index.php?accion=admin">Administración</a></li>' : '';
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $html = cargarPlantilla('html/peticiones.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ACCION_DASHBOARD' => ACCION_DASHBOARD,
            'ENLACE_ADMIN' => $enlaceAdmin,
            'NOMBRE_USUARIO' => htmlspecialchars($nombreUsuario),
            'ROL_USUARIO' => htmlspecialchars($rol),
            'BLOQUE_STAFF_PENDIENTES' => $bloqueStaffPendientes,
            'BLOQUE_STAFF_REVISION' => $bloqueStaffRevision,
            'BLOQUE_STAFF_APROBADOS' => $bloqueStaffAprobados,
            'LISTA_MIS_PETICIONES' => $listaMisPeticiones,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }
}
