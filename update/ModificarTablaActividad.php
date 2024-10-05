<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/CheckListData.php";
$data = array('res' => false, 'msg' => 'Error general.', 'data'=>'');

try {
  if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])) {throw new Exception("Usuario no tiene Autorización.");}
  if (empty($_POST['id'])) {throw new Exception("La información está incompleta.");}
  
  // $USUARIO = date('Ymd-His (').'jhuiza'.')'; 
  $USUARIO = date('Ymd-His (').$_SESSION['UserName'].')';
  $FileName = null; 
  if (!empty($_POST['archivo'])) {
    $FileName = 'ACT'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
    $FileDecoded = base64_decode($FileEncoded);
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName, $FileDecoded);
  }
  $actividad = new stdClass();
  $actividad->Id = $_POST['id'];
  $actividad->Observaciones = empty($_POST['observaciones']) ? null : ($_POST['observaciones']);
  $actividad->Archivo = $FileName; 
  $actividad->Usuario = $USUARIO;

  $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $result = FnModificarTablaActividad($conmy, $actividad);
  if ($result) {
      $data['msg'] = "Modificación exitosa.";
      $data['res'] = true;
      $data['data'] = $result;
  } else {
      $data['msg'] = "Error modificando Actividad.";
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



