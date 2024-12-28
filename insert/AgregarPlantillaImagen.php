<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.');

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['id']) || empty($_POST['numImagen'])) { throw new Exception("La información está incompleta.");}

    $USUARIO = date('Ymd-His (').$_SESSION['gesman']['Nombre'].')';
    $FileName = 'PLA'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
    $FileDecoded = base64_decode($FileEncoded);
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName, $FileDecoded);

    $plantilla = new stdClass();
    $plantilla->Id = $_POST['id'];
    $plantilla->Imagen = $FileName; 
    $plantilla->Usuario = $USUARIO;

    $numImagen = $_POST['numImagen'];
    if (!in_array($numImagen, array(1, 2, 3, 4))) {
      throw new Exception("Número de imagen inválido.");
    }
    $result = FnRegistrarPlantillaImagen($conmy, $plantilla, $numImagen);
    
    if ($result) {
      $data['msg'] = "Registro exitoso.";
      $data['res'] = true;
    } else {
      $data['msg'] = "Error al procesar la solicitud.";
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


