<?php 
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.');

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id']) || empty($_POST['numImagen'])) { throw new Exception("La información está incompleta."); }
    
    $numImagen = (int)$_POST['numImagen'];
    if ($numImagen < 1 || $numImagen > 4) {
      throw new Exception("El número de imagen es inválido.");
    }
    // CONVERTIR 'ID' COMO ENTERO
    $id = (int)$_POST['id'];
    if (FnEliminarPlantillaImagen($conmy, $id, $numImagen)) {
        $data['msg'] = "Eliminación existosa.";
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