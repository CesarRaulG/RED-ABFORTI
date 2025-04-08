<?php

/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

include('../header.php');
include('../functions.php');
include_once("../includes/config.php");

checkAccess(6);
$userArea = $_SESSION['area_users'];
?>

<section class="content">
  <!-- Small boxes (Stat box) -->
  <div class="row">
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-green" style="cursor: pointer;" onclick="window.location.href='status-fac.php';">
        <div class="inner">
          <h3>Nueva</h3>
          <p>Factura/No Recurrentes</p>
        </div>
        <div class="icon">
          <i class="ion-plus-round"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-purple" style="cursor: pointer;" onclick="window.location.href='panel.php';">
        <div class="inner">
          <h3><?php
              countUsersNotInvoices('IN')
              ?></h3>
          <p>Facturas faltantes</p>
        </div>
        <div class="icon">
          <i class="ion-document-text"></i>
        </div>

      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-blue" style="cursor: pointer;" onclick="window.location.href='invoice-validated.php';">
        <div class="inner">
          <h3><?php
              countUsersValidatedInvoices('IN')
              ?></h3>
          <p>Facturas validadas</p>
        </div>
        <div class="icon">
          <i class="ion ion-checkmark"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-orange" style="cursor: pointer;" onclick="window.location.href='fondo-fijo.php';">
        <div class="inner">
        <h3>Nueva</h3>
        <p> Factura/Fondos Fijo</p>
        </div>
        <div class="icon">
          <i class="ion-paper-airplane"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-blue" style="cursor: pointer;" onclick="document.getElementById('formCalendario').submit();">
        <div class="inner">
          <h3>Calendario</h3>
          <p>de Pagos</p>
        </div>
        <div class="icon">
          <i class="fa-regular fa-calendar-days"></i>
        </div>
      </div>
    </div>
    <!-- Formulario oculto para enviar el rol por POST -->
    <form id="formCalendario" action="../../router.php" method="POST" style="display: none;">
      <input type="hidden" name="rol" value="<?php echo $_SESSION['rol']; ?>">
    </form>
  </div>
</section>



<?php
include('../footer.php');
?>