<?php


include('header.php');
include('functions.php');

?>

<h1>Lista de proveedores</h1>
<hr>

<div class="row">
	
	<div class="col-xs-12">
		<div id="response" class="alert alert-success" style="display:none;">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<div class="message"></div>
		</div>
	
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>Customers</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php getCustomers(); ?>
			</div>

			<!-- Formulario oculto para enviar el ID del proveedor -->
			<form id="editProviderForm" action="provider-edit.php" method="post" style="display:none;">
				<input type="hidden" name="id_proveedor" id="providerIdInput">
			</form>
		</div>
	</div>
<div>

<div id="delete_customer" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Delete Customer</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this customer?</p>
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
        document.querySelectorAll('.edit-provider').forEach(function(button) {
            button.addEventListener('click', function() {
                var providerId = this.getAttribute('data-id');
                document.getElementById('providerIdInput').value = providerId;
                document.getElementById('editProviderForm').submit();
            });
        });
    });
</script>

<?php
	include('footer.php');
?>