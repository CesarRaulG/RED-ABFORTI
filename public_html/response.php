 <?php


include_once('includes/config.php');

// show PHP errors
ini_set('display_errors', 1);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
}

$action = isset($_POST['action']) ? $_POST['action'] : "";


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Login to system
// 'login', se utiliza para autenticar a un usuario en el sistema
/*
if ($action == 'login') {
    // Verificar la conexión a la base de datos
    if ($mysqli->connect_error) {
        die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
    }

    // Iniciar sesión
    session_start();

    // Obtener datos del formulario
    $username = mysqli_real_escape_string($mysqli, $_POST['username']);
    $pass_encrypt = md5(mysqli_real_escape_string($mysqli, $_POST['password']));

    // Consultar la base de datos para verificar las credenciales
    $query = "SELECT * FROM `users` WHERE username='$username' AND `password` = '$pass_encrypt'";
    $results = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    $count = mysqli_num_rows($results);

    if ($count > 0) {
        // Si las credenciales son válidas, obtener los datos del usuario
        $row = $results->fetch_assoc();

		// Obtener el ID del usuario
		$userId = $row['id']; // Asegúrate de que 'id' sea el nombre correcto del campo en la tabla 'users'


        // Obtener el área del proveedor si el rol es 7
        $area = null;
         // Si el rol es 7 (proveedor), obtener el área y verificar fechas de actualización de documentos
		if ($row['rol'] == 7) {
			// Obtener el RFC del proveedor
            $rfcProveedor = $row['username'];
            
            // Obtener el área del proveedor
			$query_provider = "SELECT area, id_proveedor, cod_empresa, rfc  FROM providers WHERE rfc = ?";
            $stmt = $mysqli->prepare($query_provider);
            $stmt->bind_param('s', $rfcProveedor);
            $stmt->execute();
            $stmt->bind_result($area, $idProveedor, $cod_empresaP, $rfc);
            $stmt->fetch();
            $stmt->close();

            // Consultar la tabla providers para obtener las fechas de actualización de documentos
            $query_provider = "SELECT constancia_update_date, opinion_update_date FROM providers WHERE rfc = ?";
            $stmt = $mysqli->prepare($query_provider);
            $stmt->bind_param('s', $rfcProveedor);
            $stmt->execute();
            $stmt->bind_result($constancia_update_date, $opinion_update_date);
            $stmt->fetch();
            $stmt->close();			

            // Verificar si los documentos necesitan ser renovados
            $current_datetime = new DateTime();
            $constancia_datetime = new DateTime($constancia_update_date);
            $opinion_datetime = new DateTime($opinion_update_date);

            // Calcular la diferencia en días
            $constancia_diff = $current_datetime->diff($constancia_datetime)->days;
            $opinion_diff = $current_datetime->diff($opinion_datetime)->days;

            // Definir el umbral de renovación en días (ejemplo: 30 días)
            $renovation_threshold = 30;

            // Comprobar si alguno de los documentos necesita ser renovado
            if ($constancia_diff > $renovation_threshold || $opinion_diff > $renovation_threshold) {
				// Almacenar el RFC en la sesión
				$_SESSION['renew_rfc'] = $rfcProveedor;                				
                echo json_encode(array(
                    'status' => 'Warning',
                    'message' => 'Debe renovar los documentos de constancia y opinión.',
                    'redirect' => 'co-edit.php'
                ));
                exit();
            }
			// Almacenar el ID del proveedor en la sesión solo para proveedores
            $_SESSION['id_proveedor'] = $idProveedor;
			$_SESSION['cod_empresa'] = $cod_empresaP;
			$_SESSION['rfc'] = $rfc;
        }
	

        // Establecer la sesión con el nombre de usuario, rol y área (solo el primer dígito del área)
        $_SESSION['login_username'] = $row['username'];
        $_SESSION['rol'] = $row['rol'];
		$_SESSION['user_id'] = $userId;
        $_SESSION['area'] = $area ? substr($area, 0, 1) : null;
		$_SESSION['area_users'] = $row['area_users']; // Asumiendo que 'area_users' es el campo en la tabla 'users'


        // Procesar la opción de recordar y configurar la cookie con una fecha de caducidad prolongada
        if (isset($_POST['remember'])) {
            session_set_cookie_params(604800); // Una semana (en segundos)
            session_regenerate_id(true);
        }

        // Redirigir según el rol y el área del usuario
        if ($row['rol'] == 1) {
            // Redirigir al administrador
            $redirect_url = 'dashboard.php';
        } elseif ($row['rol'] >= 2 && $row['rol'] <= 6) {
            // Redirigir a los usuarios según el rol
            switch ($row['rol']) {
                case 2:
                    $redirect_url = 'users/abforti/dashboard.php';
                    break;
                case 3:
                    $redirect_url = 'users/beexen/dashboard.php';
                    break;
                case 4:
                    $redirect_url = 'users/upper/dashboard.php';
                    break;
                case 5:
                    $redirect_url = 'users/inmobiliaria/dashboard.php';
                    break;
                case 6:
                    $redirect_url = 'users/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            }
        } elseif ($row['rol'] == 7) {
            // Redirigir a los proveedores según el área
            $first_digit_area = $_SESSION['area'];
            switch ($first_digit_area) {
                case '1':
                    $redirect_url = 'suppliers/abforti/dashboard.php';
                    break;
                case '2':
                    $redirect_url = 'suppliers/beexen/dashboard.php';
                    break;
                case '3':
                    $redirect_url = 'suppliers/upper/dashboard.php';
                    break;
                case '4':
                    $redirect_url = 'suppliers/inmobiliaria/dashboard.php';
                    break;
                case '5':
                    $redirect_url = 'suppliers/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            }
        } elseif ($row['rol'] >= 8 && $row['rol'] <= 12) {
            // Redirigir a los proveedores según el área
            switch ($row['rol']) {
                case 8:
                    $redirect_url = 'control/abforti/dashboard.php';
                    break;
                case 9:
                    $redirect_url = 'control/beexen/dashboard.php';
                    break;
                case 10:
                    $redirect_url = 'control/upper/dashboard.php';
                    break;
                case 11:
                    $redirect_url = 'control/inmobiliaria/dashboard.php';
                    break;
                case 12:
                    $redirect_url = 'control/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            }
        } elseif ($row['rol'] >= 13 && $row['rol'] <= 18) {
            // Redirigir a los proveedores según el área
            switch ($row['rol']) {
                case 13:
                    $redirect_url = 'super_users/abforti/dashboard.php';
                    break;
                case 14:
                    $redirect_url = 'super_users/beexen/dashboard.php';
                    break;
                case 15:
                    $redirect_url = 'super_users/upper/dashboard.php';
                    break;
                case 16:
                    $redirect_url = 'super_users/inmobiliaria/dashboard.php';
                    break;
                case 17:
                    $redirect_url = 'super_users/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            }
        }  else {
            // Redirigir a una página genérica si el rol no es reconocido
            $redirect_url = 'index.php';
        }

        echo json_encode(array(
            'status' => 'Success',
            'redirect' => $redirect_url
        ));
    } else {
        // Si las credenciales son inválidas, mostrar un mensaje de error
        echo json_encode(array(
            'status' => 'Error',
            'message' => '¡Inicio de sesión incorrecto! Inténtelo de nuevo',
			'redirect' => 'index.php'
        ));
    }
}
*/


