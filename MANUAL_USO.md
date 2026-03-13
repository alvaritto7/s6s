# Manual de uso — s6s (Intelligent Supply System)

**Para quién es este manual:** Cualquier persona que use la página, aunque no haya usado una web así antes. Cada pantalla y cada botón se explican paso a paso.

**Diferencia con el otro manual:** Este es el **manual de uso** (usuario final). Para la documentación técnica del código (archivos, métodos, API, base de datos), ver **MANUAL_TECNICO.md**.

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
11. [Gestión de usuarios](#11-gestión-de-usuarios)
12. [Mi cuenta](#12-mi-cuenta)
13. [Cerrar sesión](#13-cerrar-sesión)
14. [Elementos que ves en todas las páginas](#14-elementos-que-ves-en-todas-las-páginas)
15. [Mensajes que puede mostrar la página](#15-mensajes-que-puede-mostrar-la-página)
16. [Historial de cambios del manual](#16-historial-de-cambios-del-manual)

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
| **Staff** | Persona de almacén o revisión | Todo lo del empleado, **más**: entrar en **Administración** (solo ver resumen y gráficos), y en **Peticiones** puede **cambiar el estado** de las solicitudes (aprobar, denegar, marcar entregado, etc.). **No** puede ver ni generar los informes de inventario/pedidos ni añadir/editar/desactivar productos del catálogo. |
| **Administrador** | Responsable total del sistema | Todo lo del staff, **más**: en **Administración** puede **ver y generar informes** de inventario y pedidos (en HTML imprimible), **gestionar el catálogo de productos** (añadir, editar y desactivar productos) y acceder a **Gestión de usuarios** (cambiar rol, activar/desactivar y eliminar usuarios). |

Resumen rápido:

- **Empleado:** usar la app (inventario, peticiones, wishlist). Sin acceso a Administración.
- **Staff:** usar la app + revisar y cambiar estados de pedidos + ver resumen y gráficos en Administración. Sin informes ni gestión de productos.
- **Administrador:** todo lo anterior + ver/generar informes, gestionar productos (altas, ediciones, desactivar) y gestión de usuarios (cambiar rol, activar/desactivar, eliminar).

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
- **Lista de productos:** Tarjetas con cada producto: nombre, descripción, stock disponible y un botón **“Solicitar”** (si hay stock). Si el stock está por debajo del **umbral crítico**, la tarjeta se resalta visualmente (borde anaranjado, texto “Stock bajo” y una **barra de color rojo/naranja** que indica de forma visual lo cerca que está de agotarse).

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
- **Lista de propuestas:** cada una muestra título, descripción (si tiene), quién la propuso, fecha, **número de votos** y, según tu caso:
  - Un botón **“Votar”** si todavía no has votado esa propuesta.
  - Un botón desactivado **“Votado”** si ya la has votado (así ves claramente que ya has participado en esa propuesta).
  - Si la propuesta es **tuya**, verás la etiqueta **“Tu propuesta”** en azul sobre la tarjeta para identificarla rápido.

**Qué puedes hacer:**

1. **Crear propuesta:** Rellenar al menos el título, opcionalmente la descripción, y pulsar **“Crear propuesta”**. Verás un mensaje de confirmación.
2. **Votar:** Pulsar **“Votar”** en una propuesta. En cuanto votas, **sin recargar la página**, el número de votos sube en pantalla y el botón cambia a **“Votado”** en color destacado y queda desactivado para que veas que ya no puedes votar otra vez.

Si no hay propuestas, verás un mensaje tipo “No hay propuestas aún”. Si falla la conexión, aparecerá un aviso de error.

---

## 10. Administración

**Qué es:** Panel para ver **resumen**, **gráficos** y (solo administrador) **consultar informes imprimibles** y **gestionar el catálogo de productos**.

**Quién puede entrar:** Solo **staff** y **administrador**. El empleado no ve el enlace “Administración” ni puede abrir esta página.

**Qué ves según tu rol:**

### Si eres Administrador

- **Resumen:** tres tarjetas con números: **Productos activos**, **Total pedidos**, **Stock bajo umbral**.
- **Estadísticas:** dos gráficos (productos por categoría y pedidos por estado).
- **Informes:** texto explicativo y dos botones para abrir informes en una nueva pestaña del navegador: uno de **Inventario** y otro de **Pedidos**. Desde esa pestaña podrás usar la opción del navegador **“Imprimir” → “Guardar como PDF”** si quieres el PDF físico.
- **Gestión de productos:** botón **“Añadir producto”**, formulario (nombre, descripción, categoría, stock, umbral crítico, imagen) y una **lista de productos** con botones **“Editar”** y **“Desactivar”** en cada uno. Al desactivar, la página pide **confirmación** antes de hacerlo.
- **Gestión de usuarios:** un bloque con el enlace **“Ir a Gestión de usuarios”** que lleva a una página donde el administrador puede ver todos los usuarios y cambiar su rol, activar/desactivar la cuenta o eliminar usuarios (ver apartado 11).

### Si eres Staff

- **Resumen:** las mismas tres tarjetas (productos activos, total pedidos, stock bajo umbral).
- **Estadísticas:** los mismos dos gráficos.
- **Informes:** mismo título pero con el texto **“Solo el administrador puede ver y generar informes de inventario y pedidos”** (no hay botones de descarga).
- **Gestión de productos:** mismo título pero con el texto **“Solo el administrador puede añadir, editar o desactivar productos del catálogo”** (no hay formulario ni lista de productos).

**Qué puede hacer solo el Administrador aquí:**

1. **Ver informes:** Pulsar el botón de **Inventario** o **Pedidos** para abrir el informe correspondiente en una nueva pestaña.
   - En el **informe de Inventario** verás una tabla con cada producto, su stock y un **precio unitario simulado**, además del **valor total** de cada línea y un **resumen final** con el valor económico total del inventario.
   - En el **informe de Pedidos** verás la tabla de pedidos y, al final, un **resumen de consumo mensual por departamento** (simulado), para que el administrador pueda ver en qué área se está gastando más material cada mes.
   - Si necesitas un PDF físico, usa en esa pestaña la opción del navegador **“Imprimir”** y elige **“Guardar como PDF”**.
2. **Añadir producto:** Pulsar **“Añadir producto”**, rellenar el formulario (nombre obligatorio, categoría obligatoria, resto opcional) y pulsar **“Guardar”**.
3. **Editar producto:** Pulsar **“Editar”** en un producto de la lista, cambiar los datos en el formulario y pulsar **“Guardar”**.
4. **Desactivar producto:** Pulsar **“Desactivar”**; confirmar en el mensaje que aparece. El producto deja de mostrarse en el catálogo pero no se borra de la base de datos.
5. **Ir a Gestión de usuarios:** Pulsar **“Ir a Gestión de usuarios”** para abrir la página descrita en el apartado 11.

Si un staff intenta acceder a los informes, gestionar productos o gestionar usuarios por la URL o por la API, recibirá un mensaje de que solo el administrador puede hacerlo.

---

## 11. Gestión de usuarios

**Qué es:** Página donde el **administrador** puede ver **todos los usuarios** de la aplicación y **cambiar su rol**, **activar o desactivar** la cuenta, o **eliminar** el usuario de forma definitiva.

**Quién puede entrar:** Solo el **administrador**. El staff y el empleado no ven el enlace a esta página ni pueden abrirla (si intentan entrar por la URL, se les redirige al Dashboard).

**Cómo llegar:** Desde **Administración** (apartado 10), en el bloque **“Gestión de usuarios”**, pulsar **“Ir a Gestión de usuarios”**. En el menú de la cabecera también aparece el enlace **“Gestión de usuarios”** cuando has iniciado sesión como administrador.

**Qué ves:**

- **Título:** “Gestión de usuarios” y un texto que explica que puedes cambiar rol, activar/desactivar y eliminar, y que no puedes eliminar ni desactivar tu propia cuenta.
- **Tabla de usuarios:** cada fila muestra **ID**, **Email**, **Nombre**, **Rol** (desplegable: Empleado, Staff, Administrador), **Activo** (desplegable: Sí / No) y **Acciones** (botón **“Eliminar”**; en tu propia fila aparece “—” en lugar del botón).

**Qué puedes hacer:**

1. **Cambiar el rol:** En el desplegable **Rol** de cualquier usuario, elige **Empleado**, **Staff** o **Administrador**. Al cambiar, se guarda solo y aparece un mensaje de confirmación. El usuario afectado tendrá los nuevos permisos la próxima vez que inicie sesión (o en la misma sesión si ya está dentro).
2. **Activar o desactivar:** En el desplegable **Activo** elige **Sí** o **No**. Si pones **No**, ese usuario **no podrá iniciar sesión** hasta que alguien le vuelva a activar. **No puedes desactivar tu propia cuenta** (en tu fila solo aparece “Sí (tú)”).
3. **Eliminar usuario:** Pulsa **“Eliminar”** en la fila del usuario. La aplicación pide **confirmación** (“¿Eliminar usuario? Se eliminará de forma definitiva…”). Si confirmas, el usuario se borra de la base de datos y ya no podrá entrar. **No puedes eliminar tu propia cuenta** (en tu fila no hay botón Eliminar). Los pedidos o propuestas que haya creado ese usuario se mantienen en el sistema, pero asociados a un usuario ya inexistente.

Si intentas desactivarte a ti mismo o eliminarte desde la API, verás un mensaje de error. Si no eres administrador y abres esta página por error, serás redirigido al inicio.

---

## 12. Mi cuenta

**Qué es:** Página donde **cualquier usuario logueado** puede ver sus datos (nombre, email, rol) y **cambiar su nombre** o **cambiar su contraseña**.

**Quién puede entrar:** Empleado, staff y administrador (todos los que tengan sesión iniciada).

**Cómo llegar:** En el **menú de la cabecera** aparece el enlace **“Mi cuenta”** (entre Wishlist y Administración). También puedes ir desde el Dashboard o escribiendo la dirección correspondiente.

**Qué ves:**

- **Datos de la cuenta:** tu **nombre** (editable en un cuadro de texto), tu **email** (solo lectura) y tu **rol** (solo lectura). Un botón **“Guardar nombre”** para guardar los cambios de nombre.
- **Cambiar contraseña:** tres campos: **Contraseña actual**, **Nueva contraseña** y **Repetir nueva contraseña**. La nueva debe tener al menos 6 caracteres. Botón **“Cambiar contraseña”** para aplicar el cambio.

**Qué puedes hacer:**

1. **Cambiar tu nombre:** Escribe el nuevo nombre en el cuadro y pulsa **“Guardar nombre”**. Verás un mensaje de confirmación y el nombre se actualizará en la cabecera en la próxima carga (o de inmediato si la página se refresca).
2. **Cambiar tu contraseña:** Rellena la contraseña actual y la nueva (y su repetición). Si la contraseña actual no es correcta o las dos nuevas no coinciden, la aplicación te avisará. Si todo es correcto, verás un mensaje de éxito y ya podrás iniciar sesión con la nueva contraseña.

---

## 13. Cerrar sesión

**Qué es:** Salir de tu cuenta para que nadie que use el mismo ordenador pueda seguir con tu sesión.

**Dónde está:** En la **parte superior derecha** de todas las páginas una vez dentro (junto a tu nombre y rol), como botón **“Cerrar sesión”**.

**Qué hacer:** Pulsar **“Cerrar sesión”**. La aplicación te saca de la sesión y te lleva de nuevo a la pantalla de **Iniciar sesión**. La sesión se cierra solo si se usa ese botón (no por escribir una dirección en la barra del navegador), por seguridad.

---

## 14. Elementos que ves en todas las páginas

Una vez dentro (después del login), en **Inventario**, **Peticiones**, **Wishlist**, **Mi cuenta**, **Administración** y (solo administrador) **Gestión de usuarios** verás siempre:

- **Cabecera (arriba):**
  - **Logo (isotipo):** al pulsarlo vas al **Dashboard** (página principal).
  - **Menú:** enlaces a Inventario, Peticiones, Wishlist, **Mi cuenta**, Administración (si tienes permiso) y, si eres **administrador**, también **Gestión de usuarios**. El enlace de la página en la que estás suele verse resaltado (subrayado o en otro color).
  - **Tu nombre y rol:** por ejemplo “María García (empleado)”.
  - **Cerrar sesión:** botón para salir de la sesión (ver apartado 12).
- **En móvil o pantalla pequeña:** un botón **“Menú”** que abre/cierra el menú de navegación.
- **Pie de página (abajo):** marca s6s, enlaces rápidos (Dashboard, Inventario, Peticiones, Wishlist) y datos del proyecto (autores, TFC DAW). No incluye enlace a Administración para no exponer esa zona; el acceso es solo por el menú si tienes permiso.

---

## 15. Mensajes que puede mostrar la página

- **Mensajes en rojo:** errores (credenciales incorrectas, email ya usado, contraseñas no coinciden, campos obligatorios, etc.).
- **Mensajes en verde:** éxito (cuenta creada, solicitud enviada, estado actualizado, etc.).
- **Ventanas emergentes (SweetAlert):** confirmaciones (por ejemplo antes de aprobar, denegar o desactivar), avisos de stock bajo (una vez por sesión en Dashboard para admin/staff) y errores de conexión (“Comprueba tu conexión e intenta de nuevo”). En muchos casos el cuadro emergente muestra el **isotipo hexagonal de s6s** en grande como icono, en lugar del icono genérico verde o rojo.
- **Estados de carga:** mientras se cargan datos (inventario, wishlist, etc.) pueden verse textos como “Cargando catálogo…” o “Cargando propuestas…” y, en algunas pantallas, animaciones de carga (skeletons).

Si ves un mensaje de error de conexión, comprueba que tienes internet o que el servidor de la aplicación está encendido.

---

## 16. Historial de cambios del manual

**Cómo mantener el manual al día:** Cada vez que en la aplicación se **añada** una pantalla, botón o funcionalidad, **se quite** algo o **se cambie** el comportamiento (por ejemplo permisos de roles), actualiza este documento en la sección correspondiente y añade una línea abajo con la fecha y el cambio.

| Fecha       | Cambio |
|------------|--------|
| (fecha de hoy) | Creación del manual. Roles: empleado, staff, administrador. Páginas: Login, Registro, Dashboard, Inventario, Peticiones, Wishlist, Administración. Diferencias staff vs administrador (informes y gestión de productos solo admin). Filtros, confirmaciones, mensajes de error de conexión, cerrar sesión por POST. |
| (fecha de hoy) | Datos de prueba ampliados: 7 usuarios (1 admin, 1 staff, 5 empleados), 18 productos (varios con stock bajo umbral, 1 inactivo), 17 pedidos en todos los estados, 9 propuestas wishlist y múltiples votos. **Nota:** estos datos se insertan solo si la base está vacía al iniciar la app. Si ya tenías datos, para cargar los nuevos borra la base de datos en MySQL y vuelve a entrar (login) para que se recreen tablas y se inserten los datos de prueba. |
| (fecha de hoy) | **Gestión de usuarios** (solo administrador): nueva página accesible desde Administración o desde el menú. Listado de usuarios con cambio de rol (empleado/staff/administrador), activar/desactivar cuenta (los desactivados no pueden iniciar sesión) y eliminar usuario. No se puede eliminar ni desactivar la propia cuenta. Manual actualizado con apartado 11 y renumeración de secciones. |
| (fecha de hoy) | Añadida nota al inicio: este es el manual de **uso**; para documentación técnica del código ver **MANUAL_TECNICO.md**. |
| (fecha de hoy) | Inventario: tarjetas de productos en **stock crítico** con aviso “Stock bajo”, borde ligeramente anaranjado y barra de color rojo/naranja que indica visualmente lo cerca que está el stock del umbral crítico. Informes: informes de Inventario y Pedidos en HTML con cabecera corporativa (logo arriba a la derecha y línea azul), valor económico total del inventario y resumen mensual de consumo por departamento; se pueden imprimir o guardar como PDF desde el navegador. Wishlist: al votar, el botón pasa a **“Votado”** sin recargar la página. SweetAlert2: cuadros de diálogo de confirmación y éxito usando el isotipo de s6s como icono. |
| (fecha de hoy) | **Mi cuenta:** nueva página accesible desde el menú para todos los usuarios. Permite ver nombre, email y rol; editar el nombre (Guardar nombre) y cambiar la contraseña (contraseña actual + nueva + repetición). Wishlist: las propuestas creadas por el usuario actual muestran la etiqueta **"Tu propuesta"** en azul para identificarlas. |

