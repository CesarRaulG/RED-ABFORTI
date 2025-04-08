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
?>

<section class="content">

  <div class="row">
    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-purple" style="cursor: pointer;" onclick="window.location.href='invoice-list.php';">
        <div class="inner">
          <h3><?php
              countUsersValidatedInvoices('AB')
              ?>
          </h3>
          <p>Comprobantes de pago</p>
        </div>
        <div class="icon">
          <i class="ion ion-ios-paper-outline"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-red" style="cursor: pointer;" onclick="window.location.href='invoice-list.php';">
        <div class="inner">
          <h3><?php
              sumTotalDineroByCompanyPrefix('AB')
              ?></h3>
          <p>Cantidad debida</p>
        </div>
        <div class="icon">
          <i class="ion ion-social-usd"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-maroon" style="cursor: pointer;" onclick="window.location.href='customer-list.php';">
        <div class="inner">
          <h3><?php
              countUsersProviders('AB')
              ?></h3>
          <p>Proveedores totales</p>
        </div>
        <div class="icon">
          <i class="ion ion-ios-people"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-maroon" style="cursor: pointer;" onclick="window.location.href='invoice-validated.php';">
        <div class="inner">
          <h3><?php
              countConFac('AB')
              ?></h3>
          <p>Visualizacion de Facturas</p>
        </div>
        <div class="icon">
          <i class="ion ion-ios-people"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <div class="small-box bg-maroon" style="cursor: pointer;" onclick="window.location.href='invoice-validated.php';">
        <div class="inner">
          <h3><?php
              countComp('AB')
              ?></h3>
          <p>Complementos Faltantes</p>
        </div>
        <div class="icon">
          <i class="ion ion-ios-people"></i>
        </div>
      </div>
    </div>
    
  </div>
</section>



<?php
include('../footer.php');
?>