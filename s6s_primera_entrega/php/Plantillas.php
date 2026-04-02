<?php
// Sustitución de placeholders {{}} en plantillas HTML.
declare(strict_types=1);

function cargarPlantilla(string $rutaRelativa, array $sustituciones): string
{
    $ruta = RUTA_RAIZ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);
    $html = @file_get_contents($ruta);
    if ($html === false) {
        return '';
    }
    foreach ($sustituciones as $clave => $valor) {
        $html = str_replace('{{' . $clave . '}}', (string) $valor, $html);
    }
    return $html;
}
