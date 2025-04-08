<?php
include_once("../includes/config.php");
include_once("header.php");

// Consulta para obtener los proveedores
function getCustomers($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Salida de cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // La consulta para seleccionar proveedores donde el prefijo del código de empresa coincida
    $query = "SELECT * FROM providers WHERE cod_empresa LIKE ? ORDER BY razon_social ASC";

    // Preparar la consulta
    $stmt = $mysqli->prepare($query);
    $companyPrefix = $companyPrefix . '%'; // Agregar el comodín para buscar por prefijo
    $stmt->bind_param("s", $companyPrefix); // 's' especifica el tipo de parámetro como string
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        print '<table class="table table-striped table-hover table-bordered" id="data-table"><thead><tr>
                <th>Razon Social</th>
                <th>Correo electronico</th>
                <th>Telefono</th>
                <th>Action</th>
              </tr></thead><tbody>';

        while ($row = $results->fetch_assoc()) {
            print '
                <tr>
                    <td>' . $row["razon_social"] . '</td>
                    <td>' . $row["correo_electronico"] . '</td>
                    <td>' . $row["telefono"] . '</td>
                    <td>
                        <button class="btn btn-primary btn-xs edit-provider" data-id="' . $row["id_proveedor"] . '">
                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                        </button>
                        <button class="btn btn-danger btn-xs delete-customer" data-id="' . $row["id_proveedor"] . '">
                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>';
        }

        print '</tbody></table>';
    } else {
        echo "<p>No hay proveedores para mostrar</p>";
    }

    // Liberar la memoria asociada con un resultado
    $results->free();
    $stmt->close();

    // Cerrar la conexión
    $mysqli->close();
}

function getCfdiInvoices($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta SQL
    $query = "SELECT 
                id,
                folio, 
                nombre_em, 
                serie, 
                total, 
                rfc_emisor                
              FROM cfdi
              WHERE cod_empresa LIKE ? 
              AND ((validated = 2 AND no_recurrent = 0) OR
               (validated = 1 AND no_recurrent = 3)
              )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener los resultados
        $results = $stmt->get_result();

        // Verificar si hay resultados
        if ($results->num_rows > 0) {

            // Iniciar la tabla HTML con una nueva columna "Generar TXT"
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th> <!-- Checkbox para seleccionar todas -->
                            <th>Proveedor</th>
                            <th>Folio</th>
                            <th>Serie</th>
                            <th>Total</th>
                            <th>Acciones</th>   
                            <th>Generar TXT</th> <!-- Nueva columna -->
                        </tr>
                    </thead>
                    <tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while ($row = $results->fetch_assoc()) {
                // Formatear el total como cantidad con separadores de miles y decimales
                $totalFormateado = number_format($row["total"], 2);
                print '
                    <tr>
                        <td><input type="checkbox" class="select-invoice" value="' . $row["id"] . '"></td> <!-- Checkbox para cada factura -->
                        <td>' . $row["nombre_em"] . '</td>
                        <td>' . $row["folio"] . '</td>
                        <td>' . $row["serie"] . '</td>
                        <td>$' . $totalFormateado . '</td> <!-- Mostrar el total formateado -->                        
                        <td>
                            <button class="btn btn-primary btn-xs edit-invoice" data-id="' . $row["id"] . '">
                                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                            </button>                         
                            <button class="btn btn-info btn-xs up-invoice" data-id="' . $row["id"] . '">
                                <span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>                        
                            </button>  
                        </td>
                        <td> <!-- Nueva celda para el botón "Generar TXT" -->
                            <form action="../gen_txt.php" method="POST" style="margin: 0;">                    
                                <input type="hidden" name="action" value="factura_id">
                                <input type="hidden" name="factura_id" value="' . htmlspecialchars($row["id"]) . '">
                                <button type="submit" class="btn btn-light btn-xs">Generar TXT</button>
                            </form>                          
                        </td>
                    </tr>';
            }
            print '</tbody></table>';
        } else {
            // Mostrar mensaje si no hay resultados
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar la memoria asociada con los resultados
        $results->free();
        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}




//Consulta para sumar el total del dinero en las facturas
function sumTotalDineroByCompanyPrefix($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para sumar el total del dinero en las facturas que coinciden con los primeros dos dígitos del código de empresa
    $query = "SELECT SUM(total) AS total_dinero
              FROM cfdi              
              WHERE SUBSTRING(cod_empresa, 1, 2) = ?
              AND (
                (validated = 1 AND no_recurrent = 3 ) OR 
                (validated = 2 AND no_recurrent = 0)
              )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Solo los primeros dos dígitos
        $companyPrefix = substr($companyPrefix, 0, 2);
        // Ligar parámetros
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_dinero);
        $stmt->fetch();

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();

    // Si no hay facturas, el valor puede ser NULL, así que lo ajustamos a 0
    if ($total_dinero === null) {
        $total_dinero = 0;
    }

    // Formatear el número con separadores de miles y decimales, por ejemplo 10,000.00
    echo number_format($total_dinero, 2, '.', ',');
}

