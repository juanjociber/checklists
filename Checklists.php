<?php 
  session_start();
  if(!isset($_SESSION['UserName']) || !isset($_SESSION['CliId'])){
    header("location:/gesman");
    exit();
  }
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once $_SERVER['DOCUMENT_ROOT']."/checklists/datos/CheckListData.php"; 
  
  $CLIID = $_SESSION['CliId'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CheckLists | GPEM S.A.C</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/fontawesome-free-5.9.0-web/css/all.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select2-4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="/mycloud/library/gpemsac/css/gpemsac.css"> 
    <link rel="stylesheet" href="/gesman/menu/sidebar.css">
    <style>
      a.link-colecciones {
        color: black;
        text-decoration: none;
      }
      .divselect {
        cursor: pointer;
        transition: all .25s ease-in-out;
      }
      .divselect:hover {
        background-color: #ccd1d1;
        transition: background-color .5s;
      }
      .select2-selection__rendered {
        line-height: 36px !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
      }
      .select2-search__field{
        border: 1px solid #ced4da !important;
        height: 37px !important;
      }
      .select2-search__field:focus{
        color: #212529;
        background-color: #fff !important;
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25) !important;
      }
      .select2-container .select2-selection--single {
        height: 37px !important;
        border: 1px solid #ced4da !important;
      }
      .select2-selection__arrow {
        display: none !important;
      }
    </style>
</head>
<body>
  <?php require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/menu/sidebar.php';?>
  <div class="container section-top">
    <div class="row border-bottom mb-3 fs-5">
      <div class="col-12 fw-bold d-flex justify-content-between">
        <p class="m-0 p-0 text-secondary"><?php echo $_SESSION['CliNombre'];?></p>
        <span class="fas fa-cog text-secondary" style="font-size: 28px; cursor:pointer" onclick="FnAgregarPlantilla()"></span>
      </div>
    </div>
    <div class="row mb-1">
      <div class="col-6 col-sm-3 mb-2">
        <p class="m-0" style="font-size:12px;">Checklist</p>
        <input type="text" class="form-control" id="txtChecklist">
      </div>
      <div class="col-6 col-sm-3 mb-2">
        <p class="m-0" style="font-size:12px;">Equipo</p>
        <select class="js-example-responsive" name="cbActivo1" id="cbActivo1" style="width: 100%"></select>
      </div>
      <div class="col-6 col-sm-3 mb-3">
        <p class="m-0" style="font-size:12px;">Fecha Inicial</p>
        <input type="date" class="form-control" id="dtpFechaInicial" value="<?php echo date('Y-m-d');?>"/>
      </div>
      <div class="col-6 col-sm-3 mb-3">
        <p class="m-0" style="font-size:12px;">Fecha Final</p>
        <input type="date" class="form-control" id="dtpFechaFinal" value="<?php echo date('Y-m-d');?>"/>
      </div>
      <div class="col-12 mb-2">
        <button type="button" class="btn btn-outline-primary w-100 form-control" onclick="FnBuscarChecklists(); return false;"><i class="fas fa-search"></i> Buscar</button>
      </div>  
    </div>  
    <div class="row" id="tblChecklists">
      <div class="col-12">
        <p class="fst-italic">Haga clic en el botón Buscar para obtener resultados.</p>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-12 font-weight-bold d-flex justify-content-center mb-3">
        <button type="button" id="btnPrimero" class="btn btn-sm btn-outline-primary d-none mx-2" onclick="FnBuscarPrimero(); return false;">PRIMERO</button>
        <button type="button" id="btnSiguiente" class="btn btn-sm btn-outline-primary d-none mx-2" onclick="FnBuscarSiguiente(); return false;">SIGUIENTE</button>
      </div>
    </div>
    <div class="row p-2">            
      <div class="col-12 text-center mb-3 d-none" id="divPaginacion">
        <button type="button" class="btn btn-outline-primary" onclick="fnNuevaPagina(); return false;"><i class="fas fa-chevron-down"></i> Ver mas.. </button>
      </div>
    </div>
  </div>
  <div class="container-loader-full">
    <div class="loader-full"></div>
  </div> 

  <script src="/mycloud/library/jquery-3.5.1/jquery-3.5.1.js"></script>
  <script src="/checklists/js/CheckLists.js"></script>
  <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
  <script src="/mycloud/library/select2-4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="/gesman/menu/sidebar.js"></script>
</body>
</html>