<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";
  $data = array('res' => false, 'msg' => 'Error general.');
  
  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['Id']) || empty($input['respuestas'])) {
      echo json_encode(array('res' => false, 'msg' => 'Datos incompletos para enviar al servidor.'));
      exit;
    }
    $USUARIO = date('Ymd-His (') . $_SESSION['gesman']['Nombre'] . ')';
    // INICIAR TRANSACCIÓN
    $conmy->beginTransaction();

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
    // IMAGENES CHECKLIST
    FnModificarChecklistImagenes($conmy, $fileNames, $USUARIO, $input['Id']);
    // RESPUESTAS
    if (!empty($input['respuestas'])) {
      FnAgregarModificarCheckListActividad($conmy, $input['respuestas'], $input['Id'], $USUARIO);
    }
    $conmy->commit();
    
    $data['msg'] = "Modificación realizada con éxito.";
    $data['res'] = true; 
  } catch (PDOException $ex) {
    if ($conmy->inTransaction()) {
      $conmy->rollBack();
    }
    $data['msg'] = $ex->getMessage();
  } catch (Exception $ex) {
    if ($conmy->inTransaction()) {
      $conmy->rollBack();
    }
    $data['msg'] = $ex->getMessage();
  }
  echo json_encode($data);
?>

