<?php
/*******************************************************************************
*  Red AbForti                                                                 *
*                                                                              *
* Version: 1.0	                                                               *
* Developer:  Cesar Gonzalez                                 				           *
*******************************************************************************/

include_once ('../header.php');
include_once ('../functions.php');
include('../../redirrecciones.php');

checkAccess(17);
$rol = $_SESSION['rol'];
// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-nrec.php', $validated, $no_recurrent);


error_reporting(E_ALL);
ini_set('display_errors', 1);

$postID = $_POST['id'] ?? null;

if ($postID === null) {
    echo "No provider ID provided.";
    exit;
}

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// Output any connection error
if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
}

// Function to fetch data from a table by cfdi_id
function fetchDataByCfdiId($mysqli, $table, $cfdi_id)
{
    $query = "SELECT * FROM $table WHERE cfdi_id = '" . $mysqli->real_escape_string($cfdi_id) . "'";
    $result = $mysqli->query($query);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// Fetch data from cfdi table
$query = "SELECT * FROM cfdi WHERE id = '" . $mysqli->real_escape_string($postID) . "'";
$result = $mysqli->query($query);
$cfdiData = $result->fetch_assoc();
$result->close();

// Fetch provider area from providers table using the RFC of the provider
$providerQuery = "SELECT area FROM providers WHERE rfc = '" . $mysqli->real_escape_string($cfdiData['rfc_emisor']) . "'";
$providerResult = $mysqli->query($providerQuery);
$providerData = $providerResult->fetch_assoc();
$providerArea = $providerData['area'] ?? '';
$providerResult->close();

// Fetch data from related tables
$conceptosData = fetchDataByCfdiId($mysqli, 'conceptos', $postID);
$imRetData = fetchDataByCfdiId($mysqli, 'imRet', $postID);
$imTraData = fetchDataByCfdiId($mysqli, 'imTra', $postID);
$retencionesData = fetchDataByCfdiId($mysqli, 'retenciones', $postID);
$trasladosData = fetchDataByCfdiId($mysqli, 'traslados', $postID);

// Close the connection
$mysqli->close();



$FormaPago = array(
    '01' => '01 (Efectivo)',
    '02' => '02 (Cheque nominativo)',
    '03' => '03 (Transferencia electrónica de fondos)',
    '04' => '04 (Tarjeta de crédito)',
    '05' => '05 (Monedero electrónico)',
    '06' => '06 (Dinero electrónico)',
    '08' => '08 (Vales de despensa)',
    '12' => '12 (Dación en pago)',
    '13' => '13 (Pago por subrogación)',
    '14' => '14 (Pago por consignación)',
    '15' => '15 (Condonación)',
    '17' => '17 (Compensación)',
    '23' => '23 (Novación)',
    '24' => '24 (Confusión)',
    '25' => '25 (Remisión de deuda)',
    '26' => '26 (Prescripción o caducidad)',
    '27' => '27 (A satisfacción del acreedor)',
    '28' => '28 (Tarjeta de débito)',
    '29' => '29 (Tarjeta de servicios)',
    '30' => '30 (Aplicación de anticipos)',
    '31' => '31 (Intermediario pagos)',
    '99' => '99 (Por definir)'
);

$usoCfdi= array(
    'G01' => 'G01(Adquisición de mercancías.',
    'G02' => 'G02(Devoluciones, descuentos o bonificaciones)',
    'G03' => 'G03(Gastos en general)',
    'I01' => 'I01(Construcciones)',
    'I02' => 'I02(Mobiliario y equipo de oficina por inversiones)',
    'I03' => 'I03(Equipo de transporte)',
    'I04' => 'I04(Equipo de computo y accesorios)',
    'I05' => 'I05(Dados, troqueles, moldes, matrices y herramental)',
    'I06' => 'I06(Comunicaciones telefónicas)',
    'I07' => 'I07(Comunicaciones satelitales)',
    'I08' => 'I08(Otra maquinaria y equipo)',
    'D01' => 'D01(Honorarios médicos, dentales y gastos hospitalarios)',
    'D02' => 'D02(Gastos médicos por incapacidad o discapacidad)',
    'D03' => 'D03(Gastos funerales)',
    'D04' => 'D04(Donativos)',
    'D05' => 'D05(Intereses reales efectivamente pagados por créditos hipotecarios)',
    'D06' => 'D06(Aportaciones voluntarias al SAR)',
    'D07' => 'D07(Primas por seguros de gastos médicos)',
    'D08' => 'D08(Gastos de transportación escolar obligatoria)',
    'D09' => 'D09(Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones)',
    'D10' => 'D10(Pagos por servicios educativos)',
    'S01' => 'S01(Sin efectos fiscales)',
    'CP01' => 'CP01(Pagos)',
    'CN01' => 'CN01(Nómina)'
);

$efectoComprobante = array(
    'I' => 'Ingreso',
    'E' => 'Egreso',
    'T'	=> 'Traslado',
    'N' => 'Nómina',        
    'P' => 'Pago'
);

$regimenF = array(
    '601' => '601 (General de Ley Personas Morales)',
    '603' => '603 (Personas Morales con Fines no Lucrativos)',
    '605' => '605 (Sueldos y Salarios e Ingresos Asimilados a Salarios)',
    '606' => '606 (Arrendamiento)',
    '607' => '607 (Régimen de Enajenación o Adquisición de Bienes)',
    '608' => '608 (Demás ingresos)',
    '610' => '610 (Residentes en el Extranjero sin Establecimiento Permanente en México)',
    '611' => '611 (Ingresos por Dividendos (socios y accionistas)',
    '612' => '612 (Personas Físicas con Actividades Empresariales y Profesionales)',
    '614' => '614 (Ingresos por intereses)',
    '615' => '615 (Régimen de los ingresos por obtención de premios)',
    '616' => '616 (Sin obligaciones fiscales)',
    '620' => '620 (Sociedades Cooperativas de Producción que optan por diferir sus ingresos)',
    '621' => '621 (Incorporación Fiscal)',
    '622' => '622 (Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras)',
    '623' => '623 (Opcional para Grupos de Sociedades)',
    '624' => '624 (Coordinados)',
    '625' => '625 (Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas)',
    '626' => '626 (Régimen Simplificado de Confianza)'
    
);

$impuestosT = array(
    '001' => 'ISR',
    '002' => 'IVA',
    '003' => 'IEPS'

);

?>

<style>
   .button-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }

    .area-btn {
        margin: 10px;
    }

    .validate-btn {
        margin-top: 20px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-check {
        margin-bottom: 0.5rem;
    }
    
    .validate-btn {
        margin-top: 1rem;
    }
    .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
</style>

<div class="header-container">
    <!-- Ícono de flecha con enlace -->
    <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
        <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
    </a>

    <!-- Título "Mis facturas" -->
    <h1>Datos de la factura</h1>
</div>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>

<!-- Información de la factura -->
<div class="row">
    <div class="col-xs-6">
        <p><strong>RFC Emisor:</strong> <?php echo $cfdiData['rfc_emisor']; ?></p>
        <p><strong>Nombre Emisor:</strong> <?php echo $cfdiData['nombre_em']; ?></p>
        <p><strong>Folio:</strong> <?php echo $cfdiData['folio']; ?></p>
        <p><strong>RFC receptor:</strong> <?php echo $cfdiData['rfc_receptor']; ?></p>
        <p><strong>Nombre receptor:</strong> <?php echo $cfdiData['nombre_receptor']; ?></p>
        <p><strong>Codigo postal del receptor:</strong> <?php echo $cfdiData['domicilio_fiscal_receptor']; ?></p>
        <p><strong>Regimal fiscal receptor:</strong>
            <?php $regimenFiscal_em = $cfdiData['regimen_fiscal_em'];
            echo isset($regimenF[$regimenFiscal_em]) ? $regimenF[$regimenFiscal_em] : 'Regimen fiscal indefinido' ?>
        </p>
        <p><strong>Uso CFDI:</strong>
            <?php $codigoUsoCfdi = $cfdiData['uso_cfdi'];
            echo isset($usoCfdi[$codigoUsoCfdi]) ? $usoCfdi[$codigoUsoCfdi] : 'Uso de cfdi no definido' ?>
        </p>
        <!-- Añadir otros campos necesarios de la tabla cfdi -->
    </div>
    <div class="col-xs-6">
        <p><strong>Folio fiscal:</strong> <?php echo $cfdiData['uuid']; ?></p>
        <p><strong>Serie:</strong> <?php echo $cfdiData['serie']; ?></p>
        <p><strong>Código postal, fecha y hora de emisión:</strong> <?php echo $cfdiData['lugar_expedicion']; ?></p>
        <?php echo $cfdiData['fecha']; ?>
        <p><strong>Efecto de comprobante:</strong>
            <?php $comprobante = $cfdiData['tipodecomprobante'];
            echo isset($efectoComprobante[$comprobante]) ? $efectoComprobante[$comprobante] : 'Tipo de comprobante indefinido' ?>
        </p>

        <p><strong>Regimen fiscal:</strong>
            <?php $regimenFiscal = $cfdiData['regimen_fiscal_receptor'];
            echo isset($regimenF[$regimenFiscal]) ? $regimenF[$regimenFiscal] : 'Regimen fiscal indefinido' ?>
        </p>

        <p><strong>Exportacion:</strong> <?php echo $cfdiData['exportacion']; ?></p>
    </div>
</div>
<br>
<!-- Tabla de Conceptos -->
<div class="row">
    <div class="col-xs-12 table-responsive">
        <h2>Conceptos</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Clave Prod. Serv.</th>
                    <th>No. Identificación</th>
                    <th>Cantidad</th>
                    <th>Clave Unidad</th>
                    <th>Unidad</th>
                    <th>Valor unitario</th>
                    <th>Importe</th>
                    <th>Descuento</th>
                    <th>Objeto Impuesto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conceptosData as $concepto): ?>
                    <tr>
                        <td><?php echo $concepto['clave_prod_serv']; ?></td>
                        <td><?php echo $concepto['no_identificacion']; ?></td>
                        <td><?php echo $concepto['cantidad']; ?></td>
                        <td><?php echo $concepto['clave_unidad']; ?></td>
                        <td><?php echo $concepto['unidad']; ?></td>
                        <td><?php echo $concepto['valor_unitario']; ?></td>
                        <td><?php echo $concepto['importe']; ?></td>
                        <td><?php echo $concepto['descuento']; ?></td>
                        <td><?php echo $concepto['objeto_imp']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="col-xs-6 table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conceptosData as $concepto): ?>
                    <tr>
                        <td><?php echo $concepto['descripcion']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="col-xs-6 table-responsive">
        <table class="table table">
            <thead>
                <tr>
                    <th>Impuesto</th>
                    <th>Tipo</th>
                    <th>Base</th>
                    <th>Tipo Factor</th>
                    <th>Tasa o Cuota</th>
                    <th>Importe</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($imTraData as $imTra): ?>
                    <tr>
                        <td><?php $impuestosTra = $imTra['impuesto'];
                        echo isset($impuestosT[$impuestosTra]) ? $impuestosT[$impuestosTra] : 'Impuesto indefinido' ?>
                        </td>
                        <td>Traslado</td>
                        <td><?php echo $imTra['base']; ?></td>
                        <td><?php echo $imTra['tipoFactor']; ?></td>
                        <td><?php echo $imTra['tasaOCuota']; ?></td>
                        <td><?php echo $imTra['importe']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($imRetData as $imRet): ?>
                    <tr>
                        <td><?php $impuestosRet = $imRet['impuesto'];
                        echo isset($impuestosT[$impuestosRet]) ? $impuestosT[$impuestosRet] : 'Impuesto indefinido' ?>
                        </td>
                        <td>Retencion</td>
                        <td><?php echo $imRet['base']; ?></td>
                        <td><?php echo $imRet['tipoFactor']; ?></td>
                        <td><?php echo $imRet['tasaOCuota']; ?></td>
                        <td><?php echo $imRet['importe']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Tabla de Traslados -->
