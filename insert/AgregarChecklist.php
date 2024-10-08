<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/CheckListData.php";
  $data = array('res' => false, 'msg' => 'Error general.');
  
  try {
    if (empty($_SESSION['CliId']) && empty($_SESSION['UserName'])) {
      throw new Exception("Usuario no tiene Autorización.");
    }
    // LECTURA A DATOS DEL JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['Id']) || empty($input['respuestas'])) {
      echo json_encode(array('res' => false, 'msg' => 'Datos incompletos para enviar al servidor.'));
      exit;
    }
    $USUARIO = date('Ymd-His (') . $_SESSION['UserName'] . ')';
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // INICIAR TRANSACCIÓN
    $conmy->beginTransaction();
    // PROCESAR IMÁGENES
    $fileNames = FnProcesarImagenes($input, $input['Id']);
    // IMAGENES CHECKLIST
    FnModificarChecklistImagenes($conmy, $fileNames, $USUARIO, $input['Id']);
    // RESPUESTAS
    if (!empty($input['respuestas'])) {
      FnAgregarModificarActividad($conmy, $input['respuestas'], $input['Id'], $USUARIO);
    }
    $conmy->commit();
    
    $data['msg'] = "Datos guardados exitosamente.";
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

