<?php

function obtenerNombreRol($rol) {
    if ($rol == 7) {
        return 'Proveedores';
    } elseif ($rol >= 13 && $rol <= 18) {
        return 'Super Usuario';
    } elseif ($rol >= 2 && $rol <= 6) {
        return 'Usuario';
    } elseif ($rol >= 8 && $rol <= 12) {
        return 'Control';
    } else {
        return 'Desconocido'; // Rol no válido
    }
}

function obtenerRedireccion($rol, $paginaActual, $validated = null, $no_recurrent = null)
{
    global $redirecciones;

    // Obtener el nombre del rol basado en el número de rol
    $nombreRol = obtenerNombreRol($rol);

    // Depuración: Verificar el nombre del rol
    error_log("Rol: $rol, Nombre del rol: $nombreRol");

    // Verificar si el nombre del rol existe en las redirecciones
    if (!isset($redirecciones[$nombreRol][$paginaActual])) {
        error_log("Redirección no encontrada para $nombreRol -> $paginaActual");
        return 'dashboard.php'; // Redirección por defecto si no se encuentra la página
    }

    $regla = $redirecciones[$nombreRol][$paginaActual];

    // Si la regla es un array con condiciones
    if (is_array($regla) && isset($regla['conditions'])) {
        foreach ($regla['conditions'] as $condicion) {
            if ($validated == $condicion['validated'] && $no_recurrent == $condicion['no_recurrent']) {
                error_log("Redirección condicional encontrada: " . $condicion['redirect']);
                return $condicion['redirect'];
            }
        }
        error_log("Redirección por defecto: " . $regla['default']);
        return $regla['default']; // Redirección por defecto si no se cumple ninguna condición
    }

    // Si la regla es una cadena (redirección directa)
    error_log("Redirección directa: " . $regla);
    return $regla;
}

// redirecciones.php
$redirecciones = [
    'Proveedores' => [
        'invoice-list.php' => 'dashboard.php',
        'status-fac.php' => 'dashboard.php',
        'information.php' => 'dashboard.php',
        'calendario.php' => 'suppliers/empresa/dashboard.php'
    ],
    'Super Usuario' => [
        'status-fac.php' => 'dashboard.php',
        'panel.php' => 'dashboard.php',
        'invoice-list.php' => [
        'default' => 'panel.php', // Corregido aquí
        'conditions' => [
            ['validated' => 1, 'no_recurrent' => 0, 'redirect' => 'panel.php'],
            ['validated' => 1, 'no_recurrent' => 2, 'redirect' => 'panel.php'],
        ],
        'calendario.php' => 'super_users/empresa/dashboard.php',
        'fondo-fijo.php' => 'dashboard.php'

    ],
        'invoice-edit.php' => [
            'default' => 'invoice-list.php',
            'conditions' => [
                ['validated' => 1, 'no_recurrent' => 0, 'redirect' => 'invoice-list.php'],
                ['validated' => 1, 'no_recurrent' => 2, 'redirect' => 'invoice-nrec.php'],
            ],
        ],
        'invoice-nrec.php' => 'panel.php',
        'invoice-validated.php' => 'dashboard.php',
        'calendario.php' => 'dashboard.php'
    ],
    'Usuario' => [
        'status-fac.php' => 'dashboard.php',
        'panel.php' => 'dashboard.php',
        'invoice-list.php' => 'panel.php',
        'invoice-edit.php' => [
            'default' => 'invoice-list.php',
            'conditions' => [
                ['validated' => 1, 'no_recurrent' => 0, 'redirect' => 'invoice-list.php'],
                ['validated' => 0, 'no_recurrent' => 2, 'redirect' => 'invoice-nrec.php'],
                ['validated' => 1, 'no_recurrent' => 2, 'redirect' => 'invoice-nrec.php'],
            ],
        ],
        'invoice-nrec.php' => 'panel.php',
        'invoice-validated.php' => 'dashboard.php',
        'calendario.php' => 'users/{$empresa}/dashboard.php',
        'fondo-fijo.php' => 'dashboard.php'

    ],
    'Control' => [
        'invoice-list.php' => 'dashboard.php',
        'invoice-edit.php' => 'invoice-list.php',
        'invoice-up.php' => 'invoice-list.php',
        'invoice-upload.php' => 'invoice-list.php',
        'customer-list.php' => 'dashboard.php',
        'provider-edit.php' => 'customer-list.php',
        'invoice-validated.php' => 'dashboard.php',
    ],
];



?>