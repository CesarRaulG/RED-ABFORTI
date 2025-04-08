<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../header.php');
include('../functions.php');
include('../../redirrecciones.php');

// Verificar el acceso
checkAccess(7, '5');

// Obtener el RFC del proveedor actual desde la sesión
$rfc = $_SESSION['login_username'];
$rol = $_SESSION['rol'];

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 0; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-list.php', $validated, $no_recurrent);

// Obtener el cod_empresa usando el RFC
$cod_empresa = getCodEmpresa($rfc);

if ($cod_empresa !== null) {
    // Mostrar las facturas para el cod_empresa obtenido y el RFC
    echo 
    '<div class="header-container">
        <!-- Ícono de flecha con enlace -->
        <a href="' . $paginaRedireccion . '" class="btn-flecha">
            <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
        </a>
        
        <!-- Título "Mis facturas" -->
        <h1>Mis facturas</h1>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div id="response" class="alert alert-success" style="display:none;">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <div class="message"></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Facturas</h4>
                </div>
                <div class="panel-body form-group form-group-sm">';
                    getInvoices($cod_empresa, $rfc);
        echo  ' </div>
            </div>
        </div>
    </div>';
} else {
    echo "<p>No se encontró un código de empresa asociado a su cuenta.</p>";
}

?>

<style>
    /* Estilo para XML resaltado */
    .xml-tag {
        color: #0074D9;
        font-weight: bold;
    }

    .xml-attribute {
        color: #FF851B;
    }

    .xml-value {
        color: #2ECC40;
    }

    /* Personalización para el panel */
    .panel-heading {
        color: white !important;
        font-weight: bold;
    }

    /* Botones mejorados */
    .btn-primary,
    .btn-secondary,
    .btn-success {
        margin: 1px;
    }

    #xmlContent {
        font-size: 14px;
        color: #333;
        background-color: #f4f4f4;
        padding: 10px;
        border-radius: 5px;
    }
</style>
<!-- Modal para mostrar PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">Documento PDF</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="pdfFrame" src="" width="100%" height="600px"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar XML -->
<div class="modal fade" id="xmlModal" tabindex="-1" role="dialog" aria-labelledby="xmlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="xmlModalLabel">Contenido del XML</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- El contenido del XML formateado aparecerá aquí -->
                <div id="xmlContent" style="white-space: pre-wrap; word-wrap: break-word; max-height: 900px; overflow-y: auto; background-color: #f8f9fa; padding: 5px; border: 0.2px solid #ddd;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>




<!-- Modal para cargar archivos -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div id="response" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Subir XML y PDF</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="upload_comple" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_comple">
                    <input type="hidden" name="id" id="invoiceId">

                    <div class="form-group">
                        <label for="xmlFile">Archivo XML</label>
                        <input type="file" name="xmlFile" id="xmlFile" class="form-control" accept=".xml" required>
                    </div>
                    <div class="form-group">
                        <label for="pdfFile">Archivo PDF</label>
                        <input type="file" name="pdfFile" id="pdfFile" class="form-control" accept=".pdf" required>
                    </div>

                    <button type="submit" id="action_upload_comple" class="btn btn-success">Subir Archivos</button>
                </form>
            </div>
        </div>
    </div>
</div>




<script>
    // Asignar el evento a todos los botones con clase 'open-pdf-modal'
    $(document).on('click', '.open-pdf-modal', function() {
        var fileUrl = $(this).data('file');

        // Establecer la URL del archivo en el iframe dentro del modal
        $('#pdfFrame').attr('src', fileUrl);
    });

    // Limpiar el iframe cuando se cierra el modal para evitar que el PDF siga cargado
    $('#pdfModal').on('hidden.bs.modal', function() {
        $('#pdfFrame').attr('src', '');
    });



    // Asignar el evento a los botones para abrir el modal y pasar el ID
    $(document).on('click', '.open-upload-modal', function() {
        var invoiceId = $(this).data('id'); // Obtener el ID de la factura
        $('#invoiceId').val(invoiceId); // Asignar el ID al campo oculto
        $('#uploadModal').modal('show'); // Mostrar el modal
    });

    $('#uploadModal').on('show.bs.modal', function() {
        // Eliminar mensajes previos al abrir el modal
        $(this).find('.alert').remove();
    });

    // Mostrar contenido XML en el modal
    $(document).on('click', '.open-xml-modal', function() {
        var fileUrl = $(this).data('file');
        $('#xmlContent').text('Cargando...');

        $.ajax({
            url: fileUrl,
            type: 'GET',
            dataType: 'text',
            success: function(response) {
                // Aplicar formato al XML
                var prettyXml = formatXml(response);
                $('#xmlContent').html('<pre>' + prettyXml + '</pre>');
            },
            error: function() {
                $('#xmlContent').text('Error al cargar el archivo XML.');
            }
        });
    });

    function formatXml(xml) {
        var formatted = '';
        var reg = /(>)(<)(\/*)/g;
        xml = xml.replace(reg, '$1\r\n$2$3');
        var pad = 0;
        xml.split('\r\n').forEach(function(node) {
            var indent = 0;
            if (node.match(/.+<\/\w[^>]*>$/)) {
                indent = 0;
            } else if (node.match(/^<\/\w/)) {
                if (pad !== 0) {
                    pad -= 1;
                }
            } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
                indent = 1;
            }
            formatted += new Array(pad + 1).join('  ') + node + '\r\n';
            pad += indent;
        });
        return formatted.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>



<?php
include('../footer.php');
?>