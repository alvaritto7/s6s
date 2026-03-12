# Manual de uso — s6s (Intelligent Supply System)

**Para quién es este manual:** Cualquier persona que use la página, aunque no haya usado una web así antes. Cada pantalla y cada botón se explican paso a paso.

**Importante:** Este documento debe actualizarse cada vez que se añada, quite o cambie algo en la aplicación (pantallas, botones, roles, mensajes). Así siempre tendrás un manual al día.

---

## Índice

1. [¿Qué es s6s?](#1-qué-es-s6s)
2. [Entrar en la página](#2-entrar-en-la-página)
3. [Los tres tipos de usuario (roles)](#3-los-tres-tipos-de-usuario-roles)
4. [Página de Iniciar sesión](#4-página-de-iniciar-sesión)
5. [Página de Registro](#5-página-de-registro)
6. [Página principal (Dashboard)](#6-página-principal-dashboard)
7. [Inventario](#7-inventario)
8. [Peticiones](#8-peticiones)
9. [Wishlist](#9-wishlist)
10. [Administración](#10-administración)
11. [Cerrar sesión](#11-cerrar-sesión)
12. [Elementos que ves en todas las páginas](#12-elementos-que-ves-en-todas-las-páginas)
13. [Mensajes que puede mostrar la página](#13-mensajes-que-puede-mostrar-la-página)
14. [Historial de cambios del manual](#14-historial-de-cambios-del-manual)

---

## 1. ¿Qué es s6s?

s6s es una aplicación web para gestionar **material de oficina o empresa**: ver qué hay en el catálogo (inventario), **pedir material** (solicitudes), y proponer **compras nuevas** (wishlist) y votar entre compañeros.

- **Inventario:** Ver productos disponibles y solicitar material.
- **Peticiones:** Ver el estado de tus solicitudes (pendiente, aprobado, entregado, etc.).
- **Wishlist:** Proponer “me gustaría que compraran esto” y votar las propuestas de otros.

Según si eres **empleado**, **staff** o **administrador**, podrás hacer más o menos cosas (lo detallamos más abajo).

---

## 2. Entrar en la página

Abre el navegador (Chrome, Firefox, Edge, etc.) y escribe la dirección de la aplicación. Por ejemplo, si está en tu ordenador: `http://localhost/s6s/` o `http://localhost/s6s/index.php`.

La primera vez te llevará a la pantalla de **Iniciar sesión**. Si no tienes cuenta, desde ahí puedes ir a **Registrarte**.

---

## 3. Los tres tipos de usuario (roles)

En s6s hay **tres roles**. Lo que puedes hacer depende de con cuál entres.

| Rol | Quién suele ser | Qué puede hacer |
|-----|-----------------|------------------|
| **Empleado** | Cualquier usuario que se registre desde “Registro” | Ver **Inventario**, **Peticiones** y **Wishlist**. Pedir material, ver sus solicitudes, crear propuestas en la wishlist y votar. **No** ve el menú ni la página de **Administración**. |
| **Staff** | Persona de almacén o revisión | Todo lo del empleado, **más**: entrar en **Administración** (solo ver resumen y gráficos), y en **Peticiones** puede **cambiar el estado** de las solicitudes (aprobar, denegar, marcar entregado, etc.). **No** puede exportar informes en PDF ni añadir/editar/desactivar productos del catálogo. |
| **Administrador** | Responsable total del sistema | Todo lo del staff, **más**: en **Administración** puede **exportar PDFs** (inventario y pedidos) y **gestionar el catálogo de productos** (añadir, editar y desactivar productos). |

Resumen rápido:

- **Empleado:** usar la app (inventario, peticiones, wishlist). Sin acceso a Administración.
- **Staff:** usar la app + revisar y cambiar estados de pedidos + ver resumen y gráficos en Administración. Sin PDFs ni gestión de productos.
- **Administrador:** todo lo anterior + exportar PDFs y gestionar productos (altas, ediciones, desactivar).

---

## 4. Página de Iniciar sesión

**Qué es:** La pantalla donde escribes tu **email** y **contraseña** para entrar.

**Qué ves:**

- El **logotipo** de s6s arriba.
- Un **formulario** con:
  - **Email:** tu correo (ej.: `empleado@s6s.local`).
  - **Contraseña:** la que te hayan dado o la que pusiste al registrarte.
- Botón **“Entrar”**.
- Un enlace: **“¿No tienes cuenta? Registrarse como empleado”**.

**Qué hacer:**

1. Escribe tu email en el primer campo.
2. Escribe tu contraseña en el segundo.
3. Pulsa **“Entrar”**.

Si algo falla (email o contraseña incorrectos), verás un **mensaje en rojo** encima del formulario. Si acabas de registrarte, puede aparecer un **mensaje en verde** diciendo que la cuenta se ha creado; luego ya puedes iniciar sesión.

---

## 5. Página de Registro

**Qué es:** Donde te **das de alta** como nuevo usuario. Siempre entras como **empleado** (no como administrador ni staff).

**Qué ves:**

- El **logotipo** de s6s.
- Un texto que dice que te registras como **empleado** y que podrás usar Inventario, Peticiones y Wishlist.
- Un **formulario** con:
  - **Nombre**
  - **Email**
  - **Contraseña (mín. 6 caracteres)**
  - **Repetir contraseña**
- Botón **“Crear cuenta”**.
- Enlace: **“¿Ya tienes cuenta? Iniciar sesión”**.

**Qué hacer:**

1. Rellena nombre, email y contraseña (y repite la contraseña).
2. Pulsa **“Crear cuenta”**.

Si falta algo o el email ya está usado, verás un **mensaje en rojo**. Si todo va bien, verás un **mensaje en verde** y podrás ir a **Iniciar sesión** para entrar con ese email y contraseña.

---

## 6. Página principal (Dashboard)

**Qué es:** La **página de inicio** una vez dentro. Solo la ves si ya has iniciado sesión.

**Qué ves:**

- Arriba: **cabecera** con el logo, el **menú** (Inventario, Peticiones, Wishlist y, si te toca, Administración), tu **nombre y rol** y el botón **“Cerrar sesión”** (esto se explica en el apartado 12).
- En el centro:
  - Un saludo: **“Bienvenido, [tu nombre]”**.
  - Un texto corto que invita a elegir una sección.
  - Si eres **administrador o staff** y hay productos con stock bajo, puede aparecer un **aviso** (alertas de stock) y, la primera vez que entres en la sesión, un **popup** recordándolo.
  - **Tres tarjetas** (o cuatro si eres admin/staff) para ir a:
    - **Inventario** — Catálogo y solicitud de material.
    - **Peticiones** — Estado de tus solicitudes.
    - **Wishlist** — Propuestas y votaciones.
    - **Administración** (solo si eres administrador o staff).

**Qué hacer:** Pulsa en la tarjeta de la sección a la que quieras ir (Inventario, Peticiones, Wishlist o Administración).

---

## 7. Inventario

**Qué es:** El **catálogo de productos** (material) que la empresa tiene. Aquí puedes **ver** qué hay y **solicitar** material.

**Quién puede usarlo:** Empleado, staff y administrador (todos).

**Qué ves:**

- **Título:** “Inventario” y un texto que dice que puedes filtrar por categoría.
- **Buscar por nombre:** Un cuadro de búsqueda para escribir y filtrar productos por nombre.
- **Categoría:** Botones para filtrar: **Todas**, **IT**, **Mobiliario**, **Consumibles** (las categorías pueden variar).
- **Lista de productos:** Tarjetas con cada producto: nombre, descripción, stock disponible y un botón **“Solicitar”** (si hay stock). Si el stock está bajo umbral, puede verse un aviso en la tarjeta.

**Qué puedes hacer:**

1. **Filtrar por categoría:** Pulsar **Todas**, **IT**, **Mobiliario** o **Consumibles** para ver solo esos productos.
2. **Buscar por nombre:** Escribir en el cuadro “Buscar por nombre…” para que la lista se filtre en tiempo real.
3. **Solicitar material:** Pulsar **“Solicitar”** en una tarjeta. Se abre una **ventana (modal)** con:
   - **Producto:** desplegable (ya viene elegido el que pulsaste).
   - **Unidades:** número (por defecto 1).
   - **Prioridad:** Baja, Normal o Alta.
   - **Motivo:** texto opcional.
   - Botones **“Cancelar”** (cierra la ventana sin enviar) y **“Enviar solicitud”** (envía la petición).

Después de enviar, verás un mensaje de confirmación y la solicitud aparecerá en **Peticiones** con estado “pendiente”.

Si no hay conexión o falla algo, la página mostrará un mensaje de error (por ejemplo que compruebes la conexión).

---

## 8. Peticiones

**Qué es:** Donde ves **todas tus solicitudes de material** y su **estado** (pendiente, en revisión, aprobado, denegado, entregado). Si eres **staff o administrador**, además ves bloques para **revisar** las de otros (pendientes y en revisión) y cambiarles el estado.

**Quién puede usarlo:** Todos. Staff y administrador ven bloques extra de “Pendientes” y “En revisión” con botones de acción.

**Qué ves:**

- **Título** y texto explicando los estados (Pendiente → En Revisión → Aprobado/Denegado → Entregado).
- Si eres **staff o administrador:**
  - **“Pendientes (pasar a revisión)”:** lista de solicitudes en estado *pendiente* con botón **“Pasar a revisión”**.
  - **“En revisión (staff)”:** lista de solicitudes *en revisión* con botones **“Aprobar”**, **“Denegar”** y **“Marcar entregado”**.
- **“Nueva solicitud”:** formulario para pedir material desde esta página (producto, unidades, prioridad, motivo) y botón **“Enviar solicitud”**.
- **“Mis solicitudes”:** lista de **tus** solicitudes con filtros por estado: **Todas**, **Pendiente**, **En revisión**, **Aprobado**, **Denegado**, **Entregado**.

**Qué puedes hacer (todos):**

1. **Nueva solicitud:** Elegir producto, unidades, prioridad, opcionalmente motivo, y pulsar **“Enviar solicitud”**. Verás un mensaje de confirmación y la lista se actualizará (o la página se recargará).
2. **Filtrar “Mis solicitudes”:** Pulsar uno de los botones de estado para ver solo las solicitudes en ese estado.

**Qué pueden hacer solo staff y administrador:**

1. En **“Pendientes”:** Pulsar **“Pasar a revisión”** en una solicitud para cambiar su estado a *en revisión*.
2. En **“En revisión”:** Pulsar **“Aprobar”**, **“Denegar”** o **“Marcar entregado”**. Antes de confirmar, la página pedirá **confirmación** (Sí/Cancelar). Al aprobar o marcar entregado, si aplica se descontará el stock del producto.

Si falla la conexión, verás un mensaje de error indicando que compruebes la conexión.

---

## 9. Wishlist

**Qué es:** Un **tablón de propuestas** de compra (“me gustaría que compraran esto”). Puedes **crear** propuestas y **votar** las de otros. Las propuestas se ordenan por número de votos.

**Quién puede usarlo:** Empleado, staff y administrador (todos).

**Qué ves:**

- **Título** y texto explicando que son propuestas de compra y que puedes votar.
- **“Nueva propuesta”:** formulario con **Título** (obligatorio), **Descripción** (opcional) y botón **“Crear propuesta”**.
- **Lista de propuestas:** cada una muestra título, descripción (si tiene), quién la propuso, fecha, **número de votos** y un botón **“Votar”** (o el texto “Ya has votado” si ya votaste esa propuesta).

**Qué puedes hacer:**

1. **Crear propuesta:** Rellenar al menos el título, opcionalmente la descripción, y pulsar **“Crear propuesta”**. Verás un mensaje de confirmación.
2. **Votar:** Pulsar **“Votar”** en una propuesta. Solo se puede votar una vez por propuesta; después verás “Ya has votado”.

Si no hay propuestas, verás un mensaje tipo “No hay propuestas aún”. Si falla la conexión, aparecerá un aviso de error.

---

## 10. Administración

**Qué es:** Panel para ver **resumen**, **gráficos** y (solo administrador) **exportar PDFs** y **gestionar el catálogo de productos**.

**Quién puede entrar:** Solo **staff** y **administrador**. El empleado no ve el enlace “Administración” ni puede abrir esta página.

**Qué ves según tu rol:**

### Si eres Administrador

- **Resumen:** tres tarjetas con números: **Productos activos**, **Total pedidos**, **Stock bajo umbral**.
- **Estadísticas:** dos gráficos (productos por categoría y pedidos por estado).
- **Informes:** texto explicativo y dos botones: **“Inventario (PDF)”** y **“Pedidos (PDF)”** para descargar informes en PDF.
- **Gestión de productos:** botón **“Añadir producto”**, formulario (nombre, descripción, categoría, stock, umbral crítico, imagen) y una **lista de productos** con botones **“Editar”** y **“Desactivar”** en cada uno. Al desactivar, la página pide **confirmación** antes de hacerlo.

### Si eres Staff

- **Resumen:** las mismas tres tarjetas (productos activos, total pedidos, stock bajo umbral).
- **Estadísticas:** los mismos dos gráficos.
- **Informes:** mismo título pero con el texto **“Solo el administrador puede exportar informes en PDF”** (no hay botones de descarga).
- **Gestión de productos:** mismo título pero con el texto **“Solo el administrador puede añadir, editar o desactivar productos del catálogo”** (no hay formulario ni lista de productos).

**Qué puede hacer solo el Administrador aquí:**

1. **Exportar PDF:** Pulsar **“Inventario (PDF)”** o **“Pedidos (PDF)”** para descargar el informe correspondiente.
2. **Añadir producto:** Pulsar **“Añadir producto”**, rellenar el formulario (nombre obligatorio, categoría obligatoria, resto opcional) y pulsar **“Guardar”**.
3. **Editar producto:** Pulsar **“Editar”** en un producto de la lista, cambiar los datos en el formulario y pulsar **“Guardar”**.
4. **Desactivar producto:** Pulsar **“Desactivar”**; confirmar en el mensaje que aparece. El producto deja de mostrarse en el catálogo pero no se borra de la base de datos.

Si un staff intenta exportar PDF o gestionar productos por la URL o por la API, recibirá un mensaje de que solo el administrador puede hacerlo.

---

## 11. Cerrar sesión

**Qué es:** Salir de tu cuenta para que nadie que use el mismo ordenador pueda seguir con tu sesión.

**Dónde está:** En la **parte superior derecha** de todas las páginas una vez dentro (junto a tu nombre y rol), como botón **“Cerrar sesión”**.

**Qué hacer:** Pulsar **“Cerrar sesión”**. La aplicación te saca de la sesión y te lleva de nuevo a la pantalla de **Iniciar sesión**. La sesión se cierra solo si se usa ese botón (no por escribir una dirección en la barra del navegador), por seguridad.

---

## 12. Elementos que ves en todas las páginas

Una vez dentro (después del login), en **Inventario**, **Peticiones**, **Wishlist** y **Administración** verás siempre:

- **Cabecera (arriba):**
  - **Logo (isotipo):** al pulsarlo vas al **Dashboard** (página principal).
  - **Menú:** enlaces a Inventario, Peticiones, Wishlist y, si te toca, Administración. El enlace de la página en la que estás suele verse resaltado (subrayado o en otro color).
  - **Tu nombre y rol:** por ejemplo “María García (empleado)”.
  - **Cerrar sesión:** botón para salir de la sesión (ver apartado 11).
- **En móvil o pantalla pequeña:** un botón **“Menú”** que abre/cierra el menú de navegación.
- **Pie de página (abajo):** marca s6s, enlaces rápidos (Dashboard, Inventario, Peticiones, Wishlist) y datos del proyecto (autores, TFC DAW). No incluye enlace a Administración para no exponer esa zona; el acceso es solo por el menú si tienes permiso.

---

## 13. Mensajes que puede mostrar la página

- **Mensajes en rojo:** errores (credenciales incorrectas, email ya usado, contraseñas no coinciden, campos obligatorios, etc.).
- **Mensajes en verde:** éxito (cuenta creada, solicitud enviada, estado actualizado, etc.).
- **Ventanas emergentes (SweetAlert):** confirmaciones (por ejemplo antes de aprobar, denegar o desactivar), avisos de stock bajo (una vez por sesión en Dashboard para admin/staff) y errores de conexión (“Comprueba tu conexión e intenta de nuevo”).
- **Estados de carga:** mientras se cargan datos (inventario, wishlist, etc.) pueden verse textos como “Cargando catálogo…” o “Cargando propuestas…” y, en algunas pantallas, animaciones de carga (skeletons).

Si ves un mensaje de error de conexión, comprueba que tienes internet o que el servidor de la aplicación está encendido.

---

## 14. Historial de cambios del manual

**Cómo mantener el manual al día:** Cada vez que en la aplicación se **añada** una pantalla, botón o funcionalidad, **se quite** algo o **se cambie** el comportamiento (por ejemplo permisos de roles), actualiza este documento en la sección correspondiente y añade una línea abajo con la fecha y el cambio.

| Fecha       | Cambio |
|------------|--------|
| (fecha de hoy) | Creación del manual. Roles: empleado, staff, administrador. Páginas: Login, Registro, Dashboard, Inventario, Peticiones, Wishlist, Administración. Diferencias staff vs administrador (PDF y gestión de productos solo admin). Filtros, confirmaciones, mensajes de error de conexión, cerrar sesión por POST. |
| (fecha de hoy) | Datos de prueba ampliados: 7 usuarios (1 admin, 1 staff, 5 empleados), 18 productos (varios con stock bajo umbral, 1 inactivo), 17 pedidos en todos los estados, 9 propuestas wishlist y múltiples votos. **Nota:** estos datos se insertan solo si la base está vacía al iniciar la app. Si ya tenías datos, para cargar los nuevos borra la base de datos en MySQL y vuelve a entrar (login) para que se recreen tablas y se inserten los datos de prueba. |

