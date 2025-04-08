<?php


include('../header.php');
include('../functions.php');
checkAccess(9);

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
				<h4 class="mando">Proveedores</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php getCustomers('BE'); ?> <!-- 1=ABFORTI, 2=BEEXEn, 3=UPPER, 4=INMO, 5=INNOVET -->
			</div>
			<!-- Formulario oculto para enviar el ID del proveedor -->
			<form id="editProviderForm" action="provider-edit.php" method="POST" style="display:none;">
				<input type="hidden" name="id_proveedor" id="providerIdInput">
			</form>
		</div>
	</div>
<div>

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
	include('../footer.php');
?>