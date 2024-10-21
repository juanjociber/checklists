<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.');
  
  try {
    if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['plaid']) || empty($_POST['descripcion'])) {throw new Exception("La información está incompleta.");}

    // $USUARIO = date('Ymd-His (').'jhuiza'.')';
    $USUARIO = date('Ymd-His (').$_SESSION['UserName'].')';
    $plantillaPregunta = new stdClass();
    $plantillaPregunta->Plaid = $_POST['plaid'];
    $plantillaPregunta->Descripcion = $_POST['descripcion'];
    $plantillaPregunta->Creacion = $USUARIO;
    $plantillaPregunta->Actualizacion = $USUARIO;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (FnRegistrarPlantillaPregunta($conmy, $plantillaPregunta)) {
      $data['msg'] = "Se registró la Actividad.";
      $data['res'] = true;
    } else {
      $data['msg'] = "Error registrando la Actividad.";
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