<?php
session_start(); // Iniciar la sesión
include('header.php');
include_once("includes/config.php");

// Obtener el código de empresa de la sesión
$cod_empresa = isset($_SESSION['cod_empresa']) ? $_SESSION['cod_empresa'] : '';

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
$empresa = isset($empresas[$empresa_key]) ? $empresas[$empresa_key] : 'default';

// Generar la ruta de regreso
$ruta_regreso = "/suppliers/{$empresa}/dashboard.php";
?>
<div class="header-container">
  <!-- Ícono de flecha con enlace -->
  <a href="<?php echo $ruta_regreso; ?>" class="btn-flecha">
    <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
  </a>

  <h2 class="calendario-titulo">Calendario de Pagos 2025 - <?php echo ucfirst($empresa); ?></h2>

</div>

<div class="calendario-container">
  <img src="/images/calendario_pagos_2025.jpg" alt="Calendario de Pagos 2025" class="calendario-img">
  <br>

</div>



<style>
  .calendario-container {
    text-align: center;
    margin: 20px auto;
    max-width: 100%;
    padding: 10px;
  }

  .calendario-titulo {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
  }

  .calendario-img {
    width: 100%;
    max-width: 1000px;
    /* Para que no se agrande demasiado en pantallas grandes */
    height: auto;
    border-radius: 8px;
    /* Esquinas redondeadas opcionales */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    /* Sombra ligera */
  }
</style>


<?php
include('../footer.php');
?>