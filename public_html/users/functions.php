<?php
include_once("includes/config.php");

// get user list

function getCustomers($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Salida de cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // La consulta para seleccionar proveedores donde el prefijo del código de empresa coincida
    $query = "SELECT * FROM providers WHERE cod_empresa LIKE ? ORDER BY razon_social ASC";

    // Preparar la consulta
    $stmt = $mysqli->prepare($query);
    $companyPrefix = $companyPrefix . '%'; // Agregar el comodín para buscar por prefijo
    $stmt->bind_param("s", $companyPrefix); // 's' especifica el tipo de parámetro como string
    $stmt->execute();
    $results = $stmt->get_result();

    if($results->num_rows > 0) {
        print '<table class="table table-striped table-hover table-bordered" id="data-table"><thead><tr>
                <th>Razon Social</th>
                <th>Correo electronico</th>
                <th>Telefono</th>
                <th>Action</th>
              </tr></thead><tbody>';

        while($row = $results->fetch_assoc()) {
            print '
                <tr>
                    <td>'.$row["razon_social"].'</td>
                    <td>'.$row["correo_electronico"].'</td>
                    <td>'.$row["telefono"].'</td>
                    <td>
                        <button class="btn btn-primary btn-xs edit-provider" data-id="'.$row["id_proveedor"].'">
                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                        </button>
                        <button class="btn btn-danger btn-xs delete-customer" data-id="'.$row["id_proveedor"].'">
                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>';
        }

        print '</tbody></table>';

    } else {
        echo "<p>No hay proveedores para mostrar</p>";
    }

    // Liberar la memoria asociada con un resultado
    $results->free();
    $stmt->close();

    // Cerrar la conexión
    $mysqli->close();
}


