<?php
/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

include('../header.php');
include('../functions.php');
include('../../redirrecciones.php');

checkAccess(10);
$rol = $_SESSION['rol'];
// Obtener los valores de validated y no_recurrent (desde la base de datos o sesión)
$validated = 1; // Ejemplo: valor obtenido de la base de datos
$no_recurrent = 0; // Ejemplo: valor obtenido de la base de datos

// Obtener la página de redirección
$paginaRedireccion = obtenerRedireccion($rol, 'invoice-list.php', $validated, $no_recurrent);

?>

<style>
  .modal-title {
    font-size: 1.25rem;
  }

  .modal-body p {
    font-size: 1rem;
  }
</style>


<div class="header-container">
    <!-- Ícono de flecha con enlace -->
    <a href="<?php echo $paginaRedireccion; ?>" class="btn-flecha">
        <i class="fas fa-arrow-left"></i> <!-- Ícono de flecha (Font Awesome) -->
    </a>

    <!-- Título "Mis facturas" -->
    <h1>Facturas</h1>
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
        <h4 class="mando">Recibidas</h4>
      </div>
      <div class="panel-body form-group form-group-sm">
        <?php getCfdiInvoices('UP'); ?>
      </div>
      <!-- Formulario oculto para enviar el ID del proveedor -->
      <form id="editInvoiceForm" action="invoice-edit.php" method="post" style="display:none;">
        <input type="hidden" name="id" id="customerIdInput">
      </form>

      <!-- Formulario oculto para enviar el ID del proveedor para subida -->
      <form id="upInvoiceForm" action="invoice-up.php" method="post" style="display:none;">
        <input type="hidden" name="id" id="customerIdInputUp">
      </form>
      

      <form id="multiInvoiceForm" action="invoice-upload.php" method="post" style="display:none;">
        <input type="hidden" name="invoiceIds" id="selectedInvoices"> <!-- Cambiar "id" por "invoiceIds" -->
      </form>

    </div>
    
    <!-- Botón para acciones masivas -->
    <button id="multiActionBtn" class="btn btn-success" style="display:none;"
        onclick="submitSelectedInvoices()">Acción Múltiple</button>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Inicializar DataTables (asegúrate de que esté cargada)
    var table = $('#data-table').DataTable();

    // Seleccionar todas las facturas, incluyendo las no visibles en la página actual
      const selectAll = document.getElementById('select-all');
      const multiActionBtn = document.getElementById('multiActionBtn');
      const selectedInvoicesInput = document.getElementById('selectedInvoices');

      selectAll.addEventListener('change', function () {
        // Selecciona todas las filas en todas las páginas
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"].select-invoice', rows).prop('checked', this.checked);
        toggleMultiActionBtn();
      });

    // Evento para cada checkbox individual
  $('#data-table tbody').on('change', '.select-invoice', toggleMultiActionBtn);

function toggleMultiActionBtn() {
  const checkboxes = Array.from(document.querySelectorAll('.select-invoice'));
  const selected = checkboxes.filter(checkbox => checkbox.checked);
  if (selected.length > 1) {
    multiActionBtn.style.display = 'block';  // Mostrar el botón si hay más de una selección
  } else {
    multiActionBtn.style.display = 'none';  // Ocultar si no hay selección múltiple
  }
}
// Función para enviar facturas seleccionadas
window.submitSelectedInvoices = function () {
    // Obtener todas las facturas seleccionadas en todas las páginas
    var selected = [];
    table.$('.select-invoice:checked').each(function () {
      selected.push($(this).val());
    });

    selectedInvoicesInput.value = selected.join(',');  // Pasar los IDs seleccionados
    document.getElementById('multiInvoiceForm').submit();  // Enviar el formulario
  };

  // Event listeners para los botones de edición y subida (mantener lógica existente)
  document.querySelectorAll('.edit-invoice').forEach(function (button) {
    button.addEventListener('click', function () {
      var customerId = this.getAttribute('data-id');
      document.getElementById('customerIdInput').value = customerId;
      document.getElementById('editInvoiceForm').submit();
    });
  });

  document.querySelectorAll('.up-invoice').forEach(function (button) {
    button.addEventListener('click', function () {
      var customerIdUP = this.getAttribute('data-id');
      document.getElementById('customerIdInputUp').value = customerIdUP;
      document.getElementById('upInvoiceForm').submit();
    });
  });
});
</script>


<?php
include('../footer.php');
?>