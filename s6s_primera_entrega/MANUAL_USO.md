# Manual de uso — s6s (primera entrega)

Este documento describe cómo **instalar**, **probar** y **usar** la aplicación web incluida en esta carpeta. Está pensado para quien evalúe el proyecto o para un usuario que la pruebe por primera vez.

**Manual técnico:** Para estructura de archivos, API y base de datos, ver `MANUAL_TECNICO.md`.

---

## 1. Qué es esta aplicación

**s6s** es una aplicación web para gestionar **material de oficina**: consultar un **catálogo** (inventario), **solicitar** unidades y **seguir el estado** de las solicitudes. El **staff** y el **administrador** pueden revisar y cambiar el estado de las peticiones; el administrador además ve un **panel de resumen** y alertas de stock.

**Alcance de esta entrega:** no incluye wishlist, búsqueda por texto en inventario ni gestión de productos desde el panel de administración (el catálogo viene de la base de datos y datos iniciales).

---

## 2. Requisitos y acceso

- Navegador actual (Chrome, Firefox, Edge, etc.).
- Servidor local con PHP y MySQL (por ejemplo **XAMPP**).

**URL típica en local** (ajusta la ruta si tu carpeta tiene otro nombre):

`http://localhost/s6s/s6s_primera_entrega/index.php`

La primera vez que entras sin sesión, la aplicación te redirige a **Iniciar sesión**.

---

## 3. Usuarios de prueba

Las contraseñas están en texto plano solo para **desarrollo**. En producción habría que usar entorno seguro y políticas de contraseñas.

| Rol            | Email               | Contraseña |
|----------------|---------------------|------------|
| Administrador  | `admin@s6s.local`   | `admin`    |
| Staff          | `staff@s6s.local`   | `admin`    |
| Empleado       | `empleado@s6s.local`| `password` |
| Empleado (demo adicional) | `a@uem.es` | `empleado` |

También puedes **registrar** un usuario nuevo desde la pantalla de registro; esos usuarios son siempre **empleado**.

---

## 4. Roles y qué puede hacer cada uno

| Sección        | Empleado | Staff | Administrador |
|----------------|----------|-------|----------------|
| Dashboard      | Sí       | Sí    | Sí             |
| Inventario     | Sí       | Sí    | Sí             |
| Peticiones     | Ver las suyas y crear solicitudes | Revisar y cambiar estados de todas las que aplique el flujo | Igual que staff |
| Administración | No       | Sí    | Sí             |

- **Empleado:** inventario y peticiones; no entra en Administración.
- **Staff:** además puede usar Administración (resumen, alertas de stock, avisos). No tiene en esta versión informes exportables ni CRUD de productos en pantalla.
- **Administrador:** mismo acceso que staff en esta entrega; en Administración verá textos informativos donde funciones futuras (informes, alta de productos en pantalla) están marcadas como **pendientes**.

---

## 5. Pantallas principales

### Iniciar sesión

Introduce **email** y **contraseña** y pulsa entrar. Si falla, verás un mensaje de error en la propia página.

### Registro

Formulario con nombre, email, contraseña y repetición. Crea una cuenta con rol **empleado**. Si el email ya existe, se muestra error.

### Dashboard

Resumen de acceso a **Inventario**, **Peticiones** y, si aplica, **Administración**. Los usuarios con rol staff o administrador pueden ver **alertas de stock** si hay productos por debajo del umbral.

### Inventario

Lista el catálogo cargado desde el servidor. Puedes **filtrar por categoría** con los botones («Todas» y una por categoría). **No hay búsqueda por nombre** en esta versión.

Si un producto tiene stock disponible, puedes pulsar **Solicitar**, rellenar unidades, prioridad y motivo, y enviar. Eso crea una petición (se gestiona en la sección Peticiones).

### Peticiones

- **Empleado:** crea solicitudes nuevas y ve el listado de las suyas; puede filtrar por estado.
- **Staff / Admin:** además ve colas de trabajo (por ejemplo pendientes de pasar a revisión, en revisión, aprobados para marcar entregado) y botones para cambiar el estado según las reglas del sistema (pendiente → en revisión → aprobado/denegado → entregado cuando corresponda).

### Administración

Panel con **números resumen**, **avisos** (por ejemplo peticiones pendientes de revisión, productos con stock bajo) y listado de **alertas de stock**. Las secciones de informes en PDF/HTML y de **gestión de productos** desde esta pantalla están indicadas como **no implementadas** en esta entrega: el catálogo se alimenta de la base de datos, no desde formularios aquí.

---

## 6. Flujo típico de una petición

1. El empleado solicita material desde **Inventario** (o desde **Peticiones** si hay formulario de nueva solicitud).
2. La solicitud queda en estado **pendiente** (u otro inicial según el código).
3. Staff/admin revisa y puede pasar a **en revisión**, **aprobar**, **denegar**, etc.
4. Si se aprueba y se entrega material, puede marcarse **entregado**; el sistema puede **descontar stock** según la lógica implementada en base de datos.

---

## 7. Cerrar sesión

En la cabecera hay un botón **Cerrar sesión** que envía un formulario por **POST** y destruye la sesión de forma segura.

---

## 8. Problemas frecuentes

- **Página en blanco o error de base de datos:** comprueba que MySQL esté arrancado, que exista la base configurada en `configuracion.php` (`DB_NAME`) y que el usuario/contraseña de MySQL coincidan (`DB_USER`, `DB_PASS`).
- **No cargan productos:** revisa la consola del navegador y que la sesión esté iniciada (la API exige usuario logueado).