function getCfdiInvoices($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // falta 
	// Ingresada, Confirmado, Validado
    $query = "SELECT 
				id,
                folio, 
                nombre_em, 
                serie, 
                total                
              FROM cfdi
			  WHERE cod_empresa LIKE ? AND validated = 1 AND no_recurrent != 1
              ORDER BY folio";

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
        if($results->num_rows > 0) {

            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th></th>                
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while($row = $results->fetch_assoc()) {
                print '
                    <tr>
                        <td>'.$row["nombre_em"].'</td>
                        <td>'.$row["folio"].'</td>
                        <td>'.$row["serie"].'</td>
                        <td>'.$row["total"].'</td>			
                        
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="'.$row["id"].'">
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

function getNonRecurrentCfdiInvoices($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Consulta para seleccionar facturas no recurrentes
    // AND (validated = 1 OR no_recurrent = 1) super_users
    // AND (validated = 0 OR no_recurrent = 2) users

    $query = "SELECT 
                id,
                folio, 
                nombre_em, 
                serie, 
                total                
              FROM cfdi
              WHERE cod_empresa LIKE ? 
              AND (validated = 1 OR validated = 0)
              AND (no_recurrent = 1 OR no_recurrent = 2)
              ORDER BY folio";

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
        if($results->num_rows > 0) {

            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th></th>                
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while($row = $results->fetch_assoc()) {
                print '
                    <tr>
                        <td>'.$row["nombre_em"].'</td>
                        <td>'.$row["folio"].'</td>
                        <td>'.$row["serie"].'</td>
                        <td>'.$row["total"].'</td>            
                        
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="'.$row["id"].'">
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

function getInvoicesByUserArea($userId) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario
    $query = "SELECT area_users FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . $mysqli->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($areaUsers);
    $stmt->fetch();
    $stmt->close();

    // Validar el valor de $areaUsers
    if (!isset($areaUsers) || $areaUsers === null) {
        die('Invalid area_users value');
    }

    // Determinar el prefijo de la empresa basado en area_users
    $companyPrefix = '';
    if (in_array($areaUsers, [1, 2, 3, 4, 5, 6, 7, 8])) {
        $companyPrefix = 'AB';
    } elseif (in_array($areaUsers, [10, 11, 12])) {
        $companyPrefix = 'BE';
    } elseif (in_array($areaUsers, [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,25, 26, 27, 28, 29, 30, 31, 32, 33, 34])) {
        $companyPrefix = 'UP';
    } elseif ($areaUsers == 50) {
        $companyPrefix = 'IM';
    } elseif (in_array($areaUsers, [80, 81, 82, 83, 84, 85,86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97])) {
        $companyPrefix = 'IN';
    }

    // Obtener facturas del área correspondiente y con cod_empresa que coincida con el prefijo
    $query = "SELECT 
                cf.id AS cfdi_id,
                cf.folio, 
                cf.nombre_em, 
                cf.serie, 
                cf.total,
                ap.id AS ap_invoice_id,
                ap.area AS ap_area
              FROM cfdi cf
              LEFT JOIN ap_invoice ap ON cf.id = ap.cfdi_id
              WHERE ap.area = ? 
                AND cf.cod_empresa LIKE ?             
                AND cf.validated = 1
                AND cf.no_recurrent = 0
                AND ap.validacion != 1              
              ORDER BY cf.folio";

    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . $mysqli->error);
    }

    $areaLike = '%' . $areaUsers . '%';
    $companyPrefixLike = $companyPrefix . '%';
    $stmt->bind_param("is", $areaUsers, $companyPrefixLike);
    $stmt->execute();
    $results = $stmt->get_result();

    // Mostrar resultados
    if ($results->num_rows > 0) {        
         // Iniciar la tabla HTML
         print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                <th>Proveedor</th>
                <th>Folio</th>
                <th>Serie</th>
                <th>Total</th>
                <th></th>                
            </tr></thead><tbody>';        
        while ($row = $results->fetch_assoc()) {
             // Formatear el total como cantidad con separadores de miles y decimales
			 $totalFormateado = number_format($row["total"], 2);
            print '
            <tr>
                        <td>'.$row["nombre_em"].'</td>
                        <td>'.$row["folio"].'</td>
                        <td>'.$row["serie"].'</td>
                        <td>$'.$totalFormateado.'</td> <!-- Mostrar el total formateado -->
                        
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="'.$row["cfdi_id"].'">
                                <span class="glyphicon glyphicon-edit center" aria-hidden="true"></span>
                            </button>                                                                             
                        </td>                    
                    </tr>';
            }
            print '</tbody></table>';
    } else {
        echo "<p>No hay facturas para mostrar por el momento.</p>";
    }

    // Liberar resultados y cerrar conexiones
    $results->free();
    $stmt->close();
    $mysqli->close();
}


function getInvoicesUsersNorecurrente($userId) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario
    $query = "SELECT area_users FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . $mysqli->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($areaUsers);
    $stmt->fetch();
    $stmt->close();

    // Validar el valor de $areaUsers
    if (!isset($areaUsers) || $areaUsers === null) {
        die('Invalid area_users value');
    }

    // Determinar el prefijo de la empresa basado en area_users
    $companyPrefix = '';
    if (in_array($areaUsers, [1, 2, 3])) {
        $companyPrefix = 'AB';
    } elseif (in_array($areaUsers, [10, 11, 12])) {
        $companyPrefix = 'BE';
    } elseif (in_array($areaUsers, [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24])) {
        $companyPrefix = 'UP';
    } elseif ($areaUsers == 30) {
        $companyPrefix = 'IM';
    } elseif (in_array($areaUsers, [40, 41, 42, 43, 44, 45])) {
        $companyPrefix = 'IN';
    }

    // Obtener facturas del área correspondiente y con cod_empresa que coincida con el prefijo
    $query = "SELECT 
                cf.id AS cfdi_id,
                cf.folio, 
                cf.nombre_em, 
                cf.serie, 
                cf.total,
                ap.id AS ap_invoice_id,
                ap.area AS ap_area
              FROM cfdi cf
              LEFT JOIN ap_invoice ap ON cf.id = ap.cfdi_id
              WHERE ap.area = ? 
                AND cf.cod_empresa LIKE ?             
                AND (cf.validated = 1 OR cf.validated = 0)
                AND (cf.no_recurrent = 1 OR cf.no_recurrent = 2)
                AND ap.validacion != 1                              
              ORDER BY cf.folio";

    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . $mysqli->error);
    }

    $areaLike = '%' . $areaUsers . '%';
    $companyPrefixLike = $companyPrefix . '%';
    $stmt->bind_param("is", $areaUsers, $companyPrefixLike);
    $stmt->execute();
    $results = $stmt->get_result();

    // Mostrar resultados
    if ($results->num_rows > 0) {        
         // Iniciar la tabla HTML
         print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                <th>Proveedor</th>
                <th>Folio</th>
                <th>Serie</th>
                <th>Total</th>
                <th></th>                
            </tr></thead><tbody>';        
        while ($row = $results->fetch_assoc()) {
             // Formatear el total como cantidad con separadores de miles y decimales
			 $totalFormateado = number_format($row["total"], 2);
            print '
            <tr>
                        <td>'.$row["nombre_em"].'</td>
                        <td>'.$row["folio"].'</td>
                        <td>'.$row["serie"].'</td>
                        <td>$'.$totalFormateado.'</td> <!-- Mostrar el total formateado -->
                        
                        <td>
                             <button class="btn btn-primary btn-xs edit-invoice" data-id="'.$row["cfdi_id"].'">
                                <span class="glyphicon glyphicon-edit center" aria-hidden="true"></span>
                            </button>                                                                             
                        </td>                    
                    </tr>';
            }
            print '</tbody></table>';
    } else {
        echo "<p>No hay facturas para mostrar por el momento.</p>";
    }

    // Liberar resultados y cerrar conexiones
    $results->free();
    $stmt->close();
    $mysqli->close();
}

