# Manual CSS — s6s

Estilos del proyecto organizados por **objetivo**. El orden de carga debe respetarse: base → componentes → cabecera y pie → páginas → librerías externas → otros.

| Archivo | Contenido |
|---------|-----------|
| **base.css** | Variables (`:root`), reset, `body`, utilidades (`.visually-hidden`, `.color-gris`) |
| **componentes.css** | Botones, mensajes error/éxito, badges genéricos, tarjeta base, form-logout |
| **auth.css** | Login y registro (`.pagina-login`, formularios, logo) |
| **cabecera-nav.css** | Cabecera sticky, isotipo, nav, menú móvil, usuario-sesión |
| **footer.css** | Pie de página (`.pie-pagina`, columnas, copy) |
| **dashboard.css** | Página principal: hero, cards de sección, alertas, nav-secciones |
| **inventario.css** | Filtros, tarjetas producto, skeleton, modal solicitar |
| **peticiones-wishlist.css** | Listas peticiones/propuestas, badges prioridad, formularios, filtro estado |
| **admin.css** | Resumen, gráficos, gestión productos, upload, tabla productos |
| **mi-cuenta.css** | Página Mi cuenta (hero, grid, secciones) |
| **librerias-externas.css** | Sobrescrituras para librerías de terceros (p. ej. SweetAlert2, tema oscuro) |
| **otros.css** | Ajustes puntuales (p. ej. `.pagina-login .pie-pagina`) |

**Uso en plantillas:** en cada página se enlazan solo los módulos que necesita (ver `html/*.html`).

**estilos.css:** reexporta todos con `@import` por compatibilidad; no hace falta usarlo si ya enlazas los archivos por separado.
