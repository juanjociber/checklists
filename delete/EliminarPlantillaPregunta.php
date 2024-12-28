<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php"; 
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.');

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id'])) { throw new Exception("La información está incompleta.");}

    $id = (int)$_POST['id'];
    $stmt = $conmy->prepare("SELECT COUNT(*) FROM tblchkalternativas WHERE preid = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $data['msg'] = "Deberá eliminar primero las alternativas.";
    } 
    else {
      // SI NO TIENE ALTERNATIVAS SE PROCEDE A ELIMINAR PREGUNTA
      if (FnEliminarPlantillaPregunta($conmy, $id)) {
        $data['msg'] = "Eliminación existosa.";
        $data['res'] = true;
      } 
      else {
        $data['msg'] = "Error al procesar la solicitud.";
      }
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
