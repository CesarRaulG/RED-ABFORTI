<?php

include_once('includes/config.php');

// show PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
}


$action = isset($_POST['action']) ? $_POST['action'] : "";

// Obtener el rol del usuario y determinar la empresa
session_start();

if ($action == 'invoice_cancel') {
    header('Content-Type: application/json');
    session_start();

    $response = ['status' => 'error', 'message' => 'Invalid request.'];

    if (!isset($_POST['cfdi_id'], $_POST['id_ap'])) {
        $response['message'] = 'No se proporcionó un ID de CFDI válido.';
        echo json_encode($response);
        exit;
    }

    $cfdi_id = $_POST['cfdi_id'];
    $id_ap = $_POST['id_ap'];
    $cancel_reason = $_POST['cancel_reason'] ?? '';


    $userArea = $_SESSION['area_users'] ?? null;
    if ($userArea === null) {
        $response['message'] = 'Area del usuario no encontrada.';
        echo json_encode($response);
        exit;
    }

    // Obtener datos de la tabla cfdi
    $cfdiData = fetchDataFromTable($mysqli, "SELECT * FROM cfdi WHERE id = ?", 'i', [$cfdi_id]);
    if (!$cfdiData) {
        $response['message'] = 'No se encontraron datos del CFDI.';
        echo json_encode($response);
        exit;
    }

    // Guardar la cancelación
    executeQuery(
        $mysqli,
        "INSERT INTO a_cancellation (cfdi_id, id_ap, area, cancelacion, cancellation_date) VALUES (?, ?, ?, ?, NOW())",
        'iiss',
        [$cfdi_id, $id_ap, $userArea, $cancel_reason]
    );

    // Obtener el área específica de la factura
    $areaData = fetchDataFromTable($mysqli, "SELECT id FROM ap_invoice WHERE cfdi_id = ? AND area = ?", 'is', [$cfdi_id, $userArea]);

    if (!$areaData) {
        $response['message'] = 'No se encontro el area especifica para cancelar.';
        echo json_encode($response);
        exit;
    }

    // Cancelar el área específica
    executeQuery($mysqli, "DELETE FROM ap_invoice WHERE id = ?", 'i', [$areaData['id']]);

    // Verificar si aún quedan áreas validadas
    $remainingAreas = fetchDataFromTable($mysqli, "SELECT COUNT(*) AS remaining FROM ap_invoice WHERE cfdi_id = ?", 'i', [$cfdi_id]);

    if ($remainingAreas['remaining'] == 0) {
        // Mover archivo y actualizar tabla cfdi si no quedan áreas
        $fileMoveResult = moveCfdiFile($cfdiData, $_SESSION['rol'], $cfdi_id, $mysqli);
        $response = array_merge($response, $fileMoveResult);
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Area especifica cancelada, pero aun quedan otras areas activas.';
        $response['redirect'] = 'panel.php';
    }

    echo json_encode($response);
    exit;
}
function fetchDataFromTable($mysqli, $query, $paramTypes, $params)
{
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

function executeQuery($mysqli, $query, $paramTypes, $params)
{
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $stmt->close();
}

function moveCfdiFile($cfdiData, $role, $cfdi_id, $mysqli)
{
    $companies = [
        2 => 'abforti',
        3 => 'beexen',
        4 => 'upper',
        5 => 'inmobiliaria',
        6 => 'innovet'
    ];

    if (!isset($companies[$role])) {
        return ['status' => 'error', 'message' => 'Rol de usuario no válido.'];
    }

    $currentCompany = $companies[$role];
    $date = new DateTime($cfdiData['fecha_registro']);
    $year = $date->format('Y');
    $month = $date->format('m');
    $day = $date->format('d');
    $noRecurrent = $cfdiData['no_recurrent'];

    $sourcePath = $noRecurrent == 2
        ? "../users/{$currentCompany}/uploads/factura/{$year}/{$month}/NO_RECURRENTES/{$day}"
        : "../suppliers/{$currentCompany}/uploads/factura/{$year}/{$month}/{$day}";

    $destinationPath = $noRecurrent == 2
        ? "../super_users/{$currentCompany}/uploads/factura/{$year}/{$month}/NO_RECURRENTES/{$day}"
        : "../suppliers/{$currentCompany}/uploads/factura/{$year}/{$month}/{$day}";

    $nombreEm = str_replace(' ', '_', $cfdiData['nombre_em']);
    $serie = $cfdiData['serie'];
    $folio = $cfdiData['folio'];
    $uuid = $cfdiData['uuid'];

    $fileBaseName = $serie && $folio ? "{$nombreEm}_{$serie}_{$folio}" : "{$nombreEm}_" . substr($uuid, -4);

    // Mover archivo XML
    $xmlFileName = "{$fileBaseName}.xml";
    $sourceXmlFile = "{$sourcePath}/{$xmlFileName}";
    $destinationXmlFile = "{$destinationPath}/{$xmlFileName}";

    if (file_exists($sourceXmlFile)) {
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        if (!rename($sourceXmlFile, $destinationXmlFile)) {
            return ['status' => 'error', 'message' => 'Error al mover el archivo XML.'];
        }
    }

    // Mover archivo PDF
    $pdfFileName = "{$fileBaseName}.pdf";
    $sourcePdfFile = "{$sourcePath}/{$pdfFileName}";
    $destinationPdfFile = "{$destinationPath}/{$pdfFileName}";

    if (file_exists($sourcePdfFile)) {
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        if (!rename($sourcePdfFile, $destinationPdfFile)) {
            return ['status' => 'error', 'message' => 'Error al mover el archivo PDF.'];
        }
    }

    // Actualizar el estado de la factura basado en si es recurrente o no
    if ($noRecurrent == 2) {
        // Factura no recurrente
        executeQuery($mysqli, "UPDATE cfdi SET validated = 0, no_recurrent = 1, fecha_val_su = NULL WHERE id = ?", 'i', [$cfdi_id]);
    } else {
        // Factura recurrente
        executeQuery($mysqli, "UPDATE cfdi SET validated = 0, no_recurrent = 0, fecha_val_su = NULL WHERE id = ?", 'i', [$cfdi_id]);
    }

    return ['status' => 'success', 'message' => 'Los archivos se movieron correctamente.', 'redirect' => 'panel.php'];
}
/*
if ($action == 'validated_move') {
    header('Content-Type: application/json');

    $response = array('status' => 'error', 'message' => 'Invalid request.');

    // Crear un objeto DateTime con la zona horaria de México
    $mexico_timezone = new DateTimeZone('America/Mexico_City');
    $currentDate = new DateTime('now', $mexico_timezone);


    // id del registro de la tabla ap_invoice solo el que le pertenece a ese usuario
    $ap_invoice_id = $_POST['id_ap'] ?? null;

    if ($ap_invoice_id === null) {
        $response['message'] = 'No id ap_invoice.';
        echo json_encode($response);
        exit;
    }

    // id de cfdi_id de la factura
    $postID = $_POST['id'] ?? null;
    if ($postID === null) {
        $response['message'] = 'No ID provided.';
        echo json_encode($response);
        exit;
    }

    session_start();
    $userArea = $_SESSION['area_users'] ?? null; // designada desde usuarios

    if ($userArea === null) {
        $response['message'] = 'User area not found.';
        echo json_encode($response);
        exit;
    }

    $descripcion = $_POST['descripcion'] ?? ''; // Capturar la descripción desde el formulario

    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    if ($mysqli->connect_error) {
        $response['message'] = 'Database connection error: ' . $mysqli->connect_error;
        echo json_encode($response);
        exit;
    }

    $cfdiIDQuery = "SELECT cfdi_id, area FROM ap_invoice WHERE id = '" . $mysqli->real_escape_string($ap_invoice_id) . "'";
    $cfdiIDResult = $mysqli->query($cfdiIDQuery);

    if (!$cfdiIDResult) {
        $response['message'] = 'Query error while fetching CFDI ID: ' . $mysqli->error;
        echo json_encode($response);
        $mysqli->close();
        exit;
    }

    $cfdiIDRow = $cfdiIDResult->fetch_assoc();
    $cfdiID = $cfdiIDRow['cfdi_id'] ?? null;
    $invoiceArea = $cfdiIDRow['area'] ?? null;

    if ($cfdiID === null) {
        $response['message'] = 'No CFDI ID found for post ID: ' . $ap_invoice_id;
        echo json_encode($response);
        $mysqli->close();
        exit;
    }

    if ($invoiceArea !== $userArea) {
        $response['message'] = 'User does not have access to this area.';
        echo json_encode($response);
        $mysqli->close();
        exit;
    }

    // Actualizar la validación y la descripción para este área específica
    $updateAreaQuery = "UPDATE ap_invoice SET validacion = 1, descripcion = ? WHERE id = ?";
    $stmt = $mysqli->prepare($updateAreaQuery);
    if ($stmt) {
        $stmt->bind_param('si', $descripcion, $ap_invoice_id);
        if ($stmt->execute()) {
            $validationQuery = "SELECT COUNT(*) AS total, SUM(validacion) AS validated FROM ap_invoice WHERE cfdi_id = '" . $mysqli->real_escape_string($cfdiID) . "'";
            $validationResult = $mysqli->query($validationQuery);
            $validationRow = $validationResult->fetch_assoc();

            $totalAreas = $validationRow['total'];
            $validatedAreas = $validationRow['validated'];

            if ($validatedAreas == $totalAreas) {
                $query = "SELECT * FROM cfdi WHERE id = '" . $mysqli->real_escape_string($postID) . "'";
                $result = $mysqli->query($query);
                if (!$result) {
                    $response['message'] = 'Query error: ' . $mysqli->error;
                    echo json_encode($response);
                    $mysqli->close();
                    exit;
                }

                $cfdiData = $result->fetch_assoc();
                $result->close();

                $nombreEm = str_replace(' ', '_', $_POST['nombre_em']);
                $serie = $_POST['serie'];
                $folio = $_POST['folio'];
                $fechaRegistro = $_POST['fecha_registro'];
                $uuid = $_POST['uuid'];

                $date = new DateTime($fechaRegistro);
                $year = $date->format('Y');
                $month = $date->format('m');
                $day = $date->format('d');

                // Get user role and determine the company
                $role = $_SESSION['rol'];
                $companies = [
                    2 => 'abforti',
                    3 => 'beexen',
                    4 => 'upper',
                    5 => 'inmobiliaria',
                    6 => 'innovet'
                ];

                if (!isset($companies[$role])) {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Rol de usuario no válido.'
                    ));
                    $mysqli->close();
                    exit;
                }

                $currentCompany = $companies[$role];

                if ($cfdiData['no_recurrent'] == 2) {
                    $sourcePath = "{$currentCompany}/uploads/factura/NO_RECURRENTES/{$year}/{$month}/{$day}";
                    $destinationPath = "../control/{$currentCompany}/uploads/factura/NO_RECURRENTES/{$year}/{$month}/{$day}";                    
                } else {
                    $sourcePath = "../suppliers/{$currentCompany}/uploads/factura/{$year}/{$month}/{$day}";
                    $destinationPath = "../control/{$currentCompany}/uploads/factura/{$year}/{$month}/{$day}";
                }

                if ($serie && $folio) {
                    $fileName = "{$nombreEm}_{$serie}_{$folio}.xml";
                } else {
                    $fileName = "{$nombreEm}_" . substr($uuid, -4) . ".xml";
                }

                $sourceFile = "{$sourcePath}/{$fileName}";
                $destinationFile = "{$destinationPath}/{$fileName}";

                if (file_exists($sourceFile)) {
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    // Construcción del nombre del archivo PDF
                    $pdfFileName = str_replace('.xml', '.pdf', $fileName);
                    $sourcePDF = "{$sourcePath}/{$pdfFileName}";
                    $destinationPDF = "{$destinationPath}/{$pdfFileName}";

                    // Fecha actual confirmada
                    $fechaConfirmada = $currentDate->format('Y-m-d H:i:s');

                    // Verificar y manejar diferentes combinaciones de validated y no_recurrent
                    if (($cfdiData['validated'] == 0 && $cfdiData['no_recurrent'] == 2) ||
                        ($cfdiData['validated'] == 1 && $cfdiData['no_recurrent'] == 2)
                    ) {
                        $fechaConfirmada = $currentDate->format('Y-m-d H:i:s');

                        // Actualizar valores a validated=1 y no_recurrent=3
                        $updateQuery = "UPDATE cfdi 
                                            SET validated = 1, no_recurrent = 3, fecha_confirmada='{$fechaConfirmada}'  
                                            WHERE id = '" . $mysqli->real_escape_string($cfdiID) . "'";
                        if ($mysqli->query($updateQuery)) {
                            if (rename($sourceFile, $destinationFile)) {

                                // Mover también el archivo PDF si existe
                                if (file_exists($sourcePDF)) {
                                    rename($sourcePDF, $destinationPDF);
                                }
                                $response['status'] = 'success';
                                $response['message'] = 'Archivo movido correctamente y valores actualizados a validated=1 y no_recurrent=3.';
                                $response['redirect'] = 'invoice-nrec.php';
                            } else {
                                $response['message'] = 'Error al mover el archivo después de actualizar valores.';
                            }
                        } else {
                            $response['message'] = 'Error al actualizar los valores: ' . $mysqli->error;
                        }
                    } elseif ($cfdiData['validated'] == 1 && $cfdiData['no_recurrent'] == 0) {
                        // Actualizar valores a validated=2 y no_recurrent=0
                        $fechaConfirmada = $currentDate->format('Y-m-d H:i:s');
                        $updateQuery = "UPDATE cfdi 
                                            SET validated = 2, no_recurrent = 0, fecha_confirmada='{$fechaConfirmada}'  
                                            WHERE id = '" . $mysqli->real_escape_string($cfdiID) . "'";
                        if ($mysqli->query($updateQuery)) {
                            if (rename($sourceFile, $destinationFile)) {

                                // Mover también el archivo PDF si existe
                                if (file_exists($sourcePDF)) {
                                    rename($sourcePDF, $destinationPDF);
                                }
                                $response['status'] = 'success';
                                $response['message'] = 'Archivo movido correctamente y valores actualizados a validated=2 y no_recurrent=0.';
                                $response['redirect'] = 'invoice-list.php';
                            } else {
                                $response['message'] = 'Error al mover el archivo después de actualizar valores.';
                            }
                        } else {
                            $response['message'] = 'Error al actualizar los valores: ' . $mysqli->error;
                        }
                    } else {
                        // Condición no manejada
                        $response['message'] = 'Condición no manejada: validated=' . $cfdiData['validated'] . ', no_recurrent=' . $cfdiData['no_recurrent'];
                    }
                } else {
                    $response['message'] = 'El archivo fuente no existe.';
                }
            } else {
                $response['message'] = 'Área validada, pero aún quedan otras áreas por validar.';
                $response['redirect'] = 'panel.php';
            }
        } else {
            $response['message'] = 'Error al ejecutar la actualización: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = 'Error al preparar la consulta: ' . $mysqli->error;
    }

    $mysqli->close();
    echo json_encode($response);
    exit;
}
    */


if ($action == 'fondo_fijo') {
    if (isset($_FILES['archivo_pdf']) && isset($_POST['archivo_temporal'])) {
        $archivoPDF = $_FILES['archivo_pdf'];
        $archivoTemporal = $_POST['archivo_temporal'];
        $nombreOriginal = $_POST['nombre_original'];
        $extension = $_POST['extension'];
        $UUID = $_POST['UUID'];
        $fechaEmision = $_POST['fecha_emision'];
        /*
            $mesEmision = date('Y-m', strtotime($fechaEmision));
            $mesActual = date('Y-m');
    
            if ($mesEmision !== $mesActual) {
            echo "Error: El XML no corresponde al mes actual.";
            exit;
            }
    */

        $role = $_SESSION['rol'];
        $companies = [
            2 => 'abforti',
            3 => 'beexen',
            4 => 'upper',
            5 => 'inmobiliaria',
            6 => 'innovet'
        ];

        if (!isset($companies[$role])) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Rol de usuario no valido.'
            ));
            $mysqli->close();
            exit;
        }

        $company = $companies[$role];

        $f_cod_empresa_mapping = [
            2 => 'AB1',
            4 => 'UP1',
            5 => 'IMO1',
            6 => 'INN1'
        ];

        if (isset($f_cod_empresa_mapping[$role])) {
            $f_cod_empresa = $f_cod_empresa_mapping[$role];
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Rol de usuario no válido para determinar cod_empresa.'
            ));
            $db->close();
            exit;
        }

        // Obtener el año y mes actual en México
        date_default_timezone_set('America/Mexico_City');
        $fechaRegistro = date('Y-m-d H:i:s');

        // Obtener el año y mes actual en México
        $anoActual = date('Y');
        $mesActual = date('m');
        $diaActual = date('d');

        // Directorio base
        $directorioBase = $_SERVER['DOCUMENT_ROOT'] . "/users/{$company}/uploads/factura/{$anoActual}/{$mesActual}/FONDO_FIJO/{$diaActual}";

        // Crear directorios basados en año, mes y día
        if (!file_exists($directorioBase)) {
            if (!mkdir($directorioBase, 0777, true)) {
                die("No se puede crear el directorio: " . $directorioBase);
            }
        }

        // Directorio permanente final
        $directorioPermanente = $directorioBase . '/';

        // Función para obtener los últimos 4 dígitos del UUID
        function obtenerUltimosCuatroDigitos($UUID)
        {
            return substr($UUID, -4);
        }



        // Leer el XML
        $xmlString = file_get_contents($archivoTemporal);
        if ($xmlString === false) {
            die("Error al leer el contenido del archivo XML.");
        }


        $xml = new SimpleXMLElement($xmlString);
        $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
        $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

        $nombreEmisor = (string) ($xml->xpath('//cfdi:Emisor')[0]['Nombre'] ?? null);
        $serie = (string) ($xml['Serie'] ?? null);
        $folio = (string) ($xml['Folio'] ?? null);

        if ($serie && $folio) {
            $nombreArchivoBase = $nombreEmisor . ' ' . $serie . ' ' . $folio;
        } else {
            $nombreArchivoBase = $nombreEmisor . ' ' . obtenerUltimosCuatroDigitos($UUID);
        }

        $nombreArchivoBase = str_replace(' ', '_', $nombreArchivoBase);

        $nombreArchivoPDF = $directorioPermanente . $nombreArchivoBase . '.pdf';
        $nombreArchivoXML = $directorioPermanente . $nombreArchivoBase . '.' . $extension;

        // Extraer solo el nombre del archivo y su extensión
        $ArchivoPDF = $nombreArchivoBase . '.pdf';
        $ArchivoXML = $nombreArchivoBase . '.' . $extension;

        // Conectar a la base de datos
        $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        // Extraer RFC del emisor y UUID de la factura
        $rfcEmisor = (string)($xml->xpath('//cfdi:Emisor')[0]['Rfc'] ?? null);
        $uuid = (string)($xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] ?? null);

        // Verificar si ya existe una factura con el mismo RFC y UUID
        $stmt = $db->prepare("SELECT * FROM cfdi WHERE rfc_emisor = ? AND uuid = ?");
        $stmt->bind_param("ss", $rfcEmisor, $uuid);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $mensaje = "La factura ya ha sido subida anteriormente.";
        } else {
            $userId = $_SESSION['user_id'];
            $f_cod_empresa = $f_cod_empresa_mapping[$role];

            $cfdiData = array(
                'rfc_emisor' => (string) ($xml->xpath('//cfdi:Emisor')[0]['Rfc'] ?? null),
                'lugar_expedicion' => (string) ($xml['LugarExpedicion'] ?? null),
                'nombre_em' => (string) ($xml->xpath('//cfdi:Emisor')[0]['Nombre'] ?? null),
                'regimen_fiscal_em' => (string) ($xml->xpath('//cfdi:Emisor')[0]['RegimenFiscal'] ?? null),
                'serie' => (string) ($xml['Serie'] ?? null),
                'folio' => (string) ($xml['Folio'] ?? null),
                'total' => (string) ($xml['Total'] ?? null),
                'sello' => (string) ($xml['Sello'] ?? null),
                'rfc_receptor' => (string) ($xml->xpath('//cfdi:Receptor')[0]['Rfc'] ?? null),
                'nombre_receptor' => (string) ($xml->xpath('//cfdi:Receptor')[0]['Nombre'] ?? null),
                'domicilio_fiscal_receptor' => (string) ($xml->xpath('//cfdi:Receptor')[0]['DomicilioFiscalReceptor'] ?? null),
                'regimen_fiscal_receptor' => (string) ($xml->xpath('//cfdi:Receptor')[0]['RegimenFiscalReceptor'] ?? null),
                'uuid' => (string) ($xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] ?? null),
                'uso_cfdi' => (string) ($xml->xpath('//cfdi:Receptor')[0]['UsoCFDI'] ?? null),
                'fechaTimbrado' => (string) ($xml->xpath('//tfd:TimbreFiscalDigital')[0]['FechaTimbrado'] ?? null),
                'total_impuestos_trasladados' => (string) ($xml->xpath('//cfdi:Comprobante/cfdi:Impuestos')[0]['TotalImpuestosTrasladados'] ?? null),
                'total_impuestos_retenidos' => (string) ($xml->xpath('//cfdi:Comprobante/cfdi:Impuestos')[0]['TotalImpuestosRetenidos'] ?? null),
                'codigo_estatus' => 'pendiente',
                'estado' => 'activo',

                'fecha' => (string) ($xml['Fecha'] ?? null),
                'formapago' => (string) ($xml['FormaPago'] ?? null),
                'no_certificado' => (string) ($xml['NoCertificado'] ?? null),
                'subtotal' => (string) ($xml['SubTotal'] ?? null),
                'moneda' => (string) ($xml['Moneda'] ?? null),
                'exportacion' => (string) ($xml['Exportacion'] ?? null),
                'tipodecomprobante' => (string) ($xml['TipoDeComprobante'] ?? null),
                'metodopago' => (string) ($xml['MetodoPago'] ?? null),
                'fecha_registro' => $fechaRegistro,
                'f_cod_empresa' => $f_cod_empresa,  // Incluimos el cod_empresa
                'factura_xml' => $ArchivoXML,
                'factura_pdf' => $ArchivoPDF,
                'user_id' => $userId  // Incluimos el user_id



            );



            // Insertar datos de cfdiData
            $stmtCFDI = $db->prepare("INSERT INTO cfdi (rfc_emisor, lugar_expedicion, nombre_em, regimen_fiscal_em, serie, folio, total, sello, rfc_receptor, nombre_receptor, domicilio_fiscal_receptor, regimen_fiscal_receptor, uuid, uso_cfdi, fechaTimbrado, total_impuestos_trasladados, total_impuestos_retenidos, codigo_estatus, estado, fecha, formapago, no_certificado, subtotal, moneda, exportacion, tipodecomprobante, metodopago, fecha_registro, f_cod_empresa, factura_xml, factura_pdf, user_id ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmtCFDI === false) {
                die("Error en la preparación de la consulta: " . $db->error);
            }
            $stmtCFDI->bind_param(
                "ssssssdsssssssssssssssssssssssss",
                $cfdiData['rfc_emisor'],
                $cfdiData['lugar_expedicion'],
                $cfdiData['nombre_em'],
                $cfdiData['regimen_fiscal_em'],
                $cfdiData['serie'],
                $cfdiData['folio'],
                $cfdiData['total'],
                $cfdiData['sello'],
                $cfdiData['rfc_receptor'],
                $cfdiData['nombre_receptor'],
                $cfdiData['domicilio_fiscal_receptor'],
                $cfdiData['regimen_fiscal_receptor'],
                $cfdiData['uuid'],
                $cfdiData['uso_cfdi'],
                $cfdiData['fechaTimbrado'],
                $cfdiData['total_impuestos_trasladados'],
                $cfdiData['total_impuestos_retenidos'],
                $cfdiData['codigo_estatus'],
                $cfdiData['estado'],
                $cfdiData['fecha'],
                $cfdiData['formapago'],
                $cfdiData['no_certificado'],
                $cfdiData['subtotal'],
                $cfdiData['moneda'],
                $cfdiData['exportacion'],
                $cfdiData['tipodecomprobante'],
                $cfdiData['metodopago'],
                $cfdiData['fecha_registro'],
                $cfdiData['f_cod_empresa'],
                $cfdiData['factura_xml'],
                $cfdiData['factura_pdf'],
                $cfdiData['user_id']  // user_id

            );
            if (!$stmtCFDI->execute()) {
                die("Error en la ejecución de la consulta: " . $stmtCFDI->error);
            }
            $cfdi_id = $stmtCFDI->insert_id;
            $stmtCFDI->close();



            $conceptosData = [];
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);

            foreach ($xml->xpath('//cfdi:Concepto') as $concepto) {
                $conceptosData[] = array(
                    'objeto_imp' => (string) ($concepto['ObjetoImp'] ?? null),
                    'clave_prod_serv' => (string) ($concepto['ClaveProdServ'] ?? null),
                    'cantidad' => (string) ($concepto['Cantidad'] ?? null),
                    'clave_unidad' => (string) ($concepto['ClaveUnidad'] ?? null),
                    'unidad' => (string) ($concepto['Unidad'] ?? null),
                    'descripcion' => (string) ($concepto['Descripcion'] ?? null),
                    'valor_unitario' => (string) ($concepto['ValorUnitario'] ?? null),
                    'importe' => (float) ($concepto['Importe'] ?? null),
                    'descuento' => (string) ($concepto['Descuento'] ?? null),
                    'no_identificacion' => (string) ($concepto['NoIdentificacion'] ?? null)
                );
            }

            // Insertar datos de conceptosData
            if (!empty($conceptosData)) {
                $stmtConcepto = $db->prepare("INSERT INTO conceptos (cfdi_id, objeto_imp, clave_prod_serv, cantidad, clave_unidad, unidad, descripcion, valor_unitario, importe, descuento, no_identificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmtConcepto === false) {
                    die("Error en la preparación de la consulta: " . $db->error);
                }
                foreach ($conceptosData as $concepto) {
                    $stmtConcepto->bind_param(
                        "issdsssdsss",
                        $cfdi_id,
                        $concepto['objeto_imp'],
                        $concepto['clave_prod_serv'],
                        $concepto['cantidad'],
                        $concepto['clave_unidad'],
                        $concepto['unidad'],
                        $concepto['descripcion'],
                        $concepto['valor_unitario'],
                        $concepto['importe'],
                        $concepto['descuento'],
                        $concepto['no_identificacion']
                    );
                    if (!$stmtConcepto->execute()) {
                        die("Error en la ejecución de la consulta: " . $stmtConcepto->error);
                    }
                }
                $stmtConcepto->close();
            }

            // Obtener datos de impuestos retenidos (imRet)
            $imRet = [];
            foreach ($xml->xpath('//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion') as $imRetenciones) {
                $imRet[] = array(
                    'base' => (string) ($imRetenciones['Base'] ?? null),
                    'impuesto' => (string) ($imRetenciones['Impuesto'] ?? null),
                    'tipofactor' => (string) ($imRetenciones['TipoFactor'] ?? null),
                    'tasaocuota' => (string) ($imRetenciones['TasaOCuota'] ?? null),
                    'importe' => (string) ($imRetenciones['Importe'] ?? null)
                );
            }

            // Insertar datos de impuestos retenidos
            if (!empty($imRet)) {
                $stmtImRet = $db->prepare("INSERT INTO imRet (cfdi_id, base, impuesto, tipoFactor, tasaOCuota, importe) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmtImRet === false) {
                    die("Error en la preparacion de la consulta: " . $db->error);
                }
                foreach ($imRet as $concepto) {
                    $stmtImRet->bind_param(
                        "isssss",
                        $cfdi_id,
                        $concepto['base'],
                        $concepto['impuesto'],
                        $concepto['tipofactor'],
                        $concepto['tasaocuota'],
                        $concepto['importe']
                    );
                    if (!$stmtImRet->execute()) {
                        die("Error en la ejecución de la consulta: " . $stmtImRet->error);
                    }
                }
                $stmtImRet->close();
            }

            // Obtener datos de impuestos trasladados (imTra)
            $imTra = [];
            foreach ($xml->xpath('//cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado') as $imTraslados) {
                $imTra[] = array(
                    'base' => (string) ($imTraslados['Base'] ?? null),
                    'impuesto' => (string) ($imTraslados['Impuesto'] ?? null),
                    'tipofactor' => (string) ($imTraslados['TipoFactor'] ?? null),
                    'tasaocuota' => (string) ($imTraslados['TasaOCuota'] ?? null),
                    'importe' => (string) ($imTraslados['Importe'] ?? null)
                );
            }

            // Insertar datos de impuestos trasladados
            if (!empty($imTra)) {
                $stmtImTra = $db->prepare("INSERT INTO imTra (cfdi_id, base, impuesto, tipoFactor, tasaOCuota, importe) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmtImTra === false) {
                    die("Error en la preparacion de la consulta: " . $db->error);
                }
                foreach ($imTra as $concepto) {
                    $stmtImTra->bind_param(
                        "isssss",
                        $cfdi_id,
                        $concepto['base'],
                        $concepto['impuesto'],
                        $concepto['tipofactor'],
                        $concepto['tasaocuota'],
                        $concepto['importe']
                    );
                    if (!$stmtImTra->execute()) {
                        die("Error en la ejecución de la consulta: " . $stmtImTra->error);
                    }
                }
                $stmtImTra->close();
            }

            // Obtener datos de retenciones
            $retenciones = [];
            foreach ($xml->xpath('//cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion') as $retencion) {
                $retenciones[] = array(
                    'impuesto' => (string) ($retencion['Impuesto'] ?? null),
                    'importe' => (string) ($retencion['Importe'] ?? null)
                );
            }


            // Insertar datos de retenciones
            if (!empty($retenciones)) {
                $stmtRetenciones = $db->prepare("INSERT INTO retenciones (cfdi_id, impuesto, importe) VALUES (?, ?, ?)");
                if ($stmtRetenciones === false) {
                    die("Error en la preparacion de la consulta: " . $db->error);
                }
                foreach ($retenciones as $concepto) {
                    $stmtRetenciones->bind_param(
                        "iss",
                        $cfdi_id,
                        $concepto['impuesto'],
                        $concepto['importe']
                    );
                    if (!$stmtRetenciones->execute()) {
                        die("Error en la ejecucion de la consulta: " . $stmtImTra->error);
                    }
                }
                $stmtRetenciones->close();
            }

            // Obtener datos de traslados
            $traslados = [];
            foreach ($xml->xpath('//cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado') as $tras) {
                $traslados[] = array(
                    'base' => (string) ($tras['Base'] ?? null),
                    'impuesto' => (string) ($tras['Impuesto'] ?? null),
                    'tipofactor' => (string) ($tras['TipoFactor'] ?? null),
                    'tasaocuota' => (string) ($tras['TasaOCuota'] ?? null),
                    'importe' => (string) ($tras['Importe'] ?? null)
                );
            }


            // Insertar datos de traslados
            if (!empty($traslados)) {
                $stmtTraslados = $db->prepare("INSERT INTO traslados (cfdi_id, base, impuesto, tipofactor, tasaocuota, importe) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmtTraslados === false) {
                    die("Error en la preparacion de la consulta: " . $db->error);
                }
                foreach ($traslados as $concepto) {
                    $stmtTraslados->bind_param(
                        "isssss",
                        $cfdi_id,
                        $concepto['base'],
                        $concepto['impuesto'],
                        $concepto['tipofactor'],
                        $concepto['tasaocuota'],
                        $concepto['importe']
                    );
                    if (!$stmtTraslados->execute()) {
                        die("Error en la ejecución de la consulta: " . $stmtTraslados->error);
                    }
                }
                $stmtTraslados->close();
            }
            // Aquí debes agregar la lógica para mover el archivo XML y PDF
            $rutaFinalPDF = $directorioPermanente . $nombreArchivoPDF;

            if (!move_uploaded_file($archivoPDF['tmp_name'], $nombreArchivoPDF)) {
                error_log("Error al mover el archivo PDF: " . error_get_last()['message']);
                die("Error al mover el archivo PDF.");
            }

            // Mover el archivo temporal al directorio permanente
            if (rename($archivoTemporal, $nombreArchivoXML)) {
                $mensaje = "El archivo ha sido subido y procesado con éxito.";
                // Actualizar el campo no_recurrent en la tabla cfdi
                $fixed_fund = 10;

                $updateQuery = $db->prepare("UPDATE cfdi SET fixed_fund = ? WHERE uuid = ?");
                $updateQuery->bind_param("is", $fixed_fund, $uuid);
                $updateQuery->execute();
                $updateQuery->close();

                $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

            // Fetch area_users for the given user_id
            $stmtUser = $db->prepare("SELECT area_users FROM users WHERE id = ?");
            if ($stmtUser === false) {
                die("Error en la preparación de la consulta para users: " . $db->error);
            }
            $stmtUser->bind_param("i", $userId);
            $stmtUser->execute();
            $resultUser = $stmtUser->get_result();
            $area = null;

            if ($resultUser->num_rows > 0) {
                $row = $resultUser->fetch_assoc();
                $area = $row['area_users'];
            } else {
                $area = 100; // Default value if not found
            }
            $stmtUser->close();

            // Insert the record into ap_invoice table
            $validated_F_F=1; // Campo para Fondo fijo si lo sube el usuario
            $stmtApInvoice = $db->prepare("INSERT INTO ap_invoice (cfdi_id, area, validated_F_F) VALUES (?, ?, ?)");
            if ($stmtApInvoice === false) {
                die("Error en la preparación de la consulta para ap_invoice: " . $db->error);
            }
            $stmtApInvoice->bind_param("iii", $cfdi_id, $area, $validated_F_F);
            if (!$stmtApInvoice->execute()) {
                die("Error en la ejecución de la consulta para ap_invoice: " . $stmtApInvoice->error);
            }
            $stmtApInvoice->close();
            } else {
                $mensaje = "Error al mover el archivo al directorio permanente.";
            }
        }
        // Cerrar conexión a la base de datos
        $db->close();
    } else {
        $mensaje = "No se recibió el archivo temporal correctamente.";
    }

    echo $mensaje;
}
