# Manual de uso — s6s

Guía para probar la aplicación: login, inventario, peticiones y panel de administración según el rol.

**Base de datos:** MySQL, nombre configurado en `configuracion.php` (`DB_NAME`).

---

## Acceso

Abrir la URL del proyecto en el navegador. Usuarios de prueba (contraseñas en texto plano en entorno de desarrollo):

- Administrador: `admin@s6s.local` / `admin`
- Staff: `staff@s6s.local` / `admin`
- Empleado: `empleado@s6s.local` / `password`

También se puede registrar un usuario nuevo (rol empleado).

---

## Secciones

| Sección | Empleado | Staff / Admin |
|---------|----------|----------------|
| Dashboard | Sí | Sí; enlace a Administración si aplica |
| Inventario | Sí | Sí |
| Peticiones | Sí (propias + nueva solicitud) | Sí + revisión de solicitudes de otros |
| Administración | No | Sí (resumen y alertas) |

---

## Inventario

El catálogo se filtra por **categoría** (botones «Todas» y una por categoría). No hay búsqueda por texto en esta versión.

---

## Peticiones (flujo)

1. El usuario crea una solicitud (estado pendiente).
2. Staff puede pasar a revisión, aprobar o denegar.
3. Las aprobadas pueden marcarse como entregadas (descuento de stock según lógica del sistema).

---

## Cerrar sesión

Botón en la cabecera (envío POST).
