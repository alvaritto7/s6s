<?php
/**
 * BaseDeDatos.php — Motor de almacenamiento s6s (Intelligent Supply System)
 *
 * Almacenamiento en MySQL vía PDO. La base de datos y tablas se crean
 * automáticamente al usar la aplicación (login, registro, etc.); los datos
 * de prueba se insertan si las tablas están vacías.
 *
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 * Proyecto: TFC DAW - Universidad Europea de Madrid
 */

declare(strict_types=1);

class BaseDeDatos
{
    private PDO $pdo;

    /** Estados del flujo de trabajo (Workflow) según anteproyecto */
    public const ESTADO_PENDIENTE   = 'pendiente';
    public const ESTADO_EN_REVISION = 'en_revision';
    public const ESTADO_APROBADO    = 'aprobado';
    public const ESTADO_DENEGADO    = 'denegado';
    public const ESTADO_ENTREGADO   = 'entregado';

    public function __construct()
    {
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $dsnSinDb = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
        $this->pdo = new PDO($dsnSinDb, DB_USER, DB_PASS, $opciones);
        $nombreDb = preg_replace('/[^a-zA-Z0-9_]/', '', DB_NAME);
        $this->pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $nombreDb . '` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->pdo->exec('USE `' . $nombreDb . '`');
        $this->asegurarEsquemaYSeed();
    }

