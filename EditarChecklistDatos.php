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
  $supervisores = array();
  $isAuthorized = false;
  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";
  
  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(is_numeric($ID) && $ID > 0){
      $checklist = FnBuscarChecklist($conmy, $CLIID, $ID);
      if($checklist){
        $isAuthorized = true;
        $claseHabilitado = "btn-outline-primary";
        $atributoHabilitado = ""; 
        $supervisores = FnBuscarSupervisores($conmy);
      }
    }
  } catch (PDOException $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  } catch (Exception $e) {
      $errorMessage = $e->getMessage();
      $conmy = null;
  }

  // VERIFICANDO SI SUPERVISOR PERTENECE AL CLIENTE
  $supervisorValido = false;
  foreach ($supervisores as $supervisor) {
    if ($supervisor['supervisor'] == $checklist->Supervisor) {
      $supervisorValido = true;
      break;
    }
  }
  $supervisorInputValue = $supervisorValido ? $checklist->Supervisor : '';

  
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
    @media(min-width:768px){.contenedor-imagen{display:grid;grid-template-columns:1fr 1fr !important;gap:10px;}}
    @media(min-width:1200px){.contenedor-imagen{grid-template-columns:1fr 1fr 1fr 1fr !important;}}
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
          <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? $checklist->Nombre : 'UNKNOWN'; ?></p>
        </div>
      </div>
      <?php if ($isAuthorized): ?>
      <div class="row">
        <div class="col-12">
          <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb">                        
              <li class="breadcrumb-item active fw-bold" aria-current="page">DATOS</li>
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckList.php?id=<?php echo $ID ?>" class="text-decoration-none">CHECKLIST</a></li>
              <!-- <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListActividad.php?id=<?php echo $ID ?>" class="text-decoration-none">ACTIVIDAD</a></li> -->
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListObservacion.php?id=<?php echo $ID ?>" class="text-decoration-none">OBSERVACION</a></li>
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListValidacion.php?id=<?php echo $ID ?>" class="text-decoration-none">VALIDACION</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <!-- CABECERA -->
      <div class="row">
        <!-- FECHA -->
        <div class="col-12 col-md-4 mt-2">
          <label for="dtpFecha" class="form-label mb-0">Fecha:</label>
          <input type="date" class="form-control text-secondary text-uppercase fw-bold" id="dtpFecha" value="<?php echo $checklist->Fecha ?>">
        </div>
        <!-- CLIENTE -->
        <div class="col-6 col-md-4 mt-2">
          <label for="txtCliente" class="form-label mb-0">Cliente:</label>
          <input type="text" class="form-control text-secondary fw-bold" id="txtCliente" value="<?php echo $checklist->CliNombre ?>" disabled>
        </div>
        <div class="col-6 col-md-4 mt-2">
          <label for="txtCliente" class="form-label mb-0">RUC:</label>
          <input type="text" class="form-control text-secondary fw-bold" id="txtRuc" value="<?php echo $checklist->CliRuc ?>" disabled>
        </div>
        <div class="col-12 col-md-6 mt-2">
          <label class="form-label mb-0">Contacto:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtContacto" value="<?php echo $checklist->CliContacto ?>"></input>
        </div>
        <!-- SUPERVISOR -->
        <div class="custom-select-container col-12 col-md-6 mt-2">
          <label for="txtSupervisor" class="form-label mb-0">Supervisor:</label>
          <div class="custom-select-wrapper">
            <input type="text" class="custom-select-input text-secondary fw-bold" id="txtSupervisor" value="<?php echo  $checklist->Supervisor;?>"/>
            <span class="custom-select-arrow"><i class="bi bi-chevron-down"></i></span>
            <div id="supervisorList" class="custom-select-list">
              <!-- SUPERVISORES -->
              <?php foreach ($supervisores as $supervisor): ?>
                <div class="custom-select-item" data-value="<?php echo ($supervisor['idsupervisor']); ?>">
                  <?php echo ($supervisor['supervisor']); ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Nombre Equipo:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquNombre" value="<?php echo $checklist->EquNombre ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Marca:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquMarca" value="<?php echo $checklist->EquMarca ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Modelo:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquModelo" value="<?php echo $checklist->EquModelo ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Placa:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquPlaca" value="<?php echo $checklist->EquPlaca ?>"></input>
        </div>
        <div class="col-6 col-md-6 mt-2">
          <label class="form-label mb-0">Serie:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquSerie" value="<?php echo $checklist->EquSerie ?>"></input>
        </div>
        <div class="col-6 col-md-6 mt-2">
          <label class="form-label mb-0">Motor:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquMotor" value="<?php echo $checklist->EquMotor ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Transmisión:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquTransmision" value="<?php echo $checklist->EquTransmision ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Diferencial:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquDiferencial" value="<?php echo $checklist->EquDiferencial ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Kilometraje:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquKm" value="<?php echo $checklist->EquKm ?>"></input>
        </div>
        <div class="col-6 col-md-3 mt-2">
          <label class="form-label mb-0">Horas de motor:</label>
          <input type="text" class="form-control text-secondary fw-bold" style="font-size:15px" id="txtEquHm" value="<?php echo $checklist->EquHm ?>"></input>
        </div>
      </div>
      <!-- BOTON GUARDAR -->
      <div class="row mt-2">
        <div class="col-12 mt-2">
          <button class="btn btn-outline-primary pt-2 pb-2 mb-2 col-12 fw-bold" onclick="FnModificarChecklist()"><i class="fas fa-save"></i> GUARDAR</button>
        </div>
      </div>
      <?php endif ?>
    </div>

    <div class="container-loader-full">
      <div class="loader-full"></div>
    </div>

    <script src="/checklist/js/EditarCheckListDatos.js"></script>
    <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <script src="/gesman/menu/sidebar.js"></script>
  </body>
</html>