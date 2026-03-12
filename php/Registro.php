<?php
/**
 * Controlador Registro — Alta de nuevos usuarios con rol empleado.
 *
 * Solo permite crear cuentas con el rol más bajo (empleado): no administran nada,
 * solo usan Inventario, Peticiones y Wishlist. Los roles administrador y staff
 * se crean desde la base de datos o por un admin (fuera de este formulario).
 *
 * Autores: Hugo Turrillo, Marcos Gutierrez, Álvaro Labrador
 */

declare(strict_types=1);

class Registro
{
    private BaseDeDatos $bd;

    public function __construct()
    {
        $this->bd = new BaseDeDatos();
    }

    /**
     * GET: muestra el formulario de registro.
     * POST: valida datos, comprueba que el email no exista, crea usuario como empleado y redirige a login.
     */
    public function ejecutar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarRegistro();
            return;
        }
        $this->mostrarFormulario();
    }

    /**
     * Carga la plantilla HTML de registro y reemplaza placeholders.
     */
    private function mostrarFormulario(): void
    {
        $mensajeError = $_SESSION['registro_error'] ?? '';
        $mensajeExito = $_SESSION['registro_exito'] ?? '';
        unset($_SESSION['registro_error'], $_SESSION['registro_exito']);

        $mensajeErrorHtml = $mensajeError !== '' ? '<p class="mensaje-error" role="alert">' . htmlspecialchars($mensajeError) . '</p>' : '';
        $mensajeExitoHtml = $mensajeExito !== '' ? '<p class="mensaje-exito" role="status">' . htmlspecialchars($mensajeExito) . '</p>' : '';
        $valorNombre = htmlspecialchars((string) ($_POST['nombre'] ?? ''));
        $valorEmail = htmlspecialchars((string) ($_POST['email'] ?? ''));
        $footer = cargarPlantilla('html/componentes/footer.html', ['ANIO' => date('Y')]);

        $html = cargarPlantilla('html/registro.html', [
            'ASSET_ISOTIPO' => ASSET_ISOTIPO,
            'ASSET_FAVICON' => ASSET_FAVICON,
            'ASSET_LOGOTIPO' => ASSET_LOGOTIPO,
            'MENSAJE_ERROR' => $mensajeErrorHtml,
            'MENSAJE_EXITO' => $mensajeExitoHtml,
            'VALOR_NOMBRE' => $valorNombre,
            'VALOR_EMAIL' => $valorEmail,
            'FOOTER' => $footer,
        ]);
        echo $html;
    }

    /**
     * Valida nombre, email, contraseña y confirmación. Si el email ya existe, rechaza.
     * Crea el usuario con rol ROL_EMPLEADO para que solo use la aplicación sin permisos de administración.
     */
    private function procesarRegistro(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password2'] ?? '');

        if ($nombre === '') {
            $_SESSION['registro_error'] = 'El nombre es obligatorio.';
            header('Location: index.php?accion=' . ACCION_REGISTRO);
            exit;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['registro_error'] = 'Introduce un email válido.';
            header('Location: index.php?accion=' . ACCION_REGISTRO);
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['registro_error'] = 'La contraseña debe tener al menos 6 caracteres.';
            header('Location: index.php?accion=' . ACCION_REGISTRO);
            exit;
        }

        // Confirmación de contraseña para evitar errores de escritura
        if ($password !== $password2) {
            $_SESSION['registro_error'] = 'Las contraseñas no coinciden.';
            header('Location: index.php?accion=' . ACCION_REGISTRO);
            exit;
        }

        // Que el email no esté ya registrado (un empleado por cuenta)
        if ($this->bd->obtenerUsuarioPorEmail($email) !== null) {
            $_SESSION['registro_error'] = 'Ya existe una cuenta con ese email.';
            header('Location: index.php?accion=' . ACCION_REGISTRO);
            exit;
        }

        $this->bd->insertarUsuario([
            'nombre'   => $nombre,
            'email'   => $email,
            'password'=> $password,
            'rol'     => ROL_EMPLEADO,
            'activo'  => true,
        ]);

        $_SESSION['registro_exito'] = 'Cuenta creada. Ya puedes iniciar sesión como empleado.';
        header('Location: index.php?accion=' . ACCION_LOGIN);
        exit;
    }
}
