<?php
include('../header.php');
include('../functions.php');
include('../../redirrecciones.php');

checkAccess(12);
$rol = $_SESSION['rol'];

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-upload.php', $validated, $no_recurrent);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoiceIds'])) {
    $invoiceIds = $_POST['invoiceIds'];

    if (empty($invoiceIds)) {
        echo "No se seleccionaron facturas.";
        exit;
    }
    // Mostrar las facturas seleccionadas
    echo '<div class="header-container">
        <!-- Ícono de flecha con enlace -->
        <a href="' . $paginaRedireccion . '" class="btn-flecha">
            <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
        </a>
        <h1>Facturas Seleccionadas</h1>
    </div>';
    $invoiceIdsArray = explode(',', $invoiceIds); // Separar los IDs si vienen en formato CSV
}

$totalFacturas = 0; // Variable para sumar todos los totales


?>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>

<div class="row">
    <?php
    foreach ($invoiceIdsArray as $id) {
        // Obtener datos de la tabla 'cfdi' para cada ID de factura
        $query = "SELECT * FROM cfdi WHERE id = '" . $mysqli->real_escape_string($id) . "'";
        $result = $mysqli->query($query);
        if ($cfdiData = $result->fetch_assoc()) {
            $totalFacturas += $cfdiData['total']; // Sumar el total de cada factura
            ?>
            <div class="col-md-6 table-responsive">
                <h4><strong><?php echo $cfdiData['nombre_em']; ?></strong></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Descripción</th>
                            <th style="width: 30%; text-align: right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total:</td>
                            <td style="text-align: right;">$ <?php echo number_format($cfdiData['total'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mostrar otros detalles de la factura -->
                <p><strong>RFC Emisor:</strong> <?php echo $cfdiData['rfc_emisor']; ?></p>
                <p><strong>Nombre Emisor:</strong> <?php echo $cfdiData['nombre_em']; ?></p>
                <p><strong>Folio:</strong> <?php echo $cfdiData['folio']; ?></p>
                <p><strong>RFC receptor:</strong> <?php echo $cfdiData['rfc_receptor']; ?></p>
                <p><strong>Nombre receptor:</strong> <?php echo $cfdiData['nombre_receptor']; ?></p>
            </div>
            <?php
        }
        $result->close();
    }
    ?>
</div>

<div class="row">
    <div class="col-md-12">
        <h3>Total de las facturas: $ <?php echo number_format($totalFacturas, 2); ?></h3>
    </div>
</div>

<form method="POST" id="upload_more" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload_more">
    <input type="hidden" name="invoiceIds" id="selectedInvoices">
    <input type="hidden" name="rol" value="<?php echo htmlspecialchars($_SESSION['rol']); ?>">

    <?php foreach ($invoiceIdsArray as $id): ?>
        <input type="hidden" name="ids[]" value="<?php echo $id; ?>">
    <?php endforeach; ?>

    <div class="row">
        <div class="col-md-6 col-sm-12 mb-3">
            <label for="compro_pago">Comprobante de pago</label>
            <div>
                <input type="file" id="compro_pago" name="compro_pago" accept=".pdf" class="form-control required"
                    aria-label="file example" required>
            </div>
        </div>        
        <div class="col-md-6 col-sm-12 mb-3">
            <div class="form-group">
                <label for="banco">Seleccione de que banco es el comprobante</label>
                <select name="banco" id="banco" class="form-control required">
                    <option value="">Seleccione</option>
                    <option value="1">Banamex</option>
                    <option value="2">Banco Base Pesos</option>
                    <option value="3">Banco Base Dolares</option>
                    <option value="4">BBVA Pesos</option>
                    <option value="5">Mifel</option>
                    <option value="6">BBVA Dolares</option>
                    <option value="7">Santander</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-md-4">
            <br>
            <div class="mb-3">
                <input type="button" id="action_upload_more" class="btn btn-success float-left" value="Validar">

            </div>
        </div>
    </div>
</form>

<?php include('../footer.php'); ?>
