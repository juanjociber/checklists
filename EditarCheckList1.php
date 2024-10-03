<?php
    session_start();

    if(empty($_SESSION['UserName']) || empty($_SESSION['CliId']) || empty($_SESSION['CliNombre'])){
        header("location:/gesman/Salir.php");
        exit();
    }

    $ID=empty($_GET['id']) ? 0 : $_GET['id'];
    $DATOS=array();

    require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
    require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos2/CheckListsData1.php";

    try{
        $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $CHECKLIST=array();
        $CHECKLIST=FnBuscarCheckList($conmy, $_SESSION['CliId'], $ID);

        if(!empty($CHECKLIST['id'])){
          $CHK_PREGUNTAS=array();
          $CHK_PREGUNTAS=FnBuscarCheckListPreguntas($conmy, $CHECKLIST['id']);
          $PLA_ALTERNATIVAS=array();
          if(count($CHK_PREGUNTAS)>0){
            $preguntas=array_map(function($elem) {
              return $elem['preid'];
            }, $CHK_PREGUNTAS);
            
            $PLA_ALTERNATIVAS=FnBuscarPlantillaPreguntasAlternativas($conmy, $preguntas);
            foreach($CHK_PREGUNTAS as $valor){
              $DATOS[$valor['preid']]=array(
                'id'=>$valor['id'], 
                'preid'=>$valor['preid'], 
                'pregunta'=>$valor['pregunta'], 
                'alternativas'=>array()
              );
            }

            foreach($PLA_ALTERNATIVAS as $valor){
              $DATOS[$valor['preid']]['alternativas'][$valor['respuesta']]=array(
                'id'=>0,
                'estado'=>0
              );
            }

            foreach($CHK_PREGUNTAS as $valor){
              $DATOS[$valor['preid']]['alternativas'][$valor['respuesta']]=array(
                'id'=>$valor['id'],
                'estado'=>1
              );
            }
          }else{
            $PLA_PREGUNTAS=array();
            $PLA_PREGUNTAS=FnBuscarPlantillaPreguntas($conmy, $CHECKLIST['plaid']);

            $preguntas = array_map(function($elem) {
              return $elem['id'];
            }, $PLA_PREGUNTAS);    

            $PLA_ALTERNATIVAS=FnBuscarPlantillaPreguntasAlternativas($conmy, $preguntas);

            foreach($PLA_PREGUNTAS as $valor){
              $DATOS[$valor['id']]=array(
                'id'=>0,
                'preid'=>$valor['id'],
                'pregunta'=>$valor['pregunta'],
                'alternativas'=>array()
              );
            }

            foreach($PLA_ALTERNATIVAS as $valor){
              $DATOS[$valor['preid']]['alternativas'][$valor['respuesta']]=array(
                'id'=>0,
                'estado'=>0
              );
            }    
          }
        }
        $conmy==null;
    } catch(PDOException $ex) {
        $conmy = null;
        print_r($ex);
    } catch (Exception $ex) {
        $conmy = null;
        print_r($ex);
    }

     // echo '<pre>';
    // print_r($DATOS);
    // echo '</pre>';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar CheckList | GPEM SAC.</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/fontawesome-free-5.9.0-web/css/all.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
    <link rel="stylesheet" href="/mycloud/library/gpemsac/css/gpemsac.css"> 
    <link rel="stylesheet" href="/gesman/menu/sidebar.css">
</head>
  <style>
    .contenedor-imagen{display:grid !important;gap:10px; grid-template-columns:1fr 1fr !important;}
    @media(min-width:769px){.contenedor-imagen{grid-template-columns:1fr 1fr 1fr 1fr !important;}}
  </style>
<body>
  <?php require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/menu/sidebar.php';?>
  <div class="container section-top">

    <div class="row border-bottom mb-3 fs-5">
      <div class="col-12 fw-bold" style="display:flex; justify-content:space-between;">
        <p class="m-0 p-0 text-secondary"><?php echo $_SESSION['CliNombre'];?></p>
        <input type="hidden" id="txtId" value="<?php echo $ID;?>" readonly/>
        <p class="m-0 p-0 text-center text-secondary"><?php echo empty($CHECKLIST['nombre'])?null:$CHECKLIST['nombre'];?></p>
      </div>
    </div>

  <!-- autorizacion -->
    <!-- MENU -->
    <div class="row">
      <div class="col-12">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
          <ol class="breadcrumb">                        
            <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListDatos.php?id=<?php echo $ID ?>" class="text-decoration-none">DATOS</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page">CHECKLIST</li>
            <!-- <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListActividad.php?id=<?php echo $ID ?>" class="text-decoration-none">ACTIVIDAD</a></li> -->
            <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListObservacion.php?id=<?php echo $ID ?>" class="text-decoration-none">OBSERVACION</a></li>
            <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListValidacion.php?id=<?php echo $ID ?>" class="text-decoration-none">VALIDACION</a></li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- PLANTILLAS -->
    <div class="row">
      <div class="col-12 mb-2 border-bottom bg-light">
        <p class="mt-2 mb-2 fw-bold text-secondary">SILUETAS</p>
      </div>
      <div class="contenedor-imagen mt-2">
        <div class="card p-0">
          <div class="card-header p-0 bg-transparent text-center">Lado derecho</div>
          <img id="imagen1" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen1) ? '0.jpg' : $plantilla->Imagen1 ?>" class="img-fluid" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
        <div class="card p-0 ">
          <div class="card-header p-0 bg-transparent text-center">Anterior</div>
          <img id="imagen2" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen2) ? '0.jpg' : $plantilla->Imagen2 ?>" class="img-fluid" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
        <div class="card p-0 ">
          <div class="card-header p-0 bg-transparent text-center">Lado izquierdo</div>
          <img id="imagen3" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen3) ? '0.jpg' : $plantilla->Imagen3 ?>" class="img-fluid" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
        <div class="card p-0 ">
          <div class="card-header p-0 bg-transparent text-center">Posterior</div>
          <img id="imagen4" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen4) ? '0.jpg' : $plantilla->Imagen4 ?>" class="img-fluid" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
      </div>
    </div>


    <div class="row mb-1 p-1">
      <div class="col-12 mb-2 border-bottom bg-light">
        <p class="mt-2 mb-2 fw-bold text-secondary">PREGUNTAS Y RESPUESTAS</p>
      </div>
      <?php 
        foreach($DATOS as $key=>$valor){
          echo '<div class="col-12 mb-2">';                   
          echo '<p class="m-0 fw-bold">'.$valor['pregunta'].'</p>';
          foreach($valor['alternativas'] as $key2=>$valor2){
            $checked='';
            if($valor2['estado']==1){$checked='checked';}
            echo '
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="rbPregunta'.$key.'" id="rbPregunta'.$key.'" '.$checked.' datapreid="'.$valor['id'].'" dataprenombre="'.$valor['pregunta'].'" value="'.$key2.'">
                <label class="form-check-label" for="rbPregunta'.$key.'">'.$key2.'</label>
            </div>';
          }
        echo '</div>';
        }
      ?>
    </div>
          
  <!-- endif -->
  </div>

  <!-- <script src="/checklist/js/EditarCheckList.js"></script> -->
  <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
  <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  <script src="/gesman/menu/sidebar.js"></script>
</body>
</html>