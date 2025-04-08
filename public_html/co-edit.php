<?php
session_start();

include('header_reg.php');
include('functions.php');

// Habilitar la muestra de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener el RFC desde la sesión
$rfc = $_SESSION['renew_rfc'] ?? null;

if ($rfc === null) {
    echo "No provider ID provided.";
    exit;
}

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
}

// the query
$query = "SELECT * FROM providers WHERE rfc = '" . $mysqli->real_escape_string($rfc) . "'";
$result = $mysqli->query($query);

// mysqli select query
if ($result) {
    $row = $result->fetch_assoc();
    if (!$row) {
        echo "No provider found.";
        exit;
    }

    $rfc = $row['rfc'];
    $correo_electronico = $row['correo_electronico'];
    $razon_social = $row['razon_social'];
    $direccion = $row['direccion'];
    $telefono = $row['telefono'];
    $cuenta = $row['cuenta'];
    $clabe = $row['clabe'];
    $swift = $row['swift'];
    $banco = $row['banco'];
    $titular = $row['titular'];
    $referencia = $row['referencia'];
    $concepto = $row['concepto'];
    $constancia = $row['constancia'];
    $opinion = $row['opinion'];
} else {
    echo "No provider found.";
    exit;
}

// close connection
$mysqli->close();
?>

<h1 class="h3 mb-3 text-gray-800" align="center"><strong>EDITAR PROVEEDOR</strong></h1>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="panel-body form-group form-group-sm">
            <form method="POST" id="update_documents" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_documents">
                <input type="hidden" name="rfc" value="<?php echo htmlspecialchars($rfc); ?>">
                <div class="col-xs-12">
                    <div class="panel-heading">
                        <h4 style="margin-left: -30px;">Datos generales</h4>
                    </div>
                </div>
                <div id="persona_moral_fields">
                    <!-- Campos para persona moral -->                    
                    <div class="panel panel-default">
                        <div class="panel-body form-group form-group-sm">
                            <!-- Documentos para persona moral -->
                            <h3 style="margin-left: 10px;">Documentos</h3>
                            <div class="row mb-12">
                                <div class="col-xs-12 col-md-6">
                                    <span class="font-small input-group-text mb-3">Constancia de Situación Fiscal</span>
                                    <div class="mb-3">
                                        <input type="file" id="constancia" name="constancia" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <span class="font-small input-group-text mb-3">Opinión de Situación Fiscal</span>
                                    <div class="mb-3">
                                        <input type="file" id="opinion" name="opinion" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-xs-12 col-md-12">
                            <button id="action_update_documents" type="submit" class="btn btn-success btn-lg btn-block">Actualizar Documentos</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('update_documents').addEventListener('submit', function(event) {
    var constancia = document.getElementById('constancia').files.length;
    var opinion = document.getElementById('opinion').files.length;

    if (!constancia || !opinion) {
        alert('Por favor, sube todos los documentos requeridos.');
        event.preventDefault(); // Evita el envío del formulario
    }
});
</script>

<?php
 include('footer.php');
?>
