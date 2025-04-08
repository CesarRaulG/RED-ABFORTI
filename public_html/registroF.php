<?php
include('header_reg.php');

?>

<h1 class="h3 mb-2 text-gray-800" align="center"><strong>NUEVO PROVEEDOR</strong></h1>
<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="panel-body form-group form-group-sm">
            <form method="post" id="add_fis" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_fis">
                <!-- Agrega el campo oculto para el tipo de persona -->
                <input type="hidden" name="tipo_persona2" value="2">
                <div class="col-xs-12">
                    <div class="panel-heading">
                        <h4 style="margin-left: -30px;" >Datos generales</h4>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label for="tipo_persona2">Tipo de persona:</label>
                            <select class="form-control" id="tipo_persona2" name="tipo_persona2" onchange="handleTipoPersonaChange()" required>
                                <option value="2">Persona Física</option>
                                <option value="1">Persona Moral</option>
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
                <div id="persona_fisica_fields">
                    <!-- Campos para persona física -->
                    <div class="col-xs-12">
                        <p>Favor de Ingresar la información para iniciar el proceso</p>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="rfcF">RFC</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="rfcF" name="rfcF"  oninput="validarRFC(this);">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="razon_socialF">Razon Social</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="razon_socialF" name="razon_socialF">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="correo_electronicoF">Correo Electronico</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="correo_electronicoF" name="correo_electronicoF" >
                            </div>
                        </div>        
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="direccionF">Direccion</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="direccionF" name="direccionF">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="telefonoF">Telefono</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="telefonoF" name="telefonoF">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group ">
                                <P class="text-left">Para que empresa facturas</P>
                                <select class="form-control margin-bottom required" id="departamentoFisica" name="departamentoFisica" required>
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
                                <label for="cuentaF">CUENTA</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="cuentaF" name="cuentaF" required>
                            </div>
                        </div>                       
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="clabeF">CLABE Interbancaria</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="clabeF" name="clabeF" required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="monedaF">Moneda:</label>
                                <select class="form-control required" id="monedaF" name="monedaF" required>
                                    <option value="">Seleccione</option>
                                    <option value="USD">USD</option>
                                    <option value="MXP">MXP</option>
                                </select>
                            </div>
                            <div class="form-group" id="MUF" style="display: none;">
                                <label for="swiftF">SWIFT</label>
                                <input type="text" class="form-control margin-bottom copy-input" id="swiftF" name="swiftF">
                            </div>                            
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="bancoF">Banco</label>
                                <select name="bancoF" id="bancoF" class="form-control required">
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
                                <label for="titularF">Titular</label>
                                <input type="text" class="form-control margin-bottom copy-input required" id="titularF" name="titularF" required>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="referenciaF">Referencia(Opcional)</label>
                                <input type="text" class="form-control margin-bottom copy-input" id="referenciaF" name="referenciaF">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="form-group">
                                <label for="conceptoF">Concepto(Opcional)</label>
                                <input type="text" class="form-control margin-bottom copy-input" id="conceptoF" name="conceptoF">
                            </div>
                        </div>                        
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body form-group form-group-sm">
                            <div class="card-header py-3" style="margin-left: 20px;"><strong>DOCUMENTACIÓN </strong></div>
                            <div class="row mb-6">
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Constancia</span>
                                    <div class="mb-3">
                                        <input type="file" id="constancia_F" name="constancia_F" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Opinion</span>
                                    <div class="mb-3">
                                        <input type="file" id="opinion_F" name="opinion_F" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Comprobante de Domicilio </span>
                                    <div class="mb-3">
                                        <input type="file" id="comprobante_domicilio_F" name="comprobante_domicilio_F" accept=".pdf" class="form-control required" aria-label="file example" required>

                                    </div>
                                </div>                            
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Acta de Nacimiento </span>
                                    <div class="mb-3">
                                        <input type="file" id="nacimiento" name="nacimiento" accept=".pdf" class="form-control required" aria-label="file example" required>

                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">INE</span>
                                    <div class="mb-3">
                                        <input type="file" id="ine" name="ine" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <span class="font-small input-group-text mb-3">Caratula bancaria</span>
                                    <div class="mb-3">
                                        <input type="file" id="bancarioF" name="bancarioF" accept=".pdf" class="form-control required" aria-label="file example" required>
                                    </div>
                                </div>
                            </div>
                                <!-- Agregar un campo oculto para opciones_seleccionadas -->
                            <input type="hidden" name="area_F" id="area_F">
                            <input type="hidden" name="cod_empresa" id="cod_empresa">

                            <div class="col-xs-12 col-md-4">
                                <input type="submit" id="action_add_fis" class="btn btn-success float-left" value="Add Fis">
                            </div>                                                                 
                        </div>                       
                    </div>
                </div>                                                                          
            </form>
        </div>
    </div>
</div>
    



   

    <!--DEPARTAMENTO FISICA -->
    <script>
        document.getElementById("departamentoFisica").addEventListener("change", function() {
            const selectedValue = this.value;

            // Ocultar todas las opciones de áreas
            document.querySelectorAll('.form-group[id$="OptionsFisica"]').forEach(function(option) {
                option.style.display = "none";
            });

            // Mostrar el div correspondiente a la opción seleccionada
            if (selectedValue !== "0") {
                const selectedOptionId = selectedValue === "1" ? "abFortiOptionsFisica" :
                    selectedValue === "2" ? "beExEnOptionsFisica" :
                    selectedValue === "3" ? "upperLogisticsOptionsFisica" :
                    selectedValue === "4" ? "inmobiliariaOptionsFisica" :
                    selectedValue === "5" ? "innovetOptionsFisica" :
                    "";

                document.getElementById(selectedOptionId).style.display = "block";
            }
        });

        document.getElementById("action_add_fis").addEventListener("click", function(event) {
            // Prevenir el envío del formulario por defecto
            event.preventDefault();
            // Actualizar las opciones seleccionadas antes de enviar el formulario
            actualizarAreasSeleccionadas_F();
        });

        function actualizarAreasSeleccionadas_F() {
            let empresa_F = document.getElementById("departamentoFisica").value; // Obtener el valor de la empresa seleccionada
            let areasSeleccionadas_F = empresa_F; // Inicializar el string de áreas seleccionadas con el valor de la empresa

            // Obtener todas las casillas de verificación seleccionadas
            document.querySelectorAll('.checkbox-list input[type="checkbox"]:checked').forEach(function(checkbox) {
                
                // Concatenar el valor de la casilla de verificación al string de áreas seleccionadas
                areasSeleccionadas_F += checkbox.value;
            });

            // Actualizar el valor del campo oculto "opciones_seleccionadas" con el string de áreas seleccionadas
            document.getElementById("area_F").value = areasSeleccionadas_F;
            
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
        const tipoPersona = document.getElementById("tipo_persona2").value;
        if (tipoPersona === "1") {
            window.location.href = "registroM.php";
        }
         }

         document.getElementById("monedaF").addEventListener("change", function() {
            const selectedValue = this.value;

            // Ocultar todas las opciones de áreas
            document.querySelectorAll('.form-group[id$="MUF"]').forEach(function(option) {
                option.style.display = "none";
            });

            // Mostrar el div correspondiente a la opción seleccionada
            if (selectedValue === "USD") {
                const selectedOptionId = "MUF";
                document.getElementById(selectedOptionId).style.display = "block";
            }
        });

         
    </script>

    



<?php
 include('footer.php');
 ?>