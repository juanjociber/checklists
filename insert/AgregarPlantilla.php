<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/PlantillaData.php";
  $data = array('res' => false, 'msg' => 'Error general.', 'id' => 0);

  try {
    if (empty($_POST['tipo'])) { throw new Exception("Campo tipo obligatorio."); }
    // $USUARIO = date('Ymd-His (').'jhuiza'.')';
    $USUARIO = date('Ymd-His (').$_SESSION['UserName'].')';
    $plantilla = new stdClass();
    $plantilla->Tipo = $_POST['tipo'];
    $plantilla->Imagen1 = !empty($_POST['imagen1']) ? $_POST['imagen1'] : null;
    $plantilla->Imagen2 = !empty($_POST['imagen2']) ? $_POST['imagen2'] : null;
    $plantilla->Imagen3 = !empty($_POST['imagen3']) ? $_POST['imagen3'] : null;
    $plantilla->Imagen4 = !empty($_POST['imagen4']) ? $_POST['imagen4'] : null;
    $plantilla->Creacion = $USUARIO;
    $plantilla->Usuario = $USUARIO;

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // VERIFICAR SI 'TIPO' EXISTE
    if (FnTipoPlantillaExiste($conmy, $plantilla->Tipo)) { throw new Exception("El tipo '{$plantilla->Tipo}' ya existe."); }
    // REGISTRAR PLANTILLA
    $id = FnRegistrarPlantilla($conmy, $plantilla);
    if ($id) {
      $data['msg'] = "Registro insertado correctamente.";
      $data['res'] = true;
      $data['id'] = $id;
    } 
    else {
      $data['msg'] = "Error al insertar el registro.";
    }
  } catch (PDOException $ex) {
      $data['msg'] = $ex->getMessage();
      $data['res'] = false;
      $conmy = null;
  } catch (Exception $ex) {
      $data['msg'] = $ex->getMessage();
      $data['res'] = false;
      $conmy = null;
  }
  echo json_encode($data);
?>


