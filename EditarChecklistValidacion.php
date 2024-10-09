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
  $isAuthorized = false;
  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(is_numeric($ID) && $ID > 0){
      $checklist = FnBuscarCheckList($conmy, $CLIID, $ID);
      if($checklist->Id){
        $isAuthorized = true;
        $claseHabilitado = "btn-outline-primary";
        $atributoHabilitado = ""; 
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
      .grid{display:grid;gap:20px}
      @media(min-width:768px){.grid{ grid-template-columns:1fr 1fr !important; }}
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
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListDatos.php?id=<?php echo $ID ?>" class="text-decoration-none">DATOS</a></li>
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckList.php?id=<?php echo $ID ?>" class="text-decoration-none">CHECKLIST</a></li>
              <!-- <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListActividad.php?id=<?php echo $ID ?>" class="text-decoration-none">ACTIVIDAD</a></li> -->
              <li class="breadcrumb-item fw-bold"><a href="/checklist/EditarCheckListObservacion.php?id=<?php echo $ID ?>" class="text-decoration-none">OBSERVACION</a></li>                        
              <li class="breadcrumb-item active fw-bold" aria-current="page">VALIDACION</li>
            </ol>
          </nav>
        </div>
      </div>

      <div class="border border-1" style="padding:10px">
        <div class="row fw-bold mb-2">
          <div class="col-12">
            <label class=" d-flex justify-content-between align-items-center text-secondary">PARTICIPANTES DE CHECKLIST :</label> 
          </div>
        </div>
        <div class="grid mb-4">
          <input type="hidden" id="txtIdChecklist" value="">
          <div class="card p-0">
            <div class="card-header bg-light text-center text-secondary">AGREGAR FIRMA DE SUPERVISOR</div>
            <canvas id="canvasEmpFirma" class="border border-1 w-100"></canvas>
            <div class="card-footer p-0 bg-transparent" style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
              <button class="w-100 p-2 border border-1 bg-light text-secondary" onclick="FnEliminarFirma(1)"><i class="fas fa-trash-alt"></i> Limpiar</button>
              <button class="w-100 p-2 border border-1 text-secondary fw-bold" onclick="FnAgregarFirma('emp')"><i class="fas fa-save"></i> Guardar</button>
            </div>
          </div>
          <div class="card p-0">
            <div class="card-header bg-light text-center text-secondary">AGREGAR FIRMA DE CLIENTE</div>
            <canvas id="canvasCliFirma" class="border border-1 w-100"></canvas>
            <div class="card-footer p-0 bg-transparent" style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
              <button class="w-100 p-2 border border-1 bg-light text-secondary" onclick="FnEliminarFirma(2)"><i class="fas fa-trash-alt"></i> Limpiar</button>
              <button class="w-100 p-2 border border-1 text-secondary fw-bold" onclick="FnAgregarFirma('cli')"><i class="fas fa-save"></i> Guardar</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif ?>
    
    <!-- <div class="container-loader-full">
      <div class="loader-full"></div>
    </div> -->
  <script src="/checklist/js/EditarCheckListValidacion.js"></script>
  <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
  <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  <script src="/gesman/menu/sidebar.js"></script>
</body>
</html>