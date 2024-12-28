<?php
  session_start();
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/data/SesionData.php";
  if(!FnValidarSesion()){
    header("location:/gesman/Salir.php");
    exit();
  }
  if(!FnValidarSesionManNivel1()){
    header("HTTP/1.1 403 Forbidden");
    exit();
  }

  $ID=empty($_GET['id']) ? 0 : $_GET['id'];
  $DATOS=array();

  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/CheckListsData.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/PlantillaData.php";

  try{
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $CHECKLIST=array();
    $CHECKLIST=FnBuscarCheckList2($conmy, $_SESSION['gesman']['CliId'], $ID);

    if(!empty($CHECKLIST['id'])){
      $plantilla = FnBuscarPlantilla($conmy, $CHECKLIST['plaid']);
      $CHK_PREGUNTAS=array();
      $CHK_PREGUNTAS=FnBuscarCheckListActividadPreguntas($conmy, $CHECKLIST['id']);
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
            'observaciones'=>$valor['observaciones'],
            'archivo'=>$valor['archivo'], 
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
        $PLA_PREGUNTAS=FnBuscarPlantillaPreguntas1($conmy, $CHECKLIST['plaid']);

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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar CheckList | GPEM S.A.C</title>
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
    .contenedor-archivo{
      display: grid; grid-template-columns: 1fr 1fr 1fr;
    }
    .observacion1{ grid-column: 1 / 4; }
    .archivo1{ grid-column: 2 / 3; }
    @media(min-width:1200px){.contenedor-archivo{grid-template-columns:1fr 0.7fr 1fr;}}
    .imagen-ajustada {
      width: auto !important;
      height: 200px;
      object-fit: contain; 
    }
  </style>
<body>
  <?php require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/menu/sidebar.php';?>
  <div class="container section-top">
    <div class="row mb-3">
      <div class="col-12 btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-outline-primary fw-bold" onclick="FnListarChecklists(); return false;"><i class="fas fa-list"></i><span class="d-none d-sm-block"> Checklists</span></button>
        <button type="button" class="btn btn-outline-primary fw-bold <?php echo $claseHabilitado;?> <?php echo $atributoHabilitado;?>" onclick="FnResumenChecklist(); return false;"><i class="fas fa-desktop"></i><span class="d-none d-sm-block"> Resumen</span></button>
      </div>
    </div>

    <div class="row border-bottom mb-3 fs-5">
      <div class="col-12 fw-bold" style="display:flex; justify-content:space-between;">
        <p class="m-0 p-0 text-secondary"><?php echo $_SESSION['gesman']['CliNombre'];?></p>
        <input type="text" class="d-none" id="txtIdChecklist" value="<?php echo $ID ?>"/>
        <input type="hidden" id="txtIdChkActividad" value="0"/>
        <input type="hidden" id="txtId" value="<?php echo $ID;?>" readonly/>
        <input type="hidden" id="txtPreId" value="0">
        <p class="m-0 p-0 text-center text-secondary"><?php echo empty($CHECKLIST['nombre'])?null:$CHECKLIST['nombre'];?></p>
      </div>
    </div>

  <!-- autorizacion -->
    <!-- MENU -->
    <div class="row">
      <div class="col-12">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
          <ol class="breadcrumb">                        
            <li class="breadcrumb-item fw-bold"><a href="/checklists/EditarCheckListDatos.php?id=<?php echo $ID ?>" class="text-decoration-none">DATOS</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page">CHECKLIST</li>
            <li class="breadcrumb-item fw-bold"><a href="/checklists/EditarCheckListObservacion.php?id=<?php echo $ID ?>" class="text-decoration-none">OBSERVACION</a></li>
            <li class="breadcrumb-item fw-bold"><a href="/checklists/EditarCheckListValidacion.php?id=<?php echo $ID ?>" class="text-decoration-none">VALIDACION</a></li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- PLANTILLAS -->
    <div class="row m-0">
      <div class="col-12 mb-2 border border-1 bg-light">
        <p class="mt-2 mb-2 fw-bold text-secondary">PLANTILLAS:</p>
      </div>
      <div class="contenedor-imagen mt-2">
        <div class="card p-0">
          <div class="card-header p-0 bg-transparent text-center">Lado derecho</div>
          <img id="imagen1" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen1) ? '0.jpg' : $plantilla->Imagen1 ?>" class="img-fluid imagen-ajustada" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
        <div class="card p-0 ">
          <div class="card-header p-0 bg-transparent text-center">Anterior</div>
          <img id="imagen2" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen2) ? '0.jpg' : $plantilla->Imagen2 ?>" class="img-fluid imagen-ajustada" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
        <div class="card p-0 ">
          <div class="card-header p-0 bg-transparent text-center">Lado izquierdo</div>
          <img id="imagen3" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen3) ? '0.jpg' : $plantilla->Imagen3 ?>" class="img-fluid imagen-ajustada" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
        <div class="card p-0 ">
          <div class="card-header p-0 bg-transparent text-center">Posterior</div>
          <img id="imagen4" src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen4) ? '0.jpg' : $plantilla->Imagen4 ?>" class="img-fluid imagen-ajustada" alt="">
          <div class="card-footer text-center p-0">
            <button type="button" class="btn btn-secondary p-0 col-12 bg-transparent border border-0 text-secondary" onclick="FnHabilitarDibujo(this)"><i class="fa fa-arrow-right"></i> Trazar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- PREGUNTAS Y REPUESTAS -->
    <div class="row mb-1 actividades p-1 mt-3">
      <div class="col-12 mb-2 border border-1 bg-light">
        <p class="mt-2 mb-2 fw-bold text-secondary">PREGUNTAS Y RESPUESTAS</p>
      </div>
      <?php
        $contador=0;   
        foreach($DATOS as $key => $valor){
          echo '
          <div class="col-12 mb-2 border border-1" style="position:relative">';                   
            echo '
            <p class="m-0 text-secondary fw-bold pregunta">'.$valor['pregunta'].'</p>';
            // VERIFICAR SI EXISTE ALGUNA ALTERNATIVA MARCADA
            $existeCheked = false;
            foreach ($valor['alternativas'] as $valor2) {
              if ($valor2['estado'] == 1) {
                $existeCheked = true;
                break; 
              }
            }
            // MOSTRAR BOTÓN EDICIÓN CON ALTERNATIVAS CHECKED
            if ($existeCheked) {
            echo '
            <div style="position:absolute; top:10px; right:10px">
              <span class="border border-0">
                <i class="fas fa-edit text-secondary" style="cursor: pointer;" dataId="'.$valor['id'].'" dataPreId="'.$valor['preid'].'" dataObservacion="'.$valor['observaciones'].'" dataArchivo="'.$valor['archivo'].'" onclick="FnModalModificarActividad(this)"></i>
              </span> 
            </div>';
            }
            echo '
            <div class="d-flex">';
              foreach ($valor['alternativas'] as $key2 => $valor2) {
              $checked = '';
              $contador+=1;
              if ($valor2['estado'] == 1) { $checked = 'checked'; }
              echo 
              '<div id="contenedorAlternativas"> 
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="radio_'.$key.'" id="'.$contador.'" '.$checked.' datapreid="'.$valor['id'].'" dataprenombre="'.$valor['pregunta'].'" value="'.$key2.'" onclick="FnModificarRespuesta(this)">
                  <input type="hidden" value="'.$valor['preid'].'">
                  <input type="hidden" id="txtEstado" value="'.$valor2['estado'].'">
                  <label class="form-check-label" for="'.$contador.'">'.$key2.'</label>
                </div>
              </div>';
              }
            echo '
            </div>';
            if(!empty($valor['observaciones'])){
            echo '  
            <div class="row observacion1 d-flex mt-2";>
              <label class="text-secondary">Observación:</label>
              <p class="mb-0 text-secondary fw-bold" id="idActividad" style="text-align: justify;">'.$valor['observaciones'].'</p>
            </div>';
            }
            if(!empty($valor['archivo'])){
            echo '
            <div class="mt-2 mb-2" style="position:relative;">
              <span onclick="FnEliminarArchivoActividad('.$valor['id'].')" style="position: absolute; color:#ede2e2; font-size:30px; top:0; left:5px; cursor:pointer;">&#x2715</span>
              <img src="/mycloud/gesman/files/'.$valor['archivo'].'" class="img-fluid imagen-ajustada" alt="">
            </div>';
            } 
          echo '
          </div>';
        }
      ?>
    </div>
    <!-- BOTON ENVIO AL SERVIDOR -->
    <div class="row">
      <div class="col-12 mt-2 mb-2">
        <button id="guardarDataEquipo" class="btn btn-outline-primary pt-2 pb-2 col-12 fw-bold" onclick="FnAgregarDatosChecklist()"><i class="fas fa-save"></i> GUARDAR</button>
      </div>
    </div>
          
  <!-- endif -->
  </div>

  <!-- MODAL DIBUJAR CANVA -->
  <div class="modal fade " id="modalAgregarCanva" tabindex="-1" aria-labelledby="modalAgregarCanvaLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title fs-5" id="modalAgregarCanvaLabel">REALIZAR TRAZADO</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body d-flex justify-content-center align-items-center">
          <canvas id="canvasDibujo" class="canvas-dibujo" ></canvas>
        </div>
        <div class="modal-footer">
          <input type="color" id="colorPickerDibujo" class="form-control form-control-color" value="#ff0000">
          <button type="button" class="btn btn-secondary" id="btnLimpiarCanvas">Limpiar</button>
          <button type="button" class="btn btn-primary" onclick="FnGuardarDibujo();">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODIFICAR DATOS DE ACTIVIDAD -->
  <div class="modal fade" id="modalModificarActividad">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header text-secondary">
          <h5 class="modal-title fs-5 fw-bold" id="modalModificarActividadLabel">MODIFICAR</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pb-1">
          <div class="row">
            <div class="col-12">
              <label for="txtObservacion" class="form-label mb-0">Observacion</label>
              <input id="txtObservacion" type="text" class="form-control mb-2"/>
            </div>
            <div class="col-12">
              <label for="fileImagen" class="form-label mb-0">Imagen</label>
              <input id="fileImagen" type="file" accept="image/*" class="form-control mb-2"/>
            </div>
            <div class="col-12 m-0">
              <div class="col-md-12 text-center" id="divImagen"><i class="fas fa-images fs-2"></i></div>
            </div>
          </div>
        </div>
        <div id="msjModicarActividad" class="modal-body pt-1"></div>
        <div class="col-12 modal-footer">
          <button type="button" class="btn btn-primary fw-bold pt-2 pb-2 col-12 w-100" onclick="FnModificarActividad();"><i class="fas fa-save"></i> GUARDAR</button>
        </div>
      </div>
    </div>
  </div>

  <div class="container-loader-full">
    <div class="loader-full"></div>
  </div>

  <script src="/checklists/js/EditarCheckList.js"></script>
  <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
  <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  <script src="/gesman/menu/sidebar.js"></script>
</body>
</html>