<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


include('header.php');
include('functions.php');
include_once("includes/config.php");


// Determinar las iniciales según el tipo de usuario
$rol = $_SESSION['rol'];

// Asignar las iniciales según el tipo de usuario
switch ($rol) {
    case 1: // Super Administrador
        $iniciales = "SA";
        break;
    case 2: // Abforti
        $iniciales = "ABF";
        break;
    case 3: // Beexen
        $iniciales = "BEE";
        break;
    case 4: // Upper Logistics
        $iniciales = "UL";
        break;
    case 5: // PIA (dentro de Upper Logistics)
        $iniciales = "PIA";
        break;
    case 6: // Inmobiliaria
        $iniciales = "INM";
        break;
    default:
        $iniciales = "DESC"; // Desconocido
}

function consultarCFDI($rfc_emisor, $rfc_receptor, $total_facturado, $uuid_timbrado)
{
    // Crear cliente SOAP
    $wsdlUrl = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl';
    $soapClient = new SoapClient($wsdlUrl);

    // Construir la expresión impresa
    $expresionImpresa = "?re={$rfc_emisor}&rr={$rfc_receptor}&tt={$total_facturado}&id={$uuid_timbrado}";

    // Realizar la consulta SOAP
    $response = $soapClient->Consulta(array('expresionImpresa' => $expresionImpresa));

    // Verificar si la respuesta es válida
    if ($response && isset($response->ConsultaResult)) {
        $resultado = $response->ConsultaResult;
        return array(
            'CodigoEstatus' => (string) $resultado->CodigoEstatus,
            'Estado' => (string) $resultado->Estado
        );
    } else {
        // Si la respuesta no es válida, devolver valores por defecto
        return array(
            'CodigoEstatus' => 'Desconocido',
            'Estado' => 'Desconocido'
        );
    }
}

function validarXMLCargar($archivos)
{
    $xmls = array();

    foreach ($archivos['tmp_name'] as $key => $tmp_name) {
        $xmlActual = array();

        $nombreOriginal = $_FILES["archivos"]["name"][$key]; // Obtener el nombre original del archivo
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION); // Obtener la extensión del archivo

        $tmp_file = $_FILES["archivos"]["tmp_name"][$key];

        // Crear una ruta temporal única para el archivo
        $tempFilePath = tempnam(sys_get_temp_dir(), 'xml_temp_');
        // Mover el archivo temporal a la ruta temporal única
        if (move_uploaded_file($tmp_file, $tempFilePath)) {
            $xmlActual['nombre_archivo_temporal'] = $tempFilePath; // Guardar el nombre del archivo temporal
            $xmlActual['nombre_original'] = $nombreOriginal; // Guardar el nombre original del archivo
            $xmlActual['extension'] = $extension; // Guardar la extensión del archivo

            // Leer el archivo XML
            $xmlString = file_get_contents($tempFilePath);

            // Inicializar el objeto SimpleXMLElement
            $xml = new SimpleXMLElement($xmlString);

            if ($xml !== false) {
                // Resto del código para procesar el XML...
                $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
                $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

                // Obtener los datos relevantes del XML
                $xmlActual['RFC_EMISOR'] = (string) $xml->xpath('//cfdi:Emisor')[0]['Rfc'];
                $xmlActual['LugarExpedicion'] = (string) $xml['LugarExpedicion']; //codigo postal
                $xmlActual['Nombre'] = (string) $xml->xpath('//cfdi:Emisor')[0]['Nombre']; //Nombre Emisor
                $xmlActual['RegimenFiscal'] = (string) $xml->xpath('//cfdi:Emisor')[0]['RegimenFiscal'];
                $xmlActual['Serie'] = (string) $xml['Serie'];
                $xmlActual['Folio'] = (string) $xml['Folio'];

                $xmlActual['TOTAL'] = (string) $xml['Total'];
                $xmlActual['SELLO'] = (string) $xml['Sello'];

                $xmlActual['RFC_RECEPTOR'] = (string) $xml->xpath('//cfdi:Receptor')[0]['Rfc'];
                $xmlActual['NombreR'] = (string) $xml->xpath('//cfdi:Receptor')[0]['Nombre']; //Nombre Receptor
                $xmlActual['DomicilioFiscalReceptor'] = (string) $xml->xpath('//cfdi:Receptor')[0]['DomicilioFiscalReceptor'];//codigo postal
                $xmlActual['RegimenFiscalReceptor'] = (string) $xml->xpath('//cfdi:Receptor')[0]['RegimenFiscalReceptor'];

                // Obtener el UUID del nodo tfd:TimbreFiscalDigital
                $uuidNode = $xml->xpath('//tfd:TimbreFiscalDigital');
                if (!empty($uuidNode)) {
                    $xmlActual['UUID'] = (string) $uuidNode[0]['UUID'];
                } else {
                    $xmlActual['UUID'] = 'No encontrado';
                }
                // Consultar el CFDI en el servicio web SOAP
                $resultadoConsulta = consultarCFDI($xmlActual['RFC_EMISOR'], $xmlActual['RFC_RECEPTOR'], $xmlActual['TOTAL'], $xmlActual['UUID']);
                $xmlActual['CODIGO_ESTATUS'] = $resultadoConsulta['CodigoEstatus'];
                $xmlActual['ESTADO'] = $resultadoConsulta['Estado'];

                // Agregar los datos del XML al arreglo de resultados
                $xmls[] = $xmlActual;
            } else {
                // Manejar el caso en que la inicialización de $xml falló
                echo "Error: No se pudo inicializar el objeto SimpleXMLElement.";
            }
        } else {
            // Manejar el caso en que no se pudo mover el archivo
            echo "Error: no se pudo mover el archivo temporal a la ruta única.";
        }
    }

    return $xmls;
}


