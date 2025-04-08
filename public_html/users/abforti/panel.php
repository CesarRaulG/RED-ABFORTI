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
checkAccess(2);
$rol = $_SESSION['rol'];
// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'panel.php', $validated, $no_recurrent);

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
	<div id="response" class="alert alert-success" style="display:none;">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<div class="message"></div>
	</div>
</div>
<div class="row">
	<div class="col-md-6 mb-3">
		<div class="card">
			<div class="card-header">
				<a href="invoice-list.php">
					<h4>Proveedor</h4><?php countUsersIn('AB'); ?>
				</a>
			</div>
		</div>
	</div>

	<div class="col-md-6 mb-3">
		<div class="card">
			<div class="card-header">
				<a href="invoice-nrec.php">
					<h4>No recurrentes</h4><?php countUsersINNR('AB'); ?>
				</a>
			</div>
		</div>
	</div>
</div>



<?php
include('../footer.php');
?>