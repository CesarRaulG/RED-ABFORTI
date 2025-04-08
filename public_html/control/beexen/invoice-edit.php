<?php
/*******************************************************************************
*  Red AbForti                                                                 *
*                                                                              *
* Version: 1.0	                                                               *
* Developer:  Cesar Gonzalez                                 				   *
*******************************************************************************/
//checkAccess(8);
//Podria utilizar solo un archivo para todas las empresas. 
include('../header.php');
include('../functions.php');

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
function fetchDataByCfdiId($mysqli, $table, $cfdi_id) {
    $query = "SELECT * FROM $table WHERE cfdi_id = '$cfdi_id'";
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

// Obtener RFC del emisor
$rfc_emisor = $cfdiData['rfc_emisor'];

// Consulta para obtener los días de crédito del proveedor
$query = "SELECT credito FROM providers WHERE rfc = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $rfc_emisor);
$stmt->execute();
$stmt->bind_result($creditDays);
$stmt->fetch();
$stmt->close();

// Fetch data from related tables
$conceptosData = fetchDataByCfdiId($mysqli, 'conceptos', $postID);
$imRetData = fetchDataByCfdiId($mysqli, 'imRet', $postID);
$imTraData = fetchDataByCfdiId($mysqli, 'imTra', $postID);
$retencionesData = fetchDataByCfdiId($mysqli, 'retenciones', $postID);
$trasladosData = fetchDataByCfdiId($mysqli, 'traslados', $postID);

// Mapeo de áreas
$areaMap = [
    1 => 'Control',
    2 => 'GCH',
    3 => 'Informatica',
    4 => 'PDT',
    5 => 'ESR',
    6 => 'Direccion',
    10 => 'Comercial',
    11 => 'Ingenieria',
    12 => 'Administracion',
    13 => 'CEDIM',
    14 => 'PIA 10',
    15 => 'CEVA',
    16 => 'GDL CVA',
    17 => 'GDL SLS',
    18 => 'MX CVA',
    19 => 'CEDIC',
    20 => 'PIA 41',
    21 => 'MANZANILLO',
    22 => 'TRANSPORTE QRO',
    23 => 'CEVA FREIGH',
    24 => 'GDL CVA',
    30 => 'Administracion',
    40 => 'Direccion',
    41 => 'Atencion a clientes',
    42 => 'Logistica',
    43 => 'Mantenimiento',
    44 => 'Adquisiones',
    45 => 'Calidad'
];


// Consulta para obtener las áreas del CFDI junto con las descripciones
$query = "SELECT GROUP_CONCAT(CONCAT(a.area, ' - ', a.descripcion) SEPARATOR ', ') as area_descripciones
          FROM ap_invoice a
          WHERE a.cfdi_id = ?";

// Preparar y ejecutar la declaración
if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $stmt->bind_result($areaDescripcionesString);
    $stmt->fetch();

    // Convertir el resultado en un formato legible para la tabla
    if ($areaDescripcionesString) {
        $areaDescripciones = explode(', ', $areaDescripcionesString);
        
        // Inicializar la tabla HTML
        $tablaHTML = "<div class='table-container'>
                        <table class='styled-table'>
                            <thead>
                                <tr>
                                    <th>Área</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                        <tbody>";

        // Iterar a través de cada área y descripción
        foreach ($areaDescripciones as $areaDescripcion) {
            // Separar el área y la descripción
            list($areaID, $descripcion) = explode(' - ', $areaDescripcion);

            // Mapear el área usando el array $areaMap
            $areaNombre = isset($areaMap[$areaID]) ? $areaMap[$areaID] : "Área desconocida";

            // Añadir cada área y descripción a la tabla
            $tablaHTML .= "<tr>
                              <td>{$areaNombre}</td>
                              <td>{$descripcion}</td>
                           </tr>";
        }

        // Cerrar la tabla HTML
        $tablaHTML .= "</tbody></table></div>";
    } else {
        $tablaHTML = "<p>No hay áreas o descripciones asignadas</p>";
    }

    $stmt->close();
} else {
    $tablaHTML = "<p>Error al obtener las áreas y descripciones</p>";
}



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

    .table th,
    .table td {
        vertical-align: middle;
        text-align: center;
    }

    .align-items-center {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .d-flex {
        display: flex;
        align-items: center;
    }

    .button-container {
        margin: 20px;
        /* Ajusta el valor según el espacio que desees */
    }

    .form-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 100px;
        /* Ajusta el valor para el espacio entre los formularios */
    }

    .highlighted-text {
        background-color: #9cbeff;
        /* Color de fondo llamativo */
        color: #000;
        /* Color del texto */
        font-size: 1.2em;
        /* Tamaño de fuente más grande */
        padding: 10px;
        /* Espacio alrededor del texto */
        border-radius: 5px;
        /* Bordes redondeados */
        text-align: center;
        /* Centrar el texto */
    }

    .highlighted-text strong {
        font-weight: bold;
        /* Texto en negrita */
    }
