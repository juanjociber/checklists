<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";
  $data = array('res' => false, 'msg' => 'Error general.');

  try {
    if (empty($_SESSION['CliId']) && empty($_SESSION['UserName'])) { throw new Exception("Usuario no tiene Autorización."); }
    if (empty($_POST['id']) || empty($_POST['tipo'])) { throw new Exception("La información está incompleta.");}
    
    $USUARIO = date('Ymd-His (') . $_SESSION['UserName'] . ')';
    $FileName = 'VB'.'_'.$_POST['id'].'_'.uniqid().'.jpeg';
    $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
    $FileDecoded = base64_decode($FileEncoded);
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/gesman/files/".$FileName, $FileDecoded);
    
    $checklist = new stdClass();
    $checklist->Id = $_POST['id'];
    $checklist->EmpFirma = ($_POST['tipo'] === 'emp') ? $FileName : null; 
    $checklist->CliFirma = ($_POST['tipo'] === 'cli') ? $FileName : null; 
    $checklist->Usuario = $USUARIO;

    // DETERMINAR QUE CAMPO ACTUALIZAR
    $campo = ($_POST['tipo'] === 'emp') ? 'emp_firma' : 'cli_firma';

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conmy->prepare("UPDATE tblchecklists SET $campo = :Firma, actualizacion = :Actualizacion WHERE id = :Id");
    $params = array(':Firma' => $FileName, ':Actualizacion' => $USUARIO, ':Id' => $checklist->Id);
    $result = $stmt->execute($params);

    if ($result) {
      $data['msg'] = "Firma generada exitosamente";
      $data['res'] = true;
    } 
    else {
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
