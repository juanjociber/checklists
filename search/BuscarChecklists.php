<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";
  $data = array('res' => false, 'pag' => 0, 'msg' => 'Error general.', 'data'=>array());

  try {
    if (empty($_SESSION['CliId'])) {throw new Exception("Usuario no tiene Autorización.");}
    if (empty($_POST['fechainicial']) || empty($_POST['fechafinal'])) {throw new Exception("Las fechas de búsqueda están incompletas.");}

    $checklist = new stdClass();
    $checklist->CliId = $_SESSION['CliId'];
    $checklist->Nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
    $checklist->Equipo = !empty($_POST['equipo']) ? $_POST['equipo'] : 0;
    $checklist->FechaInicial = $_POST['fechainicial'];
    $checklist->FechaFinal = $_POST['fechafinal'];
    $checklist->Pagina = !empty($_POST['pagina']) ? (int)$_POST['pagina'] : 0;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $checklists = FnBuscarCheckLists($conmy, $checklist);

    if ($checklists['pag'] > 0) {
      $data['res'] = true;
      $data['msg'] = 'Ok.';
      $data['pag'] = $checklists['pag'];
      $data['data'] = $checklists['data'];
    } else {  
      $data['msg'] = 'No existen registros en la base de datos.';
    }
  } catch(PDOException $ex) {
    $data['msg'] = $ex->getMessage();
    $conmy = null;
  } catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
    $conmy = null;
  }
  echo json_encode($data);
?>
