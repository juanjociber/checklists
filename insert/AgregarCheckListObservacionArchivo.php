<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListData.php";
  $data = array('res' => false, 'msg' => 'Error general.', 'data'=>'');

  try {
    if (empty($_SESSION['CliId']) && empty($_SESSION['UserName'])) { throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id'])) {throw new Exception("La información está incompleta.");}

  // $USUARIO = date('Ymd-His (').'jhuiza'.')';
  $USUARIO = date('Ymd-His (') . $_SESSION['UserName'] . ')';
  $FileName = 'OBS'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
  $FileEncoded = str_replace("data:image/jpeg;base64,", "",$_POST['archivo']);
  $FileDecoded = base64_decode($FileEncoded);
  file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName, $FileDecoded);

  $observacion = new stdClass();
  $observacion->Id = $_POST['id'];
  $observacion->Archivo = $FileName;
  $observacion->Usuario = $USUARIO;

  $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $result = FnRegistrarCheckListObservacionArchivo($conmy, $observacion);
  if ($result) {
    $data['msg'] = "Se registro archivo";
    $data['res'] = true;
    $data['data'] = $result;
  } else {
    $data['msg'] = "Error en agregar silueta.";
  }
  } catch (PDOException $ex) {
    $data['msg'] = $ex->getMessage();
    $conmy = null;
  } catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
    $conmy = null;
  }
  echo json_encode($data);
?>

