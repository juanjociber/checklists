<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/CheckListsData.php";
  $data =array('res' => false,'msg' => 'Error general.');

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id'])) { throw new Exception("La información está incompleta."); }

    $USUARIO = date('Ymd-His (') . $_SESSION['UserName'] . ')';
    $id = (int)$_POST['id'];
    $usuario = $USUARIO;

    if (FnEliminarCheckListObservacionArchivo($conmy, $id)) {
      $data['msg'] = "Eliminación existosa.";
      $data['res'] = true;
    } else {
      $data['msg'] = "Error al procesar la solicitud.";
    }
    $conmy = null;
  } catch (PDOException $ex) {
      $data['msg'] = $ex->getMessage();
      $conmy = null;
  } catch (Exception $ex) {
      $data['msg'] = $ex->getMessage();
      $conmy = null;
  } 
  echo json_encode($data);
?>