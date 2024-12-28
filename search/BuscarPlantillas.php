<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.', 'data' => array());

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    $tipo = !empty($_POST['tipo']) ? $_POST['tipo'] : null;

    $plantillas = FnBuscarPlantillas($conmy, $tipo);
    if (!empty($plantillas)) {
      $data['res'] = true;
      $data['msg'] = 'Ok.';
      $data['data'] = $plantillas;
    } else {
      $data['msg'] = 'No existen registros en la base de datos.';
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
