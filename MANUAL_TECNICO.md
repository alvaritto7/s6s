# Manual técnico — s6s (Intelligent Supply System)

**Para quién es este manual:** Desarrolladores o personas que vayan a tocar el código. Explica **qué hace cada archivo**, **cada clase**, **cada método** y cómo se relacionan entre sí. No explica cómo usar la web como usuario (eso está en **MANUAL_USO.md**).

**Diferencia entre los dos manuales:**
- **MANUAL_USO.md** = Manual de **uso** de la aplicación (usuario final: pantallas, botones, roles, paso a paso).
- **MANUAL_TECNICO.md** = Manual **técnico** del proyecto (código: archivos, métodos, API, base de datos).

**Importante:** Este documento debe actualizarse cada vez que se añada, quite o modifique un archivo, un método, un recurso de la API o una tabla/columna en la base de datos.

---

## Índice

1. [Estructura del proyecto](#1-estructura-del-proyecto)
2. [Flujo de una petición (front controller)](#2-flujo-de-una-petición-front-controller)
3. [configuracion.php](#3-configuracionphp)
4. [php/Plantillas.php](#4-phpplantillasphp)
5. [php/BaseDeDatos.php](#5-phpbasedatosphp)
6. [index.php (detalle de rutas)](#6-indexphp-detalle-de-rutas)
7. [Controladores PHP](#7-controladores-php)
8. [php/Api.php (recursos y métodos)](#8-phpapiphp-recursos-y-métodos)
9. [Plantillas HTML](#9-plantillas-html)
10. [JavaScript](#10-javascript)
11. [CSS](#11-css)
12. [Historial de cambios del manual técnico](#12-historial-de-cambios-del-manual-técnico)

---

## 1. Estructura del proyecto

```
s6s/
├── index.php              # Front controller: todas las peticiones pasan por aquí
├── configuracion.php      # Constantes (BD, roles, acciones, assets)
├── MANUAL_USO.md         # Manual para el usuario final (qué hace cada pantalla)
├── MANUAL_TECNICO.md     # Este archivo (qué hace cada archivo y método)
├── php/
│   ├── BaseDeDatos.php   # Conexión MySQL, tablas, seed, todos los métodos de datos
│   ├── Plantillas.php    # Función cargarPlantilla() para reemplazar {{PLACEHOLDER}}
│   ├── Login.php         # Formulario de login y cerrar sesión
│   ├── Registro.php      # Formulario de registro (rol empleado)
│   ├── Dashboard.php     # Página principal tras el login
│   ├── Inventario.php    # Página del catálogo (productos por categoría)
│   ├── Peticiones.php    # Página de solicitudes y revisión (staff/admin)
│   ├── Wishlist.php      # Página de propuestas y votos
│   ├── Admin.php         # Panel administración (gráficos, PDF, productos; staff/admin)
│   ├── GestionUsuarios.php # Página gestión de usuarios (solo administrador)
│   └── Api.php           # Respuestas JSON y PDF; recurso en GET/POST
├── html/
│   ├── login.html
│   ├── registro.html
│   ├── dashboard.html
│   ├── inventario.html
│   ├── peticiones.html
│   ├── wishlist.html
│   ├── admin.html
│   ├── admin_usuarios.html
│   └── componentes/
│       └── footer.html
├── js/
│   ├── nav.js            # Menú móvil (toggle)
│   ├── login.js          # (si existe) lógica del formulario login
│   ├── registro.js       # (si existe) lógica del formulario registro
│   ├── dashboard.js      # Popup alertas de stock (una vez por sesión)
│   ├── inventario.js     # Filtros, búsqueda, modal solicitar material, llamadas API
│   ├── peticiones.js     # Nueva solicitud, cambiar estado, filtros
│   ├── wishlist.js       # Cargar propuestas, votar, crear propuesta
│   ├── admin.js          # Gráficos Chart.js
│   ├── admin-productos.js # CRUD productos (listar, añadir, editar, desactivar)
│   └── admin-usuarios.js # Listar usuarios, cambiar rol, activo, eliminar
├── css/
│   └── estilos.css       # Estilos globales
└── imagenes/             # logotipo_transparente.png, isotipo_transparente.png, etc.
```

---

## 2. Flujo de una petición (front controller)

1. El usuario abre una URL, por ejemplo `index.php?accion=inventario` o envía un formulario a `index.php` con `accion=login`.
2. **index.php** se ejecuta siempre primero. Carga `configuracion.php` (que a su vez carga `php/Plantillas.php`) y `php/BaseDeDatos.php`. Inicia la sesión PHP si no está iniciada.
3. **index.php** lee `$accion` desde `$_GET['accion']` o `$_POST['accion']`.
4. Según el valor de `$accion`, hace una de estas cosas:
   - **logout:** Solo si el método es POST, llama a `Login::cerrarSesion()` y termina.
   - Si el usuario **está logueado** y pide `login`, `registro` o acción vacía → redirige a **dashboard**.
   - **api:** Carga `Api.php` y ejecuta `(new Api())->ejecutar()`. La API devuelve JSON (o PDF) y termina; no se carga ninguna plantilla HTML.
   - Si el usuario **no está logueado** y la acción no es `login` ni `registro` → redirige a **login**.
   - **registro** → carga `Registro.php` y ejecuta `ejecutar()`.
   - **login** → carga `Login.php` y ejecuta `ejecutar()`.
   - **dashboard** → carga `Dashboard.php` y ejecuta `ejecutar()`.
   - **inventario** → carga `Inventario.php` y ejecuta `ejecutar()`.
   - **peticiones** → carga `Peticiones.php` y ejecuta `ejecutar()`.
   - **wishlist** → carga `Wishlist.php` y ejecuta `ejecutar()`.
   - **admin:** Comprueba que el rol sea administrador o staff; si no, redirige a dashboard. Carga `Admin.php` y ejecuta `ejecutar()`.
   - **admin_usuarios:** Comprueba que el rol sea administrador; si no, redirige a dashboard. Carga `GestionUsuarios.php` y ejecuta `ejecutar()`.
   - Cualquier otra acción → redirige a **login**.

Cada controlador (Login, Dashboard, etc.) suele construir HTML con `cargarPlantilla()` y hacer `echo $html`, o redirigir con `header('Location: ...')`.

---

## 3. configuracion.php

**Qué hace:** Define constantes globales que usa el resto del proyecto. Se carga una sola vez desde **index.php**.

**Constantes definidas:**

| Constante | Uso |
|-----------|-----|
| `RUTA_RAIZ` | `__DIR__` del archivo (raíz del proyecto s6s). Para construir rutas a plantillas e imágenes. |
| `DB_HOST` | Host MySQL (p. ej. `'localhost'`). |
| `DB_NAME` | Nombre de la base de datos (p. ej. `'s6s'`). |
| `DB_USER` | Usuario MySQL. |
| `DB_PASS` | Contraseña MySQL. |
| `DB_CHARSET` | Charset (p. ej. `'utf8mb4'`). |
| `ROL_ADMINISTRADOR` | `'administrador'`. |
| `ROL_STAFF` | `'staff'`. |
| `ROL_EMPLEADO` | `'empleado'`. |
| `ACCION_LOGIN` | `'login'`. |
| `ACCION_REGISTRO` | `'registro'`. |
| `ACCION_LOGOUT` | `'logout'`. |
| `ACCION_DASHBOARD` | `'dashboard'`. |
| `ACCION_INVENTARIO` | `'inventario'`. |
| `ACCION_PETICIONES` | `'peticiones'`. |
| `ACCION_WISHLIST` | `'wishlist'`. |
| `ACCION_ADMIN` | `'admin'`. |
| `ACCION_ADMIN_USUARIOS` | `'admin_usuarios'`. |
| `ACCION_API` | `'api'`. |
| `ASSET_LOGOTIPO` | Ruta relativa del logotipo (p. ej. `'imagenes/logotipo_transparente.png'`). |
| `ASSET_ISOTIPO` | Ruta relativa del isotipo. |
| `ASSET_FAVICON` | Ruta del favicon. |

Al final del archivo se hace `require_once` de **php/Plantillas.php**, para que la función `cargarPlantilla()` esté disponible en todo el proyecto.

---

## 4. php/Plantillas.php

**Qué hace:** Proporciona una única función para cargar archivos HTML y reemplazar placeholders.

**Función:**

- **`cargarPlantilla(string $rutaRelativa, array $sustituciones): string`**
  - **$rutaRelativa:** Ruta del archivo respecto a la raíz del proyecto, p. ej. `'html/login.html'` o `'html/componentes/footer.html'`. Las barras se convierten al separador del sistema.
  - **$sustituciones:** Array asociativo `['NOMBRE' => 'valor', ...]`. En el HTML, cada `{{NOMBRE}}` se sustituye por el valor (convertido a string).
  - **Devuelve:** El contenido del archivo con las sustituciones aplicadas, o cadena vacía si el archivo no se puede leer.
  - No hay herencia de plantillas ni bucles; es sustitución simple.

---

## 5. php/BaseDeDatos.php

**Qué hace:** Clase que encapsula el acceso a MySQL con PDO. Al instanciarse, crea la base de datos si no existe, ejecuta `asegurarEsquemaYSeed()` (crea tablas si no existen e inserta datos de prueba si están vacías) y deja la conexión lista para el resto de métodos.

**Constantes públicas (estados de pedidos):**

- `ESTADO_PENDIENTE` = `'pendiente'`
- `ESTADO_EN_REVISION` = `'en_revision'`
- `ESTADO_APROBADO` = `'aprobado'`
- `ESTADO_DENEGADO` = `'denegado'`
- `ESTADO_ENTREGADO` = `'entregado'`

**Métodos privados (uso interno):**

- **`asegurarEsquemaYSeed(): void`** — Ejecuta `CREATE TABLE IF NOT EXISTS` para `usuarios`, `categorias`, `productos`, `pedidos`, `propuestas_wishlist`, `votos`. Si alguna tabla está vacía, inserta datos de prueba (usuarios, categorías, productos, pedidos, propuestas, votos).
- **`query(string $sql, array $params = []): array`** — SELECT; devuelve array de filas asociativas.
- **`queryOne(string $sql, array $params = []): ?array`** — SELECT; devuelve una fila o null.
- **`execute(string $sql, array $params = []): void`** — Ejecuta INSERT/UPDATE/DELETE sin devolver filas.
- **`executeUpdate(string $sql, array $params = []): bool`** — Ejecuta UPDATE/DELETE y devuelve true si `rowCount() > 0`.
- **`normalizarActivo(array $rows): array`** — Convierte el campo `activo` de cada fila a booleano (para productos).

**Esquema de tablas (resumen):**

| Tabla | Columnas principales |
|-------|----------------------|
| **usuarios** | id, email, password, nombre, rol (enum: administrador, staff, empleado), activo (tinyint) |
| **categorias** | id, nombre, slug (único) |
| **productos** | id, nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo |
| **pedidos** | id, usuario_id, producto_id, unidades, prioridad (baja/normal/alta), motivo, estado (pendiente/en_revision/aprobado/denegado/entregado), fecha_creacion, fecha_actualizacion |
| **propuestas_wishlist** | id, titulo, descripcion, usuario_id, fecha_creacion |
| **votos** | id, propuesta_id, usuario_id, fecha; UNIQUE(propuesta_id, usuario_id) |

**Métodos públicos — Usuarios:**

- **`obtenerUsuarios(): array`** — Todas las filas de `usuarios` (incluye password; la API no lo expone).
- **`obtenerUsuarioPorId(int $id): ?array`** — Un usuario por ID.
- **`obtenerUsuarioPorEmail(string $email): ?array`** — Un usuario por email (para login).
- **`guardarUsuarios(array $usuarios): bool`** — Transacción: por cada elemento, INSERT si id no existe, UPDATE si existe. Campos: email, password, nombre, rol, activo.
- **`insertarUsuario(array $datosUsuario): int`** — INSERT y devuelve `lastInsertId()`.
- **`actualizarUsuario(int $id, array $datosActualizados): bool`** — UPDATE solo de los campos presentes en `$datosActualizados` entre: email, password, nombre, rol, activo.
- **`eliminarUsuario(int $id): bool`** — DELETE del usuario. No comprueba integridad referencial (pedidos/propuestas pueden quedar con usuario_id huérfano).

**Métodos públicos — Categorías:**

- **`obtenerCategorias(): array`** — Todas las categorías ordenadas por id.
- **`obtenerCategoriaPorId(int $id): ?array`** — Una categoría por ID.

**Métodos públicos — Productos:**

- **`obtenerProductos(): array`** — Todos los productos; `activo` normalizado a booleano.
- **`obtenerProductosPorCategoria($categoriaIdOSlug): array`** — Productos filtrados por id o slug de categoría.
- **`obtenerProductoPorId(int $id): ?array`** — Un producto por ID.
- **`guardarProductos(array $productos): bool`** — Transacción: INSERT o UPDATE por cada elemento.
- **`insertarProducto(array $datosProducto): int`** — INSERT producto; devuelve lastInsertId.
- **`actualizarProducto(int $id, array $datosActualizados): bool`** — UPDATE de campos permitidos: nombre, descripcion, categoria_id, stock, umbral_critico, imagen, activo.
- **`eliminarProducto(int $id): bool`** — En realidad pone `activo = 0` (soft delete).
- **`obtenerProductosBajoUmbral(): array`** — Productos con `activo=1`, `umbral_critico > 0` y `stock <= umbral_critico`.

**Métodos públicos — Pedidos:**

- **`obtenerPedidos(): array`** — Todos los pedidos, ordenados por id DESC.
- **`obtenerPedidosPorUsuario(int $usuarioId): array`** — Pedidos de un usuario.
- **`obtenerPedidosPorEstado(string $estado): array`** — Pedidos por estado (pendiente, en_revision, etc.).
- **`obtenerPedidoPorId(int $id): ?array`** — Un pedido por ID.
- **`insertarPedido(array $datosPedido): int`** — INSERT; campos: usuario_id, producto_id, unidades, prioridad, motivo, estado (por defecto pendiente).
- **`obtenerUnidadesReservadas(int $productoId): int`** — Suma de unidades de pedidos en estado pendiente o en_revision para ese producto.
- **`obtenerStockDisponible(int $productoId): int`** — Stock del producto menos unidades reservadas (mínimo 0).
- **`actualizarPedido(int $id, array $datosActualizados): bool`** — Actualiza el pedido; si el nuevo estado es `entregado`, descuenta las unidades del stock del producto.

**Métodos públicos — Wishlist (propuestas y votos):**

- **`obtenerPropuestas(): array`** — Todas las propuestas.
- **`obtenerPropuestaPorId(int $id): ?array`** — Una propuesta por ID.
- **`insertarPropuesta(array $datosPropuesta): int`** — INSERT; titulo, descripcion, usuario_id.
- **`obtenerVotos(): array`** — Todos los votos.
- **`contarVotosPorPropuesta(int $propuestaId): int`** — Número de votos de una propuesta.
- **`usuarioYaVoto(int $propuestaId, int $usuarioId): bool`** — True si ya existe voto.
- **`insertarVoto(int $propuestaId, int $usuarioId): bool`** — INSERT voto si no existe; devuelve false si ya votó.
- **`obtenerPropuestasOrdenadasPorVotos(): array`** — Propuestas con subconsulta de conteo de votos, ordenadas por votos DESC y fecha DESC.

---

## 6. index.php (detalle de rutas)

- **Líneas 1–15:** `require` de configuracion y BaseDeDatos; inicio de sesión si no está iniciada.
- **Líneas 17–26:** Si `accion === logout` y método POST → Login::cerrarSesion() y exit.
- **Líneas 28–33:** Si hay sesión y acción es login, registro o vacía → redirige a dashboard.
- **Líneas 34–38:** Si acción es `api` → Api::ejecutar() y exit.
- **Líneas 40–43:** Si no hay sesión y acción no es login ni registro → redirige a login.
- **Líneas 45–101:** Dispatcher: registro, login, dashboard, inventario, peticiones, wishlist, admin (comprobando rol staff o admin), admin_usuarios (comprobando rol admin). Cada uno hace require del controlador y ejecutar().
- **Línea 103–104:** Cualquier otra acción → redirige a login.

---

## 7. Controladores PHP

Cada controlador se instancia desde **index.php** y se llama a **`ejecutar()`**. No hay un método común “run” más allá de esa convención.

### Login.php

- **`ejecutar(): void`** — Si es POST llama a `procesarLogin()`, si no a `mostrarFormulario()`.
- **`mostrarFormulario(): void`** (privado) — Limpia mensajes de sesión (login_error, registro_exito), carga `html/login.html` con placeholders (ASSET_*, MENSAJE_ERROR, MENSAJE_EXITO, FOOTER) y hace echo.
- **`procesarLogin(): void`** (privado) — Lee email y password de POST; valida que no estén vacíos; obtiene usuario por email con `BaseDeDatos::obtenerUsuarioPorEmail`; comprueba que exista y esté activo y que la contraseña coincida (comparación en claro). Si todo va bien, guarda en sesión `usuario_id`, `usuario_nombre`, `usuario_rol` y redirige a dashboard; si no, guarda `login_error` y redirige a login.
- **`cerrarSesion(): void`** (público) — Vacía `$_SESSION`, destruye la cookie de sesión, `session_destroy()`, redirige a login. Llamado desde index cuando accion=logout por POST.

### Registro.php

- **`ejecutar(): void`** — POST → `procesarRegistro()`, GET → `mostrarFormulario()`.
- **`mostrarFormulario(): void`** (privado) — Carga `html/registro.html` con mensajes y valores (VALOR_NOMBRE, VALOR_EMAIL, etc.).
- **`procesarRegistro(): void`** (privado) — Valida nombre, email (filter_var), contraseña (mín. 6), coincidencia de contraseñas; comprueba que el email no exista; inserta usuario con `insertarUsuario` con rol ROL_EMPLEADO; guarda mensaje de éxito en sesión y redirige a login.

### Dashboard.php

- **`ejecutar(): void`** — Obtiene alertas de stock (`obtenerProductosBajoUmbral`), determina si el usuario puede ver Admin (`puedeAdmin` según rol). Construye bloque de alertas HTML y enlace a Administración si aplica. Carga `html/dashboard.html` con NOMBRE_USUARIO, ROL_USUARIO, BLOQUE_ALERTAS, ENLACE_ADMIN, ENLACE_ADMIN_CARD, ALERTAS_CANTIDAD, PUEDE_ADMIN, FOOTER.

### Inventario.php

- **`ejecutar(): void`** — Obtiene categorías, construye botones de filtro (data-categoria = slug). Carga `html/inventario.html` con LISTA_CATEGORIAS, ENLACE_ADMIN, etc. El listado de productos se pide por JavaScript vía API (recurso `productos`).

### Peticiones.php

- **`ejecutar(): void`** — Obtiene pedidos del usuario y, si es staff o admin, pedidos por estado pendiente y en_revision. Construye bloques HTML para “Pendientes (pasar a revisión)” y “En revisión (staff)” con botones de acción (data-estado, data-pedido-id). Construye lista de “Mis solicitudes”. Carga `html/peticiones.html` con BLOQUE_STAFF_PENDIENTES, BLOQUE_STAFF_REVISION, LISTA_MIS_PETICIONES, etc. Los cambios de estado se envían por JS a la API (`cambiar_estado_pedido`).

### Wishlist.php

- **`ejecutar(): void`** — Solo carga `html/wishlist.html` con ENLACE_ADMIN, NOMBRE_USUARIO, ROL_USUARIO, FOOTER. Las propuestas y votos se cargan y envían por JS (API `propuestas`, `votar`, `crear_propuesta`).

### Admin.php

- **`ejecutar(): void`** — Si el rol no es administrador ni staff, redirige a dashboard. Obtiene productos, categorías, pedidos, alertas; calcula totales y datos para gráficos (porCategoria, porEstado). Según si es administrador o no, construye `$bloqueInformesPdf` (botones PDF o mensaje “solo administrador”), `$bloqueGestionProductos` (formulario y lista o mensaje), `$bloqueGestionUsuarios` (enlace a admin_usuarios o vacío). Carga `html/admin.html` con TOTAL_PRODUCTOS, TOTAL_PEDIDOS, TOTAL_ALERTAS, DATOS_GRAFICOS, BLOQUE_*, FOOTER. Los gráficos se dibujan con Chart.js en admin.js; los productos se gestionan con admin-productos.js (API producto_crear, producto_actualizar, producto_eliminar).

### GestionUsuarios.php

- **`ejecutar(): void`** — Si el rol no es administrador, redirige a dashboard. Carga `html/admin_usuarios.html` con NOMBRE_USUARIO, ROL_USUARIO, USUARIO_ID (id del usuario en sesión para que el JS no muestre “Eliminar” en su fila), FOOTER. La tabla de usuarios se rellena por admin-usuarios.js (API usuarios, usuario_actualizar_rol, usuario_actualizar_activo, usuario_eliminar).

---

## 8. php/Api.php (recursos y métodos)

La API se invoca con **index.php?accion=api&recurso=nombre_recurso**. Requiere sesión (si no hay sesión devuelve 401). El método que atiende cada recurso es privado.

**Métodos auxiliares:**

- **`esStaffOAdmin(): bool`** — True si rol es administrador o staff.
- **`esAdministrador(): bool`** — True si rol es administrador.
- **`responderJson(array $datos, int $codigo = 200): void`** — Envía cabecera Content-Type application/json y codigo HTTP, hace echo de json_encode($datos).

**Recursos GET (lectura):**

| recurso | Método que lo atiende | Respuesta |
|---------|------------------------|-----------|
| categorias | devolverCategorias | { categorias: [...] } |
| productos | devolverProductos | { productos: [...], categorias: [...] } (productos activos) |
| alertas_stock | devolverAlertasStock | { alertas: [...] } (productos bajo umbral) |
| propuestas | devolverPropuestas | { propuestas: [...], votosPorPropuesta: {...}, usuarioYaVoto: {...} } (propuestas ordenadas por votos, conteos y si el usuario ya votó cada una) |
| pdf_inventario | generarPdfInventario | PDF (solo administrador; si no 403). Usa DomPDF. |
| pdf_pedidos | generarPdfPedidos | PDF (solo administrador; si no 403). |
| usuarios | devolverUsuarios | { usuarios: [...] } sin campo password (solo administrador; si no 403). |

**Recursos POST (acción):**

| recurso | Método | Parámetros (POST) | Quién |
|---------|--------|-------------------|-------|
| votar | procesarVoto | propuesta_id | Cualquier usuario logueado |
| crear_pedido | procesarCrearPedido | producto_id, unidades, prioridad, motivo | Cualquier usuario logueado |
| cambiar_estado_pedido | procesarCambiarEstadoPedido | pedido_id, estado | Staff o administrador |
| crear_propuesta | procesarCrearPropuesta | titulo, descripcion | Cualquier usuario logueado |
| producto_crear | procesarProductoCrear | nombre, descripcion, categoria_id, stock, umbral_critico, imagen (opcional) | Solo administrador |
| producto_actualizar | procesarProductoActualizar | id, nombre, descripcion, categoria_id, stock, umbral_critico, imagen (opcional) | Solo administrador |
| producto_eliminar | procesarProductoEliminar | id | Solo administrador (soft delete: activo=0) |
| usuario_actualizar_rol | procesarUsuarioActualizarRol | id, rol (empleado|staff|administrador) | Solo administrador |
| usuario_actualizar_activo | procesarUsuarioActualizarActivo | id, activo (0|1). No permite desactivar al usuario actual. | Solo administrador |
| usuario_eliminar | procesarUsuarioEliminar | id. No permite eliminar al usuario actual. | Solo administrador |

**Método privado adicional:** `subirImagenProducto(string $nombreCampo): string` — Procesa el archivo subido en `$_FILES[$nombreCampo]`, redimensiona con GD si está disponible y guarda en `imagenes/productos/`; devuelve la ruta relativa o cadena vacía.

---

## 9. Plantillas HTML

Cada plantilla usa placeholders `{{NOMBRE}}` que el controlador reemplaza con `cargarPlantilla()`.

| Archivo | Placeholders típicos |
|---------|----------------------|
| **html/login.html** | ASSET_ISOTIPO, ASSET_FAVICON, ASSET_LOGOTIPO, MENSAJE_ERROR, MENSAJE_EXITO, FOOTER |
| **html/registro.html** | ASSET_*, MENSAJE_ERROR, MENSAJE_EXITO, VALOR_NOMBRE, VALOR_EMAIL, FOOTER |
| **html/dashboard.html** | ASSET_*, NOMBRE_USUARIO, ROL_USUARIO, ENLACE_ADMIN, BLOQUE_ALERTAS, ENLACE_ADMIN_CARD, ALERTAS_CANTIDAD, PUEDE_ADMIN (data-* para JS), FOOTER |
| **html/inventario.html** | ASSET_*, ACCION_DASHBOARD, ENLACE_ADMIN, NOMBRE_USUARIO, ROL_USUARIO, LISTA_CATEGORIAS, FOOTER |
| **html/peticiones.html** | ASSET_*, ACCION_DASHBOARD, ENLACE_ADMIN, NOMBRE_USUARIO, ROL_USUARIO, BLOQUE_STAFF_PENDIENTES, BLOQUE_STAFF_REVISION, LISTA_MIS_PETICIONES, FOOTER |
| **html/wishlist.html** | ASSET_*, ACCION_DASHBOARD, ENLACE_ADMIN, NOMBRE_USUARIO, ROL_USUARIO, FOOTER |
| **html/admin.html** | ASSET_*, ACCION_DASHBOARD, NOMBRE_USUARIO, ROL_USUARIO, TOTAL_PRODUCTOS, TOTAL_PEDIDOS, TOTAL_ALERTAS, DATOS_GRAFICOS, BLOQUE_INFORMES_PDF, BLOQUE_GESTION_PRODUCTOS, BLOQUE_GESTION_USUARIOS, FOOTER |
| **html/admin_usuarios.html** | ASSET_*, ACCION_DASHBOARD, NOMBRE_USUARIO, ROL_USUARIO, USUARIO_ID (data-usuario-id en body), FOOTER |
| **html/componentes/footer.html** | ANIO |

Las páginas internas (dashboard, inventario, etc.) incluyen cabecera con logo, nav (Inventario, Peticiones, Wishlist, Administración y si aplica Gestión de usuarios), nombre de usuario, rol y formulario de Cerrar sesión (POST accion=logout).

---

## 10. JavaScript

| Archivo | Qué hace |
|---------|----------|
| **nav.js** | Obtiene `#btn-menu-mobile` y `#nav-principal`. Al hacer clic en el botón, toggle de la clase `nav-abierto` en el nav y cambia el texto del botón (Menú / Cerrar) y el atributo `aria-expanded`. |
| **login.js** | (Si existe) Lógica del formulario de login (validación cliente, etc.). |
| **registro.js** | (Si existe) Lógica del formulario de registro. |
| **dashboard.js** | Lee `data-alertas-cantidad` y `data-puede-admin` del body. Si hay alertas y puede admin y existe Swal, comprueba `sessionStorage` con clave `s6s_alerta_stock_visto`; si no se ha mostrado aún, muestra un SweetAlert de aviso y guarda en sessionStorage que ya se mostró (una vez por sesión). |
| **inventario.js** | Carga productos vía API (recurso productos). Filtra por categoría (botones con data-categoria) y por búsqueda por nombre. Dibuja tarjetas; botón “Solicitar” abre un modal con formulario (producto, unidades, prioridad, motivo). Envío con API crear_pedido. |
| **peticiones.js** | Carga/refresca datos si aplica; formulario “Nueva solicitud” (producto, unidades, prioridad, motivo) → API crear_pedido. Botones “Pasar a revisión”, “Aprobar”, “Denegar”, “Marcar entregado” envían API cambiar_estado_pedido con pedido_id y estado. Filtro de “Mis solicitudes” por estado. Confirmaciones con SweetAlert. |
| **wishlist.js** | Pide API propuestas; muestra lista con votos y botón Votar (o “Ya has votado”). Envío de voto con API votar. Formulario “Nueva propuesta” (título, descripción) → API crear_propuesta. |
| **admin.js** | Lee `#datos-graficos` (JSON con categorias y estados). Inicializa dos gráficos Chart.js (productos por categoría, pedidos por estado). |
| **admin-productos.js** | Lista productos vía API productos; botones Editar y Desactivar. Formulario para añadir/editar (nombre, descripción, categoría, stock, umbral, imagen); envío con API producto_crear o producto_actualizar. Desactivar con confirmación → API producto_eliminar. |
| **admin-usuarios.js** | Lista usuarios vía API usuarios. Tabla con select de rol y de activo; al cambiar, POST a usuario_actualizar_rol o usuario_actualizar_activo. Botón Eliminar (excepto en la fila del usuario actual, usando data-usuario-id) con confirmación → API usuario_eliminar. |

---

## 11. CSS

**css/estilos.css** contiene los estilos globales de la aplicación: cabecera, navegación, tarjetas del dashboard, formularios, tablas, botones, mensajes de error/éxito, estados de carga, pie de página, estilos para badges de estado (pendiente, en_revision, etc.) y responsive (menú móvil con .nav-abierto). No se documenta cada regla aquí; el archivo es único y centralizado.

---

## 12. Historial de cambios del manual técnico

Cada vez que añadas o quites un archivo, un método público, un recurso de la API o una tabla/columna, actualiza la sección correspondiente de este documento y añade una línea abajo.

| Fecha       | Cambio |
|------------|--------|
| (fecha de hoy) | Creación del manual técnico. Documentados: estructura del proyecto, index.php, configuracion.php, Plantillas.php, BaseDeDatos (esquema y métodos), controladores Login, Registro, Dashboard, Inventario, Peticiones, Wishlist, Admin, GestionUsuarios, Api (todos los recursos), plantillas HTML, JS y CSS. Diferenciación clara con MANUAL_USO.md. |

