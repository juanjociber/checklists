<?php
  session_start();
  if(!isset($_SESSION['UserName']) || !isset($_SESSION['CliId'])){
    header("location:/gesman");
    exit();
  }
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklist/datos/ChecklistData.php";
  
  $CLIID = $_SESSION['CliId'];
  $ID = empty($_GET['id'])?0:$_GET['id'];
  $isAuthorized = false;
  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(is_numeric($ID) && $ID > 0){
      $checklist = FnBuscarChecklist($conmy, $ID, $CLIID);
      if($checklist){
        $isAuthorized = true;
        $claseHabilitado = "btn-outline-primary";
        $atributoHabilitado = ""; 
        $observaciones = FnBuscarObservaciones($conmy, $ID);
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observación | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/fontawesome-free-5.9.0-web/css/all.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
    <link rel="stylesheet" href="/mycloud/library/gpemsac/css/gpemsac.css"> 
    <link rel="stylesheet" href="/gesman/menu/sidebar.css">
    <style>
      .img-fluid{height:100%;}
      .imagen-observacion{display:grid; display:grid;grid-template-columns:25% 50% 25%; }
      @media(min-width:768px){.imagen-observacion{grid-template-columns:2fr 1.5fr 2fr}}
    </style>
</head>
<body>
  <?php require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/menu/sidebar.php';?>
  <div class="container section-top">
    <div class="row mb-3">
      <div class="col-12 btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-outline-primary fw-bold" onclick="FnListarChecklists(); return false;"><i class="fas fa-list"></i><span class="d-none d-sm-block"> CheckLists</span></button>
        <button type="button" class="btn btn-outline-primary fw-bold <?php echo $claseHabilitado;?> <?php echo $atributoHabilitado;?>" onclick="FnResumenChecklist(); return false;"><i class="fas fa-desktop"></i><span class="d-none d-sm-block"> Resumen</span></button>
      </div>
    </div>
    <div class="row border-bottom mb-3 fs-5">
      <div class="col-12 fw-bold d-flex justify-content-between">
        <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? $_SESSION['CliNombre'] : 'UNKNOWN'; ?></p>
        <input type="hidden" id="txtIdChecklist" value="<?php echo $ID ?>"/>
        <input type="hidden" id="txtIdChecklistObs" value="0"/>
        <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? $checklist->Nombre : 'UNKNOWN'; ?></p>
      </div>
    </div>
    <?php if ($isAuthorized): ?>
    <div class="row">
      <div class="col-12">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
          <ol class="breadcrumb">                        
            <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistDatos.php?id=<?php echo $ID ?>" class="text-decoration-none">DATOS</a></li>
            <li class="breadcrumb-item fw-bold"><a href="/checklist/InsertarChecklist.php?id=<?php echo $ID ?>" class="text-decoration-none">CHECKLIST</a></li>
            <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistActividad.php?id=<?php echo $ID ?>" class="text-decoration-none">ACTIVIDAD</a></li>                        
            <li class="breadcrumb-item active fw-bold" aria-current="page">OBSERVACION</li>
            <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarChecklistValidacion.php?id=<?php echo $ID ?>" class="text-decoration-none">VALIDACION</a></li>
          </ol>
        </nav>
      </div>
    </div>
    <select class="mb-2 border boder-0 p-1 d-none" name="listaDeDispositivos" id="listaDeDispositivos"></select>
    <div class="row bg-light fw-bold mb-2 m-0">
      <div class="col-12">
        <label class="pt-2 pb-2 d-flex justify-content-between align-items-center text-secondary">OBSERVACIONES <i class="fas fa-plus fw-bold" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="left" title="Agregar" onclick="FnModalAgregarObservacion()"></i></label> 
      </div>
    </div>
    <div class="row m-0">
      <?php foreach($observaciones as $observacion): ?>
        <div class="border border-1 mb-2 pt-2 pb-2">
          <div class="row p-1">
            <div class="col-12 col-md-8">
              <div class="d-flex";>
                <i class="fas fa-check text-secondary" style="margin-right:10px; margin-top:4px;"></i>
                <p class="mb-0 text-secondary fw-bold" id="idActividad" style="text-align: justify;"><?php echo $observacion['descripcion'] ?></p>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="d-flex justify-content-md-end input-grop-icons" style="margin-left: 15px">
                <span class="input-group-text bg-white border border-0"><i class="fas fa-edit text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Editar" style="cursor: pointer;" onclick="FnModalModificarObservacion(<?php echo $observacion['id'] ?>)"></i></span>
                <span class="input-group-text bg-white border border-0"><i class="fas fa-trash-alt text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Eliminar" style="cursor: pointer;" onclick="FnModalEliminarObservacion(<?php echo $observacion['id'] ?>)"></i></span>
                <span class="input-group-text bg-white border border-0"><i class="fa fa-paperclip text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Agregar Archivo" style="cursor: pointer;" onclick="FnModalAgregarArchivo(<?php echo ($observacion['id']); ?>)"></i></span>
                <span class="input-group-text bg-white border border-0"><i class="fas fa-camera-retro text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Abrir cámara" onclick="FnAbrirCamara(<?php echo ($observacion['id']); ?>)"></i></span>              
              </div>
            </div>
          </div>
          <?php if($observacion['archivo']):?>
            <div class="p-1 mb-1 mt-1">
              <div class="imagen-observacion mt-2">
                <div class="card p-0" style="grid-column:2/3">
                  <div class="card-header p-0 bg-light text-center">Imagen</div>
                  <img src="/mycloud/gesman/files/<?php echo ($observacion['archivo']); ?>" class="img-fluid" alt="">
                  <div class="card-footer p-0 text-center">
                    <button class="p-0 bg-light text-secondary w-100 text-center border border-0" onclick="FnEliminarArchivo(<?php echo $observacion['id']?>)">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endif ?>
        </div>
      <?php endforeach ?>
    </div>
    <?php endif ?>

    <!-- MODAL CÁMARA -->
    <div class="modal" id="modalMostrarCamara">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title fs-5 fw-bold" id="modalAgregarArchivoLabel">TOMAR FOTO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-1">
            <div class="row">                      
              <div class="col-12">
                <video muted="muted" id="video" style="display: none;"></video>
                <canvas id="canvas1" style="display: none;"></canvas>
              </div>
            </div>
          </div>
          <div id="msjAgregarArchivo" class="modal-body pt-1"></div>
          <div class="col-12 modal-footer">
            <button type="button" class="btn btn-primary fw-bold pt-2 pb-2 col-12 w-100" onclick="FnAgregarFoto()"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
        </div>
    </div>
    
    <!-- MODAL AGREGAR ARCHIVO -->
    <div class="modal fade" id="modalAgregarArchivo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title fs-5 fw-bold" id="modalAgregarArchivoLabel">AGREGAR ARCHIVO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-1">
            <div class="row">                      
              <div class="col-12">
                <label for="adjuntarImagenInput" class="form-label mb-0">Imagen</label>
                <input id="fileImagen" type="file" accept="image/*" class="form-control mb-2"/>
              </div>
              <div class="col-12 m-0">
                <div class="col-md-12 text-center" id="divImagen"><i class="fas fa-images fs-2"></i></div>
              </div>
            </div>
          </div>
          <div id="msjAgregarArchivo" class="modal-body pt-1"></div>
          <div class="col-12 modal-footer">
            <button type="button" class="btn btn-primary fw-bold pt-2 pb-2 col-12 w-100" onclick="FnAgregarArchivo();"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL AGREGAR OBSERVACION -->
    <div class="modal fade" id="modalAgregarObservacion" tabindex="-1" aria-labelledby="modalObservacionLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title text-uppercase fw-bold" id="modalObservacionLabel">Agregar Observación</h5>
            <button type="btton" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control" id="txtObservacion">
          </div>
          <div id="msjAgregarObservacion" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary fw-bold w-100" id="btnAgregarActividad" onclick="FnAgregarObservacion()"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL EDITAR OBSERVACION -->
    <div class="modal fade" id="modalModificarObservacion" tabindex="-1" aria-labelledby="modalModificarObservacionLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title text-uppercase fw-bold" id="modalModificarObservacionLabel">MODIFICAR OBSERVACION</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control" id="txtObservacion2">
          </div>
          <div id="msjAgregarObservacion2" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary fw-bold w-100" id="btnModificarObservacion" onclick="FnModificarObservacion()"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      </div>
    </div>

    <div class="container-loader-full">
      <div class="loader-full"></div>
    </div>
  <script src="/checklist/js/EditarChecklistObservacion.js"></script>
  <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
  <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  <script src="/gesman/menu/sidebar.js"></script>
</body>
</html>