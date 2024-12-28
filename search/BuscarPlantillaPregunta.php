<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/PlantillaData.php";
  $data = array('res' => false,'msg' => 'Error general.', 'data'=>null);

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    if(empty($_POST['id'])){ throw new Exception("La informacion esta incompleta."); }
    
    $plantillaPregunta = FnBuscarPlantillaPregunta($conmy, $_POST['id']);
    if ($plantillaPregunta) {
      $data['res'] = true;
      $data['msg'] = 'Ok.';
      $data['data'] = $plantillaPregunta;
    } else {
      $data['msg'] = 'No existen registros en la base de datos.';
    }
    $conmy = null;
  } catch(PDOException $ex){
      $data['msg'] = $ex->getMessage();
      $conmy = null;
  } catch (Exception $ex) {
      $data['msg'] = $ex->getMessage();
      $conmy = null;
  } 
  echo json_encode($data);
?>