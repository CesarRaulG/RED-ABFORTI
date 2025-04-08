<?php
/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/
include('../functions.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate_move') {
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
                    $sourcePath = "uploads/factura/{$year}/{$month}/NO_RECURRENTES/{$day}";                    
                    $destinationPath = "../../control/{$currentCompany}/uploads/factura/{$year}/{$month}/NO_RECURRENTES/{$day}";
                } else {
                    $sourcePath = "../../suppliers/{$currentCompany}/uploads/factura/{$year}/{$month}/{$day}";
                    $destinationPath = "../../control/{$currentCompany}/uploads/factura/{$year}/{$month}/{$day}";
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
                    // Facturas NO_RECURENTES
                    if (($cfdiData['validated'] == 0 && $cfdiData['no_recurrent'] == 2) || 
                        ($cfdiData['validated'] == 1 && $cfdiData['no_recurrent'] == 2)) {
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
                                $response['message'] = 'Archivo movido correctamente y valores actualizados a validated=1 y no_recurrent=3. NO_RECURRENTES';
                                $response['redirect'] = 'invoice-nrec.php';
                            } else {
                                $response['message'] = 'Error al mover el archivo después de actualizar valores.';
                            }
                        } else {
                            $response['message'] = 'Error al actualizar los valores: ' . $mysqli->error;
                        }
                    } elseif ($cfdiData['validated'] == 1 && $cfdiData['no_recurrent'] == 0) { //Facturas Proovedores
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
                                $response['message'] = 'Archivo movido correctamente y valores actualizados a validated=2 y no_recurrent=0. PROVEEDOR';
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


?>
