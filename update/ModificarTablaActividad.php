<?php
require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/ChecklistData.php";
$data = array('res' => false, 'msg' => 'Error general.');

try {
    // Validación de datos
    if (empty($_POST['id']) || empty($_POST['descripcion']) || empty($_POST['respuesta'])) {
        throw new Exception("La información está incompleta.");
    }
    
    $FileName = null;
    if(!empty($_POST['archivo'])) {
        $FileName = 'ACT_' . $_POST['id'] . '_' . uniqid() . '.jpeg';
        $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
        $FileDecoded = base64_decode($FileEncoded);
        file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName, $FileDecoded);
    }

    $USUARIO = date('Ymd-His (').'jhuiza'.')'; 
    $actividad = new stdClass();
    $actividad->Id = $_POST['id'];
    $actividad->Descripcion = $_POST['descripcion'];
    $actividad->Respuesta = $_POST['respuesta'];
    $actividad->Observaciones = empty($_POST['observaciones']) ? null : ($_POST['observaciones']);
    $actividad->Archivo = $FileName;
    $actividad->Usuario = $USUARIO;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (FnModificarTablaActividad($conmy, $actividad)) {
        $data['msg'] = "Modificación exitosa.";
        $data['res'] = true;
    } else {
        $data['msg'] = "Error modificando Actividad.";
    }
} catch (PDOException $ex) {
    $data['msg'] = 'Error en la base de datos: ' . $ex->getMessage();
} catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
} 
echo json_encode($data);
?>


