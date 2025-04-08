<?php

include('header.php');



?>

<style>
        .group-1 {
            background-color: #f8d7da; /* Rojo claro */
        }
        .group-2 {
            background-color: #d1ecf1; /* Azul claro */
        }
        .group-3 {
            background-color: #fff3cd; /* Amarillo claro */
        }
        .group-4 {
            background-color: #c3e6cb; /* Verde claro */
        }
    </style>

<h1>Add User</h1>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<div class="message"></div>
</div>

<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>User Information</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<form method="post" id="add_user">
					<input type="hidden" name="action" value="add_user">

					<div class="row">
						<div class="col-xs-4">
							<input type="text" class="form-control margin-bottom required" name="name"
								placeholder="Name">
						</div>
						<div class="col-xs-4">
							<input type="text" class="form-control margin-bottom required" name="username"
								placeholder="Enter username">
						</div>
						<div class="col-xs-4">
							<input type="text" class="form-control margin-bottom required" name="email"
								placeholder="Enter user's email address">
						</div>
					</div>

					<div class="row">
						<div class="col-xs-4">
							<input type="text" class="form-control" name="phone"
								placeholder="Enter user's phone number">
						</div>
						<div class="col-xs-4">
							<input type="password" class="form-control required" name="password" id="password"
								placeholder="Enter user's password">
						</div>
						<div class="col-xs-4">
							<select class="form-control" id="rol" name="rol" required>
								<!-- El value representa el rol que se le va a dar -->
								<option value="0">Seleccione</option>
								<option class="group-1" value="1">Administrador</option>
								
								<option class="group-2" value="2">AB</option>
								<option class="group-2" value="3">BE</option>
								<option class="group-2" value="4">UPPER</option>
								<option class="group-2" value="5">INMO</option>
								<option class="group-2" value="6">INNO</option>

								<option class="group-3" value="8">ABC</option>
								<option class="group-3" value="9">BEC</option>
								<option class="group-3" value="10">UPPERC</option>
								<option class="group-3" value="11">INMOC</option>
								<option class="group-3" value="12">INNOC</option>

								<option class="group-4" value="13">ABSU</option>
								<option class="group-4" value="14">BESU</option>
								<option class="group-4" value="15">UPPERSU</option>
								<option class="group-4" value="16">INMOSU</option>
								<option class="group-4" value="17">INNOSU</option>
							</select>
						</div>
					</div>
					<!-- Campo adicional para seleccionar el área, oculto inicialmente -->
					<div id="areaSelection" class="row" style="display:none;">
						<div class="col-xs-4">
							<select class="form-control" id="area" name="area">
								<option value="0">Seleccione el área</option>
								<!-- Opciones específicas se llenarán dinámicamente -->
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 margin-top btn-group">
							<input type="submit" id="action_add_user" class="btn btn-success float-right"
								value="Add user" data-loading-text="Adding...">
						</div>
					</div>					
				</form>
			</div>
		</div>
	</div>
<div>

