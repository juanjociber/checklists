<?php 
  session_start();
  if(!isset($_SESSION['UserName']) || !isset($_SESSION['CliId'])){
    header("location:/gesman");
    exit();
  }
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListData.php";
  
  $CLIID = $_SESSION['CliId'];
  $ID = empty($_GET['id'])?0:$_GET['id'];
  $Estado=0;
  $checkListActividades = array();
  $observaciones = array();
  $isAuthorized = false;
  $Nombre='UNKNOWN';
  $NUMERO=0;
  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $checklist = FnBuscarCheckList($conmy, $CLIID, $ID);
    if(is_numeric($ID) && $ID > 0){
      if($checklist){
        $isAuthorized = true;
        $Nombre = $checklist->Nombre;
        $Estado = $checklist->Estado;   
        $checkListActividades = FnBuscarCheckListActividades($conmy, $ID);
        $observaciones = FnBuscarCheckListObservaciones($conmy, $ID);
      }
    }
  } catch (PDOException $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  } catch (Exception $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  }

  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";
  if($Estado == 1 || $Estado == 2){
    $claseHabilitado = "btn-outline-primary";
    $atributoHabilitado = "";
  }

?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CheckList | GPEM S.A.C</title>
    <script src="/checklists/js/html2pdf.bundle.min.js"></script>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/fontawesome-free-5.9.0-web/css/all.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
    <link rel="stylesheet" href="/mycloud/library/gpemsac/css/gpemsac.css"> 
    <link rel="stylesheet" href="/gesman/menu/sidebar.css">
    <style>
      .no-pdf { display: block; /* Mantener visible en la web */}
      @media print {
        .no-pdf { display: none !important; /* Ocultar cuando se imprima o genere PDF */}
      }
      .contenedor-respuestas{ display: grid; grid-template-columns: 1fr 1fr 3fr 1fr 1fr; margin-bottom: 10px; padding:5px; }
      .descripcion1{ grid-column: 1 / 4; }
      .verificacion1{ grid-column: 4 / 6; }
      .observacion1{ grid-column: 1 / 6; }
      .archivo1{ grid-column: 3 / 4; place-self:center;}
      @media(min-width:576px){
        .contenedor-respuestas{ grid-template-columns: 1.5fr 1fr 1fr 1fr; }
        .descripcion1{ grid-column: 1/ 3; }
        .verificacion1{ grid-column: 3/ 4; }
        .observacion1{ grid-column: 1/ 4; }
        .archivo1{ grid-column: 4/ 5; grid-row: 1 / 3; place-self:center; }
      }
        @media(min-width:992px){
          .contenedor-respuestas{ grid-template-columns: 1.5fr 1fr 1fr 0.8fr; }
          .descripcion1{ grid-column: 1/ 2; }
          .verificacion1{ grid-column: 2/ 3; }
          .archivo1{ grid-row: 1 / 3; place-self:center; }
        @media(min-width:1200px){
          .descripcion1{ grid-column: 1/ 3; }
          .contenedor-respuestas{ grid-template-columns: 1fr 1fr 0.9fr 1fr 1fr; }
          .verificacion1{ grid-column: 3 / 4; grid-row: 1/ 2; }
          .observacion1{ grid-column: 4/ 6; }
          .archivo1{ grid-column: 3 / 4; grid-row: 2 / 3; place-self:center; }
        }
      }
      .imagen-ajustada {
        width: auto !important;
        height: 200px;
        object-fit: contain; 
      }
    </style>
  </head>
  <body>
    <div class="no-pdf">
      <?php require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/menu/sidebar.php';?>
    </div>
    <div class="container section-top">
      <div class="row mb-3 no-pdf">
        <div class="col-12 btn-group" role="group" aria-label="Basic example">
          <button type="button" class="btn btn-outline-primary fw-bold" onclick="FnListarChecklists(); return false;"><i class="fas fa-list"></i><span class="d-none d-sm-block"> Checklists</span></button>
          <button type="button" class="btn btn-outline-primary fw-bold <?php echo $claseHabilitado;?> <?php echo $atributoHabilitado;?>" onclick="FnEditarChecklist(<?php echo $ID ?>); return false;"><i class="fas fa-edit"></i><span class="d-none d-sm-block"> Editar</span></button>
          <button type="button" class="btn btn-outline-primary fw-bold <?php echo $claseHabilitado;?> <?php echo $atributoHabilitado;?>" onclick="FnModalFinalizarCheckList(); return false;"><i class="fas fa-check-square"></i><span class="d-none d-sm-block"> Finalizar</span></button>
          <button type="button" id="btnCrearPdf" class="btn btn-outline-primary fw-bold <?php echo $claseHabilitado;?> <?php echo $atributoHabilitado;?>"><i class="fas fa-print"></i><span class="d-none d-sm-block"> Imprimir</span></button>
        </div>
      </div>
      <div class="row border-bottom mb-2 fs-5 no-pdf">
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 text-secondary"><?php echo $isAuthorized ? $_SESSION['CliNombre'] : 'UNKNOWN'; ?></p>
          <input type="hidden" id="idCheckList" value="<?php echo $ID;?>">
          <p class="m-0 text-secondary"><?php echo $isAuthorized ? $checklist->Nombre : 'UNKNOWN'; ?></p>
        </div>
      </div>
      <?php if ($isAuthorized): ?>
        <?php $NUMERO+=1; ?>
              <div class="row p-1 mb-2 mt-2">
          <div class="col-12 m-0 border border-1 bg-light" >
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?> - DATOS GENERALES:</p>
          </div>
          <div class="row p-1 m-0">
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary" style="font-size: 15px;">Fecha</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->Fecha ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary" style="font-size: 15px;">Cliente:</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->CliNombre ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary" style="font-size: 15px;">Teléfono:</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo empty($checklist->CliTelefono) ? 'UNKNOWN' : $checklist->CliTelefono; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary" style="font-size: 15px;">Correo:</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo empty($checklist->CliCorreo) ? 'UNKNOWN' : $checklist->CliCorreo; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary" style="font-size: 15px;">Supervisor</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo empty($checklist->Supervisor) ? 'UNKNOWN' : $checklist->Supervisor ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-bold" style="font-size: 12px;">Estado</p>
              <?php
                switch ($checklist->Estado){
                  case 1:
                    echo "<span class='badge bg-danger'>Anulado</span>";
                    break;
                  case 2:
                    echo "<span class='badge bg-primary'>Abierto</span>";
                    break;
                  case 3:
                    echo "<span class='badge bg-success'>Cerrado</span>";
                    break;
                  default:
                    echo "<span class='badge bg-secondary'>Unknown</span>";
                }
              ?>
            </div>
          </div>
        </div>
        <?php $NUMERO+=1; ?>
        <!-- DATOS DEL EQUIPO -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border border-1 bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?> - DATOS DEL EQUIPO:</p>
          </div>
          <div class="row p-1 m-0">
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nombre Equipo</p>
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->EquNombre ?></p>              
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Modelo Equipo</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->EquModelo ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Serie Equipo</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->EquSerie ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Marca Equipo</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->EquMarca ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Kilometraje</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->EquKm ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Horas Motor</p> 
              <p class="m-0 text-secondary fw-bold"><?php echo $checklist->EquHm ?></p>
            </div>
          </div>
        </div>
        <?php $NUMERO+=1; ?>
        <!-- CHECKLIST-->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-2 border border-1 bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?> - CHECKLIST:</p>
          </div>
          <!-- Carrusel para pantallas pequeñas (hasta 767px) -->
          <div id="carouselImages" class="carousel slide d-md-none" data-bs-ride="false" data-bs-interval="false">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="card p-0 h-100">
                  <div class="card-header p-0 bg-transparent text-center">Lado Derecho</div>
                  <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen1) ? '0.jpg' : $checklist->Imagen1 ?>" class="img-fluid imagen-ajustada" alt="">
                  <div class="card-footer p-0 text-center"></div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="card p-0 h-100">
                  <div class="card-header p-0 bg-transparent text-center">Anterior</div>
                  <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen2) ? '0.jpg' : $checklist->Imagen2 ?>" class="img-fluid imagen-ajustada" alt="">
                  <div class="card-footer p-0 text-center"></div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="card p-0 h-100">
                  <div class="card-header p-0 bg-transparent text-center">Lado Izquierdo</div>
                  <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen3) ? '0.jpg' : $checklist->Imagen3 ?>" class="img-fluid imagen-ajustada" alt="">
                  <div class="card-footer p-0 text-center"></div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="card p-0 h-100">
                  <div class="card-header p-0 bg-transparent text-center">Posterior</div>
                  <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen4) ? '0.jpg' : $checklist->Imagen4 ?>" class="img-fluid imagen-ajustada" alt="">
                  <div class="card-footer p-0 text-center"></div>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselImages" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
          <!-- Carrusel para pantallas entre 768px y 1199px -->
          <div id="carouselImagesTablet" class="carousel slide d-none d-md-block d-xl-none" data-bs-ride="false" data-bs-interval="false">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="row">
                  <div class="col-6">
                    <div class="card p-0">
                      <div class="card-header p-0 bg-transparent text-center">Lado Derecho</div>
                      <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen1) ? '0.jpg' : $checklist->Imagen1 ?>" class="img-fluid imagen-ajustada" alt="">
                      <div class="card-footer p-0 text-center"></div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card p-0">
                      <div class="card-header p-0 bg-transparent text-center">Anterior</div>
                      <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen2) ? '0.jpg' : $checklist->Imagen2 ?>" class="img-fluid imagen-ajustada" alt="">
                      <div class="card-footer p-0 text-center"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="row">
                  <div class="col-6">
                    <div class="card p-0">
                      <div class="card-header p-0 bg-transparent text-center">Lado Izquierdo</div>
                      <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen3) ? '0.jpg' : $checklist->Imagen3 ?>" class="img-fluid imagen-ajustada" alt="">
                      <div class="card-footer p-0 text-center"></div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card p-0">
                      <div class="card-header p-0 bg-transparent text-center">Posterior</div>
                      <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen4) ? '0.jpg' : $checklist->Imagen4 ?>" class="img-fluid imagen-ajustada" alt="">
                      <div class="card-footer p-0 text-center"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselImagesTablet" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselImagesTablet" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
          <!-- Mostrar las 4 imágenes sin carrusel para pantallas grandes (1200px o más) -->
          <div class="row d-none d-xl-flex" id="carrusel">
            <div class="col-lg-3">
              <div class="card p-0">
                <div class="card-header p-0 bg-transparent text-center">Lado Derecho</div>
                <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen1) ? '0.jpg' : $checklist->Imagen1 ?>" class="img-fluid imagen-ajustada" alt="">
                <div class="card-footer p-0 text-center"></div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card p-0">
                <div class="card-header p-0 bg-transparent text-center">Anterior</div>
                <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen2) ? '0.jpg' : $checklist->Imagen2 ?>" class="img-fluid imagen-ajustada" alt="">
                <div class="card-footer p-0 text-center"></div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card p-0">
                <div class="card-header p-0 bg-transparent text-center">Lado Izquierdo</div>
                <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen3) ? '0.jpg' : $checklist->Imagen3 ?>" class="img-fluid imagen-ajustada" alt="">
                <div class="card-footer p-0 text-center"></div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card p-0">
                <div class="card-header p-0 bg-transparent text-center">Posterior</div>
                <img src="/mycloud/gesman/files/<?php echo empty($checklist->Imagen4) ? '0.jpg' : $checklist->Imagen4 ?>" class="img-fluid imagen-ajustada" alt="">
                <div class="card-footer p-0 text-center"></div>
              </div>
            </div>
          </div>
        </div>
        <!-- <div style="margin-top:20px"></div> -->
        <div class=" mb-2 mt-3">
          <?php 
            $html = '';
            if (is_array($checkListActividades) && !empty($checkListActividades)) {
              foreach($checkListActividades as $actividad) {
                $html.='
                  <div class="contenedor-respuestas border boder-1">
                    <div class="descripcion1" style="">
                      <label class="text-secondary">Descripción:</label>
                      <p class="mb-0 text-secondary fw-bold">'.$actividad['descripcion'].'</p>
                    </div>
                    <div class="verificacion1">
                      <label class="text-secondary">Verificación:</label>
                      <div class="d-flex align-items-center">
                        <i class="far fa-check-square text-secondary" style="margin-right: 10px"></i>
                        <p class="mb-0 text-secondary fw-bold">'.$actividad['respuesta'].'</p>
                      </div>
                    </div>';
                    if (!empty($actividad['observaciones'])) {
                    $html.='
                    <div class="observacion1">
                      <label class="text-secondary">Observación:</label>
                      <p class="mb-0 text-secondary fw-bold">'.$actividad['observaciones'].'</p>
                    </div>';
                    }
                    if (!empty($actividad['archivo'])) {
                    $html .= '
                    <div class="mt-2" style="grid-column: 1 / 5; place-self: center;">
                      <img src="/mycloud/gesman/files/'.$actividad['archivo'].'" class="img-fluid imagen-ajustada" alt="">
                    </div>';
                    }
                $html.='</div>';
              }
            }
            echo $html;
          ?>
        </div>
        <?php $NUMERO+=1; ?>
        <!-- OBSERVACIONES -->
        <div class="row mb-2 mt-2">
          <div class="col-12 mb-2 border border-1 bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?> - OBSERVACIONES:</p>
          </div>
          <?php 
          $html = '';
          foreach($observaciones as $observacion) {
            $html .= '
              <div class="row mb-2 mt-2">
                <div class="col-12">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex">
                      <i class="fas fa-check text-secondary" style="margin-right:10px; margin-top:8px; font-size:10px;"></i>
                      <p class="mb-0 text-secondary" id="idObservacion" style="text-align: justify;">'.$observacion['descripcion'].'</p>
                    </div>
                  </div>
                </div>
              </div>';
              if ($observacion['archivo']) {
              $html .= '
              <div class="p-1 mb-1 d-flex justify-content-center align-items-center">
                  <img src="/mycloud/gesman/files/'.$observacion['archivo'].'" class="img-fluid imagen-ajustada" alt="">
              </div>';
              }
          }  
          echo $html;
          ?>
        </div>
        <div class="row mb-2 mt-2">
          <div class="col-12 mb-2 border border-1 bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"> V° B°</p>
          </div>
          <div class="col-6">
            <?php if(!empty($checklist->EmpFirma) || !empty($checklist->Supervisor)) : ?>
              <p class="text-center mb-0">Firma de supervisor</p>
              <div id="signatureCanvasSupervisor" class="d-flex justify-content-center align-items-center">
                <img src="/mycloud/gesman/files/<?php echo $checklist->EmpFirma ?>" class="img-fluid imagen-ajustada" alt="">
              </div>
              <p class="text-center mb-0"><?php echo $checklist->Supervisor ?></p>
            <?php endif ?>
          </div>
          <div class="col-6">
            <?php if(!empty($checklist->CliFirma) || !empty($checklist->CliContacto)) : ?>
              <p class="text-center mb-0">Firma de cliente</p>
              <div id="signatureCanvasAprobo" class="d-flex justify-content-center align-items-center">
                <img src="/mycloud/gesman/files/<?php echo $checklist->CliFirma ?>" class="img-fluid imagen-ajustada" alt="">
              </div>
              <p class="text-center mb-0"><?php echo $checklist->CliContacto ?></p>
            <?php endif ?>
          </div>
        </div>
      <?php endif ?>
    </div>
    <!-- MODAL PARA FINALIZAR CHEKCLIST -->
    <div class="modal fade" id="modalFinalizarCheckList" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Finalizar Checklist</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>                
          <div class="modal-body pb-1">
            <div class="row text-center fw-bold pt-3">                        
              <p class="text-center">Para finalizar el Checklist <?php echo $Nombre;?> haga clic en el botón CONFIRMAR.</p>                    
            </div>
          </div>
          <div class="modal-body pt-1" id="msjFinalizarChecklist"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="FnFinalizarCheckList(); return false;">CONFIRMAR</button>
          </div>              
        </div>
      </div>
    </div>
    <div class="container-loader-full">
      <div class="loader-full"></div>
    </div>
    <script src="/checklists/js/CheckList.js"></script>
    <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <script src="/mycloud/library/bootstrap-5-alerta-1.0/js/bootstrap-5-alerta-1.0.js"></script>
    <script src="/gesman/menu/sidebar.js"></script>
  </body>
</html>