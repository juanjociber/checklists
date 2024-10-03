<?php
  session_start();
  if(!isset($_SESSION['UserName']) || !isset($_SESSION['CliId'])){
    header("location:/gesman");
    exit();
  }
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/CheckListData.php";
  
  $CLIID = $_SESSION['CliId'];
  $ID = empty($_GET['id'])?0:$_GET['id'];
  $PLAID = 0;
  $tablaActividades =array();
  $actividades =array();
  $alternativas = array();
  $preids=0;
  $isAuthorized = false;
  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";
  
  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $checklist = FnBuscarChecklist($conmy, $CLIID, $ID);
    if(!empty($checklist->Id)){
      $PLAID = $checklist ->PlaId;
      $isAuthorized = true;
      $claseHabilitado = "btn-outline-primary";
      $atributoHabilitado = ""; 
      $tablaActividades = FnBuscarTablaActividades($conmy, $ID);
      $actividades = FnBuscarActividades($conmy, $PLAID);
      if(count($actividades) > 0){
        $ids = array_map(function($elemento) {
          return $elemento['id'];
        }, $actividades);
        $alternativas = FnBuscarAlternativas($conmy, $ids);
        foreach($alternativas as $alternativa){
          $datos[$alternativa['preid']][]=$alternativa;
        }
      }
    }
  } catch (PDOException $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  } catch (Exception $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  }

  

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
    .imagen-observacion{display:grid; display:grid;grid-template-columns:25% 50% 25%; }
    @media(min-width:768px){.imagen-observacion{grid-template-columns:2fr 1.5fr 2fr}}
    .img-fluid{height:100%;}
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
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListDatos.php?id=<?php echo $ID ?>" class="text-decoration-none">DATOS</a></li>
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckList.php?id=<?php echo $ID ?>" class="text-decoration-none">CHECKLIST</a></li>
              <!-- <li class="breadcrumb-item active fw-bold" aria-current="page">ACTIVIDAD</li> -->
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListObservacion.php?id=<?php echo $ID ?>" class="text-decoration-none">OBSERVACION</a></li>
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListValidacion.php?id=<?php echo $ID ?>" class="text-decoration-none">VALIDACION</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <!-- PREGUNTAS Y RESPUESTAS -->
      <div class="actividades">
        <?php 
        $html = ''; 
        foreach ($tablaActividades as $actividad) {
          $html .= '
            <div class="row border border-1 pb-2 mb-2">
              <div class="col-12 d-flex pt-1 pb-1 bg-light fw-light justify-content-between">
                <span class="text-secondary">EDICIÓN DE PREGUNTAS Y RESPUESTAS :</span>
                <span class="border border-0">
                  <i class="fas fa-edit text-secondary" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="left" title="Editar" dataId="'.$actividad['id'].'" dataPreId="'.$actividad['preid'].'" dataChkId="'.$actividad['chkid'].'" dataDescripcion="'.$actividad['descripcion'].'" dataRespuesta="'.$actividad['respuesta'].'" dataObservacion="'.$actividad['observaciones'].'" dataArchivo="'.$actividad['archivo'].'" onclick="FnModalModificarActividad(this)"></i>
                </span>
              </div>
              <div class="col-6 mb-1">
                <label class="form-label mb-0">Descripcion:</label>
                <p class="mb-0 text-secondary fw-bold pregunta" style="text-align: justify;">'.$actividad['descripcion'].'</p>
              </div>';
          if (!empty($actividad['respuesta'])) {
            $html .= '
              <div class="col-6 mb-1">
                <label class="form-label mb-0">Respuesta:</label>
                <p class="mb-0 text-secondary fw-bold pregunta">'.$actividad['respuesta'].'</p>
              </div>';
          }

          if (!empty($actividad['observaciones']) || !empty($actividad['archivo'])) {
            $html .= '<div class="col-12 mb-1">';
            if (!empty($actividad['observaciones'])) {
              $html .= '
              <label class="form-label mb-0">Observacion:</label>
              <p class="mb-0 text-secondary fw-bold">'.$actividad['observaciones'].'</p>';
            }
            if (!empty($actividad['archivo'])) {
              $html .= '
                <label class="form-label mb-0">Archivo:</label>
                <div class="imagen-observacion mt-2">
                  <div class="card p-0" style="grid-column:2/3">
                    <div class="card-header bg-transparent text-center">Imagen</div>
                    <img id="imagen1" src="/mycloud/gesman/files/'.$actividad['archivo'].'" class="img-fluid" alt="">
                    <div class="card-footer text-center"></div>
                  </div>
                </div>';
            }
            $html .= '</div>'; 
          }
          $html .= '</div>'; 
        }
        echo $html; 
        ?>      
      </div>
      <?php endif ?>
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

    <script src="/checklist/js/EditarCheckListActividad.js"></script>
    <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <script src="/gesman/menu/sidebar.js"></script>
  </body>
</html>