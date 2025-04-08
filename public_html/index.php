<?php
include('header-login.php');
?>


<input type="checkbox" id="flip">
<div class="cover">
	<div class="front">
		<img src="images/AB-FORTI.jpg" alt="">
		<div class="text">

		</div>
	</div>
</div>

<div id="response" class="alert alert-success" style="display:none;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<div class="message"></div>
</div>

<div class="forms">
	<div class="form-content">
		<div class="login-form">
			<div class="title">Iniciar Sesion</div>
			<form accept-charset="UTF-8" role="form" method="post" id="login_form">
				<input type="hidden" name="action" value="login">
				<div class="input-boxes">
					<fieldset>
						<div class="input-box">
							<i class="fas fa-user"></i>
							<input class="form-control required" name="username" id="username" type="text" placeholder="Enter Username">
						</div>
						<div class="input-box">
							<i class="fas fa-lock"></i>
							<input class="form-control required" placeholder="Password" name="password" type="password" placeholder="Enter Password">
						</div>
						<!-- Selección de empresa -->
						<div class="input-box">
						

							<select class="form-control" name="cod_empresa">
								<option value="" disabled selected>Seleccione una empresa</option>
								<option value="AB">AB FORTI</option>
								<option value="UP">UPPER</option>
								<option value="IM">INMOBILIARIA</option>
								<option value="IN">INNOVET</option>
							</select>
							
						</div>

						<div class="button input-box">
							<input type="button" id="btn-login" value="Ingresar">
						</div>
						<div class="text sign-up-text">¿No tienes cuenta? <label><a href="registroM.php">Registrate ahora</a></label></div>

					</fieldset>
				</div>
			</form>			
		</div>
	</div>
</div>
</div>

<?php
include('footer-login.php');
?>