# Manual técnico — s6s (primera entrega)

Documentación para **desarrolladores** o evaluadores: arquitectura, configuración, API y datos. Complementa al `MANUAL_USO.md`.

---

## 1. Requisitos

| Componente | Versión / notas |
|------------|-----------------|
| PHP        | 8.0 o superior, extensión **PDO** y **pdo_mysql** |
| MySQL o MariaDB | Compatible con UTF-8 (`utf8mb4`) |
| Servidor web | Apache con `mod_rewrite` opcional; en XAMPP suele bastar acceder por `http://localhost/...` |

---

## 2. Estructura de carpetas (resumen)

```
s6s_primera_entrega/
├── index.php              # Front controller (?accion=)
├── configuracion.php      # Constantes DB, roles, acciones
├── css/                   # Estilos
├── html/                  # Plantillas HTML (login, dashboard, inventario, etc.)
├── html/componentes/      # Pie de página reutilizable
├── imagenes/              # Logos, favicon
├── js/                    # Scripts por pantalla (inventario, peticiones, nav…)
└── php/
    ├── BaseDeDatos.php    # PDO, esquema, consultas
    ├── Plantillas.php     # Carga HTML y sustituye {{PLACEHOLDER}}
    ├── Login.php, Registro.php, Dashboard.php
    ├── Inventario.php, Peticiones.php, Admin.php
    └── Api.php            # Respuestas JSON (API interna)
```

---

## 3. Configuración

`configuracion.php` define:

- `RUTA_RAIZ`, constantes `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`
- Roles: `ROL_ADMINISTRADOR`, `ROL_STAFF`, `ROL_EMPLEADO`
- Acciones: `login`, `registro`, `logout`, `dashboard`, `inventario`, `peticiones`, `admin`, `api`

**Antes de ejecutar:** crea en MySQL una base vacía con el mismo nombre que `DB_NAME` (por defecto `s6s_primera_entrega`) o ajusta el nombre en el archivo. La primera carga de la aplicación ejecutará la creación de tablas y datos iniciales desde `BaseDeDatos.php` si las tablas no existen o están vacías según la lógica definida ahí.

---

## 4. Entrada de la aplicación

Todas las peticiones pasan por `index.php` con el parámetro **`accion`** (GET o POST):

| `accion`   | Descripción breve |
|------------|-------------------|
| (vacío o login) | Redirige a login si no hay sesión |
| `login`    | Formulario e inicio de sesión |
| `registro` | Alta de empleado |
| `logout`   | Cierre de sesión (POST) |
| `dashboard`| Panel principal |
| `inventario` | Catálogo (datos vía API en el cliente) |
| `peticiones` | Listado y flujo de solicitudes |
| `admin`    | Panel staff/admin (solo esos roles) |
| `api`      | API JSON (ver siguiente apartado) |

Si un usuario logueado usa una `accion` desconocida, se redirige al **dashboard**.

---

## 5. API (`index.php?accion=api&recurso=...`)

- **Autenticación:** salvo diseño interno, la API asume **sesión PHP iniciada**; si no hay usuario, responde `401`.
- **Formato:** JSON, `Content-Type: application/json; charset=utf-8`.

| `recurso` | Método | Descripción |
|-----------|--------|-------------|
| `categorias` | GET | Lista de categorías |
| `productos` | GET | `?categoria=` opcional; `?todos=1` solo admin (productos inactivos) |
| `alertas_stock` | GET | Productos bajo umbral |
| `crear_pedido` | POST | Cuerpo JSON: `producto_id`, `unidades`, `prioridad`, `motivo` |
| `cambiar_estado_pedido` | POST | Staff/admin: `pedido_id`, `nuevo_estado` |

Parámetros concretos y validaciones están en `php/Api.php` y `php/BaseDeDatos.php`.

---

## 6. Base de datos

- **Clase:** `BaseDeDatos` — conexión PDO, creación condicional de tablas, migraciones mínimas (p. ej. columnas) si existen en el código.
- **Datos iniciales:** usuarios de prueba, categorías, productos, pedidos de ejemplo, etc., según bloques `INSERT` o comprobaciones de tablas vacías en `BaseDeDatos.php`.
- Pueden existir tablas o métodos asociados a funcionalidades **no expuestas en la UI** de esta entrega (por ejemplo restos de propuestas); no afectan al flujo principal si no se invocan.

---

## 7. Plantillas y front-end

- Las vistas son **HTML estático** con marcadores `{{NOMBRE}}` reemplazados por `php/Plantillas.php`.
- **CSS:** `css/base.css`, `componentes.css`, `cabecera-nav.css`, `dashboard.css`, hojas por página.
- **JavaScript:** `fetch` para API; **SweetAlert2** (CDN) para mensajes en varias pantallas.

---

## 8. Seguridad (notas)

- Contraseñas: comprobar en `BaseDeDatos` / `Login.php` si se usa hash (p. ej. `password_hash`) o almacenamiento en claro solo en desarrollo.
- Sesión: `$_SESSION` con `usuario_id`, `usuario_nombre`, `usuario_rol`.
- Salida HTML: usar `htmlspecialchars` donde se inserten datos de usuario en plantillas (el código ya lo aplica en puntos generados desde PHP).

---

## 9. Alcance no incluido en esta entrega

- Wishlist / propuestas en interfaz.
- Búsqueda por texto en inventario.
- CRUD de productos e informes desde el panel de administración (textos “pendiente” en pantalla).

Para más detalle de comportamiento, revisar los controladores en `php/` y la lógica de pedidos en `BaseDeDatos.php`.
