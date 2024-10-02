<?php 
require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
$data = array('res' => false, 'msg' => 'Error general.');

try {
  // LECTURA A DATOS DEL JSON
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || !isset($input['Id']) || empty($input['respuestas'])) {
    echo json_encode(array('res' => false, 'msg' => 'Datos incompletos.'));
    exit;
  }
  $USUARIO = date('Ymd-His (').'jhuiza'.')';
  $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // PROCESAR IMAGENES
  $imageFields = array('imagen1', 'imagen2', 'imagen3', 'imagen4');
  $fileNames = array();
  foreach ($imageFields as $field) {
    if (!empty($input[$field])) {
      $fileName = 'CHK_'.$input['Id'].'_'.uniqid().'.jpeg';
      $fileEncoded = str_replace("data:image/jpeg;base64,", "", $input[$field]);
      $fileDecoded = base64_decode($fileEncoded);
      file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$fileName, $fileDecoded);
      /** ALMACENAR NOMBRE DE ARCHIVO */
      $fileNames[$field] = $fileName; 
    } else {
      $fileNames[$field] = null; 
    }
  }
  // ACTUALIZAR TABLA : tblchecklists
  $sql = "UPDATE tblchecklists SET imagen1 = :Imagen1, imagen2 = :Imagen2, imagen3 = :Imagen3, imagen4 = :Imagen4, actualizacion = :Actualizacion WHERE id = :Id";
  $stmt = $conmy->prepare($sql);
  $stmt->execute(array(
    ':Imagen1' => $fileNames['imagen1'],
    ':Imagen2' => $fileNames['imagen2'],
    ':Imagen3' => $fileNames['imagen3'],
    ':Imagen4' => $fileNames['imagen4'],
    ':Actualizacion' => $USUARIO,
    ':Id' => $input['Id'] 
  ));
  // INSERTAR TABLA : tblchkactividades
  if (!empty($input['respuestas'])) {
    $sqlRespuestas = "INSERT INTO tblchkactividades (preid, chkid, descripcion, respuesta, observaciones, archivo, estado, creacion) 
                      VALUES (:Preid, :Chkid, :Descripcion, :Respuesta, :Observaciones, :Archivo, :Estado, :Creacion)";
    $stmtRespuestas = $conmy->prepare($sqlRespuestas);
    
    foreach ($input['respuestas'] as $respuesta) {
      $stmtRespuestas->execute(array(
        ':Preid' => $respuesta['Preid'],
        ':Chkid' => $input['Id'],
        ':Descripcion' => $respuesta['Descripcion'],
        ':Respuesta' => $respuesta['Respuesta'],
        ':Observaciones' => null, 
        ':Archivo' => null, 
        ':Estado' => 2,
        ':Creacion' => $USUARIO
      ));
    }
  }
  $data['msg'] = "Datos guardados exitosamente.";
  $data['res'] = true;
} catch (PDOException $ex) {
    $data['msg'] = $ex->getMessage();
    $conmy = null;
} catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
    $conmy = null;
}
echo json_encode($data);

?>

