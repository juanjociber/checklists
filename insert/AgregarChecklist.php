<?php 
  session_start();
  $datos=array('res'=>false, 'id'=>0, 'msg'=>'Error General.');

  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/solicitudes/data/SolicitudesData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListsData.php";

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(empty($_SESSION['CliId']) || empty($_SESSION['UserName']) || empty($_SESSION['UserNombre'])){throw new Exception("Se ha perdido la conexión.");}
    if(empty($_POST['solid']) || empty($_POST['plaid']) || empty($_POST['fecha'])){throw new Exception("La información esta incompleta.");}

    $checklist=array();
    $solicitud=array();

    $solicitud=FnBuscarSolicitud($conmy, $_POST['solid'], $_SESSION['CliId']);
    if(empty($solicitud['id'])){throw new Exception("No se encontró la Solicitud.");}

    $checklist['cliid']=$_SESSION['CliId'];
    $checklist['solid']=$solicitud['id'];
    $checklist['plaid']=$_POST['plaid'];
    $checklist['equid']=$solicitud['equid'];
    $checklist['fecha']=$_POST['fecha'];
    $checklist['cliruc']=$solicitud['cliruc'];
    $checklist['clinombre']=$solicitud['clinombre'];
    $checklist['clidireccion']=$solicitud['clidireccion'];
    $checklist['clicontacto']=$solicitud['clicontacto'];
    $checklist['clitelefono']=$solicitud['clitelefono'];
    $checklist['clicorreo']=$solicitud['clicorreo'];
    $checklist['supervisor']=$_SESSION['UserNombre'];
    $checklist['equcodigo']=$solicitud['equcodigo'];
    $checklist['equnombre']=$solicitud['equnombre'];
    $checklist['equmarca']=$solicitud['equmarca'];
    $checklist['equmodelo']=$solicitud['equmodelo'];
    $checklist['equplaca']=$solicitud['equplaca'];
    $checklist['equserie']=$solicitud['equserie'];
    $checklist['equmotor']=$solicitud['equmotor'];
    $checklist['equtransmision']=$solicitud['equtransmision'];
    $checklist['equdiferencial']=$solicitud['equdiferencial'];
    $checklist['equkm']=$solicitud['equkm'];
    $checklist['equhm']=$solicitud['equhm'];
    $checklist['usuario']=date('Ymd-His').' ('.$_SESSION['UserName'].')';

    $id=FnRegistrarCheckList($conmy, $checklist);
    if(empty($id)){throw new Exception("Error al procesar la solicitud.");}

    $datos['id']=$id;
    $datos['res']=true;
    $datos['msg']='CheckList generado exitosamente.';

    $conmy=null;
  } catch(PDOException $ex){
    $datos['msg']=$ex->getMessage();
    $conmy=null;
  } catch (Exception $ex) {
    $datos['msg']=$ex->getMessage();
    $conmy=null;
  }
  echo json_encode($datos);
?>
