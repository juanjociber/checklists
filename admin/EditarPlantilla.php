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
  $CLIID = $_SESSION['gesman']['CliId'];

  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/PlantillaData.php";

  $plantilla= new stdClass();
  $plantillaPreguntas =array();
  $datos =array();
  $PLAID = empty($_GET['id'])?0:$_GET['id'];
  $isAuthorized = false;
  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(is_numeric($PLAID) && $PLAID > 0){
      $plantilla = FnBuscarPlantilla($conmy, $PLAID);
      if ($plantilla) {
        $isAuthorized = true;
        $plantillaPreguntas = FnBuscarPlantillaPreguntas($conmy, $PLAID);
        if(count($plantillaPreguntas) > 0){
          $ids = array_map(function($elemento) {
            return $elemento['id'];
          }, $plantillaPreguntas);
          $alternativas = FnBuscarAlternativas($conmy, $ids);
          foreach($alternativas as $alternativa){
            $datos[$alternativa['preid']][]=$alternativa;
          }
        }
      }
    }
    $conmy = null;
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
  <title>Plantilla | GPEM S.A.C</title>
  <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
  <link rel="stylesheet" href="/mycloud/library/fontawesome-free-5.9.0-web/css/all.css">
  <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
  <link rel="stylesheet" href="/mycloud/library/bootstrap-5.0.2-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
  <link rel="stylesheet" href="/mycloud/library/gpemsac/css/gpemsac.css"> 
  <link rel="stylesheet" href="/gesman/menu/sidebar.css">
  <style>
    .modal-fullscreen { max-width: 100%; width: 100%; height: 100%; margin: 0; }
    .canvas-dibujo { border: 1px solid #ccc; background-color: #fff; }
    @media(min-width:768px){ .contenedor-imagen{ display: grid; grid-template-columns:1fr 1fr; gap:10px; } .grid{display: grid; grid-template-columns:1fr 1fr; gap:10px; }}
    @media(min-width:992px){ .contenedor-imagen{ grid-template-columns:1fr 1fr 1fr 1fr; }}
    @media(min-width:1200px){ .grid{ grid-template-columns:1fr 1fr 1fr 1fr; }}
    @media(max-width:576px){ .actividad-flex{ display: flex !important; flex-direction: column; } .input-grop-icons{ padding-left: 15px; }}
    @media(min-width:577px){ .actividad-flex{ align-items: center; justify-content: space-between; }}
    .imagen-ajustada {
      width: auto !important;
      height: 200px;
      object-fit: contain; 
    }
  </style>
</head>
<body>
  <?php require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/menu/sidebar.php';?>
  <div class="container section-top">
    <input type="hidden" id="txtIdPlantilla" value="<?php echo $PLAID?>">
    <input type="hidden" id="txtIdActividad" value="0">
    <input type="hidden" id="siluetaId" value="0">

    <div class="row mb-3">
      <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-outline-primary fw-bold" onclick="FnListarPlantillas(); return false;"><i class="fas fa-list"></i><span class="d-none d-sm-block"> Plantillas</span></button>
      </div>
    </div>
    <div class="row border-bottom mb-3 fs-5">
      <div class="col-12 fw-bold d-flex justify-content-between">
        <p class="m-0 p-0 text-secondary text-uppercase">CONFIGURACIÓN DE PLANTILLA</p>
        <p class="m-0 p-0 text-secondary text-uppercase"><?php echo $plantilla->Tipo ?></p>
      </div>
    </div>

    <?php if ($isAuthorized): ?>
    <input type="hidden" id="txtNumImagen" value="">
    <!-- SILUETAS -->
    <!-- Carrusel para pantallas menores a 768px (móviles) -->
    <div id="carouselSiluetas" class="carousel slide d-md-none" data-bs-ride="false" data-bs-interval="false">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="card p-0" style="position:relative;">
            <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(1)">
              <span style="margin-right:10px">LADO DERECHO</span><i class="fa fa-paperclip fw-bold"></i>
            </div>
            <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen1) ? '0.jpg' : $plantilla->Imagen1 ?>" class="img-fluid imagen-ajustada">
            <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
              <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,1)" <?php echo empty($plantilla->Imagen1) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="card p-0" style="position:relative;">
            <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(2)">
              <span style="margin-right:10px">LADO ANTERIOR</span><i class="fa fa-paperclip fw-bold"></i>
            </div>
            <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen2) ? '0.jpg' : $plantilla->Imagen2 ?>" class="img-fluid imagen-ajustada">
            <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
              <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,2)" <?php echo empty($plantilla->Imagen2) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="card p-0" style="position:relative;">
            <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(3)">
              <span style="margin-right:10px">LADO IZQUIERDO</span><i class="fa fa-paperclip fw-bold"></i>
            </div>
            <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen3) ? '0.jpg' : $plantilla->Imagen3 ?>" class="img-fluid imagen-ajustada">
            <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
              <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,3)" <?php echo empty($plantilla->Imagen3) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="card p-0" style="position:relative;">
            <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(4)">
              <span style="margin-right:10px">LADO POSTERIOR</span><i class="fa fa-paperclip fw-bold"></i>
            </div>
            <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen4) ? '0.jpg' : $plantilla->Imagen4 ?>" class="img-fluid imagen-ajustada">
            <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
              <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,4)" <?php echo empty($plantilla->Imagen4) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Botones para navegar -->
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselSiluetas" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselSiluetas" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
    <!-- Carrusel para pantallas entre 768px y 1199px (tabletas y pequeñas pantallas de PC) -->
    <div id="carouselSiluetasTablet" class="carousel slide d-none d-md-block d-xl-none" data-bs-ride="false" data-bs-interval="false">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="row">
            <div class="col-6">
              <div class="card p-0" style="position:relative;">
                <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(1)">
                  <span style="margin-right:10px">LADO DERECHO</span><i class="fa fa-paperclip fw-bold"></i>
                </div>
                <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen1) ? '0.jpg' : $plantilla->Imagen1 ?>" class="img-fluid imagen-ajustada">
                <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
                  <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,1)" <?php echo empty($plantilla->Imagen1) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card p-0" style="position:relative;">
                <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(2)">
                  <span style="margin-right:10px">LADO ANTERIOR</span><i class="fa fa-paperclip fw-bold"></i>
                </div>
                <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen2) ? '0.jpg' : $plantilla->Imagen2 ?>" class="img-fluid imagen-ajustada">
                <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
                  <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,2)" <?php echo empty($plantilla->Imagen2) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="row">
            <div class="col-6">
              <div class="card p-0" style="position:relative;">
                <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(3)">
                  <span style="margin-right:10px">LADO IZQUIERDO</span><i class="fa fa-paperclip fw-bold"></i>
                </div>
                <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen3) ? '0.jpg' : $plantilla->Imagen3 ?>" class="img-fluid imagen-ajustada">
                <div class="card-footer p-0 bg-transparent">
                  <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,3)" <?php echo empty($plantilla->Imagen3) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card p-0" style="position:relative;">
                <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-center align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(4)">
                  <span style="margin-right:10px">LADO POSTERIOR</span><i class="fa fa-paperclip fw-bold"></i>
                </div>
                <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen4) ? '0.jpg' : $plantilla->Imagen4 ?>" class="img-fluid imagen-ajustada">
                <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
                  <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,4)" <?php echo empty($plantilla->Imagen4) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Botones para navegar -->
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselSiluetasTablet" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselSiluetasTablet" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
      </button>
    </div>

    <!-- Mostrar las 4 imágenes sin carrusel para pantallas grandes (1200px o más) -->
    <div class="row d-none d-xl-flex">
      <div class="col-lg-3">
        <div class="card p-0" style="position:relative;">
          <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-between align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(1)">
            <span style="margin-right:10px">LADO DERECHO</span><i class="fa fa-paperclip fw-bold"></i>
          </div>
          <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen1) ? '0.jpg' : $plantilla->Imagen1 ?>" class="img-fluid imagen-ajustada">
          <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
            <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,1)" <?php echo empty($plantilla->Imagen1) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
          </div>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="card p-0" style="position:relative;">
          <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-between align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(2)">
            <span style="margin-right:10px">LADO ANTERIOR</span><i class="fa fa-paperclip fw-bold"></i>
          </div>
          <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen2) ? '0.jpg' : $plantilla->Imagen2 ?>" class="img-fluid imagen-ajustada">
          <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
            <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,2)" <?php echo empty($plantilla->Imagen2) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
          </div>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="card p-0" style="position:relative;">
          <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-between align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(3)">
            <span style="margin-right:10px">LADO IZQUIERDO</span><i class="fa fa-paperclip fw-bold"></i>
          </div>
          <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen3) ? '0.jpg' : $plantilla->Imagen3 ?>" class="img-fluid imagen-ajustada">
          <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
            <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,3)" <?php echo empty($plantilla->Imagen3) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
          </div>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="card p-0" style="position:relative;">
          <div class="p-2 bg-primary text-white fw-bold d-flex justify-content-between align-items-center" style="cursor: pointer;" onclick="FnModalAgregarPlantillaImagen(4)">
            <span style="margin-right:10px">LADO POSTERIOR</span><i class="fa fa-paperclip fw-bold"></i>
          </div>
          <img src="/mycloud/gesman/files/<?php echo empty($plantilla->Imagen4) ? '0.jpg' : $plantilla->Imagen4 ?>" class="img-fluid imagen-ajustada">
          <div class="card-footer p-0 bg-transparent" style="position:absolute; top:40px; left:10px; z-index:2">
           <button onclick="FnEliminarPlantillaImagen(<?php echo $PLAID?>,4)" <?php echo empty($plantilla->Imagen4) ? 'disabled' : 'enabled' ?> style="font-size:30px; color:tomato; border:unset; background-color:transparent;">&#x2715</button>
          </div>
        </div>
      </div>
    </div>

    <!-- PREGUNTAS -->
    <div class="row">
      <div class="col-12 mt-4 mb-2">
        <label class="p-2 bg-light text-secondary fw-bold d-flex justify-content-between align-items-center">PLANTILLA DE PREGUNTAS Y ALTERNATIVAS <i class="fas fa-plus fw-bold" style="cursor: pointer; margin-left:10px;" onclick="FnModalAgregarPlantillaPregunta()"></i></label> 
      </div>
      <?php foreach($plantillaPreguntas as $plantillaPregunta): ?>
        <div class="col-12 mb-3">
          <div class="p-1 border border-opacity-50">
            <div class="d-flex actividad-flex">
              <div class="d-flex">
                <i class="fas fa-check text-secondary" style="margin-right:10px; margin-top:4px;"></i>
                <p class="mb-0 text-secondary fw-bold" style="text-align: justify;"><?php echo $plantillaPregunta['descripcion'] ?></p>
              </div>
              <div class="d-flex input-grop-icons">
                <span class="input-group-text bg-white text-secondary border border-0"><i class="fas fa-edit text-secondary" style="cursor: pointer;" onclick="FnModalModificarPlantillaPregunta(<?php echo $plantillaPregunta['id'] ?>)"></i></span>
                <span class="input-group-text bg-white text-secondary border border-0 fw-bold" style="cursor: pointer;" onclick="FnModalAgregarAlternativa(<?php echo $plantillaPregunta['id'] ?>)"><i class="fas fa-plus text-secondary"></i> Alternativa</span>
                <span class="input-group-text bg-white text-secondary border border-0"><i class="fas fa-trash-alt text-secondary" style="cursor: pointer;" onclick="FnModalEliminarPlantillaPregunta(<?php echo $plantillaPregunta['id'] ?>)"></i></span>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-start mt-2" style="gap:10px; flex-wrap:wrap;" id="contenedorAlternativas">
            <?php if(!empty($datos[$plantillaPregunta['id']])): ?>
              <?php foreach($datos[$plantillaPregunta['id']] as $dato): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="radio" id="<?php echo $dato['id']?>">
                  <label class="form-check-label text-uppercase" for="<?php echo $dato['id']?>"><?php echo $dato['descripcion']?></label>
                </div>
                <div>
                  <small class="input-group-text bg-white text-secondary border border-0" style="padding-left: 0; margin-top: -1.5px;"><i class="fas fa-trash-alt text-secondary fw-light" style="cursor: pointer;" onclick="FnEliminarAlternativa(<?php echo $dato['id'] ?>)"></i></small>
                </div>
              <?php endforeach ?>
            <?php endif ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
    <?php endif ?>

    <!-- MODAL AGREGAR ACTIVIDAD -->
    <div class="modal fade" id="modalAgregarPlantillaPregunta" tabindex="-1" aria-labelledby="modalActividadLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title text-uppercase fw-bold" id="modalActividadLabel">AGREGAR PREGUNTA</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control" id="txtDescripcion">
          </div>
          <div id="msjAgregarActividad" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary fw-bold w-100" id="btnAgregarActividad" onclick="FnAgregarPlantillaPregunta()"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL EDITAR ACTIVIDAD -->
    <div class="modal fade" id="modalModificarPlantillaPregunta" tabindex="-1" aria-labelledby="modalModificarActividadLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title text-uppercase fw-bold" id="modalModificarActividadLabel">MODIFICAR ACTIVIDAD</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control" id="txtDescripcion2" value="">
          </div>
          <div id="msjModificarActividad" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary fw-bold w-100" id="btnModificarActividad" onclick="FnModificarPlantillaPregunta()"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL AGREGAR SILUETA -->
    <div class="modal fade" id="modalAgregarSilueta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title fs-5 fw-bold" id="modalAgregarSiluetaLabel">AGREGAR PLANTILLA</h5>
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
          <div id="msjAgregarImagen" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary fw-bold pt-2 pb-2 w-100" id="btnAgregarSilueta" onclick="FnAgregarPlantillaImagen();"><i class="fas fa-save"></i> GUARDAR</button>
          </div>
        </div>
      </div>
    </div>
    <!-- MODAL AGREGAR ALTERNATIVA -->
    <div class="modal fade" id="modalAgregarAlternativa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header text-secondary">
            <h5 class="modal-title fs-5 fw-bold" id="modalAgregarAlternativaLabel">AGREGAR ALTERNATIVA</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-1">
            <div class="row">                      
              <div class="col-12">
                <input class="form-control" type="text" id="txtAlternativa">
              </div>
            </div>
          </div>
          <div id="msjAgregarAlternativa" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <div class="col-12">
              <button type="button" class="btn btn-primary fw-bold pt-2 pb-2 w-100" onclick="FnAgregarAlternativa();"><i class="fas fa-save"></i> GUARDAR</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container-loader-full">
    <div class="loader-full"></div>
  </div>
  <script src="/checklists/js/EditarPlantilla.js"></script>
  <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
  <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  <script src="/gesman/menu/sidebar.js"></script>
</body>
</html>