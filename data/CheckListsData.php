<?php
    /* ================================
     TABLA: tblcheckLists 
     ================================*/
  function FnRegistrarCheckList($conmy, $checklist) {
    try {
        $stmt = $conmy->prepare("CALL spman_agregarchecklist(:_cliid, :_solid, :_plaid, :_equid, :_fecha, :_cliruc, :_clinombre, :_clidireccion, :_clicontacto, :_clitelefono, 
        :_clicorreo, :_supervisor, :_equcodigo, :_equnombre, :_equmarca, :_equmodelo, :_equplaca, :_equserie, :_equmotor, :_equtransmision, :_equdiferencial, 
        :_equkm, :_equhm, :_usuario, @_id)");
        $stmt->bindParam(':_cliid', $checklist['cliid'], PDO::PARAM_INT);
        $stmt->bindParam(':_solid', $checklist['solid'], PDO::PARAM_INT);
        $stmt->bindParam(':_plaid', $checklist['plaid'], PDO::PARAM_INT);
        $stmt->bindParam(':_equid', $checklist['equid'], PDO::PARAM_INT);
        $stmt->bindParam(':_fecha', $checklist['fecha'], PDO::PARAM_STR);
        $stmt->bindParam(':_cliruc', $checklist['cliruc'], PDO::PARAM_STR);
        $stmt->bindParam(':_clinombre', $checklist['clinombre'], PDO::PARAM_STR);
        $stmt->bindParam(':_clidireccion', $checklist['clidireccion'], PDO::PARAM_STR);
        $stmt->bindParam(':_clicontacto', $checklist['clicontacto'], PDO::PARAM_STR);
        $stmt->bindParam(':_clitelefono', $checklist['clitelefono'], PDO::PARAM_STR);
        $stmt->bindParam(':_clicorreo', $checklist['clicorreo'], PDO::PARAM_STR);
        $stmt->bindParam(':_supervisor', $checklist['supervisor'], PDO::PARAM_STR);
        $stmt->bindParam(':_equcodigo', $checklist['equcodigo'], PDO::PARAM_STR);
        $stmt->bindParam(':_equnombre', $checklist['equnombre'], PDO::PARAM_STR);
        $stmt->bindParam(':_equmarca', $checklist['equmarca'], PDO::PARAM_STR);
        $stmt->bindParam(':_equmodelo', $checklist['equmodelo'], PDO::PARAM_STR);
        $stmt->bindParam(':_equplaca', $checklist['equplaca'], PDO::PARAM_STR);
        $stmt->bindParam(':_equserie', $checklist['equserie'], PDO::PARAM_STR);
        $stmt->bindParam(':_equmotor', $checklist['equmotor'], PDO::PARAM_STR);
        $stmt->bindParam(':_equtransmision', $checklist['equtransmision'], PDO::PARAM_STR);
        $stmt->bindParam(':_equdiferencial', $checklist['equdiferencial'], PDO::PARAM_STR);
        $stmt->bindParam(':_equkm', $checklist['equkm'], PDO::PARAM_INT);
        $stmt->bindParam(':_equhm', $checklist['equhm'], PDO::PARAM_INT);
        $stmt->bindParam(':_usuario', $checklist['usuario'], PDO::PARAM_STR);
        $stmt->execute();

        $stmt = $conmy->query("SELECT @_id as id");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['id'];
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());//sera propagado al catch(Exception $ex) del nivel superior.
    }
  }

  function FnBuscarCheckLists($conmy, $checklist) {
    try {
      $checklists=array('data'=>array(), 'pag'=>0);
      $query = "";

      if(!empty($checklist->Nombre)){
        $query = " and nombre like '%".$checklist->Nombre."%'";
      }else{
        if(!empty($checklist->Equipo)){$query .=" and equid=".$checklist->Equipo;}
        $query.=" and fecha between '".$checklist->FechaInicial."' and '".$checklist->FechaFinal."'";
      }
      $query.=" limit ".$checklist->Pagina.", 15";
      $stmt = $conmy->prepare("select id, nombre, fecha, cli_nombre, cli_contacto, equ_nombre, estado from tblchecklists where cliid=:CliId".$query.";");
      $stmt->bindParam(':CliId', $checklist->CliId, PDO::PARAM_INT);
      $stmt->execute();
      $n=$stmt->rowCount();
      if($n>0){
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $checklists['data'][]=array(
            'id'=>(int)$row['id'],
            'nombre'=>$row['nombre'],
            'fecha'=>$row['fecha'],
            'clinombre'=>$row['cli_nombre'],
            'clicontacto'=>$row['cli_contacto'],
            'equnombre'=>$row['equ_nombre'],
            'estado'=>(int)$row['estado']
          );
        }
        $checklists['pag']=$n;
      }            
      return $checklists;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnBuscarCheckList($conmy, $cliid, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, cliid, solid, plaid, equid, fecha, numero, nombre, cli_ruc, cli_nombre, cli_direccion, cli_contacto, cli_telefono, cli_correo, supervisor, equ_codigo, equ_nombre, equ_marca, equ_modelo, equ_placa, equ_serie, equ_motor, equ_transmision, equ_diferencial, equ_km, equ_hm, imagen1, imagen2, imagen3, imagen4, emp_firma, cli_firma, estado FROM tblchecklists WHERE id = :Id AND cliid = :Cliid");
      $stmt->execute(array(':Id' => $id, ':Cliid' => $cliid));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
        $checklist = new stdClass();
        $checklist->Id = $row['id'];
        $checklist->CliId = $row['cliid'];
        $checklist->SolId = $row['solid'];
        $checklist->PlaId = $row['plaid'];
        $checklist->EquId = $row['equid'];
        $checklist->Fecha = $row['fecha'];
        $checklist->Numero = $row['numero'];
        $checklist->Nombre = $row['nombre'];
        $checklist->CliRuc = $row['cli_ruc'];
        $checklist->CliNombre = $row['cli_nombre'];
        $checklist->CliDireccion = $row['cli_direccion'];
        $checklist->CliContacto = $row['cli_contacto'];
        $checklist->CliTelefono = $row['cli_telefono'];
        $checklist->CliCorreo = $row['cli_correo'];
        $checklist->Supervisor = $row['supervisor'];
        $checklist->EquCodigo = $row['equ_codigo'];
        $checklist->EquNombre = $row['equ_nombre'];
        $checklist->EquMarca = $row['equ_marca'];
        $checklist->EquModelo = $row['equ_modelo'];
        $checklist->EquPlaca = $row['equ_placa'];
        $checklist->EquSerie = $row['equ_serie'];
        $checklist->EquMotor = $row['equ_motor'];
        $checklist->EquTransmision = $row['equ_transmision'];
        $checklist->EquDiferencial = $row['equ_diferencial'];
        $checklist->EquKm = $row['equ_km'];
        $checklist->EquHm = $row['equ_hm'];
        $checklist->Imagen1 = $row['imagen1'];
        $checklist->Imagen2 = $row['imagen2'];
        $checklist->Imagen3 = $row['imagen3'];
        $checklist->Imagen4 = $row['imagen4'];
        $checklist->EmpFirma = $row['emp_firma'];
        $checklist->CliFirma = $row['cli_firma'];
        $checklist->Estado = $row['estado'];
        return $checklist;
      } else {
          throw new Exception('Checklist no disponible para cliente.');
      }
    } catch (PDOException $ex) {
        throw new Exception($ex->getMessage());
    } catch (Exception $ex) {
        throw new Exception($ex->getMessage());
    }
  }

  function FnBuscarCheckList2($conmy, $cliid, $id) {
    try {
        $datos=array();
        $stmt = $conmy->prepare("select id, plaid, nombre, estado FROM tblchecklists WHERE id=:Id and cliid=:CliId;");
        $stmt->execute(array(':Id'=>$id, ':CliId'=>$cliid));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $datos['id']=$row['id'];
            $datos['plaid']=$row['plaid'];
            $datos['nombre']=$row['nombre'];
            $datos['estado']=$row['estado'];
        }            
        return $datos;
    } catch (PDOException $ex) {
        throw new Exception($ex->getMessage());
    } catch (Exception $ex) {
        throw new Exception($ex->getMessage());
    }
  }

  function FnModificarCheckList($conmy, $checklist) {
    try {
      $stmt = $conmy->prepare("UPDATE tblchecklists 
                               SET  fecha = :Fecha, cli_contacto = :CliContacto, supervisor = :Supervisor, equ_nombre = :EquNombre, equ_marca = :EquMarca, 
                               equ_modelo = :EquModelo, equ_placa = :EquPlaca, equ_serie = :EquSerie, equ_motor = :EquMotor, equ_transmision =:EquTransmision, 
                               equ_diferencial =:EquDiferencial, equ_km = :EquKm, equ_hm = :EquHm, actualizacion = :Actualizacion WHERE id = :Id");
      $params = array(
        ':Fecha' => $checklist->Fecha,
        ':CliContacto' => $checklist->CliContacto,
        ':Supervisor' => $checklist->Supervisor,
        ':EquNombre' => $checklist->EquNombre,
        ':EquMarca' => $checklist->EquMarca,
        ':EquModelo' => $checklist->EquModelo,
        ':EquPlaca' => $checklist->EquPlaca,
        ':EquSerie' => $checklist->EquSerie,
        ':EquMotor' => $checklist->EquMotor,
        ':EquTransmision' => $checklist->EquTransmision,
        ':EquDiferencial' => $checklist->EquDiferencial,
        ':EquKm' => $checklist->EquKm,
        ':EquHm' => $checklist->EquHm,
        ':Actualizacion' => $checklist->Usuario,
        ':Id' => $checklist->Id
      );
      $result = $stmt->execute($params);

      if ($stmt->rowCount() == 0) {
          throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnModificarChecklistImagenes($conmy, $fileNames, $usuario, $id) {
    try {
      $sql = "UPDATE tblchecklists SET imagen1 = :Imagen1, imagen2 = :Imagen2, imagen3 = :Imagen3, imagen4 = :Imagen4, actualizacion = :Actualizacion WHERE id = :Id";
      $stmt = $conmy->prepare($sql);
      $stmt->execute(array(
        ':Imagen1' => $fileNames['imagen1'],
        ':Imagen2' => $fileNames['imagen2'],
        ':Imagen3' => $fileNames['imagen3'],
        ':Imagen4' => $fileNames['imagen4'],
        ':Actualizacion' => $usuario,
        ':Id' => $id 
      ));
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
  }

  /* ================================
     TABLA: tblchkactividades 
     ================================*/
  function FnBuscarCheckListActividadPreguntas($conmy, $chkid) {
    try {
        $datos=array();
        $stmt = $conmy->prepare("select id, preid, descripcion, respuesta, observaciones, archivo FROM tblchkactividades WHERE chkid=:ChkId;");
        $stmt->execute(array(':ChkId'=>$chkid));

        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=array(
                'id'=>$row['id'],
                'preid'=>$row['preid'],                        
                'pregunta'=>$row['descripcion'],
                'respuesta'=>$row['respuesta'],
                'observaciones'=>$row['observaciones'],
                'archivo'=>$row['archivo']
            );
        }
        return $datos;
    } catch (PDOException $ex) {
        throw new Exception($ex->getMessage());
    } catch (Exception $ex) {
        throw new Exception($ex->getMessage());
    }
  }

  function FnBuscarCheckListActividades($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, preid, chkid, descripcion, respuesta, observaciones, archivo, estado FROM tblchkactividades WHERE chkid=:ChkId");
      $stmt->execute(array(':ChkId'=>$id));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }

  function FnModificarCheckListActividad($conmy, $actividad) {
    try {
        $stmt = $conmy->prepare("UPDATE tblchkactividades SET observaciones = :Observaciones, archivo = :Archivo, actualizacion = :Actualizacion WHERE id =:Id");
        $params = array(
            ':Observaciones' => $actividad->Observaciones,
            ':Archivo' => $actividad->Archivo,
            ':Actualizacion' => $actividad->Usuario,
            ':Id' => $actividad->Id
        );
        $result = $stmt->execute($params);
        if ($stmt->rowCount() == 0) {
            throw new Exception('Cambios no realizados.');
        }
        return $result;
    } catch (PDOException $ex) {
        error_log($ex->getMessage()); // Log del error
        throw new Exception($ex->getMessage());
    }
  }

  function FnEliminarCheckListActividadArchivo($conmy, $id) {
    try {
      $res = false;
      $stmt = $conmy->prepare("UPDATE tblchkactividades SET archivo=null WHERE id=:Id");
      $params = array(':Id' => $id);
      if ($stmt->execute($params)) {
        $res = true;
      }
      return $res;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnAgregarModificarCheckListActividad($conmy, $respuestas, $chkid, $usuario) {
    foreach ($respuestas as $respuesta) {
      try {
        if ($respuesta['Id'] > 0) {
          // MODIFICAR RESPUESTA
          $sql = "UPDATE tblchkactividades SET respuesta=:Respuesta, actualizacion=:Actualizacion WHERE id=:Id AND chkid=:Chkid";
          $stmtRespuestas = $conmy->prepare($sql);
          $stmtRespuestas->execute(array(
            ':Respuesta' => $respuesta['Respuesta'],
            ':Actualizacion' => $usuario,
            ':Id' => $respuesta['Id'],
            ':Chkid' => $chkid,
          ));
        } 
        else {
          // INSERTAR RESPUESTA NUEVA
          $sqlRespuestas = "INSERT INTO tblchkactividades (preid, chkid, descripcion, respuesta, observaciones, archivo, estado, creacion) 
              VALUES (:Preid, :Chkid, :Descripcion, :Respuesta, :Observaciones, :Archivo, :Estado, :Creacion)";
          $stmtRespuestas = $conmy->prepare($sqlRespuestas);
          $stmtRespuestas->execute(array(
            ':Preid' => $respuesta['Preid'],
            ':Chkid' => $chkid,
            ':Descripcion' => $respuesta['Descripcion'],
            ':Respuesta' => $respuesta['Respuesta'],
            ':Observaciones' => null, 
            ':Archivo' => null, 
            ':Estado' => 2,
            ':Creacion' => $usuario
          ));
        }
      } catch (PDOException $e) {
          throw new Exception($e->getMessage());
      } catch (Exception $e) {
          throw new Exception($e->getMessage());
      }
    }
  }

  /** ================================
   *  TABLA: tblchkobservaciones 
   *  ================================*/
  function FnRegistrarCheckListObservacion($conmy, $observacion) {
    try {
      $res = false;
      $stmt = $conmy->prepare("INSERT INTO tblchkobservaciones(chkid, descripcion, creacion, actualizacion) VALUES(:ChkId,:Descripcion,:Creacion,:Actualizacion)");
      $params = array(':ChkId' => $observacion->ChkId,':Descripcion' => $observacion->Descripcion,':Creacion' => $observacion->Usuario,':Actualizacion' => $observacion->Usuario);
      if ($stmt->execute($params)) {
        $res = true;
      }
      return $res;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnModificarCheckListObservacion($conmy, $observacion) {
    try {
      $stmt = $conmy->prepare("UPDATE tblchkobservaciones SET descripcion=:Descripcion, actualizacion=:Actualizacion WHERE id=:Id;");
      $params = array(':Descripcion'=>$observacion->Descripcion, ':Actualizacion'=>$observacion->Usuario, ':Id'=>$observacion->Id);
      $result = $stmt->execute($params);
      if($stmt->rowCount()==0){
        throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnBuscarCheckListObservaciones($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, chkid, descripcion, archivo, estado FROM tblchkobservaciones WHERE chkid=:ChkId");
      $stmt->execute(array(':ChkId'=>$id));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }

  function FnBuscarCheckListObservacion($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, chkid, descripcion, archivo, estado FROM tblchkobservaciones WHERE id=:Id");
      $stmt->execute(array(':Id' => $id));
      $observacion = new stdClass();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $observacion->id = $row['id'];
          $observacion->ChkId = $row['chkid'];
          $observacion->Descripcion = $row['descripcion'];
          $observacion->Archivo = $row['archivo'];
          $observacion->Estado = $row['estado'];
        }
        return $observacion;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnEliminarCheckListObservacion($conmy, $id, $chkid) {
    try {
      $stmt = $conmy->prepare("DELETE FROM tblchkobservaciones WHERE id=:Id AND chkid=:ChkId");
      $params = array(':Id' => $id, ':ChkId' => $chkid);
      $result = $stmt->execute($params);
      if($stmt->rowCount()==0){
        throw new Exception('Error al eliminar Observacion.');
      }
      return $result;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnRegistrarCheckListObservacionArchivo($conmy, $observacion) {
    try {
      $stmt = $conmy->prepare("UPDATE tblchkobservaciones SET archivo=:Archivo, actualizacion=:Actualizacion WHERE id=:Id");
      $params = array(
        ':Archivo' => $observacion->Archivo,
        ':Actualizacion' => $observacion->Usuario,
        ':Id' => $observacion->Id
      );
      $result = $stmt->execute($params);
      if ($stmt->rowCount() == 0) {
          throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnEliminarCheckListObservacionArchivo($conmy, $id) {
    try {
      $res = false;
      $stmt = $conmy->prepare("UPDATE tblchkobservaciones SET archivo=null WHERE id=:Id");
      $params = array(':Id' => $id);
      if ($stmt->execute($params)) {
        $res = true;
      }
      return $res;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

?>