<?php

include_once('includes/config.php');

// show PHP errors
ini_set('display_errors', 1);
error_reporting(E_ALL);


// output any connection error
if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
}


$action = isset($_POST['action']) ? $_POST['action'] : "";


//Edicion de proveedor dias de credito
if ($action == 'update_mf') {
    $postID = $_POST['id_proveedor'];

    // Validación de datos
    if (empty($postID) || empty($_POST['direccion'])) {
        die('Faltan datos obligatorios.');
    }

    $query = "UPDATE providers 
              SET direccion = ?, 
                  correo_electronico = ?, 
                  telefono = ?, 
                  credito = ?, 
                  cuenta = ?, 
                  clabe = ?, 
                  moneda = ?, 
                  swift = ?, 
                  banco = ?, 
                  titular = ?, 
                  referencia = ?, 
                  concepto = ? 
              WHERE id_proveedor = ?";

    $stmt = $mysqli->prepare($query);

    if ($stmt === false) {
        die('Error al preparar la consulta: ' . $mysqli->error);
    }

    $stmt->bind_param(
        "ssssssssssssi",
        $_POST['direccion'],
        $_POST['correo_electronico'],
        $_POST['telefono'],
        $_POST['credito'],
        $_POST['cuenta'],
        $_POST['clabe'],
        $_POST['moneda'],
        $_POST['swift'],
        $_POST['banco'],
        $_POST['titular'],
        $_POST['referencia'],
        $_POST['concepto'],
        $postID
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Archivo procesado correctamente." , "redirect" => "customer-list.php"]);
        exit;
    } else {
        die('Error al actualizar el proveedor: ' . $stmt->error);
    }
}




// Adding new product
//'add_product', que se utiliza para agregar un 
//nuevo producto a la base de datos.

if ($action == 'add_product') {

    // Verificar qué tipo de persona se está agregando
    $tipo_persona = $_POST['tipo_persona'];
    $rfc = $_POST['rfc'];
    $correo_electronico = $_POST['correo_electronico'];
    $razon_social = $_POST['razon_social'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    //documentacion
    $constancia = isset($_FILES['constancia']['name']) ? $_FILES['constancia']['name'] : null;
    $opinion = isset($_FILES['opinion']['name']) ? $_FILES['opinion']['name'] : null;
    $comprobante = isset($_FILES['comprobante']['name']) ? $_FILES['comprobante']['name'] : null;
    $acta = isset($_FILES['acta']['name']) ? $_FILES['acta']['name'] : null;
    $notarial = isset($_FILES['notarial']['name']) ? $_FILES['notarial']['name'] : null;

    // Obtener la cadena de valores seleccionados del formulario
    $empresa = $_POST['departamentoMoral']; // Obtener el valor de la empresa seleccionada
    $areas_seleccionadas_str = $_POST['area'];

    // Concatenar empresa y áreas seleccionadas en una sola cadena
    $empresa_areas = $areas_seleccionadas_str;

    // Directorio donde se guardarán los documentos, utilizando la razón social como parte de la ruta
    $directorio = $_SERVER['DOCUMENT_ROOT'] . "/providers/$razon_social";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    // Iterar sobre cada archivo y manejar su carga
    $documentos = array("constancia", "opinion", "comprobante", "acta", "notarial");
    foreach ($documentos as $documento) {
        if (isset($_FILES[$documento]) && $_FILES[$documento]['error'] === UPLOAD_ERR_OK) {
            $filename = basename($_FILES[$documento]['name']);
            $destino = $directorio . '/' . $filename;
            move_uploaded_file($_FILES[$documento]['tmp_name'], $destino);
            ${$documento} = $filename;
        } else {
            //${$documento} = null;  Establecer el valor en null si el archivo no se proporciona
            ${$documento} = null;
        }
    }

    // Preparar la consulta SQL
    $query = "INSERT INTO providers 
                        (
                            tipo_persona, 
                            rfc, 
                            correo_electronico, 
                            razon_social, 
                            direccion, 
                            telefono, 
                            area,
                            constancia,
                            opinion,
                            comprobante,
                            acta,
                            notarial
                        ) VALUES (
                            ?, 
                            ?, 
                            ?, 
                            ?, 
                            ?, 
                            ?,                                                                                                                            
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        );
					";

    header('Content-Type: application/json');



    //documentacion			
    $constancia = isset($constancia) ? $constancia : null;
    $opinion = isset($opinion) ? $opinion : null;
    $comprobante = isset($comprobante) ? $comprobante : null;
    $acta = isset($acta) ? $acta : null;
    $notarial = isset($notarial) ? $notarial : null;

    // Preparar la declaración
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    // Vincular los parámetros
    $stmt->bind_param('isssssisssss', $tipo_persona, $rfc, $correo_electronico, $razon_social, $direccion, $telefono, $empresa_areas, $constancia, $opinion, $comprobante, $acta, $notarial);


    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Si la inserción tiene éxito
        echo json_encode(array(
            'status' => 'Success',
            'message' => 'Registro exitoso'
        ));
    } else {
        // Si no se puede crear un nuevo registro
        echo json_encode(array(
            'status' => 'Error',
            'message' => 'There has been an error, please try again.<pre>' . $mysqli->error . '</pre><pre>' . $query . '</pre>'
        ));
    }

    //close database connection
    $mysqli->close();
}


