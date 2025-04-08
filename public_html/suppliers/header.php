<?php
/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

//check login
include("session.php");
// Verificar si el usuario es proveedor (rol=7) y pertenece al área correcta
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

// Base URL correcta
$base_url = "/suppliers/{$empresa}/";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Red Abforti</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="icon" type="image/png" href="/images/logo.jpg">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../css/AdminLTE.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../css/skin-green.css">
  <!-- Font awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- JS -->
  <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="../js/moment.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.js"></script>
  <script src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>
  <script src="../js/bootstrap.datetime.js"></script>
  <script src="../js/bootstrap.password.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>



  <!-- AdminLTE App -->
  <script src="../js/app.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/bootstrap.datetimepicker.css">
  <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.css">
  <link rel="stylesheet" href="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">
  <link rel="stylesheet" href="../css/styles.css">

</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

      <!--Logo -->
      <a href="<?php echo $base_url; ?>dashboard.php" class="logo">
        <span class="logo-mini"><b>RAF</b></span>
        <span class="logo-lg">
          <img src="/images/logo.jpg" alt="Logo" style="max-width: 150px; max-height: 35px; border-radius: 50%;">
          <b>Red</b> AbForti
        </span>
      </a>

      <!-- Header Navbar -->
      <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
          <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">

            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                <img src="../images/logo.jpg" class="user-image" alt="User Image">
                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                <span class="hidden-xs"><?php echo $_SESSION['login_username']; ?></span>
              </a>
              <ul class="dropdown-menu">
                <!-- Drop down list-->
                <li>
                  <a href="<?php echo $base_url; ?>information.php" class="btn btn-default btn-flat">Datos generales</a>
                </li>
                <li><a href="../logout.php" class="btn btn-default btn-flat">Cerrar Sesion</a></li>

              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>


    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
  <section class="sidebar">
    <ul class="sidebar-menu">
      <a href="<?php echo $base_url; ?>dashboard.php"><li class="header">MENU</li></a>
      <li class="treeview">
        <a href="<?php echo $base_url; ?>dashboard.php"><i class="fa fa-tachometer"></i> <span>Panel</span></a>
      </li>
      <li class="treeview">
        <a href="#"><i class="fa fa-file-text"></i> <span>Facturas</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?php echo $base_url; ?>status-fac.php"><i class="fa fa-plus"></i> Subir Factura</a></li>
          <li><a href="<?php echo $base_url; ?>invoice-list.php"><i class="fa fa-cog"></i> Mis Facturas</a></li>
        </ul>
      </li>
    </ul>
  </section>
</aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">


      <!-- Main content -->
      <section class="content">

        <!-- Your Page Content Here -->