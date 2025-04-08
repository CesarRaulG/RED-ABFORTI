$(document).ready(function() {

	// Load dataTables
	//Se inicializa la tabla con DataTables utilizando el selector #data-table.
	// Destruye la instancia existente si ya existe
    if ($.fn.DataTable.isDataTable("#data-table")) {
        $("#data-table").DataTable().destroy();
    }

    // Inicializa DataTables 
    $("#data-table").dataTable({
        language: {
            search: "Buscar:",
            paginate: {
                previous: "Anterior",
                next: "Siguiente"
            },
            lengthMenu: "Mostrar _MENU_ entradas",
            info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            infoEmpty: "Mostrando 0 a 0 de 0 entradas",
            infoFiltered: "(filtrado de _MAX_ entradas totales)",
            zeroRecords: "No se encontraron registros coincidentes",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            emptyTable: "No hay datos disponibles en la tabla"
        },
		dom: '<"top"lf>rt<"bottom"ip><"clear">' // Ajustar elementos (f: búsqueda, l: menú, i: info, p: paginación)

    });

	
	// add product
	// Carga de DataTables
	// Agregar producto
	//Se activa al hacer clic en un elemento con 
	//el ID #action_add_product. Llama a la función actionAddProduct().
	//SI
	$("#action_add_product").click(function(e) {
		e.preventDefault();
	    actionAddProduct();
	});

	//SI
	// registro providers
	$("#action_add_mor").click(function(e) {
			e.preventDefault();
			//console.log("Botón de agregar producto clicado");
			//console.log("Datos del formulario para enviar al servidor:");
			//console.log($("#add_mor").serialize());
			//console.log()
			actionAddMor();
		
	});
	
	// registro providers
	//SI
	$("#action_add_fis").click(function(e) {
			e.preventDefault();
			actionAddFis();
	});

	// add user
	//Agregar usuario
	//Activado al hacer clic en un elemento con 
	//el ID #action_add_user. Llama a la función actionAddUser().
	//SI
	$("#action_add_user").click(function(e) {
		e.preventDefault();
	    actionAddUser();
	});

	// Actualizar usuario:
	//SI
	$(document).on('click', "#action_update_user", function(e) {
		e.preventDefault();
		updateUser();
	});

	// 
	$(document).bind('keypress', function(e) {
		e.preventDefault;
		
        if(e.keyCode==13){
            $('#btn-login').trigger('click');
        }
    });

	//Crear factura:
	$("#action_create_invoice").click(function(e) {
		e.preventDefault();
	    actionCreateInvoice();
	});
	
	// Habilitar selección de fecha:
	var dateFormat = $(this).attr('data-vat-rate');
	$('#invoice_date, #invoice_due_date').datetimepicker({
		showClose: false,
		format: dateFormat
	});
	    
    //Agregar nueva fila de producto
    var cloned = $('#invoice_table tr:last').clone();
    $(".add-row").click(function(e) {
        e.preventDefault();
        cloned.clone().appendTo('#invoice_table'); 
    });


	$("#action_add_invoice_areas").click(function(e) {
        e.preventDefault();
        actionAddInvoiceAreas();
    });

    function actionAddInvoiceAreas() {
		var errorCounter = validateForm(); 
		if (errorCounter > 0) {
			$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
			$("#response .message").html("<strong>Error</strong>: Parece que has olvidado completar algo!");
			$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {
			$(".required").parent().removeClass("has-error");
	
			var $btn = $("#action_add_invoice_areas").button("loading");
			var formData = $("#add_invoice_areas").serialize();
			
			console.log("Form Data: ", formData); // Añadir log para ver los datos del formulario
	
			$.ajax({
				url: '../response.php', // Cambia esto a la URL de tu script PHP
				type: 'POST',
				data: formData,
				dataType: 'json',
				success: function(data) {
					if (data.status === 'success') {
						$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
						$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
						$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
						// Verificar si hay una URL de redirección
						if (data.redirect) {
						
							// Añadir un retraso de 3 segundos (3000 milisegundos) antes de redirigir
							setTimeout(function() {
								window.location.href = data.redirect;
							}, 4000);  // 3000 ms = 3 segundos
						}
					} else {
						$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
						$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
						$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					}
					$btn.button("reset");
				},
				error: function(xhr, status, error) {
					console.error('Server error:', status, error);
					$("#response .message").html("<strong>Error</strong>: Ha ocurrido un error de comunicación con el servidor.");
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				}
			});
		}
	}

	//SI
	//Departamento Moral
	function actionAddMor(){

		var errorCounter = validateForm() +  validatePersona();


		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: Parece que has olvidado completar algo!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var formData = new FormData($("#add_mor")[0]);
			var $btn = $("#action_add_mor").button("loading");
			 			 

			$.ajax({
				url: 'response.php',
				type: 'POST',
				data: formData,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function(data){
					console.log("Respuesta del servidor (éxito):", data);
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);										
				},
				error: function(data){
					console.log("Respuesta del servidor (error):", data);
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
					

				}

			});
		}
	}

	//SI
	//Departamento Fisica
	function actionAddFis(){
		var errorCounter = validateForm() +  validatePersona();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: Parece que has olvidado completar algo!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var formDataF = new FormData($("#add_fis")[0]);
			var $btn = $("#action_add_fis").button("loading");
			

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: formDataF,
				dataType: 'json',
				processData: false,
				contentType: false,				
				success: function(data){
					console.log("Respuesta del servidor (éxito):", data);
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");													
										
				},
				error: function(data){
					console.log("Respuesta del servidor (error):", data);

					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");				

				}

			});
		}
	}

	//SI
	//esta función agrega un nuevo producto utilizando los datos del formulario
	function actionAddProduct() {

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var $btn = $("#action_add_product").button("loading");

			$.ajax({
				url: 'response.php',
				type: 'POST',
				data: $("#add_product").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				},
				error: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				}

			});
		}

	}

	//SI
	//Esta función realiza una llamada AJAX para agregar un nuevo usuario, 
	function actionAddUser() {

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var $btn = $("#action_add_user").button("loading");

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: $("#add_user").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				},
				error: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				}

			});
		}

	}


	//Esta función valida los campos requeridos en un
	//formulario y devuelve el recuento de errores encontrados.
	function validateForm() {
		// error handling
		var errorCounter = 0;

		if ($("input.area-checkbox:checked").length == 0) {
            errorCounter++;
        }
	
		$(".required").each(function() {
			if ($(this).val() === '') {
				$(this).parent().addClass("has-error");
				errorCounter++;
			} else { 
				$(this).parent().removeClass("has-error"); 
			}
		});
			
		return errorCounter;
	}
	
	

	$("#action_fondo_fijo").click(function(e) {
		e.preventDefault();
		actionFondoFijo();
	});
	
	function actionFondoFijo() {
		var errorCounter = validateForm();
		if (errorCounter > 0) {
			$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
			$("#response .message").html("<strong>Error</strong>: Parece que has olvidado completar algo!");
			$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {
			$(".required").parent().removeClass("has-error");
			var formData = new FormData($("#fondo_fijo")[0]);
			formData.append('action', 'fondo_fijo'); // Añadir la acción al FormData
	
			var $btn = $("#action_fondo_fijo").button("loading");
	
			$.ajax({
				url: '../response.php',
				type: 'POST',
				data: formData,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function(data) {
					console.log("Respuesta del servidor (éxito):", data);
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
	
					if (data.redirect) {
						console.log("Redirigiendo a: ", data.redirect);
						setTimeout(function() {
							window.location.href = data.redirect;
						}, 4000);
					}
				},
				error: function(data) {
					console.log("Respuesta del servidor (error):", data);
					$("#response .message").html("<strong>Error</strong>: Hubo un problema al procesar la solicitud.");
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				}
			});
		}
	}


	

});