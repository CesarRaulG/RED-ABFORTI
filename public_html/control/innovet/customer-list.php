<?php


include('../header.php');
include('../functions.php');
include('../../redirrecciones.php');

checkAccess(12);
// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'customer-list.php', $validated, $no_recurrent);

?>
<div class="header-container">
  <!-- Ícono de flecha con enlace -->
  <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
    <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
  </a>

  <!-- Título "Mis facturas" -->
  <h1>Lista de proveedores</h1>
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
				<h4 class="mando">Proveedores</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php getCustomers('IN'); ?> <!-- 1=ABFORTI, 2=BEEXEn, 3=UPPER, 4=INMO, 5=INNOVET -->
			</div>

			<!-- Formulario oculto para enviar el ID del proveedor -->
			<form id="editProviderForm" action="provider-edit.php" method="post" style="display:none;">
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