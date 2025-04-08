<?php
// router.php
session_start(); // Iniciar la sesión

// Obtener el código de empresa de POST
$cod_empresa = isset($_POST['cod_empresa']) ? $_POST['cod_empresa'] : '';

// Tomar los primeros dos caracteres del código de empresa
$empresa_key = substr($cod_empresa, 0, 2);

// Definir el array de empresas
$empresas = [
    'AB' => 'abforti',
    'UP' => 'upper',
    'IM' => 'inmobiliaria',
    'IN' => 'innovet'
];

// Determinar la empresa basada en el código
if (isset($empresas[$empresa_key])) {
    $empresa = $empresas[$empresa_key];
    // Guardar el código de empresa en la sesión
    $_SESSION['cod_empresa'] = $cod_empresa;
    // Redirigir a calendario.php
    header("Location: calendario.php");
    exit();
} else {
    // Manejar el caso en que el código de empresa no sea válido
    die("Código de empresa no válido.");
}
?>