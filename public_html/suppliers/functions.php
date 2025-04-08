<?php
include_once("includes/config.php");


function getInvoices($cod_empresa, $rfc)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
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
    $cod_prefix = substr($cod_empresa, 0, 2);
    $rutaBase = $basePaths[$cod_prefix] ?? null;

    if (!$rutaBase) {
        die('El prefijo del cod_empresa no tiene una ruta configurada.');
    }

    // Obtener rutas de facturas
    $empresas = [
        'AB' => '/suppliers/abforti/uploads/factura/',
        'BE' => '/suppliers/beexen/uploads/factura/',
        'IN' => '/suppliers/innovet/uploads/factura/',
        'UP' => '/suppliers/upper/uploads/factura/',
        'IM' => '/suppliers/inmobiliaria/uploads/factura/',
    ];
    $rutaCom = $empresas[$cod_prefix] ?? null;

    if (!$rutaCom) {
        die('El prefijo del cod_empresa no tiene una ruta configurada.');
    }

    // Consulta SQL
    $query = "SELECT cfdi.id AS id, cfdi.nombre_em, cfdi.serie, cfdi.folio, cfdi.total, cfdi.fecha_registro, 
                     cfdi.fecha_confirmada, cfdi.fecha_pagada, cfdi.ruta_com_pago, cfdi.validated, cfdi.no_recurrent, 
                     cfdi.metodopago, cfdi.factura_xml, cfdi.factura_pdf, cfdi.cod_empresa, complements.comple, complements.ruta_xml, complements.ruta_pdf
              FROM cfdi
              LEFT JOIN complements ON cfdi.id = complements.cfdi_id
              WHERE cfdi.cod_empresa = ? AND cfdi.rfc_emisor = ?
              ORDER BY cfdi.ruta_com_pago, cfdi.folio";

    // Preparar y ejecutar la consulta
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ss", $cod_empresa, $rfc);
        $stmt->execute();
        $results = $stmt->get_result();

        if ($results->num_rows > 0) {
            // Iniciar la tabla
            echo '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0">
                  <thead>
                      <tr>
                          <th>Nombre</th>
                          <th>Serie</th>
                          <th>Folio</th>
                          <th>Total Factura</th>
                          <th>Factura</th>
                          <th>Ingresada</th>
                          <th>Validada</th>
                          <th>Pagada</th>
                          <th>Acciones</th>
                      </tr>
                  </thead>
                  <tbody>';

            $ultimo_complemento = '';
            $rows_buffer = [];
            $rowspan_count = 0;

            // Procesar cada fila de resultados
            while ($row = $results->fetch_assoc()) {
                $fecha_registro = !empty($row["fecha_registro"]) ? date("d-M-y H:i", strtotime($row["fecha_registro"])) : '';
                $fecha_confirmada = !empty($row["fecha_confirmada"]) ? date("d-M-y H:i", strtotime($row["fecha_confirmada"])) : '';
                $fecha_pagada = !empty($row["fecha_pagada"]) ? date("d-M-y H:i", strtotime($row["fecha_pagada"])) : '';

                
                // Obtener el año y mes de la fecha_pagada
                $year = !empty($row['fecha_pagada']) ? date('Y', strtotime($row['fecha_pagada'])) : date('Y'); // Usar año actual si fecha_pagada es null
                $month = !empty($row['fecha_pagada']) ? date('m', strtotime($row['fecha_pagada'])) : date('m'); // Usar mes actual si fecha_pagada es null

                $rutaArchivo = $rutaBase . $year . "/" . $month . "/" . $row['ruta_com_pago'];
                $rutaPDF = $rutaCom . $row['ruta_pdf'];
                $rutaXML = $rutaCom . $row['ruta_xml'];
                $totalFormateado = number_format($row["total"], 2);

                // Obtener rutas de facturas (XML y PDF)
                $rutaFacturaXML = getRutaFactura($row, 'xml');
                $rutaFacturaPDF = getRutaFactura($row, 'pdf');

                // Verificar si el complemento de pago cambió
                if ($ultimo_complemento !== $row['ruta_com_pago']) {
                    // Imprimir las filas almacenadas previamente con rowspan
                    if ($rowspan_count > 0) {
                        imprimirFilasConRowspan($rows_buffer, $rowspan_count);
                    }

                    // Reiniciar el buffer y el contador de rowspan
                    $rows_buffer = [];
                    $rowspan_count = 0;
                    $ultimo_complemento = $row['ruta_com_pago'];
                }

                // Almacenar la fila actual en el buffer
                $rows_buffer[] = [
                    'nombre_em' => $row["nombre_em"],
                    'serie' => $row["serie"],
                    'folio' => $row["folio"],
                    'total' => $totalFormateado,
                    'rutaCompletaPDF' => $rutaFacturaPDF, // Pasar la ruta del PDF directamente
                    'rutaCompletaXML' => $rutaFacturaXML, // Pasar la ruta del XML directamente
                    'fecha_registro' => $fecha_registro,
                    'fecha_confirmada' => $fecha_confirmada,
                    'fecha_pagada' => $fecha_pagada,
                    'acciones' => generarAcciones($row, $rutaArchivo, $rutaXML, $rutaPDF)
                ];

                $rowspan_count++;
            }

            // Imprimir las filas restantes
            if ($rowspan_count > 0) {
                imprimirFilasConRowspan($rows_buffer, $rowspan_count);
            }

            echo '</tbody></table>';
        } else {
            echo "<p>No hay facturas para mostrar.</p>";
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

    if ($row['validated'] == 3 && $row['no_recurrent'] == 0) {
        return "control/$empresa/uploads/factura/$year/$month/$day/" . ($type === 'xml' ? $row['factura_xml'] : $row['factura_pdf']);
    } elseif ($row['validated'] == 0 && $row['no_recurrent'] == 0) {
        return "suppliers/$empresa/uploads/factura/$year/$month/$day/" . ($type === 'xml' ? $row['factura_xml'] : $row['factura_pdf']);
    } else {
        return null;
    }
}

