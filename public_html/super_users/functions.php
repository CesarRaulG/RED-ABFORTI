<?php
include_once("includes/config.php");

/*******************************************************************************
 *  Red AbForti                                                                 *
 *                                                                              *
 * Version: 1.0	                                                               *
 * Developer:  Cesar Gonzalez                                 				           *
 *******************************************************************************/

function getCfdiInvoices($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Mapeo de áreas
    $areaMap = [
        1 => 'DIRECCION',
        2 => 'ADMINISTRACION',
        3 => 'COMERCIAL',
        4 => 'CONTROL',
        5 => 'TECNOLOGIAS DE LA INFORMACION',
        6 => 'GCH',
        7 => 'ESR',
        8 => 'PDT',
        13 => 'CEVA',
        14 => 'CEDIM',
        15 => 'INSUMOS',
        16 => 'TRANSPORTE QRO',
        17 => 'PIA 10',
        18 => 'CVA GDL',
        19 => 'CEVA GDL',
        20 => 'CVA MTY',
        21 => 'CORPORATIVO',
        22 => 'QRO',
        23 => 'COMERCIAL',
        24 => 'GDL SLS',
        25 => 'CVA MX',
        26 => 'CEDIC',
        27 => 'GERENCIA OPERACIONES',
        28 => 'CALAMANDA',
        29 => 'MANZANILLO',
        30 => 'PIA 41',
        31 => 'ESR',
        32 => 'DIRECCION',
        33 => 'COMPLIANCE',
        34 => 'STUFFACTORY GDL',
        50 => 'PRODUCCION',
        52 => 'ADMINISTRACION',
        53 => 'VENTAS',
        80 => 'COSTO PRODUCCION',
        81 => 'DIRECCION',
        82 => 'LOGISTICA',
        83 => 'PROYECTOS',
        84 => 'PRODUCCION',
        85 => 'GCH',
        86 => 'CALIDAD',
        87 => 'MANTENIMIENTO',
        88 => 'CONTROL',
        89 => 'ADQUISICIONES',
        90 => 'TECNOLOGIAS DE LA INFROMACION',
        91 => 'COMERCIAL',
        92 => 'ESR',
        93 => 'ATENCION AL CLIENTE',
        94 => 'COSTEO',
        95 => 'DISEÑO',
        96 => 'MAQUINADOS',
        97 => 'CORPORATIVO'
    ];

    // Consulta con LEFT JOIN para incluir datos de cancelación si existen
    $query = "SELECT 
                cfdi.id,
                cfdi.folio, 
                cfdi.nombre_em, 
                cfdi.serie, 
                cfdi.total,
                a_cancellation.area AS cancel_area,
                a_cancellation.cancelacion AS cancel_reason,
                a_cancellation.cancellation_date
              FROM cfdi
              LEFT JOIN a_cancellation ON cfdi.id = a_cancellation.cfdi_id
              WHERE cfdi.cod_empresa LIKE ? 
                AND cfdi.validated = 0 
                AND cfdi.no_recurrent = 0
              ORDER BY cfdi.folio";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener los resultados
        $results = $stmt->get_result();

        // Verificar si hay resultados
        if ($results->num_rows > 0) {
            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th>Estado</th> <!-- Nueva columna para mostrar el estado -->
                    <th></th>                
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while ($row = $results->fetch_assoc()) {
                $totalFormateado = number_format($row["total"], 2);

                // Verificar si la factura tiene cancelación            
                $estado = ($row["cancel_reason"]) ?
                    '<span class="text-danger">Cancelada</span><br>
                     <strong>Departamento:</strong> ' . (isset($areaMap[$row["cancel_area"]]) ? $areaMap[$row["cancel_area"]] : "Área desconocida") . '<br>
                     <strong>Motivo:</strong> ' . $row["cancel_reason"] . '<br>
                     <strong>Fecha:</strong> ' . $row["cancellation_date"] . ''
                    : '<span class="text-success">Activa</span>';


                print '
                    <tr>
                        <td>' . $row["nombre_em"] . '</td>
                        <td>' . $row["folio"] . '</td>
                        <td>' . $row["serie"] . '</td>
                        <td>$' . $totalFormateado . '</td> 
                        <td>' . $estado . '</td> <!-- Muestra el estado de la factura -->
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="' . $row["id"] . '">
                                <span class="glyphicon glyphicon-edit center" aria-hidden="true"></span>
                            </button>                                                                              
                        </td>					
                    </tr>';
            }

            // Cerrar la tabla HTML
            print '</tbody></table>';
        } else {
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar la memoria asociada con los resultados
        $results->free();
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

function getNonRecurrentCfdiInvoices($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }
    // Mapeo de áreas
    $areaMap = [
        1 => 'DIRECCION',
        2 => 'ADMINISTRACION',
        3 => 'COMERCIAL',
        4 => 'CONTROL',
        5 => 'TECNOLOGIAS DE LA INFORMACION',
        6 => 'GCH',
        7 => 'ESR',
        8 => 'PDT',
        13 => 'CEVA',
        14 => 'CEDIM',
        15 => 'INSUMOS',
        16 => 'TRANSPORTE QRO',
        17 => 'PIA 10',
        18 => 'CVA GDL',
        19 => 'CEVA GDL',
        20 => 'CVA MTY',
        21 => 'CORPORATIVO',
        22 => 'QRO',
        23 => 'COMERCIAL',
        24 => 'GDL SLS',
        25 => 'CVA MX',
        26 => 'CEDIC',
        27 => 'GERENCIA OPERACIONES',
        28 => 'CALAMANDA',
        29 => 'MANZANILLO',
        30 => 'PIA 41',
        31 => 'ESR',
        32 => 'DIRECCION',
        33 => 'COMPLIANCE',
        34 => 'STUFFACTORY GDL',
        50 => 'PRODUCCION',
        52 => 'ADMINISTRACION',
        53 => 'VENTAS',
        80 => 'COSTO PRODUCCION',
        81 => 'DIRECCION',
        82 => 'LOGISTICA',
        83 => 'PROYECTOS',
        84 => 'PRODUCCION',
        85 => 'GCH',
        86 => 'CALIDAD',
        87 => 'MANTENIMIENTO',
        88 => 'CONTROL',
        89 => 'ADQUISICIONES',
        90 => 'TECNOLOGIAS DE LA INFROMACION',
        91 => 'COMERCIAL',
        92 => 'ESR',
        93 => 'ATENCION AL CLIENTE',
        94 => 'COSTEO',
        95 => 'DISEÑO',
        96 => 'MAQUINADOS',
        97 => 'CORPORATIVO'
    ];

    // Consulta para seleccionar facturas no recurrentes
    $query = "SELECT 
                cfdi.id,
                cfdi.folio, 
                cfdi.nombre_em, 
                cfdi.serie, 
                cfdi.total,
                a_cancellation.area AS cancel_area,
                a_cancellation.cancelacion AS cancel_reason,
                a_cancellation.cancellation_date
              FROM cfdi
              LEFT JOIN a_cancellation ON cfdi.id = a_cancellation.cfdi_id
              WHERE cfdi.cod_empresa LIKE ? 
                AND cfdi.validated = 0 
                AND cfdi.no_recurrent = 1
              ORDER BY cfdi.folio";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener los resultados
        $results = $stmt->get_result();

        // Verificar si hay resultados
        if ($results->num_rows > 0) {

            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th>Estado</th> <!-- Nueva columna para mostrar el estado -->
                    <th></th>                
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while ($row = $results->fetch_assoc()) {

                // Formatear el total como cantidad con separadores de miles y decimales
                $totalFormateado = number_format($row["total"], 2);
                // Verificar si la factura tiene cancelación            
                $estado = ($row["cancel_reason"]) ?
                    '<span class="text-danger">Cancelada</span><br>
                 <strong>Departamento:</strong> ' . (isset($areaMap[$row["cancel_area"]]) ? $areaMap[$row["cancel_area"]] : "Área desconocida") . '<br>
                 <strong>Motivo:</strong> ' . $row["cancel_reason"] . '<br>
                 <strong>Fecha:</strong> ' . $row["cancellation_date"] . ''
                    : '<span class="text-success">Activa</span>';

                print '
                    <tr>
                        
                        <td>' . $row["nombre_em"] . '</td>
                        <td>' . $row["folio"] . '</td>
                        <td>' . $row["serie"] . '</td>
                        <td>$' . $totalFormateado . '</td> <!-- Mostrar el total formateado -->
                        <td>' . $estado . '</td> <!-- Muestra el estado de la factura -->
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="' . $row["id"] . '">
                                <span class="glyphicon glyphicon-edit center" aria-hidden="true"></span>
                            </button>                                                                             
                        </td>                    
                    </tr>';
            }

            // Cerrar la tabla HTML
            print '</tbody></table>';
        } else {
            // Mostrar mensaje si no hay resultados
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar la memoria asociada con los resultados
        $results->free();

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

function getCfdiValidated($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Mapeo de áreas
    $areaMap = [
        //ABFORTI
        1 => 'DIRECCION',
        2 => 'ADMINISTRACION',
        3 => 'COMERCIAL',
        4 => 'CONTROL',
        5 => 'TECNOLOGIAS DE LA INFORMACION',
        6 => 'GCH',
        7 => 'ESR',
        8 => 'PDT',
        //UPPER
        13 => 'CEVA',
        14 => 'CEDIM',
        15 => 'INSUMOS',
        16 => 'TRANSPORTE QRO',
        17 => 'PIA 10',
        18 => 'CVA GDL',
        19 => 'CEVA GDL',
        20 => 'CVA MTY',
        21 => 'CORPORATIVO',
        22 => 'QRO',
        23 => 'COMERCIAL',
        24 => 'GDL SLS',
        25 => 'CVA MX',
        26 => 'CEDIC',
        27 => 'GERENCIA OPERACIONES',
        28 => 'CALAMANDA',
        29 => 'MANZANILLO',
        30 => 'PIA 41',
        31 => 'ESR',
        32 => 'DIRECCION',
        33 => 'COMPLIANCE',
        34 => 'STUFFACTORY GDL',
        //INMOBILIARIA
        50 => 'PRODUCCION',
        52 => 'ADMINISTRACION',
        53 => 'VENTAS',
        //INNOVET
        80 => 'COSTO PRODUCCION',
        81 => 'DIRECCION',
        82 => 'LOGISTICA',
        83 => 'PROYECTOS',
        84 => 'PRODUCCION',
        85 => 'GCH',
        86 => 'CALIDAD',
        87 => 'MANTENIMIENTO',
        88 => 'CONTROL',
        89 => 'ADQUISICIONES',
        90 => 'TECNOLOGIAS DE LA INFROMACION',
        91 => 'COMERCIAL',
        92 => 'ESR',
        93 => 'ATENCION AL CLIENTE',
        94 => 'COSTEO',
        95 => 'DISEÑO',
        96 => 'MAQUINADOS',
        97 => 'CORPORATIVO'

    ];

    // Consulta principal
    $query = "SELECT 
                c.id,
                c.folio, 
                c.nombre_em, 
                c.serie, 
                c.total,
                c.fecha_val_su,
                GROUP_CONCAT(a.area SEPARATOR ', ') as areas
              FROM cfdi c
              LEFT JOIN ap_invoice a ON c.id = a.cfdi_id
              WHERE c.cod_empresa LIKE ? AND (c.validated = 1 OR c.validated = 3)
              GROUP BY c.id, c.folio, c.nombre_em, c.serie, c.subtotal, c.total, c.fecha_val_su
              ORDER BY c.folio";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener los resultados
        $results = $stmt->get_result();

        // Verificar si hay resultados
        if ($results->num_rows > 0) {

            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th>Area</th>
                    <th>Dia de Validacion</th> 
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while ($row = $results->fetch_assoc()) {
                // Convertir los números de área a nombres
                $areas = explode(', ', $row["areas"]);
                $areaNames = array_map(function ($area) use ($areaMap) {
                    return isset($areaMap[$area]) ? $areaMap[$area] : $area;
                }, $areas);
                $areaNamesString = implode(', ', $areaNames);

                // Formatear el total como cantidad con separadores de miles y decimales
                $totalFormateado = number_format($row["total"], 2);
                print '
                    <tr>
                        <td>' . $row["nombre_em"] . '</td>
                        <td>' . $row["folio"] . '</td>
                        <td>' . $row["serie"] . '</td>
                        <td>$' . $totalFormateado . '</td> <!-- Mostrar el total formateado -->
                        <td>' . $areaNamesString . '</td>
                        <td>' . $row["fecha_val_su"] . '</td>		                                            
                    </tr>';
            }

            // Cerrar la tabla HTML
            print '</tbody></table>';
        } else {
            // Mostrar mensaje si no hay resultados
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar la memoria asociada con los resultados
        $results->free();

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}


//Contar cuantas facturas ya han sido validadas
function countValidatedInvoices($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar solo las facturas validadas
    $query = "SELECT COUNT(*) AS total_validated
              FROM cfdi
              WHERE cod_empresa LIKE ? AND validated IN (1, 3)";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas validadas
        echo $total_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contar cuantas facturas ya estan validadas
function countNotInvoices($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar los tres tipos de facturas
    $query = "SELECT 
                (SELECT COUNT(*) FROM cfdi WHERE cod_empresa LIKE ? AND validated = 0 AND no_recurrent = 0) AS count1,
                (SELECT COUNT(*) FROM cfdi WHERE cod_empresa LIKE ? AND validated = 0 AND no_recurrent = 1) AS count2,
                (SELECT COUNT(*) FROM cfdi WHERE f_cod_empresa LIKE ? AND fixed_fund = 10) AS count3";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("sss", $companyPrefix, $companyPrefix, $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($count1, $count2, $count3);
        $stmt->fetch();

        // Calcular el total
        $total_not_validated = $count1 + $count2 + $count3;

        // Mostrar el número total de facturas
        echo $total_not_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}


//Contador de facturas para panel Proveedores
function countProviders($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar solo las facturas no validadas, excluyendo no_recurrent = 2
    $query = "SELECT COUNT(*) AS total_not_validated
              FROM cfdi
              WHERE cod_empresa LIKE ? 
              AND validated = 0
              AND no_recurrent = 0";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_not_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas no validadas
        echo $total_not_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contador de facturas para panel No Recurrentes
function countNoRecurrent($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar solo las facturas no validadas, excluyendo no_recurrent = 2
    $query = "SELECT COUNT(*) AS total_not_validated
              FROM cfdi
              WHERE cod_empresa LIKE ? 
              AND validated = 0
              AND no_recurrent = 1";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_not_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas no validadas
        echo $total_not_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contador de facturas para Fondos Fijos
function countFixedFound($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar solo las facturas no validadas, excluyendo no_recurrent = 2
    $query = "SELECT COUNT(*) AS total_fixed_fund
              FROM cfdi
              WHERE f_cod_empresa LIKE ? 
              AND fixed_fund = 10 ";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_fixed_fund);
        $stmt->fetch();

        // Mostrar el número total de facturas no validadas
        echo $total_fixed_fund;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Mostrar los Fondos fijos subidas por el usuario
function getInvoices_Fixed_Found($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Mapeo de áreas
    $areaMap = [
        1 => 'DIRECCION',
        2 => 'ADMINISTRACION',
        3 => 'COMERCIAL',
        4 => 'CONTROL',
        5 => 'TECNOLOGIAS DE LA INFORMACION',
        6 => 'GCH',
        7 => 'ESR',
        8 => 'PDT',
        13 => 'CEVA',
        14 => 'CEDIM',
        15 => 'INSUMOS',
        16 => 'TRANSPORTE QRO',
        17 => 'PIA 10',
        18 => 'CVA GDL',
        19 => 'CEVA GDL',
        20 => 'CVA MTY',
        21 => 'CORPORATIVO',
        22 => 'QRO',
        23 => 'COMERCIAL',
        24 => 'GDL SLS',
        25 => 'CVA MX',
        26 => 'CEDIC',
        27 => 'GERENCIA OPERACIONES',
        28 => 'CALAMANDA',
        29 => 'MANZANILLO',
        30 => 'PIA 41',
        31 => 'ESR',
        32 => 'DIRECCION',
        33 => 'COMPLIANCE',
        34 => 'STUFFACTORY GDL',
        50 => 'PRODUCCION',
        52 => 'ADMINISTRACION',
        53 => 'VENTAS',
        80 => 'COSTO PRODUCCION',
        81 => 'DIRECCION',
        82 => 'LOGISTICA',
        83 => 'PROYECTOS',
        84 => 'PRODUCCION',
        85 => 'GCH',
        86 => 'CALIDAD',
        87 => 'MANTENIMIENTO',
        88 => 'CONTROL',
        89 => 'ADQUISICIONES',
        90 => 'TECNOLOGIAS DE LA INFROMACION',
        91 => 'COMERCIAL',
        92 => 'ESR',
        93 => 'ATENCION AL CLIENTE',
        94 => 'COSTEO',
        95 => 'DISEÑO',
        96 => 'MAQUINADOS',
        97 => 'CORPORATIVO'
    ];

    // Consulta con LEFT JOIN para incluir datos de cancelación si existen
    $query = "SELECT 
                cfdi.id,
                cfdi.folio, 
                cfdi.nombre_em, 
                cfdi.serie, 
                cfdi.total,
                ap_invoice.area AS area,
                users.name AS nameU  -- Agregar el username del usuario                          
              FROM cfdi
              LEFT JOIN ap_invoice ON cfdi.id = ap_invoice.cfdi_id
              LEFT JOIN users ON cfdi.user_id = users.id  -- Unir con la tabla users
              WHERE cfdi.f_cod_empresa LIKE ? 
                AND cfdi.fixed_fund = 10                 
              ORDER BY cfdi.folio";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener los resultados
        $results = $stmt->get_result();

        // Verificar si hay resultados
        if ($results->num_rows > 0) {
            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th>Departamento</th>
                    <th>Enviada</th>  <!-- Nueva columna para el username -->
                    <th></th>                
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while ($row = $results->fetch_assoc()) {
                $totalFormateado = number_format($row["total"], 2);

                $estado = ($row["area"]) ?
                    (isset($areaMap[$row["area"]]) ? $areaMap[$row["area"]] : "Área desconocida")
                    : "Sin área asignada";

                print '
                    <tr>
                        <td>' . $row["nombre_em"] . '</td>
                        <td>' . $row["folio"] . '</td>
                        <td>' . $row["serie"] . '</td>
                        <td>$' . $totalFormateado . '</td> 
                        <td>' . $estado . '</td> <!-- Muestra el estado de la factura -->
                        <td>' . $row["nameU"] . '</td> <!-- Muestra el username del usuario -->
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="' . $row["id"] . '">
                                <span class="glyphicon glyphicon-edit center" aria-hidden="true"></span>
                            </button>                                                                              
                        </td>					
                    </tr>';
            }

            // Cerrar la tabla HTML
            print '</tbody></table>';
        } else {
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar la memoria asociada con los resultados
        $results->free();
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contador de fondos fijos subidos por el Super_Usuario
function countValidatedFFSU($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Consulta para contar solo las facturas validadas
    $query = "SELECT COUNT(*) AS total_validated_ff
              FROM cfdi
              WHERE f_cod_empresa LIKE ?
              AND (fixed_fund = 111 OR fixed_fund = 1111)";  //Conteo de facturas que tengan 111 o 1111

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_validated_ff);
        $stmt->fetch();

        // Mostrar el número total de facturas validadas
        echo $total_validated_ff;

        // Cerrar la declaración
        $stmt->close();
    } else {
        // Mostrar un mensaje de error si la preparación de la consulta falla
        echo "Error en la preparación de la consulta: " . $mysqli->error;
    }

    // Cerrar la conexión
    $mysqli->close();
}


function getCfdiValidatedFF($companyPrefix)
{
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Verificar la conexión
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Obtener rutas base
    $basePaths = [
        'AB' => '/control/abforti/uploads/Bancos/',
        'BE' => '/control/beexen/uploads/Bancos/',
        'IN' => '/control/innovet/uploads/Bancos/',
        'UP' => '/control/upper/uploads/Bancos/',
        'IM' => '/control/inmobiliaria/uploads/Bancos/',
    ];
    $rutaBase = $basePaths[$companyPrefix] ?? null;

    if (!$rutaBase) {
        echo "<p>Prefijo de empresa no válido: $companyPrefix</p>";
        return;
    }

    // Obtener rutas de facturas
    $empresas = [
        'AB' => 'abforti/uploads/factura/',
        'BE' => 'beexen/uploads/factura/',
        'IN' => 'innovet/uploads/factura/',
        'UP' => 'upper/uploads/factura/',
        'IM' => 'inmobiliaria/uploads/factura/',
    ];
    $rutaCom = $empresas[$companyPrefix] ?? null;

    if (!$rutaCom) {
        die('El prefijo del cod_empresa no tiene una ruta configurada.');
    }

    // Consulta SQL
    $query = "SELECT 
              cfdi.id, 
              cfdi.folio, 
              cfdi.nombre_em, 
              cfdi.serie, 
              cfdi.total, 
              cfdi.ruta_com_pago,
              cfdi.factura_xml, 
              cfdi.factura_pdf, 
              cfdi.validated, 
              cfdi.no_recurrent, 
              cfdi.f_cod_empresa,
              cfdi.fecha_registro, 
              cfdi.fecha_pagada, 
              cfdi.metodopago, 
              cfdi.fixed_fund
          FROM 
              cfdi
          WHERE 
              cfdi.f_cod_empresa LIKE ?
              AND (cfdi.fixed_fund = 111 OR cfdi.fixed_fund = 1111)
          ORDER BY 
              cfdi.ruta_com_pago";

    // Preparar y ejecutar la consulta
    if ($stmt = $mysqli->prepare($query)) {
        $companyPrefix = $companyPrefix . '%';
        $stmt->bind_param("s", $companyPrefix);
        $stmt->execute();
        $results = $stmt->get_result();

        if ($results->num_rows > 0) {
            // Iniciar la tabla
            echo '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0">
                  <thead>
                      <tr>
                          <th>Proveedor</th>
                          <th>Folio</th>
                          <th>Serie</th>
                          <th>Total</th>
                          <th>Metodo de Pago</th>
                          <th>Factura</th>
                          <th>Comprobante de Pago</th>
                      </tr>
                  </thead>
                  <tbody>';

            $ultimoDocumento = '';
            $rows = [];

            // Almacenar filas para calcular rowspan
            while ($row = $results->fetch_assoc()) {
                $rows[] = $row;
            }

            // Construir la tabla
            foreach ($rows as $index => $row) {
                $rutaFacturaXML = getRutaFactura($row, 'xml');
                $rutaFacturaPDF = getRutaFactura($row, 'pdf');
                

                // Si el documento es nuevo, calcular rowspan
                if ($row['ruta_com_pago'] !== $ultimoDocumento) {
                    $ultimoDocumento = $row['ruta_com_pago'];
                    $rowspanCount = count(array_filter($rows, function ($r) use ($ultimoDocumento) {
                        return $r['ruta_com_pago'] === $ultimoDocumento;
                    }));

                    // Mostrar la primera fila del nuevo documento
                    echo '<tr>
                          <td>' . $row["nombre_em"] . '</td>
                          <td>' . $row["folio"] . '</td>
                          <td>' . $row["serie"] . '</td>
                          <td>$' . number_format($row["total"], 2) . '</td>
                          <td>' . $row["metodopago"] . '</td>
                          <td>' . getBotonesFactura($rutaFacturaXML, $rutaFacturaPDF) . '</td>
                            <td rowspan="' . $rowspanCount . '">' . getComplementoPago($row, $rutaBase) . '</td>

                      </tr>';
                } else {
                    // Mostrar filas adicionales para el mismo documento
                    echo '<tr>
                          <td>' . $row["nombre_em"] . '</td>
                          <td>' . $row["folio"] . '</td>
                          <td>' . $row["serie"] . '</td>
                          <td>$' . number_format($row["total"], 2) . '</td>
                          <td>' . $row["metodopago"] . '</td>
                          <td>' . getBotonesFactura($rutaFacturaXML, $rutaFacturaPDF) . '</td>
                  <td style="display: none;"></td> <!-- Ocultar celda adicional -->

                      </tr>';
                }
            }
        } else {
            echo "<p>No hay facturas para mostrar por el momento.</p>";
        }

        // Liberar resultados
        $results->free();
        $stmt->close();
    }

    // Cerrar conexión
    $mysqli->close();
}

// Función auxiliar para obtener la ruta de la factura
function getRutaFactura($row, $type)
{
    $basePathsEmpresa = [
        'AB' => 'abforti',
        'BE' => 'beexen',
        'IN' => 'innovet',
        'UP' => 'upper',
        'IM' => 'inmobiliaria',
    ];

    $empresa = $basePathsEmpresa[substr($row['f_cod_empresa'], 0, 2)] ?? null;
    if (!$empresa) {
        return null;
    }

    $fecha = new DateTime($row['fecha_registro']);
    $year = $fecha->format('Y');
    $month = $fecha->format('m');
    $day = $fecha->format('d');

    if (($row['fixed_fund'] == 111) || ($row['fixed_fund'] == 1111)) {
        return "super_users/$empresa/uploads/factura/$year/$month/FONDO_FIJO/$day/" . ($type === 'xml' ? $row['factura_xml'] : $row['factura_pdf']);
    }
    return null; // Asegúrate de devolver null si no se cumple la condición
}

// Función auxiliar para generar botones de factura
function getBotonesFactura($rutaXML, $rutaPDF)
{
    $botones = '<div class="button-group" style="display: flex; gap: 10px;">';

    if ($rutaXML && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaXML)) {
        $botones .= '<button class="btn btn-primary btn-xs open-xml-modal" data-file="/' . $rutaXML . '" data-toggle="modal" data-target="#xmlModal">
                     <span class="ti ti-file-type-xml" style="font-size: 20px;" aria-hidden="true"></span>XML
                     </button>';
    } else {
        $botones .= '<span class="text-muted">No disponible</span>';
    }

    if ($rutaPDF && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaPDF)) {
        $botones .= '<button class="btn btn-warning btn-xs open-pdf-modal" data-file="/' . $rutaPDF . '" data-toggle="modal" data-target="#pdfModal">
                     <span class="ti ti-file-type-pdf" style="font-size: 20px;" aria-hidden="true"></span>PDF
                     </button>';
    } else {
        $botones .= '<span class="text-muted">No disponible</span>';
    }

    $botones .= '</div>';
    return $botones;
}

function getComplementoPago($row, $rutaBase)
{
    $complemento = '<div class="button-group" style="display: flex; gap: 10px;">';

    // Verificar si fixed_fund es 1111
    if ($row['fixed_fund'] == 1111) {
        // Generar la ruta del archivo
        $rutaArchivo = $rutaBase . date('Y', strtotime($row['fecha_pagada'])) . "/" . date('m', strtotime($row['fecha_pagada'])) . "/" . $row['ruta_com_pago'];

        // Verificar si el archivo existe
        $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . $rutaArchivo;
        if (file_exists($rutaCompleta)) {
            $complemento .= '<button class="btn btn-primary btn-xs open-pdf-modal" data-file="' . $rutaArchivo . '" data-toggle="modal" data-target="#pdfModal">
                             <span class="glyphicon glyphicon-file" aria-hidden="true"></span> Comprobante
                             </button>';
        } else {
            $complemento .= '<span class="text-muted">Documento no encontrado</span>';
        }
    } else {
        $complemento .= '<span class="text-muted">No pagada</span>';
    }

    $complemento .= '</div>';
    return $complemento;
}