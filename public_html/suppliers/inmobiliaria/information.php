<?php
include('../header.php');
include('../functions.php');
include('../../redirrecciones.php');


// Verificar el acceso
checkAccess(7, '4');

// Obtener el id_proveedor desde la sesión
$id_proveedor = $_SESSION['id_proveedor'];
$rol = $_SESSION['rol'];
// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 0; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'information.php', $validated, $no_recurrent);


if ($id_proveedor === null) {
    echo "Proveedor no encontrado.";
    exit;
}

// Conectar a la base de datos y obtener detalles del proveedor
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
}

$query = "SELECT * FROM providers WHERE id_proveedor = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_proveedor);  // 'i' indica que el parámetro es de tipo integer
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $row = $result->fetch_assoc();
    // Asigna los valores a las variables como en tu código original
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
} else {
    echo "No se encontró el proveedor.";
    exit;
}

$mysqli->close();
?>


<div class="header-container">
    <!-- Ícono de flecha con enlace -->
    <!-- Ícono de flecha con enlace -->
    <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
        <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
    </a>

    <!-- Título "Mis facturas" -->
    <h1>Informacion General</h1>
</div>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>


<div class="row">
    <div class="col-xs-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4><?php echo htmlspecialchars($razon_social); ?></h4>
                <div class="clear"></div>
            </div>
            <div class="panel-body form-group form-group-sm">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="col-sm mb-3">
                            <label for="rfc">RFC</label>
                            <input type="text" class="form-control margin-bottom copy-input required" name="rfc" id="rfc" placeholder="RFC" tabindex="1" value="<?php echo htmlspecialchars($rfc); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="direccion">Direccion</label>
                            <input type="text" class="form-control margin-bottom copy-input required" name="direccion" id="direccion" placeholder="Direccion" tabindex="2" value="<?php echo htmlspecialchars($direccion); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="razon_social">Correo Electronico</label>
                            <input type="text" class="form-control copy-input required" name="correo_electronico" id="correo_electronico" placeholder="Correo Electronico" tabindex="4" value="<?php echo htmlspecialchars($correo_electronico); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="col-sm mb-3">
                            <label for="razon_social">Razon Social</label>
                            <input type="text" class="form-control copy-input required" name="razon_social" id="razon_social" placeholder="Razon Social" tabindex="3" value="<?php echo htmlspecialchars($razon_social); ?>" readonly>
                        </div>

                        <br>
                        <div class="col-sm mb-3">
                            <label for="telefono">Telefono</label>
                            <input type="text" class="form-control copy-input required" name="telefono" id="telefono" placeholder="Telefono" tabindex="5" value="<?php echo htmlspecialchars($telefono); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Datos bancarios</h4>
                <div class="clear"></div>
            </div>
            <div class="panel-body form-group form-group-sm">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="col-sm mb-3">
                            <label for="cuenta">Cuenta</label>
                            <input type="text" class="form-control margin-bottom copy-input required" name="cuenta" id="cuenta" placeholder="Cuenta" tabindex="1" value="<?php echo htmlspecialchars($cuenta); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="clabe">Clabe</label>
                            <input type="text" class="form-control margin-bottom copy-input required" name="clabe" id="clabe" placeholder="Clabe" tabindex="2" value="<?php echo htmlspecialchars($clabe); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="moneda">Moneda</label>
                            <input type="text" class="form-control copy-input required" name="moneda" id="moneda" placeholder="Moneda" tabindex="4" value="<?php echo htmlspecialchars($moneda); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3" id="swift-container">
                            <label for="swift">Swift</label>
                            <input type="text" class="form-control copy-input required" name="swift" id="swift" placeholder="Swift" tabindex="3" value="<?php echo htmlspecialchars($swift); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="col-sm mb-3">
                            <label for="banco">Banco</label>
                            <input type="text" class="form-control copy-input required" name="banco" id="banco" placeholder="Banco" tabindex="5" value="<?php echo htmlspecialchars($banco); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="titular">Titular</label>
                            <input type="text" class="form-control copy-input required" name="titular" id="titular" placeholder="Titular" tabindex="5" value="<?php echo htmlspecialchars($titular); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="referencia">Referencia</label>
                            <input type="text" class="form-control copy-input required" name="referencia" id="referencia" placeholder="Referencia" tabindex="5" value="<?php echo htmlspecialchars($referencia); ?>" readonly>
                        </div>
                        <div class="col-sm mb-3">
                            <label for="concepto">Concepto</label>
                            <input type="text" class="form-control copy-input required" name="concepto" id="concepto" placeholder="Concepto" tabindex="5" value="<?php echo htmlspecialchars($concepto); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Días de crédito</h4>
            </div>
            <div class="panel-body form-group form-group-sm">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="col-sm mb-3">
                            <label for="credito">Días de crédito</label>
                            <input type="text" class="form-control margin-bottom copy-input required" name="credito" id="credito" placeholder="Días de crédito" value="<?php echo htmlspecialchars($credito); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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