function getCfdiValidated($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }
        
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
                GROUP_CONCAT(a.area SEPARATOR ', ') as areas
              FROM cfdi c
              LEFT JOIN ap_invoice a ON c.id = a.cfdi_id
              WHERE c.cod_empresa LIKE ? AND c.validated = 1
              GROUP BY c.id, c.folio, c.nombre_em, c.serie, c.subtotal, c.total
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
        if($results->num_rows > 0) {

            // Iniciar la tabla HTML
            print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                    <th>Proveedor</th>
                    <th>Folio</th>
                    <th>Serie</th>
                    <th>Total</th>
                    <th>Area</th>
                  </tr></thead><tbody>';

            // Iterar sobre los resultados y llenar la tabla
            while($row = $results->fetch_assoc()) {
                // Convertir los números de área a nombres
                $areas = explode(', ', $row["areas"]);
                $areaNames = array_map(function($area) use ($areaMap) {
                    return isset($areaMap[$area]) ? $areaMap[$area] : $area;
                }, $areas);
                $areaNamesString = implode(', ', $areaNames);

                print '
                    <tr>
                        <td>'.$row["nombre_em"].'</td>
                        <td>'.$row["folio"].'</td>
                        <td>'.$row["serie"].'</td>
                        <td>'.$row["total"].'</td>
                        <td>'.$areaNamesString.'</td>		                                            
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

function getInvValidated($userId) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario
    $query = "SELECT area_users FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . $mysqli->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($areaUsers);
    $stmt->fetch();
    $stmt->close();

    // Validar el valor de $areaUsers
    if (!isset($areaUsers) || $areaUsers === null) {
        die('Invalid area_users value');
    }

    // Determinar el prefijo de la empresa basado en area_users
    $companyPrefix = '';
    if (in_array($areaUsers, [1, 2, 3])) {
        $companyPrefix = 'AB';
    } elseif (in_array($areaUsers, [10, 11, 12])) {
        $companyPrefix = 'BE';
    } elseif (in_array($areaUsers, [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24])) {
        $companyPrefix = 'UP';
    } elseif ($areaUsers == 30) {
        $companyPrefix = 'IM';
    } elseif (in_array($areaUsers, [40, 41, 42, 43, 44, 45])) {
        $companyPrefix = 'IN';
    }

    // Obtener facturas del área correspondiente y con cod_empresa que coincida con el prefijo
    $query = "SELECT 
                cf.id AS cfdi_id,
                cf.folio, 
                cf.nombre_em, 
                cf.serie, 
                cf.total,
                cf.fecha_confirmada,
                ap.id AS ap_invoice_id,
                ap.area AS ap_area
              FROM cfdi cf
              LEFT JOIN ap_invoice ap ON cf.id = ap.cfdi_id
              WHERE ap.area = ? 
                AND cf.cod_empresa LIKE ?             
                AND (cf.validated = 2 OR cf.validated = 1 OR cf.validated = 3)
                AND (cf.no_recurrent = 0 OR cf.no_recurrent = 3 OR cf.no_recurrent = 4)   
                           
              ORDER BY cf.folio";

    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . $mysqli->error);
    }

    $areaLike = '%' . $areaUsers . '%';
    $companyPrefixLike = $companyPrefix . '%';
    $stmt->bind_param("is", $areaUsers, $companyPrefixLike);
    $stmt->execute();
    $results = $stmt->get_result();

    // Mostrar resultados
    if ($results->num_rows > 0) {        
         // Iniciar la tabla HTML
         print '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0"><thead><tr>
                <th>Proveedor</th>
                <th>Folio</th>
                <th>Serie</th>
                <th>Total</th>
                <th>Validacion</th>
            </tr></thead><tbody>';        
        while ($row = $results->fetch_assoc()) {


             // Formatear el total como cantidad con separadores de miles y decimales
			 $totalFormateado = number_format($row["total"], 2);

            print '
            <tr>
                        <td>'.$row["nombre_em"].'</td>
                        <td>'.$row["folio"].'</td>
                        <td>'.$row["serie"].'</td>
                        <td>$'.$totalFormateado.'</td> <!-- Mostrar el total formateado -->
                        <td>'.$row["fecha_confirmada"].'</td>                                                                    
                    </tr>';
            }
            print '</tbody></table>';
    } else {
        echo "<p>No hay facturas para mostrar por el momento.</p>";
    }

    // Liberar resultados y cerrar conexiones
    $results->free();
    $stmt->close();
    $mysqli->close();
}




