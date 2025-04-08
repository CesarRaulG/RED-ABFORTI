<?php
/*******************************************************************************
*  Red AbForti                                                                 *
*                                                                              *
* Version: 1.0	                                                               *
* Developer:  Cesar Gonzalez                                 				           *
*******************************************************************************/


include_once('../header.php');
include_once('../functions.php');
include('../../redirrecciones.php');

checkAccess(15);
$rol = $_SESSION['rol'];

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-list.php', $validated, $no_recurrent);
?>

<div class="header-container">
    <!-- Ícono de flecha con enlace -->
    <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
        <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
    </a>

    <!-- Título "Mis facturas" -->
    <h1>Facturas</h1>
</div>
<hr>

<div class="row">
	<div class="col-xs-12">
		<div id="response" class="alert alert-success" style="display:none;">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<div class="message"></div>
		</div>
	
		<div class="panel panel-default">
			<div class="panel-heading">
        <h4 class="grogu">Recibidas</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php getCfdiInvoices('UP'); ?>
			</div>
			<!-- Formulario oculto para enviar el ID del proveedor -->
			<form id="editInvoiceForm" action="invoice-edit.php" method="post" style="display:none;">
				<input type="hidden" name="id" id="customerIdInput">
			</form>
		</div>
	</div>
<div>

<div id="delete_invoice" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Delete Invoice</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this invoice?</p>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Delete</button>
		<button type="button" data-dismiss="modal" class="btn">Cancel</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Añadir event listener a los botones de edición
        document.querySelectorAll('.edit-invoice').forEach(function(button) {
            button.addEventListener('click', function() {
                var customerId = this.getAttribute('data-id');
                document.getElementById('customerIdInput').value = customerId;
                document.getElementById('editInvoiceForm').submit();
            });
        });
    });
</script>

<?php
	include('../footer.php');
?>