    /**
     * Crea las tablas si no existen e inserta datos de prueba si están vacías.
     */
    private function asegurarEsquemaYSeed(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `usuarios` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `email` varchar(255) NOT NULL,
                `password` varchar(255) NOT NULL,
                `nombre` varchar(255) NOT NULL DEFAULT '',
                `rol` enum('administrador','staff','empleado') NOT NULL DEFAULT 'empleado',
                `activo` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `categorias` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `nombre` varchar(100) NOT NULL,
                `slug` varchar(100) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `productos` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `nombre` varchar(200) NOT NULL,
                `descripcion` varchar(500) DEFAULT '',
                `categoria_id` int(11) unsigned NOT NULL,
                `stock` int(11) NOT NULL DEFAULT 0,
                `umbral_critico` int(11) NOT NULL DEFAULT 0,
                `imagen` varchar(255) DEFAULT '',
                `activo` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `categoria_id` (`categoria_id`),
                KEY `activo` (`activo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `pedidos` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `usuario_id` int(11) unsigned NOT NULL,
                `producto_id` int(11) unsigned NOT NULL,
                `unidades` int(11) NOT NULL DEFAULT 1,
                `prioridad` enum('baja','normal','alta') NOT NULL DEFAULT 'normal',
                `motivo` varchar(500) DEFAULT '',
                `estado` enum('pendiente','en_revision','aprobado','denegado','entregado') NOT NULL DEFAULT 'pendiente',
                `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `usuario_id` (`usuario_id`),
                KEY `producto_id` (`producto_id`),
                KEY `estado` (`estado`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `propuestas_wishlist` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `titulo` varchar(200) NOT NULL,
                `descripcion` text,
                `usuario_id` int(11) unsigned NOT NULL,
                `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `usuario_id` (`usuario_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `votos` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `propuesta_id` int(11) unsigned NOT NULL,
                `usuario_id` int(11) unsigned NOT NULL,
                `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `propuesta_usuario` (`propuesta_id`,`usuario_id`),
                KEY `propuesta_id` (`propuesta_id`),
                KEY `usuario_id` (`usuario_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        if ($this->queryOne('SELECT 1 FROM usuarios LIMIT 1') === null) {
            $this->pdo->exec("
                INSERT INTO usuarios (email, password, nombre, rol, activo) VALUES
                ('admin@s6s.local', 'admin', 'Administrador s6s', 'administrador', 1),
                ('staff@s6s.local', 'admin', 'Staff Almacén', 'staff', 1),
                ('empleado@s6s.local', 'password', 'Empleado Prueba', 'empleado', 1),
                ('a@uem.es', 'empleado', 'Alvaro Labrador', 'empleado', 1),
                ('maria@uem.es', 'password', 'María García', 'empleado', 1),
                ('pedro@uem.es', 'password', 'Pedro Sánchez', 'empleado', 1),
                ('laura@uem.es', 'password', 'Laura Martínez', 'empleado', 1)
            ");
        }
        if ($this->queryOne('SELECT 1 FROM categorias LIMIT 1') === null) {
            $this->pdo->exec("INSERT INTO categorias (id, nombre, slug) VALUES (1, 'IT', 'it'), (2, 'Mobiliario', 'mobiliario'), (3, 'Consumibles', 'consumibles')");
        }
        if ($this->queryOne('SELECT 1 FROM productos LIMIT 1') === null) {
            $this->pdo->exec("
                INSERT INTO productos (nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo) VALUES
                ('Portátil Dell', 'Portátil 15\", 8GB RAM', 1, 5, 2, '', 1),
                ('Monitor LG 24\"', 'Monitor Full HD', 1, 10, 3, '', 1),
                ('Teclado inalámbrico', 'Teclado QWERTY español', 1, 15, 5, '', 1),
                ('Ratón inalámbrico', 'Ratón ergonómico USB', 1, 25, 5, '', 1),
                ('Webcam HD', 'Cámara 1080p para videollamadas', 1, 8, 2, '', 1),
                ('Disco duro externo 1TB', 'USB 3.0, backup', 1, 6, 2, '', 1),
                ('Silla ergonómica', 'Silla oficina regulable', 2, 20, 5, '', 1),
                ('Escritorio', 'Mesa oficina 120cm', 2, 8, 2, '', 1),
                ('Armario archivador', 'Armario 2 puertas', 2, 4, 1, '', 1),
                ('Lámpara escritorio', 'LED regulable', 2, 12, 3, '', 1),
                ('Estantería 5 baldas', 'Metal blanco 180cm', 2, 3, 2, '', 1),
                ('Papel A4', 'Resma 500 hojas', 3, 50, 10, '', 1),
                ('Bolígrafos', 'Pack 10 unidades', 3, 3, 5, '', 1),
                ('Toner impresora', 'Toner láser negro', 3, 6, 2, '', 1),
                ('Carpeta archivador', 'Carpeta A4 con gomas', 3, 30, 8, '', 1),
                ('Bloc Pos-it', 'Pack 5 blocantes', 3, 18, 5, '', 1),
                ('Grapadora metálica', 'Grapadora oficina', 3, 2, 5, '', 1),
                ('Monitor CRT 17\"', 'Obsoleto, solo repuestos', 1, 0, 0, '', 0)
            ");
        }
        if ($this->queryOne('SELECT 1 FROM pedidos LIMIT 1') === null) {
            $this->pdo->exec("
                INSERT INTO pedidos (usuario_id, producto_id, unidades, prioridad, motivo, estado) VALUES
                (3, 1, 1, 'normal', 'Nuevo puesto', 'pendiente'),
                (3, 7, 2, 'baja', 'Reponer despacho', 'pendiente'),
                (4, 2, 1, 'alta', 'Sustitución monitor averiado', 'pendiente'),
                (5, 10, 1, 'normal', 'Lámpara nueva oficina', 'pendiente'),
                (6, 12, 2, 'baja', 'Papel para impresora', 'pendiente'),
                (3, 5, 1, 'normal', 'Teletrabajo', 'en_revision'),
                (4, 8, 1, 'alta', 'Mesa nueva', 'en_revision'),
                (5, 11, 1, 'normal', 'Estantería despacho', 'en_revision'),
                (7, 3, 2, 'baja', 'Teclados sala reuniones', 'en_revision'),
                (4, 2, 1, 'normal', 'Monitor segundo puesto', 'aprobado'),
                (6, 7, 1, 'normal', 'Silla ergonómica', 'aprobado'),
                (3, 1, 1, 'alta', 'Portátil proyecto', 'denegado'),
                (5, 17, 5, 'baja', 'Grapadoras equipo', 'denegado'),
                (4, 8, 1, 'normal', 'Material oficina', 'entregado'),
                (3, 14, 2, 'normal', 'Toner impresora', 'entregado'),
                (6, 13, 1, 'baja', 'Bolígrafos', 'entregado'),
                (7, 4, 1, 'normal', 'Ratón repuesto', 'entregado')
            ");
        }
        if ($this->queryOne('SELECT 1 FROM propuestas_wishlist LIMIT 1') === null) {
            $this->pdo->exec("
                INSERT INTO propuestas_wishlist (titulo, descripcion, usuario_id) VALUES
                ('Monitor 27\" 4K', 'Para diseño y desarrollo', 3),
                ('Auriculares con cancelación de ruido', 'Trabajo en oficina abierta', 4),
                ('Webcam HD', 'Teletrabajo y reuniones', 3),
                ('Silla gaming ergonómica', 'Para puestos con muchas horas de pantalla', 5),
                ('Impresora multifunción', 'Escáner e impresora en cada planta', 6),
                ('Cafetera de cápsulas', 'Sala de descanso', 7),
                ('Pantalla secundaria 24\"', 'Doble pantalla para desarrolladores', 4),
                ('Kit teclado y ratón inalámbrico', 'Unificar modelo en toda la empresa', 5),
                ('Plantas de oficina', 'Ambiente más agradable', 6)
            ");
        }
        if ($this->queryOne('SELECT 1 FROM votos LIMIT 1') === null) {
            $this->pdo->exec("
                INSERT INTO votos (propuesta_id, usuario_id) VALUES
                (1, 1), (1, 4), (1, 5), (1, 6),
                (2, 1), (2, 3), (2, 7),
                (3, 2), (3, 4), (3, 6),
                (4, 1), (4, 3), (4, 4), (4, 7),
                (5, 2), (5, 3), (5, 5), (5, 6), (5, 7),
                (6, 1), (6, 4), (6, 5),
                (7, 2), (7, 3), (7, 6), (7, 7),
                (8, 1), (8, 4),
                (9, 3), (9, 5), (9, 6)
            ");
        }
    }

    /**
     * Ejecuta una consulta SELECT y devuelve todas las filas como array asociativo.
     */
    private function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    /**
     * Ejecuta una consulta y devuelve una sola fila o null.
     */
    private function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ejecuta INSERT y devuelve lastInsertId.
     */
    private function execute(string $sql, array $params = []): void
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    private function executeUpdate(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------------------------
    // USUARIOS
    // -------------------------------------------------------------------------

    public function obtenerUsuarios(): array
    {
        return $this->query('SELECT id, email, password, nombre, rol, activo FROM usuarios ORDER BY id');
    }

    public function obtenerUsuarioPorId(int $id): ?array
    {
        return $this->queryOne('SELECT id, email, password, nombre, rol, activo FROM usuarios WHERE id = ?', [$id]);
    }

    public function obtenerUsuarioPorEmail(string $email): ?array
    {
        return $this->queryOne('SELECT id, email, password, nombre, rol, activo FROM usuarios WHERE email = ?', [$email]);
    }

    public function guardarUsuarios(array $usuarios): bool
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($usuarios as $u) {
                $id = (int) ($u['id'] ?? 0);
                if ($id > 0) {
                    $this->executeUpdate(
                        'UPDATE usuarios SET email = ?, password = ?, nombre = ?, rol = ?, activo = ? WHERE id = ?',
                        [
                            $u['email'] ?? '',
                            $u['password'] ?? $u['password_hash'] ?? '',
                            $u['nombre'] ?? '',
                            $u['rol'] ?? 'empleado',
                            isset($u['activo']) ? (int) (bool) $u['activo'] : 1,
                            $id,
                        ]
                    );
                } else {
                    $this->execute(
                        'INSERT INTO usuarios (email, password, nombre, rol, activo) VALUES (?, ?, ?, ?, ?)',
                        [
                            $u['email'] ?? '',
                            $u['password'] ?? $u['password_hash'] ?? '',
                            $u['nombre'] ?? '',
                            $u['rol'] ?? 'empleado',
                            isset($u['activo']) ? (int) (bool) $u['activo'] : 1,
                        ]
                    );
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function insertarUsuario(array $datosUsuario): int
    {
        $this->execute(
            'INSERT INTO usuarios (email, password, nombre, rol, activo) VALUES (?, ?, ?, ?, ?)',
            [
                $datosUsuario['email'] ?? '',
                $datosUsuario['password'] ?? $datosUsuario['password_hash'] ?? '',
                $datosUsuario['nombre'] ?? '',
                $datosUsuario['rol'] ?? 'empleado',
                isset($datosUsuario['activo']) ? (int) (bool) $datosUsuario['activo'] : 1,
            ]
        );
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizarUsuario(int $id, array $datosActualizados): bool
    {
        $campos = [];
        $params = [];
        $permitidos = ['email', 'password', 'nombre', 'rol', 'activo'];
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $datosActualizados)) {
                $campos[] = "`$campo` = ?";
                $params[] = $campo === 'activo' ? (int) (bool) $datosActualizados[$campo] : $datosActualizados[$campo];
            }
        }
        if (empty($campos)) {
            return true;
        }
        $params[] = $id;
        return $this->executeUpdate('UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = ?', $params);
    }

    // -------------------------------------------------------------------------
    // CATEGORÍAS
    // -------------------------------------------------------------------------

    public function obtenerCategorias(): array
    {
        return $this->query('SELECT id, nombre, slug FROM categorias ORDER BY id');
    }

    public function obtenerCategoriaPorId(int $id): ?array
    {
        return $this->queryOne('SELECT id, nombre, slug FROM categorias WHERE id = ?', [$id]);
    }

    // -------------------------------------------------------------------------
    // PRODUCTOS (INVENTARIO)
    // -------------------------------------------------------------------------

    public function obtenerProductos(): array
    {
        $rows = $this->query('SELECT id, nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo FROM productos ORDER BY id');
        return $this->normalizarActivo($rows);
    }

    public function obtenerProductosPorCategoria($categoriaIdOSlug): array
    {
        $categorias = $this->obtenerCategorias();
        $idBuscado = null;
        foreach ($categorias as $cat) {
            if ((string) ($cat['id'] ?? '') === (string) $categoriaIdOSlug || ($cat['slug'] ?? '') === $categoriaIdOSlug) {
                $idBuscado = (int) $cat['id'];
                break;
            }
        }
        if ($idBuscado === null) {
            return [];
        }
        $rows = $this->query('SELECT id, nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo FROM productos WHERE categoria_id = ? ORDER BY id', [$idBuscado]);
        return $this->normalizarActivo($rows);
    }

    public function obtenerProductoPorId(int $id): ?array
    {
        $row = $this->queryOne('SELECT id, nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo FROM productos WHERE id = ?', [$id]);
        return $row ? $this->normalizarActivo([$row])[0] : null;
    }

    private function normalizarActivo(array $rows): array
    {
        foreach ($rows as &$r) {
            $r['activo'] = isset($r['activo']) ? (bool) (int) $r['activo'] : true;
        }
        unset($r);
        return $rows;
    }

    public function guardarProductos(array $productos): bool
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($productos as $p) {
                $id = (int) ($p['id'] ?? 0);
                $activo = isset($p['activo']) ? (int) (bool) $p['activo'] : 1;
                if ($id > 0) {
                    $this->executeUpdate(
                        'UPDATE productos SET nombre = ?, descripcion = ?, categoria_id = ?, stock = ?, umbral_critico = ?, imagen = ?, activo = ? WHERE id = ?',
                        [
                            $p['nombre'] ?? '',
                            $p['descripcion'] ?? '',
                            (int) ($p['categoria_id'] ?? 0),
                            (int) ($p['stock'] ?? 0),
                            (int) ($p['umbral_critico'] ?? 0),
                            $p['imagen'] ?? '',
                            $activo,
                            $id,
                        ]
                    );
                } else {
                    $this->execute(
                        'INSERT INTO productos (nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo) VALUES (?, ?, ?, ?, ?, ?, ?)',
                        [
                            $p['nombre'] ?? '',
                            $p['descripcion'] ?? '',
                            (int) ($p['categoria_id'] ?? 0),
                            (int) ($p['stock'] ?? 0),
                            (int) ($p['umbral_critico'] ?? 0),
                            $p['imagen'] ?? '',
                            $activo,
                        ]
                    );
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function insertarProducto(array $datosProducto): int
    {
        $activo = isset($datosProducto['activo']) ? (int) (bool) $datosProducto['activo'] : 1;
        $this->execute(
            'INSERT INTO productos (nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $datosProducto['nombre'] ?? '',
                $datosProducto['descripcion'] ?? '',
                (int) ($datosProducto['categoria_id'] ?? 0),
                (int) ($datosProducto['stock'] ?? 0),
                (int) ($datosProducto['umbral_critico'] ?? 0),
                $datosProducto['imagen'] ?? '',
                $activo,
            ]
        );
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizarProducto(int $id, array $datosActualizados): bool
    {
        $campos = [];
        $params = [];
        $permitidos = ['nombre', 'descripcion', 'categoria_id', 'stock', 'umbral_critico', 'imagen', 'activo'];
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $datosActualizados)) {
                $campos[] = "`$campo` = ?";
                $params[] = $campo === 'activo' ? (int) (bool) $datosActualizados[$campo] : ($campo === 'stock' || $campo === 'umbral_critico' || $campo === 'categoria_id' ? (int) $datosActualizados[$campo] : $datosActualizados[$campo]);
            }
        }
        if (empty($campos)) {
            return true;
        }
        $params[] = $id;
        return $this->executeUpdate('UPDATE productos SET ' . implode(', ', $campos) . ' WHERE id = ?', $params);
    }

    public function eliminarProducto(int $id): bool
    {
        return $this->actualizarProducto($id, ['activo' => false]);
    }

    public function obtenerProductosBajoUmbral(): array
    {
        $rows = $this->query(
            'SELECT id, nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo FROM productos WHERE activo = 1 AND umbral_critico > 0 AND stock <= umbral_critico ORDER BY stock ASC'
        );
        return $this->normalizarActivo($rows);
    }

    // -------------------------------------------------------------------------
    // PEDIDOS / SOLICITUDES
    // -------------------------------------------------------------------------

    public function obtenerPedidos(): array
    {
        return $this->query('SELECT id, usuario_id, producto_id, unidades, prioridad, motivo, estado, fecha_creacion, fecha_actualizacion FROM pedidos ORDER BY id DESC');
    }

    public function obtenerPedidosPorUsuario(int $usuarioId): array
    {
        return $this->query(
            'SELECT id, usuario_id, producto_id, unidades, prioridad, motivo, estado, fecha_creacion, fecha_actualizacion FROM pedidos WHERE usuario_id = ? ORDER BY fecha_creacion DESC',
            [$usuarioId]
        );
    }

    public function obtenerPedidosPorEstado(string $estado): array
    {
        return $this->query(
            'SELECT id, usuario_id, producto_id, unidades, prioridad, motivo, estado, fecha_creacion, fecha_actualizacion FROM pedidos WHERE estado = ? ORDER BY fecha_creacion ASC',
            [$estado]
        );
    }

    public function obtenerPedidoPorId(int $id): ?array
    {
        return $this->queryOne('SELECT id, usuario_id, producto_id, unidades, prioridad, motivo, estado, fecha_creacion, fecha_actualizacion FROM pedidos WHERE id = ?', [$id]);
    }

    public function guardarPedidos(array $pedidos): bool
    {
        // No se usa en flujo actual; los pedidos se insertan/actualizan individualmente
        return true;
    }

    public function insertarPedido(array $datosPedido): int
    {
        $estado = $datosPedido['estado'] ?? self::ESTADO_PENDIENTE;
        $this->execute(
            'INSERT INTO pedidos (usuario_id, producto_id, unidades, prioridad, motivo, estado) VALUES (?, ?, ?, ?, ?, ?)',
            [
                (int) ($datosPedido['usuario_id'] ?? 0),
                (int) ($datosPedido['producto_id'] ?? 0),
                (int) ($datosPedido['unidades'] ?? 1),
                in_array($datosPedido['prioridad'] ?? '', ['baja', 'normal', 'alta'], true) ? $datosPedido['prioridad'] : 'normal',
                $datosPedido['motivo'] ?? '',
                $estado,
            ]
        );
        return (int) $this->pdo->lastInsertId();
    }

    public function obtenerUnidadesReservadas(int $productoId): int
    {
        $row = $this->queryOne(
            'SELECT COALESCE(SUM(unidades), 0) AS total FROM pedidos WHERE producto_id = ? AND estado IN (?, ?)',
            [$productoId, self::ESTADO_PENDIENTE, self::ESTADO_EN_REVISION]
        );
        return (int) ($row['total'] ?? 0);
    }

    public function obtenerStockDisponible(int $productoId): int
    {
        $producto = $this->obtenerProductoPorId($productoId);
        if (!$producto) {
            return 0;
        }
        $stock = (int) ($producto['stock'] ?? 0);
        $reservado = $this->obtenerUnidadesReservadas($productoId);
        return max(0, $stock - $reservado);
    }

    public function actualizarPedido(int $id, array $datosActualizados): bool
    {
        $pedido = $this->obtenerPedidoPorId($id);
        if (!$pedido) {
            return false;
        }
        $nuevoEstado = $datosActualizados['estado'] ?? $pedido['estado'];
        $productoId = (int) ($pedido['producto_id'] ?? 0);
        $unidades = (int) ($pedido['unidades'] ?? 0);

        $campos = [];
        $params = [];
        if (isset($datosActualizados['estado'])) {
            $campos[] = 'estado = ?';
            $params[] = $datosActualizados['estado'];
        }
        if (empty($campos)) {
            return true;
        }
        $params[] = $id;
        if (!$this->executeUpdate('UPDATE pedidos SET ' . implode(', ', $campos) . ', fecha_actualizacion = NOW() WHERE id = ?', $params)) {
            return false;
        }
        if ($nuevoEstado === self::ESTADO_ENTREGADO && $productoId > 0 && $unidades > 0) {
            $producto = $this->obtenerProductoPorId($productoId);
            if ($producto) {
                $stockActual = (int) ($producto['stock'] ?? 0);
                $this->actualizarProducto($productoId, ['stock' => max(0, $stockActual - $unidades)]);
            }
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // WISHLIST (propuestas y votos)
    // -------------------------------------------------------------------------

    public function obtenerPropuestas(): array
    {
        return $this->query('SELECT id, titulo, descripcion, usuario_id, fecha_creacion FROM propuestas_wishlist ORDER BY id DESC');
    }

    public function obtenerPropuestaPorId(int $id): ?array
    {
        return $this->queryOne('SELECT id, titulo, descripcion, usuario_id, fecha_creacion FROM propuestas_wishlist WHERE id = ?', [$id]);
    }

    public function insertarPropuesta(array $datosPropuesta): int
    {
        $this->execute(
            'INSERT INTO propuestas_wishlist (titulo, descripcion, usuario_id) VALUES (?, ?, ?)',
            [
                $datosPropuesta['titulo'] ?? '',
                $datosPropuesta['descripcion'] ?? '',
                (int) ($datosPropuesta['usuario_id'] ?? 0),
            ]
        );
        return (int) $this->pdo->lastInsertId();
    }

    public function obtenerVotos(): array
    {
        return $this->query('SELECT id, propuesta_id, usuario_id, fecha FROM votos');
    }

    public function contarVotosPorPropuesta(int $propuestaId): int
    {
        $row = $this->queryOne('SELECT COUNT(*) AS total FROM votos WHERE propuesta_id = ?', [$propuestaId]);
        return (int) ($row['total'] ?? 0);
    }

    public function usuarioYaVoto(int $propuestaId, int $usuarioId): bool
    {
        $row = $this->queryOne('SELECT 1 FROM votos WHERE propuesta_id = ? AND usuario_id = ?', [$propuestaId, $usuarioId]);
        return $row !== null;
    }

    public function insertarVoto(int $propuestaId, int $usuarioId): bool
    {
        if ($this->usuarioYaVoto($propuestaId, $usuarioId)) {
            return false;
        }
        $this->execute('INSERT INTO votos (propuesta_id, usuario_id) VALUES (?, ?)', [$propuestaId, $usuarioId]);
        return true;
    }

    public function obtenerPropuestasOrdenadasPorVotos(): array
    {
        $propuestas = $this->query(
            'SELECT p.id, p.titulo, p.descripcion, p.usuario_id, p.fecha_creacion,
                    (SELECT COUNT(*) FROM votos v WHERE v.propuesta_id = p.id) AS votos
             FROM propuestas_wishlist p
             ORDER BY votos DESC, p.fecha_creacion DESC'
        );
        foreach ($propuestas as &$p) {
            $p['votos'] = (int) ($p['votos'] ?? 0);
        }
        unset($p);
        return $propuestas;
    }
}
