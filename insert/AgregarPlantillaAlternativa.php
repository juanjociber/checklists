<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.');
  
  try {
    if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['preid']) || empty($_POST['descripcion'])) {throw new Exception("La información está incompleta.");}
    
    $USUARIO = date('Ymd-His (').$_SESSION['UserName'].')';
    $actividad = new stdClass();
    $actividad->Preid = $_POST['preid'];
    $actividad->Descripcion = $_POST['descripcion'];
    $actividad->Creacion = $USUARIO;
    $actividad->Actualizacion = $USUARIO;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (FnRegistrarPlantillaAlternativa($conmy, $actividad)) {
      $data['msg'] = "Registro exitoso.";
      $data['res'] = true;
    } else {
      $data['msg'] = "Error al procesar la solicitud.";
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