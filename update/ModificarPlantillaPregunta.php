<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.');

  try {
    if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id']) || empty($_POST['descripcion'])) {throw new Exception("La información está incompleta.");}
    
    // $USUARIO = date('Ymd-His (').'jhuiza'.')';
    $USUARIO = date('Ymd-His (').$_SESSION['UserName'].')';
    $plantillaPregunta = new stdClass();
    $plantillaPregunta->Id = $_POST['id'];
    $plantillaPregunta->Descripcion = $_POST['descripcion'];
    $plantillaPregunta->Usuario = $USUARIO;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (FnModificarPlantillaPregunta($conmy, $plantillaPregunta)) {
      $data['msg'] = "Modificación existosa.";
      $data['res'] = true;
    } else {
      $data['msg'] = "Error modificando Observación.";
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
