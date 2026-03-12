<?php
/**
 * Controlador Login — Muestra el formulario de acceso y procesa credenciales.
 * No imprime HTML directamente; usa la vista vistas/login.php.
 */

declare(strict_types=1);

class Login
{
    private BaseDeDatos $bd;

    public function __construct()
    {
        $this->bd = new BaseDeDatos();
    }

    /**
     * Único punto de entrada: GET muestra formulario, POST valida y crea sesión.
     */
    public function ejecutar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarLogin();
            return;
        }
        $this->mostrarFormulario();
    }

    /**
     * Muestra la vista de login cargando la plantilla HTML y reemplazando placeholders.
     */
    private function mostrarFormulario(): void
    {
        $mensajeError = $_SESSION['login_error'] ?? '';
        $mensajeExito = $_SESSION['registro_exito'] ?? '';
        unset($_SESSION['login_error'], $_SESSION['registro_exito']);

        $mensajeErrorHtml = $mensajeError !== '' ? '<p class="mensaje-error" role="alert">' . htmlspecialchars($mensajeError) . '</p>' : '';
        $mensajeExitoHtml = $mensajeExito !== '' ? '<p class="mensaje-exito" role="status">' . htmlspecialchars($mensajeExito) . '</p>' : '';
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $html = cargarPlantilla('html/login.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ASSET_LOGOTIPO' => ASSET_LOGOTIPO,
            'MENSAJE_ERROR' => $mensajeErrorHtml,
            'MENSAJE_EXITO' => $mensajeExitoHtml,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }

    /**
     * Valida email y contraseña contra la BD de archivos y crea sesión o redirige con error.
     */
    private function procesarLogin(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $_SESSION['login_error'] = 'Introduce email y contraseña.';
            header('Location: index.php?accion=' . ACCION_LOGIN);
            exit;
        }

        // Comprobamos que el usuario exista y esté activo (no revelamos si el fallo es email o contraseña)
        $usuario = $this->bd->obtenerUsuarioPorEmail($email);
        if ($usuario === null || !($usuario['activo'] ?? true)) {
            $_SESSION['login_error'] = 'Credenciales incorrectas.';
            header('Location: index.php?accion=' . ACCION_LOGIN);
            exit;
        }

        if ((string) ($usuario['password'] ?? '') !== $password) {
            $_SESSION['login_error'] = 'Credenciales incorrectas.';
            header('Location: index.php?accion=' . ACCION_LOGIN);
            exit;
        }

        // Sesión: guardamos solo id, nombre y rol para no exponer el hash ni datos sensibles
        $_SESSION['usuario_id']  = (int) $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'] ?? $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'] ?? ROL_EMPLEADO;

        header('Location: index.php?accion=' . ACCION_DASHBOARD);
        exit;
    }

    /**
     * Cierra la sesión y redirige al login. Llamado desde index.php cuando accion=logout.
     */
    public function cerrarSesion(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        header('Location: index.php?accion=' . ACCION_LOGIN);
        exit;
    }
}