//update_documents
// Procesar la actualización de documentos
if ($action == 'update_documents') {
    // Conectar a la base de datos de nuevo si es necesario
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    $constancia = $_FILES['constancia']['name'] ?? '';
    $opinion = $_FILES['opinion']['name'] ?? '';
    $rfc = $_GET['rfc'];

    // Obtener la razón social para usarla en la ruta del directorio
    $query_select = "SELECT constancia, opinion, razon_social FROM providers WHERE rfc = ?";
    $stmt_select = $mysqli->prepare($query_select);
    $stmt_select->bind_param('s', $rfc);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        die("Proveedor no encontrado para el RFC proporcionado.");
    }

    $razon_social = $row['razon_social'];
    $directorio = $_SERVER['DOCUMENT_ROOT'] . "/providers/" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $razon_social);

    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    // Documentos que queremos actualizar
    $documentos = array("constancia", "opinion");

    foreach ($documentos as $documento) {
        if (isset($_FILES[$documento]) && $_FILES[$documento]['error'] === UPLOAD_ERR_OK) {
            // Eliminar el documento existente si ya existe
            if (!empty($row[$documento])) {
                $archivo_existente = $directorio . '/' . $row[$documento];
                if (file_exists($archivo_existente)) {
                    unlink($archivo_existente);
                }
            }

            // Subir el nuevo documento
            $filename = basename($_FILES[$documento]['name']);
            $destino = $directorio . '/' . $filename;
            move_uploaded_file($_FILES[$documento]['tmp_name'], $destino);
            ${$documento} = $filename;
        } else {
            // Si no se proporciona un nuevo archivo, mantener el valor actual
            ${$documento} = $row[$documento];
        }
    }

    // Actualizar la base de datos con los nuevos nombres de archivo
    $query_update = "UPDATE providers SET constancia = ?, opinion = ? WHERE rfc = ?";
    $stmt_update = $mysqli->prepare($query_update);
    $stmt_update->bind_param('sss', $constancia, $opinion, $rfc);
    $stmt_update->execute();



    if ($stmt_update->affected_rows > 0) {
        echo "Documentos actualizados correctamente.";
    } else {
        echo "No se realizaron cambios en los documentos.";
    }
}

function getDatabaseConnection()
{
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }
    return $mysqli;
}

// Valida el rol y devuelve la empresa y el guardado asociados
function getEmpresaInfo($rol)
{
    $empresaRoles = [
        8 => 'abforti',
        9 => 'beexen',
        10 => 'upper',
        11 => 'inmobiliaria',
        12 => 'innovet'
    ];
    $empresaDG = [
        8 => 'AB',
        9 => 'BE',
        10 => 'UP',
        11 => 'IM',
        12 => 'IN'
    ];
    return [
        'empresa' => $empresaRoles[$rol] ?? null,
        'guardado' => $empresaDG[$rol] ?? null
    ];
}

// Valida el banco y devuelve su nombre
function validateBanco($banco, $bancoMap)
{
    if (!array_key_exists($banco, $bancoMap)) {
        die("Banco no válido");
    }
    return $bancoMap[$banco];
}