<script>
    document.getElementById('rol').addEventListener('change', function() {
        var selectedRole = this.value;
        var areaSelection = document.getElementById('areaSelection');
        var areaSelect = document.getElementById('area');

        // Limpiar opciones anteriores
        areaSelect.innerHTML = '<option value="0">Seleccione el área</option>';

        // Mostrar el campo de selección del área solo para los roles específicos
        switch (selectedRole) {
            case '2': // AB
                areaSelection.style.display = 'block';
                areaSelect.innerHTML += '<option value="1">DIRECCION</option>';
                areaSelect.innerHTML += '<option value="2">ADMINISTRACION</option>';
                areaSelect.innerHTML += '<option value="3">COMERCIAL</option>';
				areaSelect.innerHTML += '<option value="4">CONTROL</option>';
                areaSelect.innerHTML += '<option value="5">TECNOLOGIAS DE LA INFORMACION</option>';
				areaSelect.innerHTML += '<option value="6">GCH</option>';
				areaSelect.innerHTML += '<option value="7">ESR</option>';
				areaSelect.innerHTML += '<option value="8">PDT</option>';				
                break;
            case '4': // UPPER
                areaSelection.style.display = 'block';
                areaSelect.innerHTML += '<option value="13">CEVA</option>';
                areaSelect.innerHTML += '<option value="14">CEDIM</option>';
                areaSelect.innerHTML += '<option value="15">INSUMOS</option>';
				areaSelect.innerHTML += '<option value="16">TRANSPORTE QRO</option>';
                areaSelect.innerHTML += '<option value="17">PIA 10</option>';
                areaSelect.innerHTML += '<option value="18">CVA GDL</option>';
                areaSelect.innerHTML += '<option value="19">CEVA GDL</option>';
                areaSelect.innerHTML += '<option value="20">CVA MTY</option>';
                areaSelect.innerHTML += '<option value="21">CORPORATIVO</option>';
                areaSelect.innerHTML += '<option value="22">QRO</option>';
                areaSelect.innerHTML += '<option value="23">COMERCIAL</option>';
				areaSelect.innerHTML += '<option value="24">GDL SLS</option>';
				areaSelect.innerHTML += '<option value="25">CVA MX</option>';
				areaSelect.innerHTML += '<option value="26">CEDIC</option>';
				areaSelect.innerHTML += '<option value="27">GERENCIA OPERACIONES</option>';
				areaSelect.innerHTML += '<option value="28">CALAMANDA</option>';
				areaSelect.innerHTML += '<option value="29">MANZANILLO</option>';
				areaSelect.innerHTML += '<option value="30">PIA 41</option>';
				areaSelect.innerHTML += '<option value="31">ESR</option>';
				areaSelect.innerHTML += '<option value="32">DIRECCION</option>';
				areaSelect.innerHTML += '<option value="33">COMPLIANCE</option>';
				areaSelect.innerHTML += '<option value="34">STUFFACTORY GDL</option>';



                break;
            case '5': // INMO
                areaSelection.style.display = 'block';
                areaSelect.innerHTML += '<option value="50">PRODUCCION</option>';
                areaSelect.innerHTML += '<option value="51">ADMINISTRACION</option>';
                areaSelect.innerHTML += '<option value="52">VENTAS</option>';
                
                break;
            case '6': // INNO
                areaSelection.style.display = 'block';
                areaSelect.innerHTML += '<option value="80">COSTO PRODUCCION</option>';
                areaSelect.innerHTML += '<option value="81">DIRECCION</option>';
                areaSelect.innerHTML += '<option value="82">LOGISTICA</option>';
				areaSelect.innerHTML += '<option value="83">PROYECTOS</option>';
                areaSelect.innerHTML += '<option value="84">PRODUCCION</option>';
                areaSelect.innerHTML += '<option value="85">GCH</option>';
                areaSelect.innerHTML += '<option value="86">CALIDAD</option>';
                areaSelect.innerHTML += '<option value="87">MANTENIMIENTO</option>';
                areaSelect.innerHTML += '<option value="88">CONTROL</option>';
                areaSelect.innerHTML += '<option value="89">ADQUISICIONES</option>';
                areaSelect.innerHTML += '<option value="90">TECNOLOGIAS DE LA INFROMACION</option>';
				areaSelect.innerHTML += '<option value="91">COMERCIAL</option>';
                areaSelect.innerHTML += '<option value="92">ESR</option>';
                areaSelect.innerHTML += '<option value="93">ATENCION AL CLIENTE</option>';
                areaSelect.innerHTML += '<option value="94">COSTEO</option>';
                areaSelect.innerHTML += '<option value="95">DISEÑO</option>';
                areaSelect.innerHTML += '<option value="96">MAQUINADOS</option>';
                areaSelect.innerHTML += '<option value="97">CORPORATIVO</option>';


                break;
            default:
                areaSelection.style.display = 'none';
                break;
        }
    });
</script>

		<?php
		include('footer.php');
		?>