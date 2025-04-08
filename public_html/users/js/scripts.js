$(document).ready(function() {

	

	// Load dataTables
	//Se inicializa la tabla con DataTables utilizando el selector #data-table.
	// Destruye la instancia existente si ya existe
    if ($.fn.DataTable.isDataTable("#data-table")) {
        $("#data-table").DataTable().destroy();
    }

    // Inicializa DataTables con las nuevas opciones
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
	

	// registro providers
	$("#action_add_mor").click(function(e) {
			e.preventDefault();
			//console.log("Botón de agregar producto clicado");
			//console.log("Datos del formulario para enviar al servidor:");
			//console.log($("#add_mor").serialize());
			//console.log()
			actionAddMor();
		
	});

	
	
	
	// password strength
	// Fortaleza de la contraseña
	//Utiliza la biblioteca pwstrength para mostrar 
	//la fortaleza de la contraseña mientras se escribe.
	var options = {
        onLoad: function () {
            $('#messages').text('Start typing password');
        },
        onKeyUp: function (evt) {
            $(evt.target).pwstrength("outputErrorList");
        }
    };
    $('#password').pwstrength(options);

	// Actualizar documentos C y O:
	$(document).on('click', "#action_update_documents", function(e) {
		e.preventDefault();
		updateDocuments();
	});

	// Actualizar producto
	$(document).on('click', "#action_update_customer", function(e) {
		e.preventDefault();
		updateCustomer();
	});
	
	// 
	$(document).bind('keypress', function(e) {
		e.preventDefault;
		
        if(e.keyCode==13){
            $('#btn-login').trigger('click');
        }
    });
	
	// Crear cliente
	$("#action_create_customer").click(function(e) {
		e.preventDefault();
	    actionCreateCustomer();
	});

	//Seleccionar un cliente:
   	$(document).on('click', ".select-customer", function(e) {
   		e.preventDefault;
   		var customer = $(this);
   		$('#insert_customer').modal({ backdrop: 'static', keyboard: false });
   		return false;
   	});
	
	// Habilitar selección de fecha:
	var dateFormat = $(this).attr('data-vat-rate');
	$('#invoice_date, #invoice_due_date').datetimepicker({
		showClose: false,
		format: dateFormat
	});


	
	// Agrega un nuevo cliente utilizando los datos del formulario 
	function actionCreateCustomer(){

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			var $btn = $("#action_create_customer").button("loading");

			$(".required").parent().removeClass("has-error");

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: $("#create_customer").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$("#create_customer").before().html("<a href='./customer-add.php' class='btn btn-primary'>Add New Customer</a>");
					$("#create_cuatomer").remove();
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

	//Actualiza la información de un usuario existente mediante una llamada AJAX al servidor.
	function updateDocuments() {

		var $btn = $("#action_update_documents").button("loading");

	 jQuery.ajax({

		 url: 'response.php',
		 type: 'POST', 
		 data: $("#update_documents").serialize(),
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
		
   	

	//Esta función valida los campos requeridos en un
	//formulario y devuelve el recuento de errores encontrados.
	function validateForm() {
		// error handling
		var errorCounter = 0;
	
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
	
	function validatePersona() {
		var errorCounter = 0;
		var tipoPersona = $("#tipo_persona").val();
	
		// Validación específica para Persona Moral
		if (tipoPersona === "1") {
			$("#add_mor .persona_moral_required").each(function() {
				if ($(this).val() === '') {
					$(this).parent().addClass("has-error");
					errorCounter++;
				} else {
					$(this).parent().removeClass("has-error");
				}
			});
		} 
		// Validación específica para Persona Física
		else if (tipoPersona === "2") {
			$("#add_fis .persona_fisica_required").each(function() {
				if ($(this).val() === '') {
					$(this).parent().addClass("has-error");
					errorCounter++;
				} else {
					$(this).parent().removeClass("has-error");
				}
			});
		}
	
		return errorCounter;
	}

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
	
	




	$(document).ready(function() {
		$('#validate_move').on('submit', function(event) {
			event.preventDefault(); // Prevent the default form submission
			
			var formData = $(this).serialize(); // Serialize the form data
			console.log("Datos enviados:", formData); // Log the sent data
	
			$.ajax({
				url: 'move_file.php',
				type: 'POST',
				data: formData,
				dataType: 'json',
				success: function(response) {
					alert(response.message); // Mostrar siempre el mensaje de la respuesta

                // Redirigir si se proporciona una URL de redirección
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
				// se quito else {
						//alert('Error: ' + response.message);
					//}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error: ', xhr.responseText);
					alert('An error occurred while processing your request.');
				}
			});
		});
	});

	$("#action_confirm_cancel").click(function(e) {
		e.preventDefault();
	    actionUploadMore();
	});

	function actionUploadMore(){

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: Parece que has olvidado completar algo!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var formData = new FormData($("#invoice_cancel")[0]);
			var $btn = $("#action_invoice_cancel").button("loading");
			 			 

			$.ajax({
				url: '../response.php',
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

					 // Verificar si hay una URL de redirección
					 if (data.redirect) {
						console.log("Redirigiendo a: ", data.redirect);
					
						// Añadir un retraso de 3 segundos (3000 milisegundos) antes de redirigir
						setTimeout(function() {
							window.location.href = data.redirect;
						}, 4000);  // 3000 ms = 3 segundos
					}
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

	
	
	


});