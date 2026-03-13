<?php
/**
 * Controlador Api — Respuestas JSON para AJAX (catálogo, wishlist, votos).
 * Sin HTML; solo devuelve datos para el frontend.
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class Api
{
    private BaseDeDatos $bd;

    public function __construct()
    {
        $this->bd = new BaseDeDatos();
    }

    /**
     * Despacha según recurso GET y devuelve JSON.
     * Requiere sesión para la mayoría de recursos.
     */
    public function ejecutar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $recurso = $_GET['recurso'] ?? '';
        $logueado = !empty($_SESSION['usuario_id']);

        // Recursos que no exigen sesión (p. ej. categorías para filtros en login podría no usarse; aquí todo exige sesión)
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
            case 'propuestas':
                $this->devolverPropuestas();
                break;
            case 'votar':
                $this->procesarVoto();
                break;
            case 'crear_pedido':
                $this->procesarCrearPedido();
                break;
            case 'cambiar_estado_pedido':
                $this->procesarCambiarEstadoPedido();
                break;
            case 'crear_propuesta':
                $this->procesarCrearPropuesta();
                break;
            case 'producto_crear':
                $this->procesarProductoCrear();
                break;
            case 'producto_actualizar':
                $this->procesarProductoActualizar();
                break;
            case 'producto_eliminar':
                $this->procesarProductoEliminar();
                break;
            case 'pdf_inventario':
                $this->generarPdfInventario();
                break;
            case 'pdf_pedidos':
                $this->generarPdfPedidos();
                break;
            case 'usuarios':
                $this->devolverUsuarios();
                break;
            case 'usuario_actualizar_rol':
                $this->procesarUsuarioActualizarRol();
                break;
            case 'usuario_actualizar_activo':
                $this->procesarUsuarioActualizarActivo();
                break;
            case 'usuario_eliminar':
                $this->procesarUsuarioEliminar();
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

    private function esAdministrador(): bool
    {
        return ($_SESSION['usuario_rol'] ?? '') === ROL_ADMINISTRADOR;
    }

    /**
     * Genera informe de inventario en HTML con aspecto de PDF corporativo. Solo administrador.
     * No depende de DomPDF: el navegador puede imprimir o guardar como PDF.
     */
    private function generarPdfInventario(): void
    {
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede exportar informes', 'codigo' => 403], 403);
            return;
        }
        $bd = new BaseDeDatos();
        $productos = $bd->obtenerProductos();
        $categorias = $bd->obtenerCategorias();
        // En informes usamos un logotipo existente del proyecto.
        // Si en el futuro añades una versión específica para fondo blanco, aquí podemos cambiar la ruta.
        $logoUrl = ASSET_LOGOTIPO_OPACO;
        $filas = '';
        $totalInventario = 0.0;
        foreach ($productos as $p) {
            $nombreCat = '';
            foreach ($categorias as $c) {
                if ((int)($c['id'] ?? 0) === (int)($p['categoria_id'] ?? 0)) {
                    $nombreCat = $c['nombre'] ?? '';
                    break;
                }
            }
            $idProd = (int)($p['id'] ?? 0);
            $stock = (int)($p['stock'] ?? 0);
            $precio = $bd->obtenerPrecioUnitarioSimulado($idProd);
            $valor = $stock * $precio;
            $totalInventario += $valor;
            $filas .= '<tr><td>' . $idProd . '</td><td>' . htmlspecialchars($p['nombre'] ?? '') . '</td><td>' . htmlspecialchars($nombreCat) . '</td><td style="text-align:right;">' . $stock . '</td><td style="text-align:right;">' . number_format($precio, 2, ',', '.') . ' €</td><td style="text-align:right;">' . number_format($valor, 2, ',', '.') . ' €</td></tr>';
        }
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Inventario s6s</title>'
            . '<link rel="icon" type="image/png" href="' . htmlspecialchars(ASSET_FAVICON, ENT_QUOTES, 'UTF-8') . '">'
            . '<style>
            body{font-family:sans-serif;font-size:12px;color:#222;}
            .header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #00A3FF;padding-bottom:8px;margin-bottom:16px;}
            .header-logo img{max-height:40px;}
            .header-title h1{margin:0;font-size:18px;}
            .header-title p{margin:2px 0 0;font-size:11px;color:#555;}
            table{border-collapse:collapse;width:100%;margin-top:8px;}
            th,td{border:1px solid #ccc;padding:6px 8px;}
            th{background:#f5f7fa;}
            .resumen{margin-top:14px;font-weight:bold;}
        </style></head><body>';
        $html .= '<div class="header"><div class="header-title"><h1>Inventario s6s</h1><p>Generado el ' . date('d/m/Y H:i') . '</p></div><div class="header-logo"><img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="s6s"></div></div>';
        $html .= '<table><thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Precio unitario</th><th>Valor total</th></tr></thead><tbody>' . $filas . '</tbody></table>';
        $html .= '<p class="resumen">Valor económico total del inventario: ' . number_format($totalInventario, 2, ',', '.') . ' €</p>';
        $html .= '</body></html>';
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /**
     * Genera informe de pedidos en HTML con aspecto de informe corporativo. Solo administrador.
     * No depende de DomPDF: el navegador puede imprimir o guardar como PDF.
     */
    private function generarPdfPedidos(): void
    {
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede exportar informes', 'codigo' => 403], 403);
            return;
        }
        $bd = new BaseDeDatos();
        $pedidos = $bd->obtenerPedidos();
        $productos = $bd->obtenerProductos();
        $usuarios = $bd->obtenerUsuarios();
        $nombresProd = [];
        $nombresUser = [];
        $usuariosPorId = [];
        foreach ($productos as $p) {
            $nombresProd[(int)($p['id'] ?? 0)] = $p['nombre'] ?? '';
        }
        foreach ($usuarios as $u) {
            $uid = (int)($u['id'] ?? 0);
            $usuariosPorId[$uid] = $u;
            $nombresUser[$uid] = $u['nombre'] ?? $u['email'] ?? '';
        }
        // En informes usamos un logotipo existente del proyecto (igual que en login/registro).
        $logoUrl = ASSET_LOGOTIPO_OPACO;
        $filas = '';
        $consumoMensual = [];
        foreach ($pedidos as $p) {
            $idPedido = (int)($p['id'] ?? 0);
            $usuarioId = (int)($p['usuario_id'] ?? 0);
            $productoId = (int)($p['producto_id'] ?? 0);
            $unidades = (int)($p['unidades'] ?? 0);
            $estado = $p['estado'] ?? '';
            $fecha = $p['fecha_creacion'] ?? '';
            $filas .= '<tr><td>' . $idPedido . '</td><td>' . htmlspecialchars($nombresUser[$usuarioId] ?? '') . '</td><td>' . htmlspecialchars($nombresProd[$productoId] ?? '') . '</td><td style="text-align:right;">' . $unidades . '</td><td>' . htmlspecialchars($estado) . '</td><td>' . htmlspecialchars($fecha) . '</td></tr>';

            // Resumen de consumo mensual por departamento (simulado)
            if ($fecha !== '' && $unidades > 0) {
                $mes = substr((string)$fecha, 0, 7); // YYYY-MM
                $usuario = $usuariosPorId[$usuarioId] ?? [];
                $departamento = $this->resolverDepartamentoSimulado($usuario);
                $precio = $bd->obtenerPrecioUnitarioSimulado($productoId);
                $valor = $precio * $unidades;
                if (!isset($consumoMensual[$mes])) {
                    $consumoMensual[$mes] = [];
                }
                if (!isset($consumoMensual[$mes][$departamento])) {
                    $consumoMensual[$mes][$departamento] = 0.0;
                }
                $consumoMensual[$mes][$departamento] += $valor;
            }
        }
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Pedidos s6s</title>'
            . '<link rel="icon" type="image/png" href="' . htmlspecialchars(ASSET_FAVICON, ENT_QUOTES, 'UTF-8') . '">'
            . '<style>
            body{font-family:sans-serif;font-size:12px;color:#222;}
            .header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #00A3FF;padding-bottom:8px;margin-bottom:16px;}
            .header-logo img{max-height:40px;}
            .header-title h1{margin:0;font-size:18px;}
            .header-title p{margin:2px 0 0;font-size:11px;color:#555;}
            table{border-collapse:collapse;width:100%;margin-top:8px;}
            th,td{border:1px solid #ccc;padding:6px 8px;}
            th{background:#f5f7fa;}
            .resumen-titulo{margin-top:18px;font-size:14px;font-weight:bold;}
        </style></head><body>';
        $html .= '<div class="header"><div class="header-title"><h1>Pedidos s6s</h1><p>Generado el ' . date('d/m/Y H:i') . '</p></div><div class="header-logo"><img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="s6s"></div></div>';
        $html .= '<table><thead><tr><th>ID</th><th>Solicitante</th><th>Producto</th><th>Unidades</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>' . $filas . '</tbody></table>';

        // Bloque de resumen de consumo mensual por departamento
        if (!empty($consumoMensual)) {
            $html .= '<p class="resumen-titulo">Resumen de consumo mensual por departamento</p>';
            $html .= '<table><thead><tr><th>Mes</th><th>Departamento</th><th>Importe total</th></tr></thead><tbody>';
            ksort($consumoMensual);
            foreach ($consumoMensual as $mes => $departamentos) {
                ksort($departamentos);
                foreach ($departamentos as $dep => $importe) {
                    $html .= '<tr><td>' . htmlspecialchars($mes) . '</td><td>' . htmlspecialchars($dep) . '</td><td style="text-align:right;">' . number_format($importe, 2, ',', '.') . ' €</td></tr>';
                }
            }
            $html .= '</tbody></table>';
        }

        $html .= '</body></html>';
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /**
     * Departamento simulado a partir de datos de usuario (para informes).
     */
    private function resolverDepartamentoSimulado(array $usuario): string
    {
        $email = (string)($usuario['email'] ?? '');
        $rol = (string)($usuario['rol'] ?? '');
        if ($rol === ROL_ADMINISTRADOR) {
            return 'Dirección';
        }
        if ($rol === ROL_STAFF) {
            return 'Almacén / Logística';
        }
        if (stripos($email, 'it') !== false) {
            return 'IT';
        }
        if (stripos($email, 'fin') !== false || stripos($email, 'conta') !== false) {
            return 'Finanzas';
        }
        return 'General';
    }

    private function responderJson(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    }

    /** Catálogo: categorías para los filtros. */
    private function devolverCategorias(): void
    {
        $lista = $this->bd->obtenerCategorias();
        $this->responderJson(['categorias' => $lista]);
    }

    /** Catálogo: productos (todos o filtrados por categoría). Solo activos por defecto; si admin y todos=1, devuelve todos. Incluye stock_disponible. */
    private function devolverProductos(): void
    {
        $categoria = $_GET['categoria'] ?? '';
        $todos = isset($_GET['todos']) && (string) $_GET['todos'] === '1';
        if ($categoria !== '') {
            $productos = $this->bd->obtenerProductosPorCategoria($categoria);
        } else {
            $productos = $this->bd->obtenerProductos();
        }
        if (!$todos || !$this->esAdministrador()) {
            $productos = array_values(array_filter($productos, fn($p) => ($p['activo'] ?? true) !== false));
        }
        foreach ($productos as &$p) {
            $id = (int) ($p['id'] ?? 0);
            $p['stock_disponible'] = $id > 0 ? $this->bd->obtenerStockDisponible($id) : (int) ($p['stock'] ?? 0);
        }
        unset($p);
        $this->responderJson(['productos' => $productos]);
    }

    /** Alertas: productos con stock bajo umbral crítico. */
    private function devolverAlertasStock(): void
    {
        $lista = $this->bd->obtenerProductosBajoUmbral();
        $this->responderJson(['alertas' => $lista]);
    }

    /** Wishlist: propuestas ordenadas por votos (ranking). Incluye ya_votado y nombre del autor. */
    private function devolverPropuestas(): void
    {
        $propuestas = $this->bd->obtenerPropuestasOrdenadasPorVotos();
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $usuarios = [];
        foreach ($this->bd->obtenerUsuarios() as $u) {
            $usuarios[(int)($u['id'] ?? 0)] = $u['nombre'] ?? $u['email'] ?? '';
        }
        foreach ($propuestas as &$p) {
            $p['ya_votado'] = $this->bd->usuarioYaVoto((int)($p['id'] ?? 0), $usuarioId);
            $p['autor_nombre'] = $usuarios[(int)($p['usuario_id'] ?? 0)] ?? '';
        }
        unset($p);
        $this->responderJson(['propuestas' => $propuestas]);
    }

    /** Wishlist: registrar voto (POST). Evita doble voto. */
    private function procesarVoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        $input = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $propuestaId = (int) ($input['propuesta_id'] ?? $_POST['propuesta_id'] ?? 0);
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($propuestaId < 1 || $usuarioId < 1) {
            $this->responderJson(['error' => 'Datos insuficientes', 'voto' => false], 400);
            return;
        }
        if ($this->bd->usuarioYaVoto($propuestaId, $usuarioId)) {
            $this->responderJson(['mensaje' => 'Ya has votado esta propuesta', 'voto' => false]);
            return;
        }
        $ok = $this->bd->insertarVoto($propuestaId, $usuarioId);
        $this->responderJson(['voto' => $ok, 'mensaje' => $ok ? 'Voto registrado' : 'Error al registrar']);
    }

    /** Crea una solicitud (pedido). Comprueba stock disponible y bloquea. */
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

    /** Cambia el estado de un pedido (solo staff/admin). */
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
        $estadosValidos = [BaseDeDatos::ESTADO_EN_REVISION, BaseDeDatos::ESTADO_APROBADO, BaseDeDatos::ESTADO_DENEGADO, BaseDeDatos::ESTADO_ENTREGADO];
        // Permitir también "en_revision" para que staff pase pendientes a revisión
        if ($pedidoId < 1 || !in_array($nuevoEstado, $estadosValidos, true)) {
            $this->responderJson(['error' => 'Datos inválidos', 'actualizado' => false], 400);
            return;
        }
        $ok = $this->bd->actualizarPedido($pedidoId, ['estado' => $nuevoEstado]);
        $this->responderJson(['actualizado' => $ok, 'mensaje' => $ok ? 'Estado actualizado' : 'Error']);
    }

    /** Crea una propuesta en la wishlist. */
    private function procesarCrearPropuesta(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $titulo = trim((string) ($input['titulo'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($titulo === '' || $usuarioId < 1) {
            $this->responderJson(['error' => 'El título es obligatorio', 'creado' => false], 400);
            return;
        }
        $id = $this->bd->insertarPropuesta([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'usuario_id' => $usuarioId,
        ]);
        $this->responderJson(['creado' => true, 'propuesta_id' => $id, 'mensaje' => 'Propuesta creada']);
    }

    /** Crea un producto (solo administrador). Subida de imagen opcional. */
    private function procesarProductoCrear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar el catálogo de productos', 'codigo' => 403], 403);
            return;
        }
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $umbralCritico = (int) ($_POST['umbral_critico'] ?? 0);
        if ($nombre === '' || $categoriaId < 1) {
            $this->responderJson(['error' => 'Nombre y categoría obligatorios', 'creado' => false], 400);
            return;
        }
        $rutaImagen = $this->subirImagenProducto('imagen');
        $id = $this->bd->insertarProducto([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'categoria_id' => $categoriaId,
            'stock' => max(0, $stock),
            'umbral_critico' => max(0, $umbralCritico),
            'imagen' => $rutaImagen,
            'activo' => true,
        ]);
        $this->responderJson(['creado' => true, 'producto_id' => $id, 'mensaje' => 'Producto creado']);
    }

    /** Actualiza un producto (solo administrador). */
    private function procesarProductoActualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar el catálogo de productos', 'codigo' => 403], 403);
            return;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            $this->responderJson(['error' => 'ID inválido', 'actualizado' => false], 400);
            return;
        }
        $producto = $this->bd->obtenerProductoPorId($id);
        if (!$producto) {
            $this->responderJson(['error' => 'Producto no encontrado', 'actualizado' => false], 404);
            return;
        }
        $nombre = trim((string) ($_POST['nombre'] ?? $producto['nombre'] ?? ''));
        if ($nombre === '') {
            $this->responderJson(['error' => 'Nombre obligatorio', 'actualizado' => false], 400);
            return;
        }
        $rutaImagen = $this->subirImagenProducto('imagen');
        $datos = [
            'nombre' => $nombre,
            'descripcion' => trim((string) ($_POST['descripcion'] ?? $producto['descripcion'] ?? '')),
            'categoria_id' => (int) ($_POST['categoria_id'] ?? $producto['categoria_id'] ?? 0),
            'stock' => max(0, (int) ($_POST['stock'] ?? $producto['stock'] ?? 0)),
            'umbral_critico' => max(0, (int) ($_POST['umbral_critico'] ?? $producto['umbral_critico'] ?? 0)),
        ];
        if ($rutaImagen !== '') {
            $datos['imagen'] = $rutaImagen;
        }
        $ok = $this->bd->actualizarProducto($id, $datos);
        $this->responderJson(['actualizado' => $ok, 'mensaje' => $ok ? 'Producto actualizado' : 'Error']);
    }

    /** Elimina (desactiva) un producto (solo administrador). */
    private function procesarProductoEliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['error' => 'Método no permitido'], 405);
            return;
        }
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar el catálogo de productos', 'codigo' => 403], 403);
            return;
        }
        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $id = (int) ($input['id'] ?? $_POST['id'] ?? 0);
        if ($id < 1) {
            $this->responderJson(['error' => 'ID inválido', 'eliminado' => false], 400);
            return;
        }
        $ok = $this->bd->eliminarProducto($id);
        $this->responderJson(['eliminado' => $ok, 'mensaje' => $ok ? 'Producto desactivado' : 'Error']);
    }

    /**
     * Sube imagen de producto a imagenes/productos/.
     * Optimización: redimensiona a ancho máximo 800px y guarda como JPEG calidad 85 para reducir peso.
     * Si GD no está disponible, guarda el archivo original. Devuelve ruta relativa o vacío.
     */
    private function subirImagenProducto(string $nombreCampo): string
    {
        if (empty($_FILES[$nombreCampo]['tmp_name']) || !is_uploaded_file($_FILES[$nombreCampo]['tmp_name'])) {
            return '';
        }
        $dir = RUTA_RAIZ . DIRECTORY_SEPARATOR . 'imagenes' . DIRECTORY_SEPARATOR . 'productos';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES[$nombreCampo]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return '';
        }
        $tmp = $_FILES[$nombreCampo]['tmp_name'];

        // Intentar optimizar con GD: redimensionar y comprimir
        if (extension_loaded('gd')) {
            $anchoMax = 800;
            $calidad = 85;
            $origen = match ($ext) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($tmp),
                'png' => @imagecreatefrompng($tmp),
                'gif' => @imagecreatefromgif($tmp),
                'webp' => @imagecreatefromwebp($tmp),
                default => null,
            };
            if ($origen !== false && $origen !== null) {
                $anchoOrig = imagesx($origen);
                $altoOrig = imagesy($origen);
                if ($anchoOrig > $anchoMax || $altoOrig > $anchoMax) {
                    $ratio = min($anchoMax / $anchoOrig, $anchoMax / $altoOrig);
                    $anchoNuevo = (int) round($anchoOrig * $ratio);
                    $altoNuevo = (int) round($altoOrig * $ratio);
                    $destino = imagecreatetruecolor($anchoNuevo, $altoNuevo);
                    if ($destino && imagecopyresampled($destino, $origen, 0, 0, 0, 0, $anchoNuevo, $altoNuevo, $anchoOrig, $altoOrig)) {
                        $nombre = 'prod_' . uniqid() . '.jpg';
                        $ruta = $dir . DIRECTORY_SEPARATOR . $nombre;
                        if (imagejpeg($destino, $ruta, $calidad)) {
                            imagedestroy($origen);
                            imagedestroy($destino);
                            return 'imagenes/productos/' . $nombre;
                        }
                        imagedestroy($destino);
                    }
                } else {
                    $nombre = 'prod_' . uniqid() . '.jpg';
                    $ruta = $dir . DIRECTORY_SEPARATOR . $nombre;
                    if (imagejpeg($origen, $ruta, $calidad)) {
                        imagedestroy($origen);
                        return 'imagenes/productos/' . $nombre;
                    }
                }
                if ($origen) {
                    imagedestroy($origen);
                }
            }
        }

        // Sin GD o fallo: guardar original
        $nombre = 'prod_' . uniqid() . '.' . $ext;
        $ruta = $dir . DIRECTORY_SEPARATOR . $nombre;
        if (!move_uploaded_file($tmp, $ruta)) {
            return '';
        }
        return 'imagenes/productos/' . $nombre;
    }

    // -------------------------------------------------------------------------
    // Gestión de usuarios (solo administrador)
    // -------------------------------------------------------------------------

    /** Lista usuarios sin contraseña. Solo administrador. */
    private function devolverUsuarios(): void
    {
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar usuarios', 'codigo' => 403], 403);
            return;
        }
        $usuarios = $this->bd->obtenerUsuarios();
        $lista = [];
        foreach ($usuarios as $u) {
            $lista[] = [
                'id' => (int) $u['id'],
                'email' => $u['email'] ?? '',
                'nombre' => $u['nombre'] ?? '',
                'rol' => $u['rol'] ?? ROL_EMPLEADO,
                'activo' => (bool) ($u['activo'] ?? true),
            ];
        }
        $this->responderJson(['usuarios' => $lista]);
    }

    /** Actualiza el rol de un usuario. Solo administrador. */
    private function procesarUsuarioActualizarRol(): void
    {
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar usuarios', 'codigo' => 403], 403);
            return;
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $rol = trim((string) ($_POST['rol'] ?? ''));
        $rolesValidos = [ROL_EMPLEADO, ROL_STAFF, ROL_ADMINISTRADOR];
        if ($id < 1 || !in_array($rol, $rolesValidos, true)) {
            $this->responderJson(['error' => 'Datos inválidos', 'codigo' => 400], 400);
            return;
        }
        $this->bd->actualizarUsuario($id, ['rol' => $rol]);
        $this->responderJson(['ok' => true]);
    }

    /** Activa o desactiva un usuario. Solo administrador. */
    private function procesarUsuarioActualizarActivo(): void
    {
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar usuarios', 'codigo' => 403], 403);
            return;
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $activo = isset($_POST['activo']) ? (bool) $_POST['activo'] : false;
        if ($id < 1) {
            $this->responderJson(['error' => 'Datos inválidos', 'codigo' => 400], 400);
            return;
        }
        $usuarioActual = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($id === $usuarioActual) {
            $this->responderJson(['error' => 'No puedes desactivar tu propia cuenta', 'codigo' => 400], 400);
            return;
        }
        $this->bd->actualizarUsuario($id, ['activo' => $activo]);
        $this->responderJson(['ok' => true]);
    }

    /** Elimina un usuario. Solo administrador. No puede eliminarse a sí mismo. */
    private function procesarUsuarioEliminar(): void
    {
        if (!$this->esAdministrador()) {
            $this->responderJson(['error' => 'Solo el administrador puede gestionar usuarios', 'codigo' => 403], 403);
            return;
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id < 1) {
            $this->responderJson(['error' => 'Datos inválidos', 'codigo' => 400], 400);
            return;
        }
        $usuarioActual = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($id === $usuarioActual) {
            $this->responderJson(['error' => 'No puedes eliminar tu propia cuenta', 'codigo' => 400], 400);
            return;
        }
        $this->bd->eliminarUsuario($id);
        $this->responderJson(['ok' => true]);
    }
}
