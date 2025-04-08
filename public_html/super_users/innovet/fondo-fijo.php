<?php
/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				   *
 *******************************************************************************/
ini_set('max_execution_time', '300'); // 5 minutos para la ejecución del script
ini_set('max_input_time', '300'); // 5 minutos para la entrada de datos

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('../header.php');
include_once('../functions.php');
include_once("../includes/config.php");
include('../../redirrecciones.php');

checkAccess(17);

class FacturaValidator {
    private $rfc_receptor_esperado = "IET0701246J3";
    private $mensajesErrores = [];
    private $erroresPorArchivo = [];

    public function consultarCFDI($rfc_emisor, $rfc_receptor_esperado, $total_facturado, $uuid_timbrado) {
        $wsdlUrl = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl';
        $soapClient = new SoapClient($wsdlUrl);
        $expresionImpresa = "?re={$rfc_emisor}&rr={$rfc_receptor_esperado}&tt={$total_facturado}&id={$uuid_timbrado}";

        $response = $soapClient->Consulta(array('expresionImpresa' => $expresionImpresa));
        if ($response && isset($response->ConsultaResult)) {
            $resultado = $response->ConsultaResult;
            return array(
                'CodigoEstatus' => (string) $resultado->CodigoEstatus,
                'Estado' => (string) $resultado->Estado
            );
        } else {
            return array(
                'CodigoEstatus' => 'Desconocido',
                'Estado' => 'Desconocido'
            );
        }
    }

    public function validarXMLCargar($archivos) {
        $xmls = [];

        foreach ($archivos['tmp_name'] as $key => $tmp_name) {
            $xmlActual = array();
            $nombreOriginal = $_FILES["archivos"]["name"][$key];
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $tmp_file = $_FILES["archivos"]["tmp_name"][$key];
            $tempFilePath = tempnam(sys_get_temp_dir(), 'xml_temp_');

            if (move_uploaded_file($tmp_file, $tempFilePath)) {
                $xmlActual['nombre_archivo_temporal'] = $tempFilePath;
                $xmlActual['nombre_original'] = $nombreOriginal;
                $xmlActual['extension'] = $extension;
                $xmlString = file_get_contents($tempFilePath);

                try {
                    $xml = new SimpleXMLElement($xmlString);
                    $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
                    $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');

                    $fechaEmision = (string) $xml['Fecha'];
                    $rfc_receptor = (string) $xml->xpath('//cfdi:Receptor')[0]['Rfc'];
                    if ($rfc_receptor !== $this->rfc_receptor_esperado) {
                        $this->erroresPorArchivo[$nombreOriginal][] = 'El RFC del receptor no coincide.';
                        continue;
                    }

                    $emisor = $xml->xpath('//cfdi:Emisor')[0];
                    $receptor = $xml->xpath('//cfdi:Receptor')[0];
                    $timbreFiscalDigital = $xml->xpath('//tfd:TimbreFiscalDigital')[0];
                    $impuestos = $xml->xpath('//cfdi:Impuestos')[0];

                    $tras = isset($impuestos['TotalImpuestosTrasladados']) ? (string) $impuestos['TotalImpuestosTrasladados'] : '0.00';
                    $ret = isset($impuestos['TotalImpuestosRetenidos']) ? (string) $impuestos['TotalImpuestosRetenidos'] : '0.00';

                    $xmlActual['RFC_EMISOR'] = (string) $emisor['Rfc'];
                    $xmlActual['LugarExpedicion'] = (string) $xml['LugarExpedicion'];
                    $xmlActual['Nombre'] = (string) $emisor['Nombre'];
                    $xmlActual['RegimenFiscal'] = (string) $emisor['RegimenFiscal'];
                    $xmlActual['Serie'] = (string) $xml['Serie'];
                    $xmlActual['Folio'] = (string) $xml['Folio'];
                    $xmlActual['TOTAL'] = (string) $xml['Total'];
                    $xmlActual['SELLO'] = (string) $xml['Sello'];
                    $xmlActual['RFC_RECEPTOR'] = $rfc_receptor;
                    $xmlActual['NombreR'] = (string) $receptor['Nombre'];
                    $xmlActual['DomicilioFiscalReceptor'] = (string) $receptor['DomicilioFiscalReceptor'];
                    $xmlActual['RegimenFiscalReceptor'] = (string) $receptor['RegimenFiscalReceptor'];
                    $xmlActual['Fecha'] = $fechaEmision;
                    $xmlActual['TotalImpuestosTrasladados'] = $tras;
                    $xmlActual['TotalImpuestosRetenidos'] = $ret;
                    $xmlActual['UUID'] = (string) $timbreFiscalDigital['UUID'];

                    $resultadoConsulta = $this->consultarCFDI($xmlActual['RFC_EMISOR'], $this->rfc_receptor_esperado, $xmlActual['TOTAL'], $xmlActual['UUID']);

                    $xmlActual['CODIGO_ESTATUS'] = $resultadoConsulta['CodigoEstatus'];
                    $xmlActual['ESTADO'] = $resultadoConsulta['Estado'];

                    $xmls[] = $xmlActual;
                } catch (Exception $e) {
                    $this->erroresPorArchivo[$nombreOriginal][] = 'No se pudo procesar el XML. ' . $e->getMessage();
                    unlink($tempFilePath);
                    continue;
                }
            }
        }

        if (!empty($this->erroresPorArchivo)) {
            foreach ($this->erroresPorArchivo as $archivo => $errores) {
                $this->mensajesErrores[] = '<div class="alert alert-danger">Error en el archivo ' . $archivo . ': ' . implode(', ', $errores) . '</div>';
            }
        }

        if (!empty($this->mensajesErrores)) {
            echo '<div id="errores">' . implode('', $this->mensajesErrores) . '</div>';
        }

        return $xmls;
    }
}

