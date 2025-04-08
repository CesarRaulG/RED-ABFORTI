<?php
/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

include('../header.php');
include('../functions.php');
include('../../redirrecciones.php');
checkAccess(10);

$rol = $_SESSION['rol'];

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'provider-edit.php', $validated, $no_recurrent);

$postID = $_POST['id_proveedor'] ?? null;

if ($postID === null) {
    echo "No provider ID provided.";
    exit;
}

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
}

$query = "SELECT * FROM providers WHERE id_proveedor = '". $mysqli->real_escape_string($postID) . "'";
$result = mysqli_query($mysqli, $query);


if($result) {
	while ($row = mysqli_fetch_assoc($result)) {
        $rfc = $row['rfc'];
        $correo_electronico = $row['correo_electronico'];
        $razon_social = $row['razon_social'];
        $direccion = $row['direccion'];
        $telefono = $row['telefono'];        
        $credito = $row['credito'];

        $cuenta = $row['cuenta'];
        $clabe = $row['clabe'];
        $moneda = $row['moneda'];
        $swift = $row['swift'];
        $banco = $row['banco'];
        $titular = $row['titular'];
        $referencia = $row['referencia'];
        $concepto = $row['concepto'];
    }
} else {
    echo "No provider found.";
    exit;
}


$mysqli->close();
?>

<div class="header-container">
  <!-- Ícono de flecha con enlace -->
  <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
    <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
  </a>

  <!-- Título "Mis facturas" -->
  <h1>Proveedor</h1>
</div>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>

<form method="post" id="update_mf">
    <input type="hidden" name="action" value="update_mf">
    <input type="hidden" name="id_proveedor" value="<?php echo $postID; ?>">
    <div class="row">
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="mando">Datos generales (<?php echo htmlspecialchars($razon_social); ?>)</h4>
                    <div class="clear"></div>
                </div>
                <div class="panel-body form-group form-group-sm">
                    <div class="row">
                        <div class="col-xs-6">                            
                            <div class="col-sm mb-3">
                                <label for="rfc">RFC</label>
                                <input type="text" class="form-control margin-bottom copy-input" name="rfc" id="rfc" placeholder="RFC" tabindex="1" value="<?php echo htmlspecialchars($rfc); ?>" readonly>
                            </div>
                            <div class="col-sm mb-3">
                                <label for="direccion">Direccion</label>
                                <input type="text" class="form-control margin-bottom copy-input " name="direccion" id="direccion" placeholder="Direccion" tabindex="2" value="<?php echo htmlspecialchars($direccion); ?>" >
                            </div>
                            <div class="col-sm mb-3">
                                <label for="correo_electronico">Correo Electronico</label>
                                <input type="text" class="form-control copy-input required" name="correo_electronico" id="correo_electronico" placeholder="Correo Electronico" tabindex="4" value="<?php echo htmlspecialchars($correo_electronico); ?>" >
                            </div>                            
                        </div>
                        <div class="col-xs-6">
                            <div class="col-sm mb-3">
                                <label for="razon_social">Razon Social</label>
                                <input type="text" class="form-control copy-input required" name="razon_social" id="razon_social" placeholder="Razon Social" tabindex="3" value="<?php echo htmlspecialchars($razon_social); ?>" readonly >
                            </div>                
                            
                            <br>
                            <div class="col-sm mb-3">
                                <label for="telefono">Telefono</label>
                                <input type="text" class="form-control copy-input required" name="telefono" id="telefono" placeholder="Telefono" tabindex="5" value="<?php echo htmlspecialchars($telefono); ?>" >
                            </div>                                                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="mando">Datos bancarios</h4>
                    <div class="clear"></div>                    
                </div>
                <div class="panel-body form-group form-group-sm">
                    <div class="row">
                        <div class="col-xs-6">                            
                            <div class="col-sm mb-3">
                                <label for="cuenta">Cuenta</label>
                                <input type="text" class="form-control margin-bottom copy-input" name="cuenta" id="cuenta" placeholder="Cuenta" tabindex="1" value="<?php echo htmlspecialchars($cuenta); ?>" >
                            </div>
                            <div class="col-sm mb-3">
                                <label for="clabe">Clabe</label>
                                <input type="text" class="form-control margin-bottom copy-input" name="clabe" id="clabe" placeholder="Clabe" tabindex="2" value="<?php echo htmlspecialchars($clabe); ?>" >
                            </div>
                            <div class="col-sm mb-3">
                                <label for="moneda">Moneda</label>
                                <input type="text" class="form-control copy-input" name="moneda" id="moneda" placeholder="Moneda" tabindex="4" value="<?php echo htmlspecialchars($moneda); ?>" >
                            </div>
                            <div class="col-sm mb-3" id="swift-container">
                                <label for="swift">Swift</label>
                                <input type="text" class="form-control copy-input" name="swift" id="swift" placeholder="Swift" tabindex="3" value="<?php echo htmlspecialchars($swift); ?>" >
                            </div>                            
                        </div>
                        <div class="col-xs-6">                                                                                                    
                            <div class="col-sm mb-3">
                                <label for="banco">Banco</label>
                                <input type="text" class="form-control copy-input" name="banco" id="banco" placeholder="Banco" tabindex="5" value="<?php echo htmlspecialchars($banco); ?>" >
                            </div>
                            <div class="col-sm mb-3">
                                <label for="titular">Titular</label>
                                <input type="text" class="form-control copy-input" name="titular" id="titular" placeholder="Titular" tabindex="5" value="<?php echo htmlspecialchars($titular); ?>" >
                            </div>
                            <div class="col-sm mb-3">
                                <label for="referencia">Referencia</label>
                                <input type="text" class="form-control copy-input" name="referencia" id="referencia" placeholder="Referencia" tabindex="5" value="<?php echo htmlspecialchars($referencia); ?>" >
                            </div>
                            <div class="col-sm mb-3">
                                <label for="concepto">Concepto</label>
                                <input type="text" class="form-control copy-input" name="concepto" id="concepto" placeholder="Concepto" tabindex="5" value="<?php echo htmlspecialchars($concepto); ?>" >
                            </div>                                                     
                        </div>
                    </div>
                </div>        		
            </div>    
        </div>
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="mando">Días de crédito</h4>
                </div>
                <div class="panel-body form-group form-group-sm">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="col-sm mb-3">
                                <label for="credito">Días de crédito</label>
                                <input type="text" class="form-control margin-bottom copy-input" name="credito" id="credito" placeholder="Días de crédito" value="<?php echo htmlspecialchars($credito); ?>">
                            </div>
                        </div>    
                    </div>                                                       
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 margin-top btn-group">
            <input type="submit" id="action_update_mf" class="btn btn-success float-right" value="Actualizar Proveedor" data-loading-text="Actualizando...">
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var monedaInput = document.getElementById('moneda');
        var swiftContainer = document.getElementById('swift-container');
        var swiftInput = document.getElementById('swift');


        function toggleSwiftField() {
            if (monedaInput.value === 'USD') {
                swiftContainer.style.display = 'block';
            } else {
                swiftContainer.style.display = 'none';

            }
        }

        // Call the function initially to set the correct state on page load
        toggleSwiftField();

        // Add an event listener to handle changes
        monedaInput.addEventListener('input', toggleSwiftField);
    });
</script>


<?php
include('../footer.php');
?>
