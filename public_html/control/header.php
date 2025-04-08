<?php

/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

//check login
include("session.php");
// Donde $required_role es el rol requerido para esta página
// Verificar si el usuario tiene un rol de usuario válido (2 a 5)

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Red Abforti</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="icon" type="image/png" href="../images/logo.jpg">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../css/AdminLTE.css">

  <link rel="stylesheet" href="../css/skin-green.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
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
      <a href="dashboard.php" class="logo">
        <!--mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
          <b>RAF</b> </span>
        <!--logo for regular state and mobile devices -->
        <span class="logo-lg">
          <img src="../images/logo.jpg" alt="Logo" style="max-width: 150px; max-height: 35px; border-radius: 50%;">
          <b>Red</b> AbForti</span>
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
                <li><a href="../logout.php" class="btn btn-default btn-flat">Cerrar Sesion</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>


    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">

      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
          <a href="dashboard.php">
            <li class="header">MENU</li>
          </a>
          <!-- Menu 0.1 -->
          <li class="treeview">
            <a href="dashboard.php"><i class="fa fa-tachometer"></i> <span>Panel</span>
            </a>
          </li>
          <!-- Menu 1 -->
          <li class="treeview">
            <a href="#"><i class="fa fa-file-text"></i> <span>Facturas</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <!-- <li><a href="invoice-create.php"><i class="fa fa-square-plus"></i>Create Invoice</a></li> -->
              <li><a href="invoice-list.php"><i class="fa fa-cog"></i>Administrar Factura</a></li>
              <!-- <li><a href="#" class="download-csv"><i class="fa fa-download"></i>Download CSV</a></li> -->
              <li><a href="invoice-validated.php"><i class="fa fa-cog"></i>Facturas Pagadas </a></li>
            </ul>
          </li>

          <!-- Menu 3 -->
          <li class="treeview">
            <a href="#"><i class="fa fa-users"></i><span>Proveedores</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <!--  <li><a href="customer-add.php"><i class="fa fa-user-plus"></i>Add Customer</a></li> -->
              <li><a href="customer-list.php"><i class="fa fa-cog"></i>Lista de Proveedores</a></li>
            </ul>
          </li>
        </ul>
        <!-- /.sidebar-menu -->
      </section>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">


      <!-- Main content -->
      <section class="content">