if (isset($_POST['Comprobar']) && isset($_FILES['archivos'])) {
    // Obtener los resultados de la validación de los archivos XML
    $resultados = validarXMLCargar($_FILES['archivos']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Facturas</title>
    <style>
        
        table {
            border-collapse: collapse;
            width: 100%;
            overflow-x: auto;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Dispositivos mas pequeños */
        @media screen and (max-width: 600px) {
            table {
                width: 100%;
            }

            /* Ocultar el encabezado de la tabla en dispositivos pequeños */
            thead {
                display: none;
            }

            /* Mostrar cada fila de la tabla como un bloque en dispositivos pequeños */
            tbody tr {
                display: block;
                margin-bottom: 20px;
            }

            /* Estilos para las celdas en dispositivos pequeños */
            tbody td {
                display: block;
                text-align: left;
            }

            /* Estilos adicionales para la primera columna en dispositivos pequeños */
            tbody td:first-child {
                background-color: #f2f2f2; /* Color de fondo del primer campo en cada fila */
            }
        }
    </style>
</head>

<body>

        

    <form  method="post" enctype="multipart/form-data">
        <input type="file" name="archivos[]" id="inputSubirArchivo" multiple accept=".xml">
        <button type="submit" name="Comprobar" id="Comprobar">Comprobar</button>
    </form>


    <?php if (!empty($resultados)): ?>
            <h2>Resultados</h2>
        <div style="overflow-x-auto;">
            <table>
                <thead>
                    <tr>
                        <th>RFC Emisor</th>
                        <th>Razon Social</th>
                        <th>Codigo Postal</th>                    
                        <th>Serie</th>
                        <th>Folio</th>
                        <th>UUID</th>
                        <th>Regimen Fiscal</th>

                        <th>RFC Receptor</th>
                        <th>Razon Social Receptor</th>
                        <th>Codigo Postal</th>
                        <th>Regimen Fiscal Receptor</th>

                        <th>Total</th>
                        <th>Código Estatus</th>
                        <th>Estado</th>
                        <th>Guardar</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $resultado): ?>
                            <tr id="fila-<?php echo $key; ?>">
                                <td><?php echo $resultado['RFC_EMISOR']; ?></td>            
                                <td><?php echo $resultado['Nombre']; ?></td>
                                <td><?php echo $resultado['LugarExpedicion']; ?></td>
                                <td><?php echo $resultado['Serie']; ?></td>
                                <td><?php echo $resultado['Folio']; ?></td> 
                                <td><?php echo $resultado['UUID']; ?></td>
                                <td><?php echo $resultado['RegimenFiscal']; ?></td> 


                                <td><?php echo $resultado['RFC_RECEPTOR']; ?></td>
                                <td><?php echo $resultado['NombreR']; ?></td> 
                                <td><?php echo $resultado['DomicilioFiscalReceptor']; ?></td> 
                                <td><?php echo $resultado['RegimenFiscalReceptor']; ?></td> 
                        
                        
                                <td><?php echo $resultado['TOTAL']; ?></td>                        
                                <td><?php echo $resultado['CODIGO_ESTATUS']; ?></td>
                                <td><?php echo $resultado['ESTADO']; ?></td>
                        
                                <!-- Agregar el formulario y el botón de guardar -->
                <td>
                     <form class="form-guardar" data-id="<?php echo $key; ?>">
                        <input type="hidden" name="archivo_temporal" value="<?php echo $resultado['nombre_archivo_temporal']; ?>">
                        <input type="hidden" name="nombre_original" value="<?php echo $resultado['nombre_original']; ?>">
                        <input type="hidden" name="extension" value="<?php echo $resultado['extension']; ?>">
                        <input type="hidden" name="rol" value="<?php echo $iniciales ?>">
                        <input type="hidden" name="Folio" value="<?php echo $resultado['Folio']; ?> ">
                        <button type="button" class="btn-guardar">Guardar</button>
                    
                    </form>
                </td>
                            </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>    
    <?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const botonesGuardar = document.querySelectorAll('.btn-guardar');
        botonesGuardar.forEach(boton => {
            boton.addEventListener('click', function() {
                const formulario = this.closest('.form-guardar');
                const botonGuardar = this;
                const formData = new FormData(formulario);
                fetch('guardar_archivo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data); // Puedes manejar la respuesta del servidor aquí
                    botonGuardar.textContent = 'Guardado'; // Actualizar el texto del botón
                    botonGuardar.disabled = true; // Deshabilitar el botón
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    });
</script>


</body>

</html>