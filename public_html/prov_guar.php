<?php
// Verificar si se recibieron archivos
if (isset($_FILES) && !empty($_FILES)) {
    // Obtener el nombre de la razón social del formulario
    $razon_social = $_POST['razon_social'];
    echo "La razón social para personas físicas es: " . $razon_social_fisica;

    // Directorio permanente donde deseas mover los archivos
    $directorioPermanente = $_SERVER['DOCUMENT_ROOT'] . "/providers/";

    // Directorio específico para esta razón social
    $directorioRazonSocial = $directorioPermanente . $razon_social . "/";

    // Crear el directorio si no existe
    if (!file_exists($directorioRazonSocial)) {
        mkdir($directorioRazonSocial, 0777, true);
    }

    // Iterar sobre los archivos cargados
    foreach ($_FILES as $key => $value) {
        // Verificar si el archivo se ha cargado correctamente
        if ($_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            // Mover el archivo a la ubicación permanente
            $nombreArchivo = basename($_FILES[$key]['name']);
            $rutaDestino = $directorioRazonSocial . $nombreArchivo;
            move_uploaded_file($_FILES[$key]['tmp_name'], $rutaDestino);
            echo "Archivo $nombreArchivo guardado correctamente en $rutaDestino";
        } else {
            // Manejar el error si el archivo no se pudo cargar
            echo "Error al cargar el archivo " . $_FILES[$key]['name'] . ": ";
            // Manejar diferentes casos de error
        }
    }
} else {
    echo "No se han recibido archivos.";
}

?>