// Función auxiliar para generar botones de factura 

function getBotonesFactura($rutaXML, $rutaPDF)
{
    $botones = '<div class="button-group" style="display: flex; gap: 10px;">';

    if ($rutaXML && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaXML)) {
        $botones .= '<button class="btn btn-primary btn-xs open-xml-modal" data-file="/' . $rutaXML . '" data-toggle="modal" data-target="#xmlModal">
                     <span class="ti ti-file-type-xml" style="font-size: 20px;" aria-hidden="true"></span>XML
                     </button>';
    } else {
        $botones .= '<span class="text-muted">No disponible</span>';
    }

    if ($rutaPDF && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaPDF)) {
        $botones .= '<button class="btn btn-warning btn-xs open-pdf-modal" data-file="/' . $rutaPDF . '" data-toggle="modal" data-target="#pdfModal">
                     <span class="ti ti-file-type-pdf" style="font-size: 20px;" aria-hidden="true"></span>PDF
                     </button>';
    } else {
        $botones .= '<span class="text-muted">No disponible</span>';
    }

    $botones .= '</div>';
    return $botones;
}

// Función auxiliar para generar acciones
function generarAcciones($row, $rutaArchivo, $rutaXML, $rutaPDF)
{
    $acciones = '<div class="button-group" style="display: flex; gap: 10px;">';

    if ($row['validated'] == 3 && $row['no_recurrent'] == 0) {
        if ($row['metodopago'] === 'PPD') {
            if ($row['comple'] == 0) {
                $acciones .= '<button class="btn btn-primary btn-xs open-pdf-modal" data-file="' . $rutaArchivo . '" data-toggle="modal" data-target="#pdfModal">
                              <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                          </button>';
                $acciones .= '<button class="btn btn-primary open-upload-modal" data-toggle="modal" data-target="#uploadModal" data-id="' . $row['id'] . '">
                                  <span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Subir Archivos
                              </button>';
            }
            if ($row['comple'] == 1) {
                $acciones .= '<button class="btn btn-primary btn-xs open-pdf-modal" data-file="' . $rutaArchivo . '" data-toggle="modal" data-target="#pdfModal">
                              <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                          </button>';
                $acciones .= '<button class="btn btn-success btn-xs open-xml-modal" data-file="' . $rutaXML . '" data-toggle="modal" data-target="#xmlModal">
                                  <span class="glyphicon glyphicon-file" aria-hidden="true"></span> XML
                              </button>';
                $acciones .= '<button class="btn btn-warning btn-xs open-pdf-modal" data-file="' . $rutaPDF . '" data-toggle="modal" data-target="#pdfModal">
                                  <span class="glyphicon glyphicon-file" aria-hidden="true"></span> PDF
                              </button>';
            }
        } else {
            $acciones .= '<button class="btn btn-primary btn-xs open-pdf-modal" data-file="' . $rutaArchivo . '" data-toggle="modal" data-target="#pdfModal">
                              <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                          </button>';
        }
    } else {
        $acciones .= '<span class="text-muted">No disponible</span>';
    }

    $acciones .= '</div>';
    return $acciones;
}

