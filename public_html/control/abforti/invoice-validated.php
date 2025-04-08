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
checkAccess(8);
$rol = $_SESSION['rol'];

// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-validated.php', $validated, $no_recurrent);

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

<div class="header-container">
  <!-- Ícono de flecha con enlace -->
  <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
    <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
  </a>

  <!-- Título "Mis facturas" -->
  <h1>Facturas Pagadas</h1>
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
        <?php getCfdiCPValidated('AB'); ?>
      </div>
    </div>
  </div>
  <div>

    <!-- Modal para mostrar PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="pdfModalLabel">Comprobante de Pago</h5>
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

      $(document).on('click', '.open-pdf-modal', function() {
        var fileUrl = $(this).data('file');
        console.log('Cargando archivo PDF: ', fileUrl);

        // Verificar si el archivo existe en el servidor mediante una solicitud AJAX
        $.ajax({
          url: fileUrl,
          type: 'HEAD', // Usamos HEAD para verificar sin descargar el archivo
          success: function() {
            // Si el archivo existe, lo mostramos en el iframe
            $('#pdfFrame').attr('src', fileUrl);
          },
          error: function() {
            // Si el archivo no existe, mostramos un mensaje
            alert('El archivo PDF no está disponible.');
            $('#pdfFrame').attr('src', ''); // Limpiar el iframe en caso de error
          }
        });
      });

      // Limpiar el iframe cuando se cierra el modal
      $('#pdfModal').on('hidden.bs.modal', function() {
        $('#pdfFrame').attr('src', '');
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