//Numero total de proveedores
function countUsersProviders($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar los proveedores que coinciden con el prefijo de la empresa
    $query = "SELECT COUNT(*) AS total_providers
              FROM providers
              WHERE SUBSTRING(cod_empresa, 1, 2) = ?";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_providers);
        $stmt->fetch();

        // Mostrar el número total de proveedores
        echo $total_providers;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contar solo las facturas validadas
function countUsersValidatedInvoices($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }


    // Consulta para contar solo las facturas validadas
    $query = "SELECT COUNT(*) AS total_validated
              FROM cfdi                
              WHERE SUBSTRING(cod_empresa, 1, 2) = ? 
              AND (
                (validated = 2 AND no_recurrent = 0) OR 
                (validated = 1 AND no_recurrent = 3)
                )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas validadas
        echo $total_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

function getCfdiCPValidated($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Verificar la conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Obtener rutas base
    $basePaths = [
        'AB' => '/control/abforti/uploads/Bancos/',
        'BE' => '/control/beexen/uploads/Bancos/',
        'IN' => '/control/innovet/uploads/Bancos/',
        'UP' => '/control/upper/uploads/Bancos/',
        'IM' => '/control/inmobiliaria/uploads/Bancos/',
    ];
    $rutaBase = $basePaths[$companyPrefix] ?? null;

    if (!$rutaBase) {
        echo "<p>Prefijo de empresa no válido: $companyPrefix</p>";
        return;
    }

    // Obtener rutas de facturas
    $empresas = [
        'AB' => '../../suppliers/abforti/uploads/factura/',
        'BE' => '../../suppliers/beexen/uploads/factura/',
        'IN' => '../../suppliers/innovet/uploads/factura/',
        'UP' => '../../suppliers/upper/uploads/factura/',
        'IM' => '../../suppliers/inmobiliaria/uploads/factura/',
    ];
    $rutaCom = $empresas[$companyPrefix] ?? null;

    if (!$rutaCom) {
        die('El prefijo del cod_empresa no tiene una ruta configurada.');
    }

    // Consulta SQL
    $query = "SELECT cfdi.id, cfdi.folio, cfdi.nombre_em, cfdi.serie, cfdi.total, cfdi.ruta_com_pago,
                     cfdi.factura_xml, cfdi.factura_pdf, cfdi.validated, cfdi.no_recurrent, cfdi.cod_empresa, 
                     cfdi.fecha_registro, cfdi.fecha_pagada, cfdi.metodopago, complements.comple, complements.ruta_xml, complements.ruta_pdf
              FROM cfdi
              LEFT JOIN complements ON cfdi.id = complements.cfdi_id
              WHERE cfdi.cod_empresa LIKE ?
              AND ((cfdi.validated = 3 AND cfdi.no_recurrent = 0) OR
                   (cfdi.validated = 1 AND cfdi.no_recurrent = 4))
              ORDER BY cfdi.ruta_com_pago";

    // Preparar y ejecutar la consulta
    if ($stmt = $mysqli->prepare($query)) {
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);
        $stmt->execute();
        $results = $stmt->get_result();

        if ($results->num_rows > 0) {
            // Iniciar la tabla
            echo '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0">
                  <thead>
                      <tr>
                          <th>Proveedor</th>
                          <th>Folio</th>
                          <th>Serie</th>
                          <th>Total</th>
                          <th>Metodo de Pago</th>
                          <th>Factura</th>
                          <th id="document-field">Complemento de Pago</th>
                      </tr>
                  </thead>
                  <tbody>';

            $ultimoDocumento = '';
            $rows = [];

            // Almacenar filas para calcular rowspan
            while ($row = $results->fetch_assoc()) {
                $rows[] = $row;
            }

            // Construir la tabla
            foreach ($rows as $index => $row) {
                $rutaFacturaXML = getRutaFactura($row, 'xml');
                $rutaFacturaPDF = getRutaFactura($row, 'pdf');
                $rutaCompletaXML = $_SERVER['DOCUMENT_ROOT'] . '/' . $rutaFacturaXML;
                $rutaCompletaPDF = $_SERVER['DOCUMENT_ROOT'] . '/' . $rutaFacturaPDF;

                // Si el documento es nuevo, calcular rowspan
                if ($row['ruta_com_pago'] !== $ultimoDocumento) {
                    $ultimoDocumento = $row['ruta_com_pago'];
                    $rowspanCount = count(array_filter($rows, function ($r) use ($ultimoDocumento) {
                        return $r['ruta_com_pago'] === $ultimoDocumento;
                    }));

                    // Mostrar la primera fila del nuevo documento
                    echo '<tr>
                          <td>' . $row["nombre_em"] . '</td>
                          <td>' . $row["folio"] . '</td>
                          <td>' . $row["serie"] . '</td>
                          <td>$' . number_format($row["total"], 2) . '</td>
                          <td>' . $row["metodopago"] . '</td>
                          <td>' . getBotonesFactura($rutaFacturaXML, $rutaFacturaPDF) . '</td>
                          <td rowspan="' . $rowspanCount . '">' . getComplementoPago($row, $rutaBase, $rutaCom) . '</td>
                      </tr>';
                } else {
                    // Mostrar filas adicionales para el mismo documento
                    echo '<tr>
                          <td>' . $row["nombre_em"] . '</td>
                          <td>' . $row["folio"] . '</td>
                          <td>' . $row["serie"] . '</td>
                          <td>$' . number_format($row["total"], 2) . '</td>
                          <td>' . $row["metodopago"] . '</td>
                          <td>' . getBotonesFactura($rutaFacturaXML, $rutaFacturaPDF) . '</td>
                          <td style="display: none;"></td>
                      </tr>';
                }
            }

            echo '</tbody></table>';
        } else {
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar resultados
        $results->free();
        $stmt->close();
    }

    // Cerrar conexión
    $mysqli->close();
}

// Función auxiliar para obtener la ruta de la factura
function getRutaFactura($row, $type)
{
    $basePathsEmpresa = [
        'AB' => 'abforti',
        'BE' => 'beexen',
        'IN' => 'innovet',
        'UP' => 'upper',
        'IM' => 'inmobiliaria',
    ];

    $empresa = $basePathsEmpresa[substr($row['cod_empresa'], 0, 2)] ?? null;
    if (!$empresa) {
        return null;
    }

    $fecha = new DateTime($row['fecha_registro']);
    $year = $fecha->format('Y');
    $month = $fecha->format('m');
    $day = $fecha->format('d');

    if (($row['validated'] == 3 || $row['validated'] == 2) && $row['no_recurrent'] == 0) {
        return "control/$empresa/uploads/factura/$year/$month/$day/" . ($type === 'xml' ? $row['factura_xml'] : $row['factura_pdf']);
    } else {
        return "control/$empresa/uploads/factura/$year/$month/NO_RECURRENTES/$day/" . ($type === 'xml' ? $row['factura_xml'] : $row['factura_pdf']);
    }
}

// Función auxiliar para generar botones de factura
function getBotonesFactura($rutaXML, $rutaPDF)
{
    $botones = '<div class="button-group" style="display: flex; gap: 10px;">';

    if ($rutaXML && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaXML)) {
        $botones .= '<button class="btn btn-primary btn-xs open-xml-modal" data-file="/' . $rutaXML . '" data-toggle="modal" data-target="#xmlModal">
                     <span class="ti ti-file-type-xml" style="font-size: 20px;" aria-hidden="true"></span>
                     </button>';
    } else {
        $botones .= '<span class="text-muted">No disponible</span>';
    }

    if ($rutaPDF && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaPDF)) {
        $botones .= '<button class="btn btn-warning btn-xs open-pdf-modal" data-file="/' . $rutaPDF . '" data-toggle="modal" data-target="#pdfModal">
                     <span class="ti ti-file-type-pdf" style="font-size: 20px;" aria-hidden="true"></span>
                     </button>';
    } else {
        $botones .= '<span class="text-muted">No disponible</span>';
    }

    $botones .= '</div>';
    return $botones;
}