</style>

<h1>Datos de la factura</h1>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>

    <input type="hidden" name="folio" value="<?php echo $postID; ?>">    

    <!-- Información de la factura -->
    <div class="row">
        <div class="col-xs-6">            
            <p><strong>RFC Emisor:</strong> <?php echo $cfdiData['rfc_emisor']; ?></p>
            <p><strong>Nombre Emisor:</strong> <?php echo $cfdiData['nombre_em']; ?></p>
            <p><strong>Folio:</strong> <?php echo $cfdiData['folio']; ?></p>
            <p><strong>RFC receptor:</strong> <?php echo $cfdiData['rfc_receptor']; ?></p>
            <p><strong>Nombre receptor:</strong> <?php echo $cfdiData['nombre_receptor']; ?></p>
            <p><strong>Codigo postal del receptor:</strong> <?php echo $cfdiData['domicilio_fiscal_receptor']; ?></p>
            <p><strong>Regimal fiscal receptor:</strong> <?php $regimenFiscal_em = $cfdiData['regimen_fiscal_em'];
                                echo isset($regimenF[$regimenFiscal_em]) ? $regimenF[$regimenFiscal_em] : 'Regimen fiscal indefinido' ?></p>
            <p><strong>Uso CFDI:</strong> <?php $codigoUsoCfdi = $cfdiData['uso_cfdi'];
                                echo isset($usoCfdi[$codigoUsoCfdi]) ? $usoCfdi[$codigoUsoCfdi] : 'Uso de cfdi no definido' ?></p>            
            <!-- Añadir otros campos necesarios de la tabla cfdi -->
        </div>
        <div class="col-xs-6">
            <p><strong>Folio fiscal:</strong> <?php echo $cfdiData['uuid']; ?></p>
            <p><strong>Serie:</strong> <?php echo $cfdiData['serie']; ?></p>
            <p><strong>Código postal, fecha y hora de emisión:</strong> <?php echo $cfdiData['lugar_expedicion']; ?></p><?php echo $cfdiData['fecha']; ?>
            <p><strong>Efecto de comprobante:</strong> <?php $comprobante = $cfdiData['tipodecomprobante'];
                                echo isset($efectoComprobante[$comprobante]) ? $efectoComprobante[$comprobante] : 'Tipo de comprobante indefinido' ?></p>

            <p><strong>Regimen fiscal:</strong> <?php $regimenFiscal = $cfdiData['regimen_fiscal_receptor'];
                                echo isset($regimenF[$regimenFiscal]) ? $regimenF[$regimenFiscal] : 'Regimen fiscal indefinido' ?></p>

            <p><strong>Exportacion:</strong> <?php echo $cfdiData['exportacion']; ?></p>
        </div>
    </div>

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
                    <?php foreach ($conceptosData as $concepto) : ?>
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
                    <?php foreach ($conceptosData as $concepto) : ?>
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
                    <?php foreach ($imTraData as $imTra) : ?>
                        <tr>
                             <td><?php $impuestosTra = $imTra['impuesto'];
                                echo isset($impuestosT[$impuestosTra]) ? $impuestosT[$impuestosTra] : 'Impuesto indefinido' ?></td>
                             <td>Traslado</td>
                             <td><?php echo $imTra['base']; ?></td>
                             <td><?php echo $imTra['tipoFactor']; ?></td>
                             <td><?php echo $imTra['tasaOCuota']; ?></td>
                             <td><?php echo $imTra['importe']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($imRetData as $imRet) : ?>
                        <tr>
                             <td><?php $impuestosRet = $imRet['impuesto'];
                                echo isset($impuestosT[$impuestosRet]) ? $impuestosT[$impuestosRet] : 'Impuesto indefinido' ?></td>
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

    <br>
    <div class="row">
        <div class="col-xs-6">
            <p class="highlighted-text">
                Áreas validadas
                <strong><?php echo isset($tablaHTML) ? $tablaHTML : 'No especificado'; ?></strong>
            </p>
        </div>
        <div class="col-xs-6">
            <p class="highlighted-text">
                <strong>Dias de credito: </strong><?php echo isset($creditDays) ? $creditDays : 'No especificado'; ?></p>
        </div>        
    </div>
    <br>
                                                                        
<?php include('../footer.php'); ?>
