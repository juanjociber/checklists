<?php 
    session_start();
    require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
    require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/CheckListData.php";
    $data = array('res' => false, 'msg' => 'Error general.');

    try {
      if(empty($_SESSION['CliId']) && empty($_SESSION['UserName'])){throw new Exception("Usuario no tiene Autorización.");}
      if (empty($_POST['chkid']) || empty($_POST['descripcion'])) {throw new Exception("La información está incompleta.");}

      // $USUARIO = date('Ymd-His(').'jhuiza'.')';
      $USUARIO = date('Ymd-His (').$_SESSION['UserName'].')';
      $observacion = new stdClass();
      $observacion->ChkId = $_POST['chkid'];
      $observacion->Descripcion = $_POST['descripcion'];
      $observacion->Usuario = $USUARIO;

      $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      if (FnRegistrarCheckListObservacion($conmy, $observacion)) {
        $data['msg'] = "Se registró observación.";
        $data['res'] = true;
      } else {
        $data['msg'] = "Error registrando Observación.";
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
