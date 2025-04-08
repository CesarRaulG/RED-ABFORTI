<?php
/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

include_once('../header.php');
include_once('../functions.php');
checkAccess(3);

?>

<h1>Facturas</h1>
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
					<h4>Proveedor</h4><?php countUsersIn('BE') ?>
				</a>
			</div>
		</div>
	</div>

	<div class="col-md-6 mb-3">
		<div class="card">
			<div class="card-header">
				<a href="invoice-nrec.php">
					<h4>No recurrentes</h4><?php countUsersINNR('BE') ?>
				</a>
			</div>
		</div>
	</div>
</div>



<?php
	include('../footer.php');
?>