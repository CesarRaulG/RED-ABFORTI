<?php
// router.php
session_start(); // Iniciar la sesión

// Obtener el código de empresa de POST (si existe)
$cod_empresa = isset($_POST['cod_empresa']) ? $_POST['cod_empresa'] : '';

// Obtener el rol del usuario desde la sesión
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';

// Definir el array de empresas
$empresas = [
    'AB' => 'abforti',
    'UP' => 'upper',
    'IM' => 'inmobiliaria',
    'IN' => 'innovet'
];

// Determinar la empresa basada en el rol si no hay cod_empresa
if (empty($cod_empresa)) {
    if ($rol >= 2 && $rol <= 6) {
        // Usuarios normales (users)
        switch ($rol) {
            case 2:
                $cod_empresa = 'AB'; // Código para abforti
                break;
            case 4:
                $cod_empresa = 'UP'; // Código para upper
                break;
            case 5:
                $cod_empresa = 'IM'; // Código para inmobiliaria
                break;
            case 6:
                $cod_empresa = 'IN'; // Código para innovet
                break;
            default:
                die("Rol no válido o no asignado.");
        }
    } elseif ($rol >= 13 && $rol <= 18) {
        // Super usuarios (super_users)
        switch ($rol) {
            case 13:
                $cod_empresa = 'AB'; // Código para abforti
                break;
            case 15:
                $cod_empresa = 'UP'; // Código para upper
                break;
            case 16:
                $cod_empresa = 'IM'; // Código para inmobiliaria
                break;
            case 17:
                $cod_empresa = 'IN'; // Código para innovet
                break;
            default:
                die("Rol no válido o no asignado.");
        }
    } else {
        die("Rol no válido o no asignado.");
    }
} else {
    // Si hay cod_empresa, validar que sea válido
    $empresa_key = substr($cod_empresa, 0, 2);
    if (!isset($empresas[$empresa_key])) {
        die("Código de empresa no válido.");
    }
}

// Guardar el código de empresa en la sesión
$_SESSION['cod_empresa'] = $cod_empresa;

// Redirigir a calendario.php
header("Location: calendario.php");
exit();
?>