// Función auxiliar para generar el botón de TXT
function getBotonGenerarTXT($facturaId)
{
    return '<form action="../gen_txt.php" method="POST" style="margin: 0;">
            <input type="hidden" name="action" value="factura_id">
            <input type="hidden" name="factura_id" value="' . htmlspecialchars($facturaId) . '">
            <button type="submit" class="btn btn-light btn-sm">Generar TXT</button>
            </form>';
}

// Función auxiliar para generar el complemento de pago
function getComplementoPago($row, $rutaBase, $rutaCom)
{
    $complemento = '<div class="button-group" style="display: flex; gap: 10px;">';

    // Caso 1: Validated = 3 y No_Recurrent = 0
    if (($row['validated'] == 3 && $row['no_recurrent'] == 0) || ($row['validated'] == 1 && $row['no_recurrent'] == 4)) {
        $rutaArchivo = $rutaBase . date('Y', strtotime($row['fecha_pagada'])) . "/" . date('m', strtotime($row['fecha_pagada'])) . "/" . $row['ruta_com_pago'];

        $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . $rutaArchivo;

        if (file_exists($rutaCompleta)) {
            $complemento .= '<button class="btn btn-primary btn-xs open-pdf-modal" data-file="' . $rutaArchivo . '" data-toggle="modal" data-target="#pdfModal">
                             <span class="glyphicon glyphicon-file" aria-hidden="true"></span> Comprobante
                             </button>';
        } else {
            $complemento .= '<span class="text-muted">Documento no encontrado</span>';
        }
    } else {
        $complemento .= '<span class="text-muted">No pagada</span>';
    }

    // Caso 2: Pago con método PPD y complemento disponible
    if ($row['metodopago'] === 'PPD') {
        if ($row['comple'] == 1) {
            $rutaXml = $rutaCom . $row['ruta_xml'];
            $rutaPdf = $rutaCom . $row['ruta_pdf'];

            $complemento .= '<button class="btn btn-success btn-xs open-xml-modal" data-file="' . $rutaXml . '" data-toggle="modal" data-target="#xmlModal">
                             <span class="ti ti-file-type-xml" style="font-size: 20px;" aria-hidden="true"></span>
                             </button>';
            $complemento .= '<button class="btn btn-warning btn-xs open-pdf-modal" data-file="' . $rutaPdf . '" data-toggle="modal" data-target="#pdfModal">
                             <span class="ti ti-file-type-pdf" style="font-size: 20px;" aria-hidden="true"></span>
                             </button>';
        } else {
            $complemento .= '<span class="text-muted">No disponibles</span>';
        }
    }

    $complemento .= '</div>';
    return $complemento;
}



//contar solo las facturas validadas campo visualizacion de facturas
function countConFac($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }


    // Consulta para contar solo las facturas validadas
    $query = "SELECT COUNT(*) AS total_validated
              FROM cfdi                
              WHERE SUBSTRING(cod_empresa, 1, 2) = ? 
              AND (
                (validated = 3 AND no_recurrent = 0) OR 
                (validated = 1 AND no_recurrent = 4)
                )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas validadas
        echo $total_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contar complementos
function countComp($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Inicializar el resultado como 0
    $total_complement = 0;

    // Consulta para contar las facturas según las condiciones dadas
    $query = "SELECT COUNT(*) AS total_complement
              FROM cfdi
              LEFT JOIN complements ON cfdi.id = complements.cfdi_id
              WHERE SUBSTRING(cfdi.cod_empresa, 1, 2) = ? 
                AND cfdi.validated = 3 
                AND cfdi.no_recurrent = 0 
                AND (complements.comple IS NULL OR complements.comple != 1)
                AND DATEDIFF(NOW(), cfdi.fecha_pagada) > 5";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_complement);
        $stmt->fetch();

        echo $total_complement;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}