if ($action == 'login') {

    // Verificar la conexión a la base de datos
    if ($mysqli->connect_error) {
        die('Error : (' . $mysqli->connect_error . ') ' . $mysqli->connect_error);
    }

    // Obtener datos del formulario
    $username = mysqli_real_escape_string($mysqli, $_POST['username']);
    $pass_encrypt = md5(mysqli_real_escape_string($mysqli, $_POST['password']));
    $cod_empresa = isset($_POST['cod_empresa']) ? mysqli_real_escape_string($mysqli, $_POST['cod_empresa']) : null;

    // Consultar la base de datos para verificar las credenciales
    $query = "SELECT * FROM `users` WHERE username='$username' AND `password` = '$pass_encrypt'";
    $results = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    $count = mysqli_num_rows($results);

    if ($count > 0) {
        // Credenciales válidas
        $row = $results->fetch_assoc();
        $user_role = $row['rol'];
        $userId = $row['id'];

        // Validar si se requiere `cod_empresa`
        if ($user_role == 7 && empty($cod_empresa)) {
            echo json_encode([
                'status' => 'Error',
                'message' => 'Debe seleccionar una empresa para continuar.'            
            ]);
            exit();
        }

        // Validar que el `cod_empresa` coincida
        if ($user_role == 7 && !empty($cod_empresa)) {
            $empresa = substr($cod_empresa, 0, 2);
            $empresa_registrada = substr($row['cod_empresa'], 0, 2);

            if ($empresa !== $empresa_registrada) {
                echo json_encode([
                    'status' => 'Error',
                    'message' => 'La empresa seleccionada no coincide con la registrada.'
                ]);
                exit();
            }
        }

        // Iniciar sesión
        session_start();
        $_SESSION['login_username'] = $row['username'];
        $_SESSION['rol'] = $user_role;
        $_SESSION['user_id'] = $userId;
        $_SESSION['area_users'] = $row['area_users'];
        $_SESSION['name'] = $row['name'];

        // Si el usuario es proveedor (rol 7), verificar documentos
        if ($user_role == 7) {
            $rfcProveedor = $row['username'];

            // Obtener datos del proveedor
            $query_provider = "SELECT area, id_proveedor, cod_empresa, rfc, constancia_update_date, opinion_update_date FROM providers WHERE rfc = ?";
            $stmt = $mysqli->prepare($query_provider);
            $stmt->bind_param('s', $rfcProveedor);
            $stmt->execute();
            $stmt->bind_result($area, $idProveedor, $cod_empresaP, $rfc, $constancia_update_date, $opinion_update_date);
            $stmt->fetch();
            $stmt->close();

            // Verificar si los documentos necesitan ser renovados
            $current_datetime = new DateTime();
            $constancia_datetime = new DateTime($constancia_update_date);
            $opinion_datetime = new DateTime($opinion_update_date);
            $renovation_threshold = 30;

            if ($current_datetime->diff($constancia_datetime)->days > $renovation_threshold || 
                $current_datetime->diff($opinion_datetime)->days > $renovation_threshold) {
                $_SESSION['renew_rfc'] = $rfcProveedor;
                echo json_encode([
                    'status' => 'Warning',
                    'message' => 'Debe renovar los documentos de constancia y opinión.',
                    'redirect' => 'co-edit.php',
                ]);
                exit();
            }

            // Almacenar información del proveedor en la sesión
            $_SESSION['id_proveedor'] = $idProveedor;
            $_SESSION['cod_empresa'] = $cod_empresaP;
            $_SESSION['rfc'] = $rfc;
        }

        // Redirecciones según el rol y empresa
        if ($user_role == 1) {
            $redirect_url = 'dashboard.php';
        } elseif ($user_role >= 2 && $user_role <= 6) {            
            switch ($user_role) {
                case 2:
                    $redirect_url = 'users/abforti/dashboard.php';
                    break;
                case 3:
                    $redirect_url = 'users/beexen/dashboard.php';
                    break;
                case 4:
                    $redirect_url = 'users/upper/dashboard.php';
                    break;
                case 5:
                    $redirect_url = 'users/inmobiliaria/dashboard.php';
                    break;
                case 6:
                    $redirect_url = 'users/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            };                          
        } elseif ($user_role == 7) {
            $empresa = substr($cod_empresa, 0, 2);
            $redirect_url = match ($empresa) {
                'AB' => 'suppliers/abforti/dashboard.php',
                'BE' => 'suppliers/beexen/dashboard.php',
                'UP' => 'suppliers/upper/dashboard.php',
                'IM' => 'suppliers/inmobiliaria/dashboard.php',
                'IN' => 'suppliers/innovet/dashboard.php',
                default => 'index.php',
            };
        } elseif ($user_role >= 8 && $user_role <= 12) {
            switch ($user_role) {
                case 8:
                    $redirect_url = 'control/abforti/dashboard.php';
                    break;
                case 9:
                    $redirect_url = 'control/beexen/dashboard.php';
                    break;
                case 10:
                    $redirect_url = 'control/upper/dashboard.php';
                    break;
                case 11:
                    $redirect_url = 'control/inmobiliaria/dashboard.php';
                    break;
                case 12:
                    $redirect_url = 'control/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            };            
        } elseif ($user_role >= 13 && $user_role <= 18) {            
            switch ($user_role) {
                case 13:
                    $redirect_url = 'super_users/abforti/dashboard.php';
                    break;
                case 14:
                    $redirect_url = 'super_users/beexen/dashboard.php';
                    break;
                case 15:
                    $redirect_url = 'super_users/upper/dashboard.php';
                    break;
                case 16:
                    $redirect_url = 'super_users/inmobiliaria/dashboard.php';
                    break;
                case 17:
                    $redirect_url = 'super_users/innovet/dashboard.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            };   
        } else {
            $redirect_url = 'index.php';
        }
        // Respuesta exitosa
        echo json_encode([
            'status' => 'Success',
            'messaje' => 'Inicio de sesion exitoso',
            'redirect' => $redirect_url,
        ]);
    } else {
        // Credenciales inválidas
        echo json_encode([
            'status' => 'Error',
            'message' => '¡Inicio de sesión incorrecto! Inténtelo de nuevo',
            'redirect' => 'index.php',
        ]);
    }
}


// Adding new user
// 'add_user', que se utiliza para agregar un 
//nuevo usuario a la base de datos

if ($action == 'add_user') {
    $user_name = $_POST['name'];
    $user_username = $_POST['username'];
    $user_email = $_POST['email'];
    $user_phone = $_POST['phone'];
    $user_password = $_POST['password'];
    $user_rol = $_POST['rol'];
	$user_area = isset($_POST['area']) ? $_POST['area'] : 0; // Captura del área seleccionada


    // Determinar el área basada en el username
    $area = 0; // Valor por defecto
    
	// Sobrescribir el área si el área se seleccionó en el formulario
	if ($user_area > 0) {
		$area = $user_area;
	}

    // Lógica para determinar el valor del rol
    switch ($user_rol) {
        case 1:
            $rol_value = '1';
            break;
        case 2:
            $rol_value = '2';
            break;
        case 3:
            $rol_value = '3';
            break;
        case 4:
            $rol_value = '4';
            break;
        case 5:
            $rol_value = '5';
            break;
        case 6:
            $rol_value = '6';
            break;
        case 8:
            $rol_value = '8';
            break;
        case 9:
            $rol_value = '9';
            break;
        case 10:
            $rol_value = '10';
            break;
        case 11:
            $rol_value = '11';
            break;
        case 12:
            $rol_value = '12';
            break;
        case 13:
            $rol_value = '13';
            break;
        case 14:
            $rol_value = '14';
            break;
        case 15:
            $rol_value = '15';
            break;
        case 16:
            $rol_value = '16';
            break;
        case 17:
            $rol_value = '17';
            break;
        default:
            $rol_value = 7; // Otra opción por defecto
            break;
    }

    // Encriptar la contraseña
    $user_password = md5($user_password);

    // Conexión a la base de datos establecida antes de realizar consultas
    // Preparar la consulta de inserción
    $query  = "INSERT INTO users (name, username, email, phone, password, rol, area_users) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt) {
        // Enlazar los parámetros y ejecutar la consulta
        $stmt->bind_param('ssssssi', $user_name, $user_username, $user_email, $user_phone, $user_password, $rol_value, $area);
        if ($stmt->execute()) {
            // La inserción fue exitosa
            echo json_encode(array(
                'status' => 'Success',
                'message'=> 'User has been added successfully!'
            ));
        } else {
            // La ejecución de la consulta falló
            echo json_encode(array(
                'status' => 'Error',
                'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
            ));
        }
    } else {
        // La preparación de la consulta falló
        trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    // Cerrar la conexión a la base de datos
    $mysqli->close();
}





// Registro Persona Moral
if ($action == 'add_mor') {

    // Verificar qué tipo de persona se está agregando
    $tipo_persona = $_POST['tipo_persona'];
    $rfc = $_POST['rfc'];//username
    $correo_electronico = $_POST['correo_electronico'];
	// Reemplazar espacios por guiones bajos en la razón social
	$razon_social = str_replace(' ', '_', $_POST['razon_social']);
	// Normalizar caracteres especiales y reemplazar caracteres no permitidos en nombres de archivos
	$razon_social = iconv('UTF-8', 'ASCII//TRANSLIT', $razon_social);
	$razon_social = sanitize_filename($razon_social);

    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
	$cuenta =$_POST['cuenta'];
	$clabe = $_POST['clabe'];
	$moneda = $_POST['moneda'];
	$swift = $_POST['swift'];
	$banco = $_POST['banco'];
	$titular = $_POST['titular'];
	$referencia = $_POST['referencia'];
	$concepto = $_POST['concepto'];	
	$user_password = $_POST['password'];
	$empresa_id = $_POST['departamentoMoral'];
    $area_F = $_POST['area'];

	// Mapear los valores de empresa a prefijos
    $empresa_map = [
        "1" => "AB",
        "2" => "BE",
        "3" => "UP",
        "4" => "IMO",
        "5" => "INN"
    ];

    $empresa_prefix = isset($empresa_map[$empresa_id]) ? $empresa_map[$empresa_id] : '';

    // Obtener el último número del identificador para la empresa seleccionada
    $query_id = "SELECT MAX(CAST(SUBSTRING(cod_empresa, CHAR_LENGTH(?) + 1) AS UNSIGNED)) AS last_id FROM providers WHERE cod_empresa LIKE ?";
    $stmt_id = $mysqli->prepare($query_id);
    if ($stmt_id === false) {
        trigger_error('Wrong SQL: ' . $query_id . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    $like_pattern = $empresa_prefix . '%';
    $stmt_id->bind_param('ss', $empresa_prefix, $like_pattern);
    $stmt_id->execute();
    $result_id = $stmt_id->get_result();
    $row_id = $result_id->fetch_assoc();
    $last_id = $row_id['last_id'] ? $row_id['last_id'] : 0;
    $stmt_id->close();

    // Incrementar el número para el nuevo identificador
    $new_id_number = $last_id + 1;
    $new_cod_empresa = $empresa_prefix . $new_id_number;

    //documentacion
    $constancia = isset($_FILES['constancia']['name']) ? $_FILES['constancia']['name'] : null;
    $opinion = isset($_FILES['opinion']['name']) ? $_FILES['opinion']['name'] : null;
    $comprobante = isset($_FILES['comprobante']['name']) ? $_FILES['comprobante']['name'] : null;
    $acta = isset($_FILES['acta']['name']) ? $_FILES['acta']['name'] : null;
    $notarial = isset($_FILES['notarial']['name']) ? $_FILES['notarial']['name'] : null;
	$bancario = isset($_FILES['bancario']['name']) ? $_FILES['bancario']['name'] : null;


    // Obtener la cadena de valores seleccionados del formulario
    $empresa = $_POST['departamentoMoral']; // Obtener el valor de la empresa seleccionada
    $areas_seleccionadas_str = $_POST['area'];

    // Concatenar empresa y áreas seleccionadas en una sola cadena
    $empresa_areas = $areas_seleccionadas_str;

    // Directorio donde se guardarán los documentos, utilizando la razón social como parte de la ruta
    $directorio = $_SERVER['DOCUMENT_ROOT'] . "/providers/$razon_social";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    // Iterar sobre cada archivo y manejar su carga
    $documentos = array("constancia", "opinion", "comprobante", "acta", "notarial", 'bancario');
    foreach ($documentos as $documento) {
        if (isset($_FILES[$documento]) && $_FILES[$documento]['error'] === UPLOAD_ERR_OK) {
            $filename = basename($_FILES[$documento]['name']);
            $destino = $directorio . '/' . $filename;
            move_uploaded_file($_FILES[$documento]['tmp_name'], $destino);
            ${$documento} = $filename;
        } else {
            //${$documento} = null;  Establecer el valor en null si el archivo no se proporciona
			${$documento} = null;
        }
    }
	
	// Crear un objeto DateTime con la zona horaria de México
	$mexico_timezone = new DateTimeZone('America/Mexico_City');
	$current_datetime_mexico = new DateTime('now', $mexico_timezone);

	// Formatear la fecha y hora actual en el formato deseado
	$current_datetime_formatted = $current_datetime_mexico->format('Y-m-d H:i:s');

	// Actualizar las fechas de actualización de documentos
	$constancia_update_date = $current_datetime_formatted;
	$opinion_update_date = $current_datetime_formatted;
	$domicilio_update_date	= $current_datetime_formatted;

    // Preparar la consulta SQL
    $query_providers = "INSERT INTO providers 
                        (
                            tipo_persona, 
                            rfc, 
                            correo_electronico, 
                            razon_social, 
                            direccion, 
                            telefono, 
                            area,
							cuenta,
							clabe,
							moneda,
							swift,
							banco,
							titular,
							referencia,
							concepto,							
                            constancia,
                            opinion,
                            comprobante,
                            acta,
                            notarial,
							bancario,
							constancia_update_date,
							opinion_update_date,							
							domicilio_update_date,
							cod_empresa
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?							
                        );
					";

    header('Content-Type: application/json');

    

	//documentacion			
	$constancia = isset($constancia) ? $constancia : null;
	$opinion = isset($opinion) ? $opinion : null;
	$comprobante = isset($comprobante) ? $comprobante : null;
	$acta = isset($acta) ? $acta : null;
	$notarial = isset($notarial) ? $notarial : null;
	$bancario = isset($bancario) ? $bancario : null;


	// Preparar la declaración
    $stmt = $mysqli->prepare($query_providers);
    if ($stmt === false) {
        trigger_error('Wrong SQL: ' . $query_providers . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    // Vincular los parámetros
    $stmt->bind_param('sssssssssssssssssssssssss', $tipo_persona, $rfc, $correo_electronico, $razon_social, $direccion, $telefono, $empresa_areas, $cuenta, $clabe, $moneda, $swift, $banco, $titular, $referencia, $concepto, $constancia, $opinion, $comprobante, $acta, $notarial, $bancario, $constancia_update_date, $opinion_update_date, $domicilio_update_date, $new_cod_empresa);


    // Ejecutar la consulta providers
    if ($stmt->execute()) {
        // Si la inserción tiene éxito, proceder con la inserción en users

        // Asignar rol automáticamente a 7
        $rol_value = 7;
        // Encriptar la contraseña
        $user_password_encrypted = md5($user_password);
        
        $query_users = "INSERT INTO users (name, username, email, phone, password, rol, cod_empresa) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_users = $mysqli->prepare($query_users);
        
        if ($stmt_users === false) {
            trigger_error('Wrong SQL: ' . $query_users . ' Error: ' . $mysqli->error, E_USER_ERROR);
        }
        
        $stmt_users->bind_param('sssssis', $razon_social, $rfc, $correo_electronico, $telefono, $user_password_encrypted, $rol_value, $new_cod_empresa);
        
        // Ejecutar la consulta users
        if ($stmt_users->execute()) {
            echo json_encode(array(
                'status' => 'Success',
                'message'=> 'Proveedor y usuario agregados correctamente!'
            ));
        } else {
            echo json_encode(array(
                'status' => 'Error',
                'message' => 'Hubo un error al agregar el usuario.<pre>'.$mysqli->error.'</pre><pre>'.$query_users.'</pre>'
            ));
        }
    } else {
        // Si no se puede crear un nuevo registro en providers
        echo json_encode(array(
            'status' => 'Error',
            'message' => 'Hubo un error al agregar el proveedor.<pre>'.$mysqli->error.'</pre><pre>'.$query_providers.'</pre>'
        ));
    }

    // Cerrar conexiones
    $stmt->close();
    $stmt_users->close();
    $mysqli->close();
}


// Registro Persona Fisica
if($action == 'add_fis') {
    // Recoger los datos del formulario para persona física
    $tipo_persona2 = $_POST['tipo_persona2'];    
    $rfcF = $_POST['rfcF'];
    $correo_electronicoF = $_POST['correo_electronicoF'];
	// Reemplazar espacios por guiones bajos en la razón social
	$razon_socialF = str_replace(' ', '_', $_POST['razon_socialF']);
	// Normalizar caracteres especiales y reemplazar caracteres no permitidos en nombres de archivos
	$razon_socialF = iconv('UTF-8', 'ASCII//TRANSLIT', $razon_socialF);
	$razon_socialF = sanitize_filename($razon_socialF);

    $direccionF = $_POST['direccionF'];
    $telefonoF = $_POST['telefonoF'];
    $cuentaF = $_POST['cuentaF'];
    $clabeF = $_POST['clabeF'];
    $monedaF = $_POST['monedaF'];
    $swiftF = $_POST['swiftF'];
    $bancoF = $_POST['bancoF'];
    $titularF = $_POST['titularF'];
    $referenciaF = $_POST['referenciaF'];
    $conceptoF = $_POST['conceptoF'];
    $user_password = $_POST['password'];
    $empresa_id = $_POST['departamentoFisica'];
    $area_F = $_POST['area_F'];

    // Mapear los valores de empresa a prefijos
    $empresa_map = [
        "1" => "AB",
        "2" => "BE",
        "3" => "UP",
        "4" => "IMO",
        "5" => "INN"
    ];

    $empresa_prefix = isset($empresa_map[$empresa_id]) ? $empresa_map[$empresa_id] : '';

    // Obtener el último número del identificador para la empresa seleccionada
    $query_id = "SELECT MAX(CAST(SUBSTRING(cod_empresa, CHAR_LENGTH(?) + 1) AS UNSIGNED)) AS last_id FROM providers WHERE cod_empresa LIKE ?";
    $stmt_id = $mysqli->prepare($query_id);
    if ($stmt_id === false) {
        trigger_error('Wrong SQL: ' . $query_id . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    $like_pattern = $empresa_prefix . '%';
    $stmt_id->bind_param('ss', $empresa_prefix, $like_pattern);
    $stmt_id->execute();
    $result_id = $stmt_id->get_result();
    $row_id = $result_id->fetch_assoc();
    $last_id = $row_id['last_id'] ? $row_id['last_id'] : 0;
    $stmt_id->close();

    // Incrementar el número para el nuevo identificador
    $new_id_number = $last_id + 1;
    $new_cod_empresa = $empresa_prefix . $new_id_number;

    // Documentación
    $documentos = ['constancia_F', 'opinion_F', 'comprobante_domicilio_F', 'nacimiento', 'ine', 'bancarioF'];
    foreach ($documentos as $documento) {
        if (isset($_FILES[$documento]) && $_FILES[$documento]['error'] === UPLOAD_ERR_OK) {
            $filename = basename($_FILES[$documento]['name']);
            $destino = $_SERVER['DOCUMENT_ROOT'] . "/providers/$razon_socialF/$filename";
            if (!file_exists(dirname($destino))) {
                mkdir(dirname($destino), 0777, true);
            }
            move_uploaded_file($_FILES[$documento]['tmp_name'], $destino);
            ${$documento} = $filename;
        } else {
            ${$documento} = null;
        }
    }

    // Crear un objeto DateTime con la zona horaria de México
    $mexico_timezone = new DateTimeZone('America/Mexico_City');
    $current_datetime_mexico = new DateTime('now', $mexico_timezone);
    $current_datetime_formatted = $current_datetime_mexico->format('Y-m-d H:i:s');

    // Fechas de actualización de documentos
    $constancia_update_date = $current_datetime_formatted;
    $opinion_update_date = $current_datetime_formatted;
    $domicilio_update_date = $current_datetime_formatted;

    // Preparar la consulta SQL
    $query_providers = "INSERT INTO providers 
    (
        tipo_persona, 
        rfc, 
        correo_electronico, 
        razon_social, 
        direccion, 
        telefono, 
        area,
        cuenta,
        clabe,
        moneda,
        swift,
        banco,
        titular,
        referencia,
        concepto,
        constancia,
        opinion,
        comprobante,
        nacimiento,
        ine,
        bancario,
        constancia_update_date,
        opinion_update_date,
        domicilio_update_date,
        cod_empresa
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    // Preparar la declaración
    $stmt = $mysqli->prepare($query_providers);
    if ($stmt === false) {
        trigger_error('Wrong SQL: ' . $query_providers . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    // Vincular los parámetros
    $stmt->bind_param('sssssssssssssssssssssssss', $tipo_persona2, $rfcF, $correo_electronicoF, $razon_socialF, $direccionF, $telefonoF, $area_F, $cuentaF, $clabeF, $monedaF, $swiftF, $bancoF, $titularF, $referenciaF, $conceptoF, $constancia_F, $opinion_F, $comprobante_domicilio_F, $nacimiento, $ine, $bancarioF, $constancia_update_date, $opinion_update_date, $domicilio_update_date, $new_cod_empresa);

    error_log('Last ID: ' . $last_id);

    // Ejecutar la consulta providers
    if ($stmt->execute()) {
        // Si la inserción tiene éxito, proceder con la inserción en users
        $rol_value = 7;
        $user_password_encrypted = md5($user_password);
        
        $query_users = "INSERT INTO users (name, username, email, phone, password, rol, cod_empresa) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_users = $mysqli->prepare($query_users);
        
        if ($stmt_users === false) {
            trigger_error('Wrong SQL: ' . $query_users . ' Error: ' . $mysqli->error, E_USER_ERROR);
        }
        
        $stmt_users->bind_param('sssssis', $razon_socialF, $rfcF, $correo_electronicoF, $telefonoF, $user_password_encrypted, $rol_value, $new_cod_empresa);
        
        // Ejecutar la consulta users
        if ($stmt_users->execute()) {
            echo json_encode(array(
                'status' => 'Success',
                'message'=> 'Proveedor agregado correctamente!'
            ));
        } else {
            echo json_encode(array(
                'status' => 'Error',
                'message' => 'Hubo un error al agregar el usuario.<pre>'.$mysqli->error.'</pre><pre>'.$query_users.'</pre>'
            ));
        }
    } else {
        // Si no se puede crear un nuevo registro en providers
        echo json_encode(array(
            'status' => 'Error',
            'message' => 'Hubo un error al agregar el proveedor.<pre>'.$mysqli->error.'</pre><pre>'.$query_providers.'</pre>'
        ));
    }

    // Cerrar conexiones
    $stmt->close();
    $stmt_users->close();
    $mysqli->close();
}

// Update product
//'update_user', que se utiliza para actualizar la 
//información de un usuario existente en la base de datos

if($action == 'update_user') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
	}

	// user information
	$getID = $_POST['id']; // id
	$name = $_POST['name']; // name
	$username = $_POST['username']; // username
	$email = $_POST['email']; // email
	$phone = $_POST['phone']; // phone
	$password = $_POST['password']; // password

	if($password == ''){
		// the query
		$query = "UPDATE users SET
					name = ?,
					username = ?,
					email = ?,
					phone = ?
				 WHERE id = ?
				";
	} else {
		// the query
		$query = "UPDATE users SET
					name = ?,
					username = ?,
					email = ?,
					phone = ?,
					password =?
				 WHERE id = ?
				";
	}

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	if($password == ''){
		/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
		$stmt->bind_param(
			'sssss',
			$name,$username,$email,$phone,$getID
		);
	} else {
		$password = md5($password);
		/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
		$stmt->bind_param(
			'ssssss',
			$name,$username,$email,$phone,$password,$getID
		);
	}

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been updated successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
	
}



//update_documents
// Procesar la actualización de documentos

if ($action == 'update_documents') {
    // Conectar a la base de datos de nuevo si es necesario
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

    if ($mysqli->connect_error) {
        die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
    }

    $constancia = $_FILES['constancia']['name'] ?? '';
    $opinion = $_FILES['opinion']['name'] ?? '';
    $rfc = $_POST['rfc'];

    if (!$constancia || !$opinion) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los documentos son requeridos.']);
        exit;
    }

    // Obtener la razón social para usarla en la ruta del directorio
    $query_select = "SELECT constancia, opinion, razon_social, constancia_update_date, opinion_update_date FROM providers WHERE rfc = ?";
    $stmt_select = $mysqli->prepare($query_select);
    $stmt_select->bind_param('s', $rfc);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        die("Proveedor no encontrado para el RFC proporcionado.");
    }

    // Crear un objeto DateTime con la zona horaria de México
    $mexico_timezone = new DateTimeZone('America/Mexico_City');
    $current_datetime_mexico = new DateTime('now', $mexico_timezone);
    $current_datetime_formatted = $current_datetime_mexico->format('Y-m-d H:i:s');

    // Actualizar las fechas de actualización de documentos
    $constancia_update_date = $constancia ? $current_datetime_formatted : $row['constancia_update_date'];
    $opinion_update_date = $opinion ? $current_datetime_formatted : $row['opinion_update_date'];

    $razon_social = $row['razon_social'] ?? ''; // Use the value from the database if 'razon_social' is not in $_POST

	// If you want to sanitize the razon_social before using it in the path
	if ($razon_social) {
		// Normalizar caracteres especiales y reemplazar caracteres no permitidos en nombres de archivos
		$razon_social_normalizada = preg_replace('/[^A-Za-z0-9_\-]/', '_', $razon_social);
		$razon_social_normalizada = iconv('UTF-8', 'ASCII//TRANSLIT', $razon_social_normalizada);
		$razon_social_normalizada = sanitize_filename($razon_social_normalizada);
	} else {
		die('No se proporcionó razón social.');
	}


    $directorio = $_SERVER['DOCUMENT_ROOT'] . "/providers/" . $razon_social_normalizada;

    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    // Documentos que queremos actualizar
    $documentos = array("constancia", "opinion");

    foreach ($documentos as $documento) {
        if (isset($_FILES[$documento]) && $_FILES[$documento]['error'] === UPLOAD_ERR_OK) {
            // Eliminar el documento existente si ya existe
            if (!empty($row[$documento])) {
                $archivo_existente = $directorio . '/' . $row[$documento];
                if (file_exists($archivo_existente)) {
                    unlink($archivo_existente);
                }
            }

            // Subir el nuevo documento
            $filename = basename($_FILES[$documento]['name']);

            // Si ya existe un archivo con el mismo nombre en la carpeta, renombrar el nuevo archivo
            $destino = $directorio . '/' . $filename;
            if (file_exists($destino)) {
                // Añadir un sufijo de timestamp para evitar colisiones de nombre
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                $destino = $directorio . '/' . $filename;
            }

            move_uploaded_file($_FILES[$documento]['tmp_name'], $destino);
            ${$documento} = $filename;
        } else {
            // Si no se proporciona un nuevo archivo, mantener el valor actual
            ${$documento} = $row[$documento];
        }
    }

    // Actualizar la base de datos con los nuevos nombres de archivo y fechas
    $query_update = "UPDATE providers SET constancia = ?, opinion = ?, constancia_update_date = ?, opinion_update_date = ? WHERE rfc = ?";
    $stmt_update = $mysqli->prepare($query_update);
    $stmt_update->bind_param('sssss', $constancia, $opinion, $constancia_update_date, $opinion_update_date, $rfc);
    $stmt_update->execute();

    if ($stmt_update->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Documentos actualizados correctamente, por favor vuelve a iniciar sesión', 'redirect' => 'index.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se realizaron cambios en los documentos.']);
    }
}









// Delete User
//'delete_user', que se utiliza para eliminar
//un usuario de la base de datos. 

if($action == 'delete_user') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM users WHERE id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Delete User
//'delete_customer', que se utiliza para 
//eliminar un CLIENTE de la base de datos



function sanitize_filename($filename) {
    // Elimina caracteres especiales y reemplaza cualquier cosa que no sea alfanumérico, guiones o subrayados
    $filename = preg_replace('/[^A-Za-z0-9_\-]/', '', $filename);

    // Limitar la longitud del nombre del archivo a un número razonable (ej: 255 caracteres)
    $filename = substr($filename, 0, 255);

    return $filename;
}


?>