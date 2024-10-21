<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListData.php";
  $data =array('res' => false,'msg' => 'Error general.');

  try {
    if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id'])) { throw new Exception("La información está incompleta."); }

    // $USUARIO = date('Ymd-His (').'jhuiza'.')';
    $USUARIO = date('Ymd-His (') . $_SESSION['UserName'] . ')';
    $id = (int)$_POST['id'];
    $usuario = $USUARIO;
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (FnEliminarCheckListActividadArchivo($conmy, $id)) {
      $data['msg'] = "Se eliminó Archivo.";
      $data['res'] = true;
    } else {
      $data['msg'] = "Error eliminando Archivo.";
    }
  } catch (PDOException $ex) {
      $data['msg'] = $ex->getMessage();
  } catch (Exception $ex) {
      $data['msg'] = $ex->getMessage();
  } 
  $conmy = null;
  echo json_encode($data);
?>