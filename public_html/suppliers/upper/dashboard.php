<?php

/*******************************************************************************
 *  Red AbForti                                                                *
 *                                                                             *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				         *
 *******************************************************************************/
include('../header.php');
include('../functions.php');
include_once("../includes/config.php");

// Verificar acceso por rol y área
checkAccess(7, '3');
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
      <div class="small-box bg-purple" style="cursor: pointer;" onclick="window.location.href='invoice-list.php';">
        <div class="inner">
          <h3>
            <?php
            // Asegurarse de que las variables de sesión estén definidas
            if (isset($_SESSION['cod_empresa'], $_SESSION['rfc'])) {
              // Obtener las variables de sesión
              $id_proveedor = $_SESSION['id_proveedor'];
              $cod_empresaP = $_SESSION['cod_empresa'];
              $rfc = $_SESSION['rfc'];

              // Preparar la consulta para contar las facturas de ese proveedor
              $sql = "SELECT COUNT(*) AS total_facturas 
              FROM cfdi 
              WHERE cod_empresa = ? AND rfc_emisor = ?";

              // Preparar la declaración
              $stmt = $mysqli->prepare($sql);

              // Verificar si la preparación fue exitosa
              if ($stmt === false) {
                die('Error al preparar la consulta: ' . $mysqli->error);
              }

              // Vincular parámetros (cod_empresa y rfc_emisor son cadenas)
              $stmt->bind_param("ss", $cod_empresaP, $rfc);

              // Ejecutar la consulta
              $stmt->execute();

              // Vincular el resultado
              $stmt->bind_result($total_facturas);

              // Obtener el resultado
              $stmt->fetch();

              // Mostrar el número de facturas
              echo $total_facturas;

              // Cerrar la declaración
              $stmt->close();
            } else {
              echo "Error: No se encuentran definidas las variables de sesión necesarias.";
            }
            ?>
          </h3>

          <p>Facturas</p>
        </div>
        <div class="icon">
          <i class="ion-document-text"></i>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
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

    <!-- Formulario oculto para enviar cod_empresa por POST -->
    <form id="formCalendario" action="../../router.php" method="POST" style="display: none;">
      <input type="hidden" name="cod_empresa" value="<?php echo $_SESSION['cod_empresa']; ?>">
    </form>
    
  </div>

  

</section>
<!-- /.content -->



<?php
include('../footer.php');
?>