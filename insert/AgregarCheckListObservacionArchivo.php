<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";
  $data = array('res' => false, 'msg' => 'Error general.', 'data'=>'');

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id'])) {throw new Exception("La información está incompleta.");}

  $USUARIO = date('Ymd-His (') . $_SESSION['gesman']['Nombre'] . ')';
  $FileName = 'OBS'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
  $FileEncoded = str_replace("data:image/jpeg;base64,", "",$_POST['archivo']);
  $FileDecoded = base64_decode($FileEncoded);
  file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName, $FileDecoded);

  $observacion = new stdClass();
  $observacion->Id = $_POST['id'];
  $observacion->Archivo = $FileName;
  $observacion->Usuario = $USUARIO;

  $result = FnRegistrarCheckListObservacionArchivo($conmy, $observacion);
  if ($result) {
    $data['msg'] = "Registro exitoso";
    $data['res'] = true;
    $data['data'] = $result;
  } else {
    $data['msg'] = "Erro al procesar la solicitud.";
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

