<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/ChecklistData.php";
  $data = array('res' => false,'msg' => 'Error general.', 'data'=>null);

  try {
  //   if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])){throw new Exception("Usuario no tiene Autorización.");}
    if(empty($_POST['id'])){ throw new Exception("La informacion esta incompleta."); }
    
    $actividad = FnBuscarTablaActividad($conmy, $_POST['id']);
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    if ($actividad) {
      $data['res'] = true;
      $data['msg'] = 'Ok.';
      $data['data'] = $actividad;
    } else {
      $data['msg'] = 'No se encontró la actividad.';
    }

  } catch(PDOException $ex){
      $data['msg'] = $ex->getMessage();
      $conmy = null;
  } catch (Exception $ex) {
      $data['msg'] = $ex->getMessage();
      $conmy = null;
  } 
  echo json_encode($data);
?>


