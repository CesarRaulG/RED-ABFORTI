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

checkAccess(3);
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
          <p>Factura</p>
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
          countUsersNotInvoices('BE')
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
          countUsersValidatedInvoices('BE')
            ?></h3>

          <p>Facturas validadas</p>
        </div>
        <div class="icon">
          <i class="ion ion-checkmark"></i>
        </div>

      </div>
    </div>


    <!-- ./col -->
    <!-- ./col -->
    <!-- ./col -->

    
    <!-- ./col -->
  </div>
  <!-- /.row -->


</section>
<!-- /.content -->



<?php
include('../footer.php');
?>