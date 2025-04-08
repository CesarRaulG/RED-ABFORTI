<?php

/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				   *
 *******************************************************************************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once("../includes/config.php");

$mensaje = "";

if (isset($_FILES['archivo_pdf']) && isset($_POST['archivo_temporal'])) {
    $archivoPDF = $_FILES['archivo_pdf'];
    $archivoTemporal = $_POST['archivo_temporal'];
    $nombreOriginal = $_POST['nombre_original'];
    $extension = $_POST['extension'];
    $iniciales = $_POST['rol'];
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

    if (empty($archivoTemporal) || !file_exists($archivoTemporal)) {
        die("Error: El archivo temporal no existe o no es válido.");
    }


    // Obtener el año y mes actual en México
    date_default_timezone_set('America/Mexico_City');
    $fechaRegistro = date('Y-m-d H:i:s');

    // Obtener el año y mes actual en México
    $anoActual = date('Y');
    $mesActual = date('m');
    $diaActual = date('d');

    // Directorio base
    $directorioBase = $_SERVER['DOCUMENT_ROOT'] . "/suppliers/abforti/uploads/factura/";

    // Crear directorios basados en año, mes y día
    $directorioAno = $directorioBase . $anoActual;
    $directorioMes = $directorioAno . '/' . $mesActual;
    $directorioDia = $directorioMes . '/' . $diaActual;

    if (!file_exists($directorioAno)) {
        if (!mkdir($directorioAno, 0777, true)) {
            die("No se puede crear el directorio de año");
        }
    }

    if (!file_exists($directorioMes)) {
        if (!mkdir($directorioMes, 0777, true)) {
            die("No se puede crear el directorio de mes");
        }
    }

    if (!file_exists($directorioDia)) {
        if (!mkdir($directorioDia, 0777, true)) {
            die("No se puede crear el directorio de día");
        }
    }

    // Directorio permanente final
    $directorioPermanente = $directorioDia . '/';

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
        // Recuperar el cod_empresa del proveedor basado en su rfc_emisor
        $query_cod_empresa = "SELECT cod_empresa FROM providers WHERE rfc = ?";
        $stmt_cod_empresa = $db->prepare($query_cod_empresa);
        if ($stmt_cod_empresa === false) {
            die("Error en la preparación de la consulta para cod_empresa: " . $db->error);
        }
        $stmt_cod_empresa->bind_param("s", $rfcEmisor);
        if (!$stmt_cod_empresa->execute()) {
            die("Error en la ejecución de la consulta para cod_empresa: " . $stmt_cod_empresa->error);
        }
        $result_cod_empresa = $stmt_cod_empresa->get_result();
        if ($result_cod_empresa->num_rows === 0) {
            die("Proveedor no encontrado para el RFC: " . $rfcEmisor);
        }
        $row_cod_empresa = $result_cod_empresa->fetch_assoc();
        $cod_empresa = $row_cod_empresa['cod_empresa'];
        $stmt_cod_empresa->close();

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
            'cod_empresa' => $cod_empresa,  // Incluimos el cod_empresa
            'factura_xml' => $ArchivoXML,
            'factura_pdf' => $ArchivoPDF
        );



        // Insertar datos de cfdiData

        $stmtCFDI = $db->prepare("INSERT INTO cfdi (rfc_emisor, lugar_expedicion, nombre_em, regimen_fiscal_em, serie, folio, total, sello, rfc_receptor, nombre_receptor, domicilio_fiscal_receptor, regimen_fiscal_receptor, uuid, uso_cfdi, fechaTimbrado, total_impuestos_trasladados, total_impuestos_retenidos, codigo_estatus, estado, fecha, formapago, no_certificado, subtotal, moneda, exportacion, tipodecomprobante, metodopago, fecha_registro, cod_empresa, factura_xml, factura_pdf ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
            $cfdiData['cod_empresa'],
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
