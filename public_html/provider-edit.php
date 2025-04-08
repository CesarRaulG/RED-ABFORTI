<?php

include('header.php');
include('functions.php');

$postID = $_POST['id_proveedor'] ?? null;

if ($postID === null) {
    echo "No provider ID provided.";
    exit;
}

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_error .') '. $mysqli->connect_error);
}

// the query
$query = "SELECT * FROM providers WHERE id_proveedor = '" . $mysqli->real_escape_string($postID) . "'";

$result = $mysqli->query($query);

// mysqli select query
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rfc = $row['rfc'];
        $correo_electronico = $row['correo_electronico'];
        $razon_social = $row['razon_social'];
        $direccion = $row['direccion'];
        $telefono = $row['telefono'];
        $constancia = $row['constancia'];
        $opinion = $row['opinion'];
    }
} else {
    echo "No provider found.";
    exit;
}

// close connection
$mysqli->close();
?>

<h1>Proveedor</h1>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>


    <div class="row">
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Datos generales (<?php echo $razon_social; ?>)</h4>
                    <div class="clear"></div>
                </div>
                <div class="panel-body form-group form-group-sm">
                    <div class="row">
                        <div class="col-xs-6">                            
                            <div class="col-sm mb-3">
                                <label for="rfc">RFC</label>
                                <input type="text" class="form-control margin-bottom copy-input required" name="rfc" id="rfc" placeholder="RFC" tabindex="1" value="<?php echo $rfc; ?>">
                            </div>
                            <div class="col-sm mb-3">
                                <label for="direccion">Direccion</label>
                                <input type="text" class="form-control margin-bottom copy-input required" name="direccion" id="direccion"  placeholder="Direccion" tabindex="2"  value="<?php echo $direccion; ?>">
                            </div>
                            <div class="col-sm mb-3">
                                <label for="razon_social">Razon Social</label>
                                <input type="text" class="form-control copy-input required" name="razon_social" id="razon_social" placeholder="Razon Social" tabindex="3" value="<?php echo $razon_social; ?>">
                            </div>
                        </div>
                        <div class="col-xs-6">                
                            <div class="col-sm mb-3">
                                <label for="razon_social">Correo Electronico</label>
                                <input type="text" class="form-control copy-input required" name="correo_electronico" id="correo_electronico" placeholder="Correo Electronico" tabindex="4" value="<?php echo $correo_electronico; ?>">
                            </div>
                            <br>
                            <div class="col-sm mb-3">
                                <label for="telefono">Telefono</label>
                                <input type="text" class="form-control copy-input required" name="telefono" id="telefono" placeholder="Telefono" tabindex="5" value="<?php echo $telefono; ?>">
                            </div>                            
                            <!-- 
                            <div class="form-group">
                                <input type="text" class="form-control margin-bottom copy-input required" name="constancia" id="constancia" placeholder="Constancia" tabindex="6" value="<?php echo $constancia; ?>">
                            </div>
                            
                            <div class="form-group no-margin-bottom">
                                <input type="text" class="form-control required" name="opinion" id="opinion" placeholder="Opinion" tabindex="8" value="<?php echo $opinion; ?>">
                            </div>
                            -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 text-left">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Documentos</h4>
        </div>
        

		
    </div>
</div>

    </div>
    

<?php
include('footer.php');

//include( $_SERVER[ 'DOCUMENT_ROOT' ] . '/includes/modal/table_doc.html' );

?>
