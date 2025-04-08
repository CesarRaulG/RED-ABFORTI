<?php
session_start(); // Iniciar la sesión

// Obtener el código de empresa y el rol de la sesión
$cod_empresa = isset($_SESSION['cod_empresa']) ? $_SESSION['cod_empresa'] : '';
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';

// Definir el array de empresas
$empresas = [
  'AB' => 'abforti',
  'UP' => 'upper',
  'IM' => 'inmobiliaria',
  'IN' => 'innovet'
];

// Determinar la empresa basada en el código
$empresa_key = substr($cod_empresa, 0, 2);
$empresa = isset($empresas[$empresa_key]) ? $empresas[$empresa_key] : 'default';

// Determinar la carpeta basada en el rol
if ($rol >= 2 && $rol <= 6) {
  $carpeta = 'users';
} elseif ($rol == 7) {
  $carpeta = 'suppliers';
} elseif ($rol >= 13 && $rol <= 18) {
  $carpeta = 'super_users';
} else {
  die("Rol no válido o no asignado.");
}

// Construir la ruta del header.php según la carpeta y la empresa
$ruta_header = "/{$carpeta}/header.php";

// Incluir el header.php correspondiente
if (file_exists($_SERVER['DOCUMENT_ROOT'] . $ruta_header)) {
  include($_SERVER['DOCUMENT_ROOT'] . $ruta_header);
} else {
  die("No se encontró el archivo header.php para la empresa y rol seleccionados: " . $ruta_header);
}

include_once("includes/config.php");

// Construir la ruta de regreso al dashboard
$ruta_regreso = "/{$carpeta}/{$empresa}/dashboard.php";



?>
<div class="header-container">
  <a href="<?php echo $ruta_regreso; ?>" class="btn-flecha">
    <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
  </a>
  <h2 class="calendario-titulo">Calendario de Pagos 2025 - <?php echo ucfirst($empresa); ?></h2>
</div>

<div class="calendario-container">
<img src="/images/calendario_pagos_2025.jpg" 
     srcset="/images/calendario_pagos_2025.jpg 800w,
             /images/calendario_pagos_2025.jpg 1200w,
             /images/calendario_pagos_2025.jpg 1600w"
     sizes="(max-width: 768px) 100vw, 800px"
     alt="Calendario de Pagos 2025"
     class="calendario-img">  <br>
</div>
<br>
<!-- Mensaje estático -->
<div class="mensaje-advertencia">
  <p>Nota: A partir de las 15:00 horas, no se recibirán más facturas.</p>
</div>


<style>
  /* Estilos generales */
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  .header-container {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #ddd;
  }

  .btn-flecha {
    text-decoration: none;
    color: #007bff;
    font-size: 24px;
    margin-right: 15px;
  }

  .calendario-titulo {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
  }

  .calendario-container {
    text-align: center;
    margin: 20px auto;
    max-width: 100%;
    padding: 10px;
  }

  .calendario-img {
    width: 100%;
    max-width: 1000px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  }

  .mensaje-advertencia {
  position: fixed;
  bottom: 0;
  left: 230px;
  right: 0;
  background-color:rgb(221, 15, 15);
  border: 1px solidrgb(255, 255, 255);
  color:rgb(255, 255, 255);
  padding: 10px;
  text-align: center;
  font-size: 20px;
  z-index: 2000; /* Asegura que esté por encima de otros elementos */
}

  /* Estilos responsivos */
  @media (max-width: 768px) {
    .header-container {
      flex-direction: column;
      align-items: flex-start;
      padding: 10px;
    }

    .btn-flecha {
      margin-bottom: 10px;
    }

    .calendario-titulo {
      font-size: 20px;
    }

    .mensaje-advertencia {
      margin: 10px;
      font-size: 12px;
    }
  }

  @media (max-width: 480px) {
    .calendario-titulo {
      font-size: 18px;
    }

    .mensaje-advertencia {
      margin: 5px;
      padding: 8px;
      font-size: 12px;
    }
  }
</style>
<?php
include('footer.php'); // Asegúrate de que el footer.php sea común o ajusta su inclusión de manera similar
?>