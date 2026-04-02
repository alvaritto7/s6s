<?php
// API JSON: catálogo y pedidos. El resto de recursos vive en otros scripts o rutas.
declare(strict_types=1);

class Api
{
    private BaseDeDatos $bd;

    public function __construct()
    {
        $this->bd = new BaseDeDatos();
    }

    public function ejecutar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $recurso = $_GET['recurso'] ?? '';
        $logueado = !empty($_SESSION['usuario_id']);

        if (!$logueado) {
            $this->responderJson(['error' => 'No autorizado', 'codigo' => 401], 401);
            return;
        }

        switch ($recurso) {
            case 'categorias':
                $this->devolverCategorias();
                break;
            case 'productos':
                $this->devolverProductos();
                break;
            case 'alertas_stock':
                $this->devolverAlertasStock();
                break;
            case 'crear_pedido':
                $this->procesarCrearPedido();
                break;
            case 'cambiar_estado_pedido':
                $this->procesarCambiarEstadoPedido();
                break;
            default:
                $this->responderJson(['error' => 'Recurso no válido', 'recurso' => $recurso], 400);
        }
    }

    private function esStaffOAdmin(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return $rol === ROL_ADMINISTRADOR || $rol === ROL_STAFF;
    }

    private function responderJson(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    }

    private function devolverCategorias(): void
    {
        $lista = $this->bd->obtenerCategorias();
        $this->responderJson(['categorias' => $lista]);
    }

    private function devolverProductos(): void
    {
        $categoria = $_GET['categoria'] ?? '';
        $todos = isset($_GET['todos']) && (string) $_GET['todos'] === '1';
        if ($categoria !== '') {
            $productos = $this->bd->obtenerProductosPorCategoria($categoria);
        } else {
            $productos = $this->bd->obtenerProductos();
        }
        if (!$todos || ($_SESSION['usuario_rol'] ?? '') !== ROL_ADMINISTRADOR) {
            $productos = array_values(array_filter($productos, fn($p) => ($p['activo'] ?? true) !== false));
        }
        foreach ($productos as &$p) {
            $id = (int) ($p['id'] ?? 0);
            $p['stock_disponible'] = $id > 0 ? $this->bd->obtenerStockDisponible($id) : (int) ($p['stock'] ?? 0);
        }
        unset($p);
        $this->responderJson(['productos' => $productos]);
    }

    private function devolverAlertasStock(): void
    {
        $lista = $this->bd->obtenerProductosBajoUmbral();
        $this->responderJson(['alertas' => $lista]);
    }

    private function procesarCrearPedido(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $productoId = (int) ($input['producto_id'] ?? 0);
        $unidades = (int) ($input['unidades'] ?? 0);
        $prioridad = trim((string) ($input['prioridad'] ?? 'normal'));
        $motivo = trim((string) ($input['motivo'] ?? ''));
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($productoId < 1 || $unidades < 1 || $usuarioId < 1) {
            $this->responderJson(['error' => 'Datos insuficientes', 'creado' => false], 400);
            return;
        }
        $disponible = $this->bd->obtenerStockDisponible($productoId);
        if ($unidades > $disponible) {
            $this->responderJson(['error' => 'Stock insuficiente. Disponible: ' . $disponible, 'creado' => false], 400);
            return;
        }
        $id = $this->bd->insertarPedido([
            'usuario_id' => $usuarioId,
            'producto_id' => $productoId,
            'unidades' => $unidades,
            'prioridad' => $prioridad ?: 'normal',
            'motivo' => $motivo,
            'estado' => BaseDeDatos::ESTADO_PENDIENTE,
        ]);
        $this->responderJson(['creado' => true, 'pedido_id' => $id, 'mensaje' => 'Solicitud registrada']);
    }

    private function procesarCambiarEstadoPedido(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        if (!$this->esStaffOAdmin()) {
            $this->responderJson(['error' => 'Sin permiso', 'codigo' => 403], 403);
            return;
        }
        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $pedidoId = (int) ($input['pedido_id'] ?? 0);
        $nuevoEstado = trim((string) ($input['nuevo_estado'] ?? ''));
        $estadosValidos = [
            BaseDeDatos::ESTADO_EN_REVISION,
            BaseDeDatos::ESTADO_APROBADO,
            BaseDeDatos::ESTADO_DENEGADO,
            BaseDeDatos::ESTADO_ENTREGADO,
        ];
        if ($pedidoId < 1 || !in_array($nuevoEstado, $estadosValidos, true)) {
            $this->responderJson(['error' => 'Datos inválidos', 'actualizado' => false], 400);
            return;
        }
        $ok = $this->bd->actualizarPedido($pedidoId, ['estado' => $nuevoEstado]);
        $this->responderJson(['actualizado' => $ok, 'mensaje' => $ok ? 'Estado actualizado' : 'Error']);
    }
}
