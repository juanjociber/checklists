<?php 
    session_start();
    require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
    require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
    require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";
    $data = array('res' => false, 'msg' => 'Error general.');

    try {
      $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
      if (empty($_POST['chkid']) || empty($_POST['descripcion'])) {throw new Exception("La información está incompleta.");}

      $USUARIO = date('Ymd-His (').$_SESSION['gesman']['Nombre'].')';
      $observacion = new stdClass();
      $observacion->ChkId = $_POST['chkid'];
      $observacion->Descripcion = $_POST['descripcion'];
      $observacion->Usuario = $USUARIO;

      if (FnRegistrarCheckListObservacion($conmy, $observacion)) {
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
