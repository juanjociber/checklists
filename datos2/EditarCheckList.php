<?php
    session_start();

    if(empty($_SESSION['UserName']) || empty($_SESSION['CliId']) || empty($_SESSION['CliNombre'])){
        header("location:/gesman/Salir.php");
        exit();
    }

    $ID=empty($_GET['id']) ? 0 : $_GET['id'];
    $DATOS=array();

    require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php';
    require_once $_SERVER['DOCUMENT_ROOT']."/checklists/data/CheckListsData.php";

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Trabajo | GPEM SAC.</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/fontawesome-free-5.9.0-web/css/all.css">
    <link rel="stylesheet" href="/mycloud/library/gpemsac/css/gpemsac.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.0.2-dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container section-top">

        <div class="row border-bottom mb-3 fs-5">
            <div class="col-12 fw-bold" style="display:flex; justify-content:space-between;">
                <p class="m-0 p-0"><?php echo $_SESSION['CliNombre'];?></p>
                <input type="hidden" id="txtId" value="<?php echo $ID;?>" readonly/>
                <p class="m-0 p-0 text-center text-secondary"><?php echo empty($CHECKLIST['nombre'])?null:$CHECKLIST['nombre'];?></p>
            </div>
        </div>

        <div class="row mb-1 p-1">
            <?php 
                /*echo 'checklist</br>';
                print_r($CHECKLIST);
                echo '</br>checklist preguntas</br>';
                print_r($CHK_PREGUNTAS);
                echo '</br>plantilla preguntas</br>';
                print_r($PLA_PREGUNTAS);
                echo '</br>pnatilla alternativas</br>';
                print_r($PLA_ALTERNATIVAS);
                echo '</br>DATOS</br>';*/
                echo '<pre>';
                print_r($DATOS);
                echo '</pre>';
                echo '</br>';
                foreach($DATOS as $key=>$valor){
                    //echo 'preid:'.$key.'[id:'.$valor['id'].', preid:'.$valor['preid'].', pregunta:'.$valor['pregunta'].']</br>'; 
                    echo '<div class="col-12 mb-2">';                   
                    echo '<p class="m-0 fw-bold">'.$valor['pregunta'].'</p>';
                    foreach($valor['alternativas'] as $key2=>$valor2){
                        //echo 'id:'.$valor2['id'].', estado:'.$valor2['estado'].', '.$key2.'</br>';
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
           
    </div>

    <script src="/mycloud/library/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/bootstrap-5-alerta-1.0/js/bootstrap-5-alerta-1.0.js"></script>
</body>
</html>