//Contar cuantas facturas faltan de validar
function countUsersNotInvoices($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario de la sesión
    $userArea = $_SESSION['area_users']; // Asegúrate de que 'area_users' esté en la sesión

    // Consulta para contar solo las facturas que coinciden con el área del usuario
    $query = "SELECT COUNT(*) AS total_not_validated
              FROM cfdi
              INNER JOIN ap_invoice ON cfdi.id = ap_invoice.cfdi_id
              WHERE SUBSTRING(cfdi.cod_empresa, 1, 2) = ? 
              AND ap_invoice.area = ? 
              AND ap_invoice.validacion = 0
              AND (
                  (cfdi.validated = 1 AND cfdi.no_recurrent = 2) OR 
                  (cfdi.validated = 0 AND cfdi.no_recurrent = 2) OR 
                  (cfdi.validated = 1 AND cfdi.no_recurrent = 0)
              )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("ss", $companyPrefix, $userArea);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_not_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas
        echo $total_not_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}

//Contar cuantas facturas ya estan validadas
function countUsersValidatedInvoices($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario de la sesión
    $userArea = $_SESSION['area_users']; // Asegúrate de que 'area_users' esté en la sesión
    
    // Consulta para contar solo las facturas validadas
    $query = "SELECT COUNT(*) AS total_validated
              FROM cfdi
                INNER JOIN ap_invoice ON cfdi.id = ap_invoice.cfdi_id
              WHERE SUBSTRING(cfdi.cod_empresa, 1, 2) = ? 
              AND ap_invoice.area = ? 
              AND (
                (validated = 2 AND no_recurrent = 0) OR 
                (validated = 1 AND no_recurrent = 3) OR
                (validated = 3 AND no_recurrent = 0) OR
                (validated = 1 AND no_recurrent = 4)
                )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("ss", $companyPrefix, $userArea);

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


function countUsersProviders($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Consulta para contar los proveedores que coinciden con el prefijo de la empresa
    $query = "SELECT COUNT(*) AS total_providers
              FROM providers
              WHERE SUBSTRING(cod_empresa, 1, 2) = ?";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("s", $companyPrefix);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_providers);
        $stmt->fetch();

        // Mostrar el número total de proveedores
        echo $total_providers;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}




//Contador de facturas para panel Recurrentes
function countUsersIn($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario de la sesión
    $userArea = $_SESSION['area_users']; // Asegúrate de que 'area_users' esté en la sesión

    // Consulta para contar solo las facturas que coinciden con el área del usuario
    $query = "SELECT COUNT(*) AS total_not_validated
              FROM cfdi
              INNER JOIN ap_invoice ON cfdi.id = ap_invoice.cfdi_id
              WHERE SUBSTRING(cfdi.cod_empresa, 1, 2) = ? 
              AND ap_invoice.area = ? 
              AND ap_invoice.validacion = 0
              AND (
                  (cfdi.validated = 1 AND cfdi.no_recurrent = 0)
              )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("ss", $companyPrefix, $userArea);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_not_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas
        echo $total_not_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}
//Contador de facturas para panel Recurrentes
function countUsersINNR($companyPrefix) {
    // Conectar a la base de datos
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    // Mostrar cualquier error de conexión
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Obtener el área del usuario de la sesión
    $userArea = $_SESSION['area_users']; // Asegúrate de que 'area_users' esté en la sesión

    // Consulta para contar solo las facturas que coinciden con el área del usuario
    $query = "SELECT COUNT(*) AS total_not_validated
              FROM cfdi
              INNER JOIN ap_invoice ON cfdi.id = ap_invoice.cfdi_id
              WHERE SUBSTRING(cfdi.cod_empresa, 1, 2) = ? 
              AND ap_invoice.area = ? 
              AND ap_invoice.validacion = 0
              AND (
                  (cfdi.validated = 1 AND cfdi.no_recurrent = 2) OR
                  (cfdi.validated = 0 AND cfdi.no_recurrent = 2)  
              )";

    // Preparar la declaración
    if ($stmt = $mysqli->prepare($query)) {
        // Ligar parámetros
        $companyPrefix = substr($companyPrefix, 0, 2); // Solo los primeros dos dígitos
        $stmt->bind_param("ss", $companyPrefix, $userArea);

        // Ejecutar la declaración
        $stmt->execute();

        // Obtener el resultado
        $stmt->bind_result($total_not_validated);
        $stmt->fetch();

        // Mostrar el número total de facturas
        echo $total_not_validated;

        // Cerrar la declaración
        $stmt->close();
    }

    // Cerrar la conexión
    $mysqli->close();
}


?>

