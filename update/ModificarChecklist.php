<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";
  $data = array('res' => false, 'msg' => 'Error general.', 'result' => null);

try {
  $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  if(!FnValidarSesion()){throw new Exception("Usuario no tiene Autorización.");}
  if (empty($_POST['id']) || empty($_POST['fecha'])) {throw new Exception("La información está incompleta.");}

  $USUARIO = date('Ymd-His (').$_SESSION['gesman']['Nombre'].')';
  $checklist = new stdClass();
  $checklist->Id = $_POST['id'];
  $checklist->Fecha = $_POST['fecha'];
  $checklist->CliContacto = $_POST['cli_contacto'];
  $checklist->Supervisor = $_POST['supervisor'];
  $checklist->EquNombre= $_POST['equ_nombre'];
  $checklist->EquMarca = $_POST['equ_marca'];
  $checklist->EquModelo = $_POST['equ_modelo'];
  $checklist->EquPlaca = $_POST['equ_placa'];
  $checklist->EquSerie = $_POST['equ_serie'];
  $checklist->EquMotor = $_POST['equ_motor'];
  $checklist->EquTransmision = $_POST['equ_transmision'];
  $checklist->EquDiferencial = $_POST['equ_diferencial'];
  $checklist->EquKm = $_POST['equ_km'];
  $checklist->EquHm = $_POST['equ_hm'];
  $checklist->Usuario = $USUARIO;

  $result = FnModificarCheckList($conmy, $checklist);
  if ($result) {
      $data['msg'] = "Modificación realizada con éxito.";
      $data['res'] = true;
      $data['result'] = $result;
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