// Genera el directorio base
function createDirectory($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// Genera el nuevo nombre de archivo
function generateFileName($mysqli, $prefix, $banco, $serializacionEspecial)
{
    $query = "SELECT com_pago FROM cfdi WHERE com_pago LIKE CONCAT(?, '%') COLLATE utf8mb4_unicode_ci ORDER BY id DESC LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $prefix);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if ($serializacionEspecial) {
            preg_match('/1\.(\d+)/', $row['com_pago'], $matches);
            $numeroActual = (int)($matches[1] ?? 0);
            return "{$prefix}1." . str_pad($numeroActual + 1, 3, '0', STR_PAD_LEFT);
        } else {
            preg_match('/_(\d+)_C$/', $row['com_pago'], $matches);
            $numeroActual = (int)($matches[1] ?? 0);
            return "{$prefix}" . ($numeroActual + 1) . "_C";
        }
    }
    return $serializacionEspecial ? "{$prefix}1.001" : "{$prefix}1_C";
}


if (in_array($action, ['upload_more', 'payment_receipt'])) {
    $mysqli = getDatabaseConnection();

    $postIDs = $_POST['ids'] ?? ($_POST['id'] ?? null);
    if (!$postIDs || (!is_array($postIDs) && $action == 'upload_more')) {
        die(json_encode(['status' => 'error', 'message' => 'No IDs provided or invalid format.']));
    }

    $bancoMap = [
        1 => 'Banamex',
        2 => 'Banco_Base_Pesos',
        3 => 'Banco_Base_Dolares',
        4 => 'BBVA_Pesos',
        5 => 'Mifel',
        6 => 'BBVA_Dolares',
        7 => 'Santander'
    ];
    $banco = validateBanco($_POST['banco'] ?? '', $bancoMap);

    $rol = $_POST['rol'] ?? ($_SESSION['rol'] ?? null);
    $empresaInfo = getEmpresaInfo($rol);

    if (!$empresaInfo['empresa'] || !$empresaInfo['guardado']) {
        die("Empresa no válida o rol no encontrado en la sesión.");
    }

    date_default_timezone_set('America/Mexico_City');
    $anoActual = date('Y');
    $mesActual = date('m');

    $directorioBase = $_SERVER['DOCUMENT_ROOT'] . "/control/{$empresaInfo['empresa']}/uploads/Bancos/{$anoActual}/{$mesActual}/{$banco}/Egresos/";
    createDirectory($directorioBase);

    $prefix = "{$empresaInfo['guardado']}_EG_";
    $serializacionEspecial = ($banco == 2);
    $nuevoNombre = generateFileName($mysqli, $prefix, $banco, $serializacionEspecial);

    $rutaRelativa = "{$banco}/Egresos/{$nuevoNombre}.pdf";
    $rutaArchivo = $directorioBase . $nuevoNombre . ".pdf";

    if (isset($_FILES['compro_pago'])) {
        $archivo = $_FILES['compro_pago'];
        if (pathinfo($archivo['name'], PATHINFO_EXTENSION) !== 'pdf') {
            die("Solo se permiten archivos PDF.");
        }
        if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
            $fecha_pagada = (new DateTime())->format('Y-m-d H:i:s');

            // Procesar las actualizaciones según las condiciones
            $query = "
                UPDATE cfdi 
                SET 
                    validated = CASE 
                        WHEN validated = 2 AND no_recurrent = 0 THEN 3
                        WHEN validated = 1 AND no_recurrent = 3 THEN 1
                        ELSE validated
                    END,
                    no_recurrent = CASE 
                        WHEN validated = 1 AND no_recurrent = 3 THEN 4
                        ELSE no_recurrent
                    END,
                    fecha_pagada = ?,
                    com_pago = ?,
                    ruta_com_pago = ?,
                    comprobante = 1  -- Aquí se establece comprobante a 1
                WHERE id IN (" . implode(',', array_fill(0, count((array)$postIDs), '?')) . ")
            ";

            $stmt = $mysqli->prepare($query);
            $params = array_merge([$fecha_pagada, $nuevoNombre, $rutaRelativa], (array)$postIDs);
            $stmt->bind_param(str_repeat('s', 3) . str_repeat('i', count((array)$postIDs)), ...$params);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Archivo procesado correctamente.', 'redirect' => 'invoice-list.php']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la base de datos.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al mover el archivo.']);
        }
    }
}


if ($action == 'generate_txt') {
    if (isset($_POST['factura_id'])) {
        $facturaId = intval($_POST['factura_id']); // Convertir el ID de factura a entero
        if ($facturaId > 0) {
            generarTxtDesdeFacturaId($facturaId);
        } else {
            echo json_encode([
                'status' => 'Error',
                'message' => 'ID de factura inválido.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'Error',
            'message' => 'No se proporcionó el ID de la factura.'
        ]);
    }
}