<div class="row">
    <div class="col-xs-5 table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Moneda: <span style="padding-left: 80px;"><?php echo $cfdiData['moneda']; ?></span></th>
                </tr>
                <tr>
                    <th>Forma de pago: <span class="spaced-value" style="padding-left: 25px;">
                            <?php
                            // Mostrar la descripción del método de pago basado en el código
                            $codigoMetodoPago = $cfdiData['formapago'];
                            echo isset($FormaPago[$codigoMetodoPago]) ? $FormaPago[$codigoMetodoPago] : 'Método de pago no definido';
                            ?></span>
                    </th>
                </tr>
                <tr>
                    <th>Metodo de pago:
                        <span style="padding-left: 18px;">
                            <?php
                            if ($cfdiData['metodopago'] == 'PUE') {
                                echo 'PUE (Pago en una sola exhibición)';
                            } elseif ($cfdiData['metodopago'] == 'PPD') {
                                echo 'PPD (Pago en parcialidades o diferido)';
                            } else {
                                echo $cfdiData['metodopago']; // En caso de otros valores, muestra el valor tal cual
                            }
                            ?>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>


            </tbody>
        </table>
    </div>
    <div class="col-xs-7 table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">Descripción</th>
                    <th style="width: 30%; text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align: right;">$ <?php echo $cfdiData['subtotal']; ?></td>
                </tr>
                <tr>
                    <td>Impuesto traslados:</td>
                    <td style="text-align: right;">
                        <?php foreach ($trasladosData as $traslados): ?>
                            $ <?php echo $traslados['importe']; ?><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td>Impuesto retenidos:</td>
                    <td style="text-align: right;">
                        <?php foreach ($retencionesData as $retencion): ?>
                            $ <?php echo $retencion['importe']; ?><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td>Total:</td>
                    <td style="text-align: right;">$ <?php echo $cfdiData['total']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="container mt-4">
    <div class="card shadow mb-4">
        <div class="card-header">
            <strong>Escoja el área a la que irá la factura</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <form id="add_invoice_areas" method="POST">
                    <table class="table table-bordered display table-hover" width="100%" cellspacing="0"
                        style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th class="col-5">Área</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $areaNames = [
                                '80' => 'COSTO PRODUCCION',
                                '81' => 'DIRECCION',
                                '82' => 'LOGISTICA',
                                '83' => 'PROYECTOS',
                                '84' => 'PRODUCCION',
                                '85' => 'GCH',
                                '86' => 'CALIDAD',
                                '87' => 'MANTENIMIENTO',
                                '88' => 'CONTROL',
                                '89' => 'ADQUISICIONES',
                                '90' => 'TECNOLOGIAS DE LA INFROMACION',
                                '91' => 'COMERCIAL',
                                '92' => 'ESR',
                                '93' => 'ATENCION AL CLIENTE',
                                '94' => 'COSTEO',
                                '95' => 'DISEÑO',
                                '96' => 'MAQUINADOS',
                                '97' => 'CORPORATIVO'                            
                            ];

                            foreach ($areaNames as $key => $area) {
                                echo '<tr align="center">';
                                echo '<td>' . $area . '</td>';
                                echo '<td><input type="radio" name="selected_areas[]" id="area_' . $key . '" value="' . $key . '" class="area-checkbox"></td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <input type="hidden" name="cfdi_id" id="cfdi_id"
                            value="<?php echo htmlspecialchars($postID); ?>">
                        <input type="hidden" name="action" value="add_invoice_areas">
                        <button type="submit" id="action_add_invoice_areas" class="btn btn-primary">Guardar
                            Áreas</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>




					
<?php include('../footer.php'); ?>