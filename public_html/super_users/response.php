<?php

/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

include_once('includes/config.php');

// show PHP errors
ini_set('display_errors', 1);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
}

$action = isset($_POST['action']) ? $_POST['action'] : "";
// Obtener el rol del usuario y determinar la empresa
session_start();

if (isset($_POST['action']) && $_POST['action'] == 'add_invoice_areas') {
    header('Content-Type: application/json');

    if (isset($_POST['selected_areas']) && is_array($_POST['selected_areas']) && isset($_POST['cfdi_id'])) {
        $selectedAreas = $_POST['selected_areas'];
        $cfdi_id = $_POST['cfdi_id'];

        // Verificar la conexión
        if ($mysqli->connect_error) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Error de conexión: ' . $mysqli->connect_error
            ));
            exit;
        }
        date_default_timezone_set('America/Mexico_City');        

        $role = $_SESSION['rol'];
        $companies = [
            13 => 'abforti',
            14 => 'beexen',
            15 => 'upper',
            16 => 'inmobiliaria',
            17 => 'innovet'
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

        // Obtener datos de la tabla cfdi
        $query = "SELECT * FROM cfdi WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $cfdi_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cfdiData = $result->fetch_assoc();
        $stmt->close();

        if ($cfdiData) {
            if ($cfdiData['no_recurrent'] == 1) {
                // Código para manejar CFDI no recurrente
                $nombreEm = str_replace(' ', '_', $cfdiData['nombre_em']);
                $serie = $cfdiData['serie'];
                $folio = $cfdiData['folio'];
                $fechaRegistro = $cfdiData['fecha_registro'];
                $uuid = $cfdiData['uuid'];

                // Analiza la fecha para obtener el año, mes y día
                $date = new DateTime($fechaRegistro);
                $year = $date->format('Y');
                $month = $date->format('m');
                $day = $date->format('d');

                // Define rutas de origen y destino
                $sourcePath = "../super_users/{$currentCompany}/uploads/factura/{$year}/{$month}/NO_RECURRENTES/{$day}";
                $destinationPath = "../users/{$currentCompany}/uploads/factura/{$year}/{$month}/NO_RECURRENTES/{$day}";

                // Construir el nombre del archivo XML
                if ($serie && $folio) {
                    $fileName = "{$nombreEm}_{$serie}_{$folio}.xml";
                } else {
                    $fileName = "{$nombreEm}_" . substr($uuid, -4) . ".xml";
                }

                // Rutas de archivo completas
                $sourceFile = "{$sourcePath}/{$fileName}";
                $destinationFile = "{$destinationPath}/{$fileName}";

                // Manejo del archivo XML
                if (file_exists($sourceFile)) {
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    if (rename($sourceFile, $destinationFile)) {
                        // Lógica para manejar el archivo PDF
                        $pdfFileName = str_replace('.xml', '.pdf', $fileName);
                        $sourcePDF = "{$sourcePath}/{$pdfFileName}";
                        $destinationPDF = "{$destinationPath}/{$pdfFileName}";

                        if (file_exists($sourcePDF)) {
                            if (!rename($sourcePDF, $destinationPDF)) {
                                echo json_encode(array(
                                    'status' => 'error',
                                    'message' => 'Error al mover el archivo PDF.'
                                ));
                                $mysqli->close();
                                exit;
                            }
                        } else {
                            echo json_encode(array(
                                'status' => 'error',
                                'message' => 'El archivo PDF fuente no existe.'
                            ));
                            $mysqli->close();
                            exit;
                        }

                        // Actualizar la base de datos
                        $currentDate = new DateTime();
                        $fechaValSU = $currentDate->format('Y-m-d H:i:s');

                        // Insertar áreas en la tabla ap_invoice
                        $query = "INSERT INTO ap_invoice (cfdi_id, area) VALUES (?, ?)";
                        $stmt = $mysqli->prepare($query);
                        if ($stmt) {
                            foreach ($selectedAreas as $area) {
                                $stmt->bind_param("ss", $cfdi_id, $area);
                                $stmt->execute();
                            }
                            $stmt->close();
                        }

                        $updateQuery = "UPDATE cfdi SET validated = 1, no_recurrent = 2, fecha_val_su = ? WHERE id = ?";
                        $updateStmt = $mysqli->prepare($updateQuery);
                        $updateStmt->bind_param("si", $fechaValSU, $cfdi_id);
                        $updateStmt->execute();
                        $updateStmt->close();

                        echo json_encode(array(
                            'status' => 'success',
                            'message' => 'Áreas seleccionadas guardadas exitosamente, archivos movidos.',
                            'redirect' => 'invoice-nrec.php'
                        ));
                    } else {
                        echo json_encode(array(
                            'status' => 'error',
                            'message' => 'Error al mover el archivo .'
                        ));
                        $mysqli->close();
                        exit;
                    }
                } else {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'El archivo XML fuente no existe.'
                    ));
                    $mysqli->close();
                    exit;
                }
            } else {
                // Código para manejar CFDI recurrente
                $currentDate = new DateTime();
                $fechaValSU = $currentDate->format('Y-m-d H:i:s');

                // Insertar áreas en la tabla ap_invoice
                $query = "INSERT INTO ap_invoice (cfdi_id, area) VALUES (?, ?)";
                $stmt = $mysqli->prepare($query);
                if ($stmt) {
                    foreach ($selectedAreas as $area) {
                        $stmt->bind_param("ss", $cfdi_id, $area);
                        $stmt->execute();
                    }
                    $stmt->close();
                }

                $updateQuery = "UPDATE cfdi SET validated = 1, fecha_val_su = ? WHERE id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                $updateStmt->bind_param("si", $fechaValSU, $cfdi_id);
                $updateStmt->execute();
                $updateStmt->close();

                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'Áreas seleccionadas guardadas exitosamente.',
                    'redirect' => 'invoice-list.php'
                ));
            }

            $mysqli->close();
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'No se encontró el CFDI especificado.'
            ));
            $mysqli->close();
            exit;
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'No se seleccionó ninguna área o falta el ID del CFDI.'
        ));
    }

    exit;
}


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
            13 => 'abforti',
            14 => 'beexen',
            15 => 'upper',
            16 => 'inmobiliaria',
            17 => 'innovet'
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
            13 => 'AB1',
            15 => 'UP1',
            16 => 'IMO1',
            17 => 'INN1'
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
        $directorioBase = $_SERVER['DOCUMENT_ROOT'] . "/super_users/{$company}/uploads/factura/{$anoActual}/{$mesActual}/FONDO_FIJO/{$diaActual}";

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
                'factura_pdf' => $ArchivoPDF


            );



            // Insertar datos de cfdiData
            $stmtCFDI = $db->prepare("INSERT INTO cfdi (rfc_emisor, lugar_expedicion, nombre_em, regimen_fiscal_em, serie, folio, total, sello, rfc_receptor, nombre_receptor, domicilio_fiscal_receptor, regimen_fiscal_receptor, uuid, uso_cfdi, fechaTimbrado, total_impuestos_trasladados, total_impuestos_retenidos, codigo_estatus, estado, fecha, formapago, no_certificado, subtotal, moneda, exportacion, tipodecomprobante, metodopago, fecha_registro, f_cod_empresa, factura_xml, factura_pdf ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmtCFDI === false) {
                die("Error en la preparación de la consulta: " . $db->error);
            }
            $stmtCFDI->bind_param(
                "ssssssdssssssssssssssssssssssss",
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
                $cfdiData['factura_pdf']
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
                $fixed_fund = 111;

                $updateQuery = $db->prepare("UPDATE cfdi SET fixed_fund = ? WHERE uuid = ?");
                $updateQuery->bind_param("is", $fixed_fund, $uuid);
                $updateQuery->execute();
                $updateQuery->close();
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

?>