<?php
include('header_reg.php');

?>

<h1 class="h3 mb-3 text-gray-800" align="center"><strong>NUEVO PROVEEDOR</strong></h1>

<div id="response" class="alert alert-success" style="display:none;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<div class="message"></div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="panel-body form-group form-group-sm">
            <form method="POST" id="add_mor" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_mor">
                <input type="hidden" name="tipo_persona" value="1">                
                <div class="col-xs-12">
                    <div class="panel-heading">
                        <h4 style="margin-left: -30px;" >Datos generales</h4>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group" style="margin-left: 10px;">
                            <label for="tipo_persona">Tipo de persona:</label>
                            <select class="form-control" id="tipo_persona" name="tipo_persona" onchange="handleTipoPersonaChange()" required>
                                <option value="1">Persona Moral</option>
                                <option value="2">Persona Física</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label for="password">Contraseña</label>                        
							<input type="password" class="form-control required" name="password" id="password"
								placeholder="Ingrese su contraseña">
                        </div>
					</div>
                </div>
                <div id="persona_moral_fields" >
                    <!-- Campos para persona moral -->
                    <div class="col-xs-12">
                        <p>Favor de Ingresar la información para iniciar el proceso. blabla</p>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="rfc">RFC</label>
                                <input type="text" class="form-control form-control-sm margin-bottom copy-input required small-input" id="rfc" name="rfc" oninput="validarRFC(this);" required>
                            </div>
                        </div>                       
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="razon_social">Razón Social</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="razon_social" name="razon_social"  required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="correo_electronico">Correo Electrónico</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="correo_electronico" name="correo_electronico"  required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="direccion" name="direccion"  required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="telefono">Telefono</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="telefono" name="telefono"  required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <p class="text-left">Para que empresa facturas</p>
                                <select class="form-control margin-bottom required" id="departamentoMoral" name="departamentoMoral" required>
                                    <option value="">Seleccione</option>
                                    <option value="1">AB FORTI</option>
                                    <option value="3">SOLGISTIKA</option>
                                    <option value="4">45.57 INMOBILIARIA</option>
                                    <option value="5">INNOVACION EN TERMOFORMADO </option>
                                </select>
                            </div>                            
                        </div>
                    </div>
                    <div class="col-xs-12" >
                        <div class="panel-heading" >
                            <h4 style="margin-left: -25px;">Datos bancarios</h4>
                        </div>                        
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="cuenta">CUENTA</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="cuenta" name="cuenta" required>
                            </div>
                        </div>                       
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="clabe">CLABE Interbancaria</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="clabe" name="clabe" required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="moneda">Moneda:</label>
                                <select class="form-control required" id="moneda" name="moneda" required>
                                    <option value="">Seleccione</option>
                                    <option value="USD">USD</option>
                                    <option value="MXP">MXP</option>
                                </select>
                            </div>
                            <div class="form-group" id="MU" style="display: none;">
                                <label for="swift">SWIFT</label>
                                <input type="text" class="form-control margin-bottom copy-input" id="swift" name="swift">
                            </div>                            
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="banco">Banco</label>
                                <select name="banco" id="banco" class="form-control required">
                                    <option value="">Seleccione</option>
                                    <option value="ABC CAPITAL">ABC CAPITAL</option>
                                    <option value="ACTINVER">ACTINVER</option>
                                    <option value="AFIRME">AFIRME</option>
                                    <option value="ARCUS">ARCUS</option>
                                    <option value="ASP INTEGRA OPC">ASP INTEGRA OPC</option>
                                    <option value="AUTOFIN">AUTOFIN</option>
                                    <option value="AZTECA">AZTECA</option>                                    
                                    <option value="BaBien">BaBien</option>
                                    <option value="BAJIO">BAJIO</option>
                                    <option value="BANAMEX">BANAMEX</option>
                                    <option value="BANCO COVALTO">BANCO COVALTO</option>
                                    <option value="BANCOMEXT">BANCOMEXT</option>
                                    <option value="BANCOPPEL">BANCOPPEL</option>
                                    <option value="BANCO S3">BANCO S3</option>
                                    <option value="BANCREA">BANCREA</option>
                                    <option value="BANJERCITO">BANJERCITO</option>
                                    <option value="BANKAOOL">BANKAOOL</option>
                                    <option value="BANK OF AMERICA">BANK OF AMERICA</option>
                                    <option value="BANK OF CHINA">BANK OF CHINA</option>
                                    <option value="BANOBRAS">BANOBRAS</option>
                                    <option value="BANORTE">BANORTE</option>
                                    <option value="BANREGIO">BANREGIO</option>
                                    <option value="BANSI">BANSI</option>
                                    <option value="BANXICO">BANXICO</option>
                                    <option value="BARCLAYS">BARCLAYS</option>
                                    <option value="BBASE">BBASE</option>
                                    <option value="BBVA MEXICO">BBVA MEXICO</option>
                                    <option value="BMONEX">BMONEX</option>
                                    <option value="CAJA POP MEXICA">CAJA POP MEXICA</option>
                                    <option value="CAJA TELEFONIST">CAJA TELEFONIST</option>
                                    <option value="CB INTERCAM">CB INTERCAM</option>
                                    <option value="CIBANCO">CIBANCO</option>
                                    <option value="CI BOLSA">CI BOLSA</option>
                                    <option value="CLS">CLS</option>
                                    <option value="CoDi Valida">CoDi Valida</option>
                                    <option value="COMPARTAMOS">COMPARTAMOS</option>
                                    <option value="CONSUBANCO">CONSUBANCO</option>
                                    <option value="CREDICAPITAL">CREDICAPITAL</option>
                                    <option value="CREDICLUB">CREDICLUB</option>
                                    <option value="CRISTOBAL COLON">CRISTOBAL COLON</option>
                                    <option value="Cuenca">Cuenca</option>
                                    <option value="DONDE">DONDE</option>
                                    <option value="FINAMEX">FINAMEX</option>
                                    <option value="FINCOMUN">FINCOMUN</option>
                                    <option value="FOMPED">FOMPED</option>
                                    <option value="FONDO (FIRA)">FONDO (FIRA)</option>
                                    <option value="GBM">GBM</option>
                                    <option value="HIPOTECARIA FED">HIPOTECARIA FED</option>
                                    <option value="HSBC">HSBC</option>
                                    <option value="ICBC">ICBC</option>
                                    <option value="INBURSA">INBURSA</option>
                                    <option value="INDEVAL">INDEVAL</option>
                                    <option value="INMOBILIARIO">INMOBILIARIO</option>
                                    <option value="INTERCAM BANCO">INTERCAM BANCO</option>
                                    <option value="INVEX">INVEX</option>
                                    <option value="JP MORGAN">JP MORGAN</option>
                                    <option value="KLAR">KLAR</option>
                                    <option value="KUSPIT">KUSPIT</option>
                                    <option value="LIBERTAD">LIBERTAD</option>
                                    <option value="MASARI">MASARI</option>
                                    <option value="Mercado Pago W">Mercado Pago W</option>
                                    <option value="MIFEL">MIFEL</option>
                                    <option value="MIZUHO BANK">MIZUHO BANK</option>
                                    <option value="MONEXCB">MONEXCB</option>
                                    <option value="MUFG">MUFG</option>
                                    <option value="MULTIVA BANCO">MULTIVA BANCO</option>
                                    <option value="NAFIN">NAFIN</option>
                                    <option value="NU MEXICO">NU MEXICO</option>
                                    <option value="NVIO">NVIO</option>
                                    <option value="PAGATODO">PAGATODO</option>
                                    <option value="PROFUTURO">PROFUTURO</option>
                                    <option value="SABADELL">SABADELL</option>
                                    <option value="SANTANDER">SANTANDER</option>
                                    <option value="SCOTIABANK">SCOTIABANK</option>
                                    <option value="SHINHAN">SHINHAN</option>
                                    <option value="STP">STP</option>
                                    <option value="TESORED">TESORED</option>
                                    <option value="TRANSFER">TRANSFER</option>
                                    <option value="UNAGRA">UNAGRA</option>
                                    <option value="VALMEX">VALMEX</option>
                                    <option value="VALUE">VALUE</option>
                                    <option value="VECTOR">VECTOR</option>
                                    <option value="VE POR MAS">VE POR MAS</option>
                                    <option value="VOLKSWAGEN">VOLKSWAGEN</option>                            
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="titular">Titular</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="titular" name="titular" required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="referencia">Referencia(Opcional)</label>
                                <input type="text" class="form-control margin-bottom copy-input" id="referencia" name="referencia">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="concepto">Concepto(Opcional)</label>
                                <input type="text" class="form-control margin-bottom copy-input" id="concepto" name="concepto">
                            </div>
                        </div>                        
                    </div>
                    
                    
                    <div class="panel panel-default">
                        <div class="panel-body form-group form-group-sm">
                            <!-- Documentos para persona moral -->
                            <h3 style="margin-left: 10px;">Documentos</h3>
                            <div class="row mb-6">
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Constancia de Situacion fiscal</span>
                                    <div class="mb-3">
                                        <input type="file" id="constancia" name="constancia" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Opinion de Situacion fiscal</span>
                                    <div class="mb-3">
                                        <input type="file" id="opinion" name="opinion" accept=".pdf" class="form-control required" aria-label="file example" required>

                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Comprobante de Domicilio </span>
                                    <div class="mb-3">
                                        <input type="file" id="comprobante" name="comprobante" accept=".pdf" class="form-control required" aria-label="file example" required>

                                    </div>
                                </div>                            
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Acta Constitutiva </span>
                                    <div class="mb-3">
                                        <input type="file" id="acta" name="acta" accept=".pdf" class="form-control" aria-label="file example" >
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Poder Notarial </span>
                                    <div class="mb-3">
                                        <input type="file" id="notarial" name="notarial" accept=".pdf" class="form-control" aria-label="file example" >
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Caratula bancaria</span>
                                    <div class="mb-3">
                                        <input type="file" id="bancario" name="bancario" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                            </div>
                            <!-- Agregar un campo oculto para opciones_seleccionadas -->
                            <input type="hidden" name="area" id="area">                            
                            <input type="hidden" name="cod_empresa" id="cod_empresa">

                            <div class="col-xs-12 col-md-4">
                                <input type="button" id="action_add_mor" class="btn btn-success float-left" value="Add Prov">
                            </div>                           
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <div>
<div>



    <!--DEPARTAMENTO MORAL -->
    <script>
        document.getElementById("departamentoMoral").addEventListener("change", function() {
            const selectedValue = this.value;

            // Ocultar todas las opciones de áreas
            document.querySelectorAll('.form-group[id$="OptionsMoral"]').forEach(function(option) {
                option.style.display = "none";
            });

            // Mostrar el div correspondiente a la opción seleccionada
            if (selectedValue !== "0") {
                const selectedOptionId = selectedValue === "1" ? "abFortiOptionsMoral" :
                    selectedValue === "2" ? "beExEnOptionsMoral" :
                    selectedValue === "3" ? "upperLogisticsOptionsMoral" :
                    selectedValue === "4" ? "inmobiliariaOptionsMoral" :
                    selectedValue === "5" ? "innovetOptionsMoral" :
                    "";

                document.getElementById(selectedOptionId).style.display = "block";
            }
        });

        document.getElementById("action_add_mor").addEventListener("click", function(event) {
            // Prevenir el envío del formulario por defecto
            event.preventDefault();
            // Actualizar las opciones seleccionadas antes de enviar el formulario
            actualizarAreasSeleccionadas();
        });


        function actualizarAreasSeleccionadas() {
            let empresa = document.getElementById("departamentoMoral").value; // Obtener el valor de la empresa seleccionada
            let areasSeleccionadas = empresa;// Inicializar el string de áreas seleccionadas con el valor de la empresa

            // Obtener todas las casillas de verificación seleccionadas
            document.querySelectorAll('.checkbox-list input[type="checkbox"]:checked').forEach(function(checkbox) {
                // Concatenar el valor de la casilla de verificación al string de áreas seleccionadas
                areasSeleccionadas += checkbox.value;
            });

            // Actualizar el valor del campo oculto "opciones_seleccionadas" con el string de áreas seleccionadas
            document.getElementById("area").value = areasSeleccionadas;
        
        }
    
        function validarRFC(input) {
            // Convertir a mayúsculas
            input.value = input.value.toUpperCase();

            // Eliminar caracteres no permitidos
            input.value = input.value.replace(/[^A-Z0-9]/g, '');

            // Limitar la longitud a 13 caracteres
            if (input.value.length > 13) {
                input.value = input.value.substring(0, 13);
            }

            // Deshabilitar la entrada adicional si la longitud es 13
            if (input.value.length === 13) {
                input.setAttribute('maxlength', '13');
            } else {
                input.removeAttribute('maxlength');
            }
        }

        function handleTipoPersonaChange() {
            const tipoPersona = document.getElementById("tipo_persona").value;
            if (tipoPersona === "2") {
                window.location.href = "registroF.php";
            }
        }

        document.getElementById("moneda").addEventListener("change", function() {
            const selectedValue = this.value;

            // Ocultar todas las opciones de áreas
            document.querySelectorAll('.form-group[id$="MU"]').forEach(function(option) {
                option.style.display = "none";
            });

            // Mostrar el div correspondiente a la opción seleccionada
            if (selectedValue === "USD") {
                const selectedOptionId = "MU";
                document.getElementById(selectedOptionId).style.display = "block";
            }
        });
    </script>

   
   
    


<?php
 include('footer.php');
?>