// Determinar las iniciales según el tipo de usuario
$rol = $_SESSION['rol'];
$iniciales = match ($rol) {
    1 => "SA",
    2, 6 => "ABF",
    3 => "BEE",
    4 => "UL",
    5 => "PIA",
    default => "DESC"
};

if (isset($_POST['Comprobar'])) {
    $validator = new FacturaValidator();
    $resultados = $validator->validarXMLCargar($_FILES['archivos']);
}

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'fondo-fijo.php', $validated, $no_recurrent);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Facturas</title>
</head>

<body>
<div class="header-container">
    <!-- Ícono de flecha con enlace -->
    <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
        <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
    </a>

    <!-- Título "Mis facturas" -->
    <h1>Fondos Fijos</h1>
</div>
    <div class="alert-container">
        <?php
        if (!empty($mensajesErrores)) {
            echo implode('', $mensajesErrores);
        }
        ?>
    </div>
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <h1>Subir XML</h1>
            <input type="file" name="archivos[]" id="inputSubirArchivo" multiple accept=".xml">
            <button class="Btn01" type="submit" name="Comprobar" id="Comprobar">Comprobar</button>
        </form>
    </div>

    <div id="mensaje"></div> <!-- Elemento para mostrar el mensaje -->

    <?php if (!empty($resultados)): ?>
        <h2>Resultados</h2>
        <div class="table-container">
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
                    <?php foreach ($resultados as $key => $resultado): ?>
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
                            <td>
                                <form enctype="multipart/form-data" id="fondo_fijo" class="form-guardar" data-id="<?php echo $key; ?>">
                                    <input type="hidden" name="action" value="fondo_fijo">
                                    <input type="hidden" name="archivo_temporal" value="<?php echo $resultado['nombre_archivo_temporal']; ?>">
                                    <input type="hidden" name="nombre_original" value="<?php echo $resultado['nombre_original']; ?>">
                                    <input type="hidden" name="extension" value="<?php echo $resultado['extension']; ?>">
                                    <input type="hidden" name="UUID" value="<?php echo $resultado['UUID']; ?>">
                                    <input type="hidden" name="fecha_emision" value="<?php echo $resultado['Fecha']; ?>">

                                    <!-- Campo para subir el PDF -->
                                    <label for="archivo_pdf_<?php echo $key; ?>">Subir PDF:</label>
                                    <input type="file" name="archivo_pdf" id="archivo_pdf_<?php echo $key; ?>" accept=".pdf" required>

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
        document.addEventListener('DOMContentLoaded', function () {
            const botonesGuardar = document.querySelectorAll('.btn-guardar');
            const mensajeDiv = document.getElementById('mensaje'); // Obtener el elemento del mensaje

            botonesGuardar.forEach(boton => {
                boton.addEventListener('click', function () {
                    const formulario = this.closest('.form-guardar');
                    const archivoPDF = formulario.querySelector('input[name="archivo_pdf"]');

                    const botonGuardar = this;
                    const formData = new FormData(formulario);
                    const filaId = formulario.getAttribute('data-id');

                    // Validar que se haya seleccionado un archivo PDF
                    if (!archivoPDF.files.length) {
                        alert("Por favor, sube un archivo PDF.");
                        return;
                    }

                    fetch('../response.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.text())
                        .then(data => {
                            if (data.includes('El archivo ha sido subido y procesado con éxito.')) {
                                botonGuardar.textContent = 'Guardado';
                                botonGuardar.disabled = true;
                                const fila = document.getElementById(`fila-${filaId}`);
                                fila.parentNode.removeChild(fila); // Eliminar la fila de la tabla

                                // Mostrar el mensaje
                                mensajeDiv.style.display = 'block';
                                mensajeDiv.textContent = 'El archivo ha sido subido y procesado con éxito.';

                                // Ocultar el mensaje después de 8 segundos
                                setTimeout(() => {
                                    mensajeDiv.style.display = 'none';
                                }, 5000);
                            } else {
                                alert(data); // Muestra el mensaje de error recibido
                            }
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