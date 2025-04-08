<?php

/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/


include_once('../header.php');
include_once('../functions.php');
include('../../redirrecciones.php');

checkAccess(13);
$rol = $_SESSION['rol'];

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-validated.php', $validated, $no_recurrent);
?>
<div class="header-container">
  <!-- Ícono de flecha con enlace -->
  <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
    <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
  </a>

  <!-- Título "Mis facturas" -->
  <h1>Fondos Fijos</h1>
</div>

<hr>

<div class="row">
  <div class="col-xs-12">
    <div id="response" class="alert alert-success" style="display:none;">
      <a href="#" class="close" data-dismiss="alert">&times;</a>
      <div class="message"></div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">
      </div>
      <div class="panel-body form-group form-group-sm">
        <?php getCfdiValidatedFF('AB'); ?>
      </div>
    </div>
  </div>
  <div>

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



    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Añadir event listener a los botones de edición
        document.querySelectorAll('.edit-invoice').forEach(function(button) {
          button.addEventListener('click', function() {
            var customerId = this.getAttribute('data-id');
            document.getElementById('customerIdInput').value = customerId;
            document.getElementById('editInvoiceForm').submit();
          });
        });
      });
    </script>
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