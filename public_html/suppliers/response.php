<?php


include_once('includes/config.php');

// show PHP errors
ini_set('display_errors', 1);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
}

$action = isset($_POST['action']) ? $_POST['action'] : "";


// Subir archivos Complementos de Pago

if ($action == 'upload_comple') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El ID no se envió en la solicitud.'
        ]);
        exit;
    }

    $id = (int)$_POST['id'];

    // Consultar factura
    $query = "SELECT id, rfc_emisor, rfc_receptor, uuid, total, no_certificado, cod_empresa FROM cfdi WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    if (!$invoice) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Factura no encontrada.'
        ]);
        exit;
    }

	$empresaCodigo = substr($invoice['cod_empresa'], 0, 2);

	// Mapear los primeros dos dígitos a los nombres de las empresas
	$empresas = [
		'IN' => 'innovet',
		'AB' => 'abforti',
		'BE' => 'beexen',
		'UP' => 'upper',
		'IM' => 'inmobiliaria'
	];

	// Verificar si el código de empresa existe en el mapeo
	if (isset($empresas[$empresaCodigo])) {
		$empresaNombre = $empresas[$empresaCodigo];
	} else {
		// Si no se encuentra en el mapeo, se puede establecer un valor por defecto
		$empresaNombre = 'default'; // Puedes poner cualquier nombre como 'default' o gestionar el error de otra forma
	}

    if (!isset($_FILES['xmlFile']) || $_FILES['xmlFile']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se subió un archivo XML válido.'
        ]);
        exit;
    }

    if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se subió un archivo PDF válido.'
        ]);
        exit;
    }

    try {
		// Cargar el contenido del archivo XML
		$xml_content = file_get_contents($_FILES['xmlFile']['tmp_name']);
		$xml = simplexml_load_string($xml_content);
	
		// Registrar espacios de nombres
		$namespaces = $xml->getNamespaces(true);
		
		if (isset($namespaces['cfdi'])) {
			$xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
		} else {
			throw new Exception('El espacio de nombres "cfdi" no está definido en el archivo XML.');
		}

		if (isset($namespaces['tfd'])) {
			$xml->registerXPathNamespace('tfd', $namespaces['tfd']);
		} else {
			throw new Exception('El espacio de nombres "tfd" no está definido en el archivo XML.');
		}
	
		if (isset($namespaces['pago20'])) {
			$xml->registerXPathNamespace('pago20', $namespaces['pago20']);
		} else {
			throw new Exception('El espacio de nombres "pago20" no está definido en el archivo XML.');
		}
	
		// Verificar y extraer el complemento de pago
		$complemento = $xml->xpath('//cfdi:Comprobante/cfdi:Complemento');
		if (!$complemento) {
			throw new Exception('No se encontró el elemento "Complemento" en el XML.');
		}
	
		// Buscar nodos de pago
		$pagos = $complemento[0]->xpath('.//pago20:Pago');
		if (empty($pagos)) {
			throw new Exception('No se encontraron nodos de pago en el complemento.');
		}
	
		// Extraer datos de cada nodo de pago
		foreach ($pagos as $pago) {
			$idDocumento = isset($pago->xpath('./pago20:DoctoRelacionado/@IdDocumento')[0]) 
				? (string)$pago->xpath('./pago20:DoctoRelacionado/@IdDocumento')[0] 
				: null;
			$monto = isset($pago['Monto']) ? number_format((float)$pago['Monto'], 2, '.', '') : null;

		}
	
		// Extraer UUID
		$uuidNodes = $xml->xpath('//cfdi:Comprobante//tfd:TimbreFiscalDigital');
		if (!$uuidNodes || !isset($uuidNodes[0]['UUID'])) {
			throw new Exception('No se encontró el UUID en el archivo XML.');
		}
		$uuid = (string)$uuidNodes[0]['UUID'];
	
		// Extraer RFC del emisor y receptor
		$emisorNode = $xml->xpath('//cfdi:Emisor');
		$rfc_emisor = $emisorNode ? (string)$emisorNode[0]['Rfc'] : '';
	
		$receptorNode = $xml->xpath('//cfdi:Receptor');
		$rfc_receptor = $receptorNode ? (string)$receptorNode[0]['Rfc'] : '';			

		// Extraer UUID
		$uuidNodes = $xml->xpath('//cfdi:Comprobante//tfd:TimbreFiscalDigital');
		if (!$uuidNodes || !isset($uuidNodes[0]['UUID'])) {
			throw new Exception('No se encontró el UUID en el archivo XML.');
		}
		$uuid = (string)$uuidNodes[0]['UUID'];

		// Extraer RFC del emisor y receptor
		$emisorNode = $xml->xpath('//cfdi:Emisor');
		$rfc_emisor = $emisorNode ? (string)$emisorNode[0]['Rfc'] : '';

		$receptorNode = $xml->xpath('//cfdi:Receptor');
		$rfc_receptor = $receptorNode ? (string)$receptorNode[0]['Rfc'] : '';

		// Extraer certificado y otros datos del comprobante
		$comprobanteNode = $xml->xpath('//cfdi:Comprobante');
		$no_certificado = $comprobanteNode ? (string)$comprobanteNode[0]['NoCertificado'] : '';

		/*
		// Debugging: Ver los datos extraídos
		echo $invoice['total'];
		echo "\n";
		echo $invoice['uuid'];
		echo "\n";
		
		echo "RFC Emisor: $rfc_emisor\n";
		echo "RFC Receptor: $rfc_receptor\n";
		echo "No Certificado: $no_certificado\n";
		echo "IdDocumento: $idDocumento\n";
		echo "Monto: $monto\n";
		*/
	
		// Validar datos extraídos contra la base de datos
		if ($invoice['rfc_emisor'] !== $rfc_emisor) {
			echo json_encode(['status' => 'error', 'message' => 'El RFC del emisor no coincide.']);
			exit;
		}
	
		if ($invoice['rfc_receptor'] !== $rfc_receptor) {
			echo json_encode(['status' => 'error', 'message' => 'El RFC del receptor no coincide.']);
			exit;
		}

		if ($invoice['total'] !== $monto) {
			echo json_encode(['status' => 'error', 'message' => 'El total no coincide.']);
			exit;
		}

		if ($invoice['uuid'] !== $idDocumento) {
			echo json_encode(['status' => 'error', 'message' => 'El UUID no coincide con IdDocumento.']);
			exit;
		}
	
		if ($invoice['no_certificado'] !== $no_certificado) {
			echo json_encode(['status' => 'error', 'message' => 'El número de certificado no coincide.']);
			exit;
		}

        // Continuar con el proceso si todo coincide
        $serie = isset($comprobanteNode[0]['Serie']) ? (string)$comprobanteNode[0]['Serie'] : '';
        $folio = isset($comprobanteNode[0]['Folio']) ? (string)$comprobanteNode[0]['Folio'] : '';
		
		// Extraer nombre del emisor
		$nombreEmisor = $emisorNode ? (string)$emisorNode[0]['Nombre'] : '';
		$nombreEmisor = preg_replace('/[^A-Za-z0-9\s._-]/', '', $nombreEmisor); // Limpia caracteres no válidos
		$nombreEmisor = str_replace(' ', '_', $nombreEmisor); // Reemplaza espacios por guiones bajos

		// Construir el nombre base del archivo
		$nombreBase = $nombreEmisor;

        if ($serie && $folio) {
			$nombreBase .= "_{$serie}_{$folio}";
		} else {
			$nombreBase .= "_" . substr($uuid, -4); // Últimos 4 dígitos del UUID
		}

		// Aquí solo estás guardando el nombre del archivo, no la ruta completa
		$nombreArchivoXML = $nombreBase . ".xml";  // Ejemplo: 45.57_INMOBILIARIA_9012.xml
		$nombreArchivoPDF = $nombreBase . ".pdf";  // Ejemplo: 45.57_INMOBILIARIA_9012.pdf
		
		$directorioBase = $_SERVER['DOCUMENT_ROOT'] . "/suppliers/{$empresaNombre}/uploads/factura/";

        if (!file_exists($directorioBase)) {
            mkdir($directorioBase, 0755, true);
        }

        // Guardar los archivos en el servidor
		$rutaXML = $directorioBase . $nombreArchivoXML;
		if (!move_uploaded_file($_FILES['xmlFile']['tmp_name'], $rutaXML)) {
			throw new Exception('Error al guardar el archivo XML.');
		}

        $rutaPDF = $directorioBase . $nombreArchivoPDF;
		if (!move_uploaded_file($_FILES['pdfFile']['tmp_name'], $rutaPDF)) {
			throw new Exception('Error al guardar el archivo PDF.');
		}

		 // Configurar la zona horaria
		 date_default_timezone_set('America/Mexico_City');
    
		 // Obtener la fecha actual en México
		 $fechaActual = date('Y-m-d H:i:s');

        // Insertar registro en la tabla `complements`
		$query = "INSERT INTO complements (cfdi_id, comple, ruta_xml, ruta_pdf, created_at) 
		VALUES (?, 1, ?, ?, ?)";
		$stmt = $mysqli->prepare($query);

        $stmt->bind_param('isss', $id, $nombreArchivoXML, $nombreArchivoPDF, $fechaActual);

        if (!$stmt->execute()) {
            throw new Exception('Error al guardar el registro en la tabla complements: ' . $stmt->error);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Archivos guardados y registro insertado correctamente.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al procesar los archivos: ' . $e->getMessage()
        ]);
    }
}








	








?>