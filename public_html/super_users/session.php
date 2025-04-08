<?php
ob_start(); // Inicia el almacenamiento en búfer de salida
include('includes/config.php');

session_start();

if (!isset($_SESSION['login_username']) || !isset($_SESSION['rol'])) {
    header("Location: access_denied.php");
    exit();
}

function checkAccess($required_role, $required_area = null) {
    // El administrador tiene acceso a todas las áreas
    if ($_SESSION['rol'] == 1) {
        return;
    }

    // Verificar el rol del usuario
    if ($_SESSION['rol'] != $required_role) {
        header("Location: access_denied.php");
        exit();
    }

    // Verificar el área solo si es un proveedor (rol 7)
    if ($required_area !== null && $_SESSION['rol'] == 7) {
        $current_url = $_SERVER['REQUEST_URI'];
        $allowed_area = '';

        switch ($required_area) {
            case '1':
                $allowed_area = 'abforti';
                break;
            case '2':
                $allowed_area = 'beexen';
                break;
            case '3':
                $allowed_area = 'upper';
                break;
            case '4':
                $allowed_area = 'inmobiliaria';
                break;
            case '5':
                $allowed_area = 'innovet';
                break;
            default:
                header("Location: access_denied.php");
                exit();
        }

        if (strpos($current_url, $allowed_area) === false) {
            header("Location: access_denied.php");
            exit();
        }
    }
}
?>