// Función auxiliar para imprimir filas con rowspan
function imprimirFilasConRowspan($rows_buffer, $rowspan_count)
{
    foreach ($rows_buffer as $index => $row) {
        echo '<tr>';
        echo '<td>' . $row['nombre_em'] . '</td>';
        echo '<td>' . $row['serie'] . '</td>';
        echo '<td>' . $row['folio'] . '</td>';
        echo '<td>$' . $row['total'] . '</td>';
        echo '<td>' . getBotonesFactura($row['rutaCompletaXML'], $row['rutaCompletaPDF']) . '</td>';
        echo '<td>' . $row['fecha_registro'] . '</td>';
        echo '<td>' . $row['fecha_confirmada'] . '</td>';
        echo '<td>' . $row['fecha_pagada'] . '</td>';
        if ($index === 0) {
            echo '<td rowspan="' . $rowspan_count . '">' . $row['acciones'] . '</td>';
        } else {
            echo '<td style="display: none;"></td>';
        }
        echo '</tr>';
    }
}



// Obtener el cod_empresa usando el RFC
function getCodEmpresa($rfc)
{
	// Conectar a la base de datos
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// Verificar la conexión
	if ($mysqli->connect_error) {
		die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
	}

	// La consulta para obtener el cod_empresa usando el RFC
	$query = "SELECT cod_empresa FROM providers WHERE rfc = ? LIMIT 1";

	// Preparar la declaración
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s", $rfc);
	$stmt->execute();

	// Obtener el resultado
	$result = $stmt->get_result();
	$cod_empresa = null;

	// Obtener el cod_empresa
	if ($row = $result->fetch_assoc()) {
		$cod_empresa = $row['cod_empresa'];
	}

	// Liberar la memoria asociada al resultado
	$result->free();

	// Cerrar la conexión
	$mysqli->close();

	return $cod_empresa;
}


function getProviderIdByRFC($rfc)
{
	// Conectar a la base de datos
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// Verificar errores de conexión
	if ($mysqli->connect_error) {
		die('Error de conexión: (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
	}

	// Consulta para obtener el id_proveedor
	$query = "SELECT id_proveedor FROM providers WHERE rfc = ?";
	$stmt = $mysqli->prepare($query);

	if (!$stmt) {
		die('Prepare failed: ' . $mysqli->error);
	}

	$stmt->bind_param("s", $rfc);  // 's' indica que el parámetro es de tipo string
	$stmt->execute();
	$result = $stmt->get_result();

	if (!$result) {
		die('Execute failed: ' . $stmt->error);
	}

	// Obtener el id_proveedor
	$id_proveedor = null;
	if ($row = $result->fetch_assoc()) {
		$id_proveedor = $row['id_proveedor'];
	}

	// Liberar resultados y cerrar conexión
	$result->free();
	$stmt->close();
	$mysqli->close();

	return $id_proveedor;
}


function verificarBloqueoSubida($mysqli, $rfcProveedor) {
    // Consulta para obtener las facturas del proveedor
    $queryFacturas = "SELECT cfdi.fecha_pagada, cfdi.validated, cfdi.no_recurrent, cfdi.comprobante, cfdi.metodopago, complements.comple 
                      FROM cfdi 
                      LEFT JOIN complements ON cfdi.id = complements.cfdi_id 
                      WHERE cfdi.rfc_emisor = ? AND cfdi.validated = 3 AND cfdi.no_recurrent = 0";
    $stmtFacturas = $mysqli->prepare($queryFacturas);
    $stmtFacturas->bind_param("s", $rfcProveedor);
    $stmtFacturas->execute();
    $resultFacturas = $stmtFacturas->get_result();

    $bloquearSubida = false;

    while ($factura = $resultFacturas->fetch_assoc()) {
        $fecha_pagada = $factura['fecha_pagada'];
        $comprobante = $factura['comprobante'];
        $comple = $factura['comple'];  // Verificamos el campo 'comple' de la tabla 'complements'
        $metodopago = $factura['metodopago'];  // Obtenemos el método de pago

        // Verificamos solo las facturas que no tienen un complemento con 'comple = 1'
        if ($comple != 1) {
            // Verificamos que el comprobante sea 1 (complemento de pago) y que el método de pago sea PPD
            if ($comprobante == 1 && $metodopago == 'PPD') {
                $fechaPagada = new DateTime($fecha_pagada);
                $fechaActual = new DateTime();
                $diferenciaDias = $fechaActual->diff($fechaPagada)->days;

                // Si han pasado más de 5 días desde la fecha de pago
                if ($diferenciaDias > 5) {
                    $bloquearSubida = true;
                    break; // Si encontramos una factura que cumple con las condiciones, bloqueamos la subida
                }
            }
        }
    }

    $stmtFacturas->close();

    // Si se debe bloquear la subida, mostramos un mensaje y detenemos el proceso
    if ($bloquearSubida) {
        echo '<div class="alert alert-warning">No puedes subir más facturas, ya que han pasado más de 5 días desde la fecha de pago de alguna de las facturas PPD y no se ha subido el complemento de pago.</div>';
        exit;  // Detener la ejecución del script y no permitir subir nuevas facturas
    }
}