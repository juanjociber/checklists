<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/CheckListData.php";
  $data = array('res' => false, 'msg' => 'Error general.', 'data' => null);

try {
  if (empty($_SESSION['CliId']) || empty($_SESSION['UserName'])) {throw new Exception("Usuario no tiene Autorización.");}

  $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $preid = $_POST['preid'];

  $alternativas = FnBuscarAlternativas2($conmy, $preid);
  if (!empty($alternativas)) {
      $data['res'] = true;
      $data['msg'] = 'Ok.';
      $data['data'] = $alternativas;
  } else {
      $data['msg'] = 'No se encontraron resultados.';
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
