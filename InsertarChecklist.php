<?php 
  session_start();
  if(!isset($_SESSION['UserName']) || !isset($_SESSION['CliId'])){
    header("location:/gesman");
    exit();
  }
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/ChecklistData.php";
  
  $CLIID = $_SESSION['CliId'];;
  $ID = empty($_GET['id'])?0:$_GET['id'];
  $PLAID = 0;
  $checkListPreguntas =array();
  $plantillaPreguntas = array();

  $tablaActividades = array();
  
  $isAuthorized = false;
  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";
  $datos = array();
  
  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $checklist = FnBuscarChecklist($conmy, $ID, $CLIID);
    
    if(!empty($checklist->Id)){
      $checkListPreguntas = FnBuscarCheckListPreguntas($conmy, $checklist->Id);
      if(count($checkListPreguntas) == 0){
        $plantillaPreguntas=FnBuscarPlantillaPreguntas($conmy, $checklist ->PlaId);

        $ids = array_map(function($elemento) {
          return $elemento['id'];
        }, $plantillaPreguntas);
        $alternativas = FnBuscarAlternativas($conmy, $ids);

        foreach($checkListPreguntas as $checkListPregunta){
          $datos[$checkListPregunta['id']]=array('id'=>$checkListPregunta['id'], 'descripcion'=>$checkListPregunta['descripcion'], 'alternativas'=>array());
          
        }
        
        foreach($alternativas as $alternativa){
          $datos[$checkListPregunta['id']]['alternativas'][]=array('id'=>0,'respuesta'=>$checkListPregunta['respuesta']);
        }
      }else{
        foreach($checkListPreguntas as $checkListPregunta){
          $datos[$checkListPregunta['id']]=array('id'=>$checkListPregunta['id'], 'descripcion'=>$checkListPregunta['descripcion'], 'alternativas'=>array('id'=>0,'nombre'=>$checkListPregunta['respuesta']));

        }
      }

      $isAuthorized = true;
      $claseHabilitado = "btn-outline-primary";
      $atributoHabilitado = ""; 
      $plantilla = FnBuscarPlantilla($conmy, $PLAID);
      $tablaActividades = FnBuscarTablaActividades($conmy, $ID);

      // if(count($actividades) > 0){
      //   $ids = array_map(function($elemento) {
      //     return $elemento['id'];
      //   }, $actividades);
      //   $alternativas = FnBuscarAlternativas($conmy, $ids);
      //   foreach($alternativas as $alternativa){
      //     $datos[$alternativa['preid']][]=$alternativa;
      //   }
      // }
    }
  } catch (PDOException $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  } catch (Exception $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  }

  echo '<pre>';
  print_r($checkListPreguntas);
  echo '</pre>';

  echo '<pre>';
  print_r($datos);
  echo '</pre>';

  echo '<pre>';
  print_r($plantillaPreguntas);
  echo '</pre>'
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Checklist | GPEM SAC</title>
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
    @media (max-width: 768px) {
      .d-md-block {
        display: none;
      }
    }
    @media (min-width: 769px) {
      .d-md-none {
        display: none;
      }
    }
    @media only screen and (max-width: 700px) {
			video {
				max-width: 100%;
			}
		}
    .imagen-observacion{display:grid; display:grid;grid-template-columns:25% 50% 25%; }
    @media(min-width:768px){.imagen-observacion{grid-template-columns:2fr 1.5fr 2fr}}
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
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? $_SESSION['CliNombre'] : 'UNKNOWN'; ?></p>
          <input type="text" class="d-none" id="txtIdChecklist" value="<?php echo $ID ?>"/>
          <input type="hidden" id="txtIdChkActividad" value="0"/>
          <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? $checklist->Nombre : 'UNKNOWN'; ?></p>
        </div>
      </div>
      <?php if ($isAuthorized): ?>
        <div class="row">
          <div class="col-12">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
              <ol class="breadcrumb">                        
                <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistDatos.php?id=<?php echo $ID ?>" class="text-decoration-none">DATOS</a></li>
                <li class="breadcrumb-item active fw-bold" aria-current="page">CHECKLIST</li>
                <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistActividad.php?id=<?php echo $ID ?>" class="text-decoration-none">ACTIVIDAD</a></li>
                <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistObservacion.php?id=<?php echo $ID ?>" class="text-decoration-none">OBSERVACION</a></li>
                <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistValidacion.php?id=<?php echo $ID ?>" class="text-decoration-none">VALIDACION</a></li>
              </ol>
            </nav>
          </div>
        </div>
        <!-- SILUETAS -->
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

        <!-- PREGUNTAS Y RESPUESTAS -->
        <div class="row actividades mt-3">
          <div class="col-12 mb-2 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary">PREGUNTAS Y RESPUESTAS</p>
          </div>
          <?php foreach($actividades as $actividad): ?>
            <div class="col-12 mb-2 border border-opacity-50">
              <div class="p-1">
                <div class="d-flex justify-content-between" style="flex-wrap: wrap">
                  <div class="d-flex">
                    <i class="fas fa-check text-secondary" style="margin-right:10px; margin-top:4px;"></i>
                    <p class="mb-0 text-secondary fw-bold pregunta" style="text-align: justify;"><?php echo $actividad['descripcion'] ?></p>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-start mt-1" style="gap:10px; padding-left:30px; flex-wrap: wrap;" id="contenedorAlternativas">
                <?php if(!empty($datos[$actividad['id']])): ?>
                  <?php foreach($datos[$actividad['id']] as $dato): ?>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio_<?php echo $actividad['id']?>" id="<?php echo $dato['id']?>">
                        <input type="hidden" value="<?php echo $dato['preid']?>">
                        <input type="hidden" id="txtEstado" value="<?php echo $dato['estado']?>">
                        <label class="form-check-label" for="<?php echo $dato['id']?>"><?php echo $dato['descripcion']?></label>
                      </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <?php $respuestaEncontrada = false;
                foreach($tablaActividades as $tablaActividad): 
                  if ($tablaActividad['preid'] == $actividad['id']): 
                    $respuestaEncontrada = true; ?>
                    <div class="row border border-light">
                      <div class="col-12 d-flex pt-1 pb-1 bg-light fw-light justify-content-between">
                        <span class="text-secondary">EDICIÓN DE PREGUNTAS Y RESPUESTAS :</span>
                        <span class="border border-0">
                          <i class="fas fa-edit text-secondary" dataId="<?php echo $tablaActividad['id']; ?>" dataPreId="<?php echo $tablaActividad['preid']; ?>" dataDescripcion="<?php echo $actividad['descripcion']; ?>" dataRespuesta="<?php echo $tablaActividad['respuesta']; ?>" dataObservacion="<?php echo $tablaActividad['observaciones']; ?>" style="cursor: pointer;" onclick="FnModalModificarActividad(this)"></i>
                        </span>
                      </div>
                      <div class="col-6 mb-1">
                        <label>Descripción:</label>
                        <p class="mb-0 text-secondary fw-bold"><?php echo $tablaActividad['descripcion'] ?></p>
                      </div>
                      <div class="col-6 mb-1">
                        <label>Respuesta:</label>
                        <p class="mb-0 text-secondary fw-bold"><?php echo $tablaActividad['respuesta'] ?></p>
                      </div>
                      <?php if (!empty($tablaActividad['observaciones'])): ?>
                        <div class="col-6 mb-1">
                          <label>Observación:</label>
                          <p class="mb-0 text-secondary fw-bold"><?php echo $tablaActividad['observaciones'] ?></p>
                        </div>
                      <?php endif; ?>
                      <?php if ($tablaActividad['archivo']): ?>
                        <div class="imagen-observacion mt-2">
                          <label class="form-label mb-0">Archivo:</label>
                          <div class="card p-0" style="grid-column:2/3">
                            <div class="card-header bg-transparent text-center">Imagen</div>
                            <img id="imagen1" src="/mycloud/gesman/files/<?php echo $tablaActividad['archivo'] ?>" class="img-fluid" alt="">
                            <div class="card-footer text-center"></div>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>    
              <?php endif; endforeach;?>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- BOTON GUARDAR -->
        <div class="row">
          <div class="col-12 mt-2 mb-2">
            <button id="guardarDataEquipo" class="btn btn-outline-primary pt-2 pb-2 col-12 fw-bold" onclick="FnAgregarDatosChecklist()"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      <?php endif ?>
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

    <div class="modal fade" id="modalModificarActividad" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title fs-5 fw-bold" id="modalModificarActividadLabel">MODIFICAR ACTIVIDAD</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-1">
            <div class="row">
              <input type="hidden" id="txtRespuesta" value="">                      
              <div class="col-12">
                <label for="txtDescripcion" class="form-label mb-0">Descripcion</label>
                <input id="txtDescripcion" type="text" class="form-control mb-2"/>
              </div>
              <div class="col-12 mb-2">
                <label class="form-label mb-0 col-12">Respuesta</label>
                <input type="hidden" id="txtPreid" value="0">
                <div id="tblAlternativas"></div>
              </div>
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

    <script src="/checklist/js/InsertarChecklist.js"></script>
    <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <script src="/gesman/menu/sidebar.js"></script>

  </body>
</html>