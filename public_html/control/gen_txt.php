<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("../includes/config.php");
function generarTxtDesdeFacturaId($facturaId)
{
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para obtener los datos de la factura
    $query = "SELECT cfdi.factura_xml, cfdi.validated, cfdi.no_recurrent, cfdi.cod_empresa, cfdi.fecha_registro
              FROM cfdi
              WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $facturaId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $factura_xml = $row['factura_xml'];
            $validated = $row['validated'];
            $noRecurrent = $row['no_recurrent'];
            $codEmpresa = $row['cod_empresa'];
            $fechaRegistro = $row['fecha_registro'];

            // Mapear el prefijo a la carpeta de la empresa
            $basePaths = [
                'AB' => 'abforti',
                'BE' => 'beexen',
                'IN' => 'innovet',
                'UP' => 'upper',
                'IM' => 'inmobiliaria',
            ];

            $empresa = $basePaths[substr($codEmpresa, 0, 2)] ?? null;

            if (!$empresa) {
                die('Prefijo de empresa no válido.');
            }

            // Extraer año, mes y día de fecha_registro
            $fecha = new DateTime($fechaRegistro);
            $year = $fecha->format('Y');
            $month = $fecha->format('m');
            $day = $fecha->format('d');

            // Determinar la ruta base según validated y no_recurrent
            $rutaBase = (($validated == 3 || $validated == 2) && $noRecurrent == 0)
                ? "control/$empresa/uploads/factura/$year/$month/$day/"
                : "control/$empresa/uploads/factura/$year/$month/NO_RECURRENTES/$day/";

            // Combinar la ruta base con el nombre del archivo
            $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . '/' . $rutaBase . $factura_xml;

            // Verificar si el archivo existe
            if (!file_exists($rutaCompleta)) {
                die("Error: El archivo XML no se encontró en la ruta esperada: $rutaCompleta");
            }

            // Llamar a la función para generar el TXT
            generarTxtDesdeXML($rutaCompleta, $factura_xml);
        } else {
            echo "Error: No se encontró la factura con el ID proporcionado.";
        }

        $stmt->close();
    }

    $mysqli->close();
}
function generarTxtDesdeXML($xml_file, $original_file_name)
{
    // Cargar y validar el XML
    $xml = simplexml_load_file($xml_file);
    if (!$xml) {
        die("Error: El archivo XML no es válido.");
    }

    // Encabezado de columnas para el archivo TXT
    $encabezado = "Clave,Proveedor,Fecha de documento,Clave de artículo,Cantidad,Costo,Descuento,Referencia proveedor,Fecha de recepción,Factor de conversion,Unidad de compra,Clave de esquema de impuestos,Impuesto 1,Impuesto 2,Impuesto 3,Impuesto 4,Observaciones,Observaciones de partida,Monto indirecto,Número de almacén cabecera,Número de almacén partidas,Número de moneda,Tipo de cambio\n";

    $contenido = ""; // Contenido del archivo TXT

    // Simulación de los valores que se extraen del XML (ajusta esto según tu XML real)
    $productos = $xml->xpath('//cfdi:Concepto');
    $claveProveedor = 1; // Ejemplo de clave fija para proveedor

    foreach ($productos as $index => $producto) {
        $clave = $index + 1; // Ejemplo de clave incremental
        $claveArticulo = $producto['ClaveProdServ'] ?? '***';
        $cantidad = $producto['Cantidad'] ?? '***';
        $costo = $producto['ValorUnitario'] ?? '***';
        $descuento = 0; // Ajusta este valor si aplica
        $referencia = rand(1000, 9999); // Genera un número de referencia ficticio
        $fechaRecepcion = date('d/m/Y', strtotime('+10 days')); // Ejemplo de fecha de recepción

        // Generar fila
        $fila = "$clave,$claveProveedor,14/01/2023,$claveArticulo,$cantidad,$costo,$descuento,$referencia,$fechaRecepcion,,,1,0,0,0,16,Documento de compra,Documento de compra,,1,1,1,1\n";
        $contenido .= $fila;
    }

    // Unir encabezado y contenido
    $txt_final = $encabezado . $contenido;

    // Generar el nombre del archivo TXT usando el nombre original
    $nombre_sin_extension = pathinfo($original_file_name, PATHINFO_FILENAME);
    $txt_file_name = $nombre_sin_extension . '.txt';

    // Descargar el archivo TXT
    header('Content-Type: text/plain');
    header("Content-Disposition: attachment; filename=\"$txt_file_name\"");
    echo $txt_final;
    exit;
}

// Llamada para generar el TXT, por ejemplo, a través de un formulario o una acción de AJAX
// Llamada para generar el TXT con un ID de factura manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['factura_id'])) {
    $facturaId = intval($_POST['factura_id']);  // Obtén el ID de factura desde el formulario

    if ($facturaId) {
        generarTxtDesdeFacturaId($facturaId);  // Llamar a la función para generar el TXT
    } else {
        echo "Error: ID de factura no proporcionado.";
    }
}


?>
