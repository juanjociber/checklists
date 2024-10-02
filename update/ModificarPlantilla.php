<?php
require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/ChecklistData.php";

$data = array('res' => false, 'msg' => 'Error general.');

try {
    if (empty($_POST['id'])) {
        throw new Exception("La información está incompleta.");
    }

    $USUARIO = date('Ymd-His (').'jhuiza'.')';

    $FileName1 = 'ANTERIOR'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileName2 = 'DERECHO'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileName3 = 'IZQUIERDO'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileName4 = 'POSTERIOR'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
    $FileDecoded = base64_decode($FileEncoded);

    $filePaths = [
        $_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName1,
        $_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName2,
        $_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName3,
        $_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName4
    ];

    foreach ($filePaths as $filePath) {
        if (file_put_contents($filePath, $FileDecoded) === false) {
            throw new Exception('Error al guardar el archivo: ' . $filePath);
        }
    }

    $plantilla = new stdClass();
    $plantilla->id = $_POST['id'];
    $plantilla->Imagen1 = $FileName1;
    $plantilla->Imagen2 = $FileName2;
    $plantilla->Imagen3 = $FileName3;
    $plantilla->Imagen4 = $FileName4;
    $plantilla->Actualizacion = $USUARIO;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $result = FnModificarPlanilla($conmy, $plantilla);
    if ($result) {
        $data['msg'] = "Ok";
        $data['res'] = true;
    } else {
        $data['msg'] = "Error modificando datos generales.";
    }
} catch (PDOException $ex) {
    $data['msg'] = $ex->getMessage();
} catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
}

echo json_encode($data);
?>


