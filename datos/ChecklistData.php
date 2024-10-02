<?php
  /** tblchecklist */
  function FnBuscarChecklist($conmy, $id, $cliid) {
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

  /** ================================
   *  TABLA: tblchkplantillas 
   *  ================================*/
  function FnBuscarPlantillas($conmy, $tipo = null) {
    try {
      $sql = "SELECT id, tipo, imagen1, imagen2, imagen3, imagen4, estado FROM tblchkplantillas";
      if ($tipo !== null) { $sql .= " WHERE tipo = :Tipo"; }
      $stmt = $conmy->prepare($sql);
      if ($tipo !== null) { $stmt->bindParam(':Tipo', $tipo, PDO::PARAM_STR); }
      $stmt->execute();
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnBuscarPlantilla($conmy, $id){
    try {
      $stmt = $conmy->prepare("SELECT id, tipo, imagen1, imagen2, imagen3, imagen4, estado FROM tblchkplantillas WHERE id=:Id");
      $stmt -> execute(array(':Id'=>$id));
      $plantilla = new stdClass();
      while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $plantilla->Id = $row['id'];
        $plantilla->Tipo = $row['tipo'];
        $plantilla->Imagen1 = $row['imagen1'];
        $plantilla->Imagen2 = $row['imagen2'];
        $plantilla->Imagen3 = $row['imagen3'];
        $plantilla->Imagen4 = $row['imagen4'];
        $plantilla->Estado = $row['estado'];
      } 
      return $plantilla;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnRegistrarPlantilla($conmy, $plantilla) {
    try {
      $res = false;
      $stmt = $conmy->prepare("INSERT INTO tblchkplantillas(tipo, creacion, actualizacion) VALUES(:Tipo,:Creacion,:Actualizacion)");
      $params = array(':Tipo' => $plantilla->Tipo,':Creacion' => $plantilla->Creacion,':Actualizacion' => $plantilla->Usuario);
      if ($stmt->execute($params)) {
        $res = true;
      }
      return $res;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnTipoPlantillaExiste($conmy, $tipo) {
    try {
        $stmt = $conmy->prepare("SELECT COUNT(*) FROM tblchkplantillas WHERE tipo = :Tipo");
        $stmt->bindParam(':Tipo', $tipo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnRegistrarPlantillaImagen($conmy, $plantilla, $numImagen) {
    try {
      $imagenCampo = 'imagen'.$numImagen; 
      $stmt = $conmy->prepare("UPDATE tblchkplantillas SET $imagenCampo = :Imagen, actualizacion = :Actualizacion WHERE id = :Id");
      $params = array(':Imagen' => $plantilla->Imagen, ':Actualizacion' => $plantilla->Usuario, ':Id' => $plantilla->Id);
      $result = $stmt->execute($params);
      if ($stmt->rowCount() == 0) {
          throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnEliminarPlantillaImagen($conmy, $id, $numImagen) {
    try {
      $imagenCampo = 'imagen'.$numImagen;
      $stmt = $conmy->prepare("UPDATE tblchkplantillas SET ".$imagenCampo."= null WHERE id = :Id");
      $stmt->execute(array(':Id' => $id));
      return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  /** ================================
   *  TABLA: tblchkpreguntas 
   *  ================================*/
  //FnBuscarActividades($conmy, $id)
  function FnBuscarPlantillaPreguntas($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, plaid, descripcion, estado FROM tblchkpreguntas WHERE plaid=:Plaid");
      $stmt->execute(array('Plaid'=>$id));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }

  function FnRegistrarPlantillaPregunta($conmy, $plantillaPregunta) {
    try {
        $res = false;
        $stmt = $conmy->prepare("INSERT INTO tblchkpreguntas(plaid, descripcion, creacion, actualizacion) VALUES(:Plaid,:Descripcion,:Creacion,:Actualizacion)");
        $params = array(':Plaid' => $plantillaPregunta->Plaid,':Descripcion' => $plantillaPregunta->Descripcion,':Creacion' => $plantillaPregunta->Creacion,':Actualizacion' => $plantillaPregunta->Actualizacion);
        if ($stmt->execute($params)) {
            $res = true;
        }
        return $res;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnModificarPlantillaPregunta($conmy, $plantillaPregunta) {
    try {
      $stmt = $conmy->prepare("UPDATE tblchkpreguntas SET descripcion=:Descripcion, actualizacion=:Actualizacion WHERE id=:Id;");
      $params = array(':Descripcion'=>$plantillaPregunta->Descripcion, ':Actualizacion'=>$plantillaPregunta->Usuario, ':Id'=>$plantillaPregunta->Id);
      $result = $stmt->execute($params);
      if($stmt->rowCount()==0){
        throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnEliminarPlantillaPregunta($conmy, $id) {
    try {
      $stmt = $conmy->prepare("DELETE FROM tblchkpreguntas WHERE id = :Id");
      $params = array(':Id' => $id);
      $result = $stmt->execute($params);
      if($stmt->rowCount()==0){
        throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

    /** ================================
   *  TABLA: tblchkactividades 
   *  ================================*/
  function FnBuscarCheckListPreguntas($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, chkid, preid, descripcion, respuesta, observaciones, archivo, estado FROM tblchkactividades WHERE chkid=:ChkId");
      $stmt->execute(array('ChkId'=>$id));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }





  
  

  function FnBuscarActividad($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, plaid, descripcion, estado FROM tblchkpreguntas WHERE id=:Id");
      $stmt->execute(array(':Id' => $id));
      $actividad = new stdClass();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $actividad->id = $row['id'];
          $actividad->Plaid = $row['plaid'];
          $actividad->Descripcion = $row['descripcion'];
          $actividad->Estado = $row['estado'];
        }
        return $actividad;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnRegistrarAlternativa($conmy, $alternativa) {
    try {
      $res = false;
      $stmt = $conmy->prepare("INSERT INTO tblchkalternativas(preid,descripcion,creacion,actualizacion) VALUES(:Preid,:Descripcion,:Creacion,:Actualizacion)");
      $params = array(':Preid' => $alternativa->Preid,':Descripcion' => $alternativa->Descripcion,':Creacion' => $alternativa->Creacion,':Actualizacion' => $alternativa->Actualizacion);
      if ($stmt->execute($params)) {
          $res = true;
      }
      return $res;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnModificarChecklist($conmy, $checklist) {
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

  function FnBuscarAlternativas($conmy,$ids) {
    try {
      $placeholders = implode(',', $ids);

      $stmt = $conmy->prepare("SELECT id, preid, descripcion, estado FROM tblchkalternativas WHERE preid IN($placeholders)");
      $stmt->execute();
      $alternativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $alternativas;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnBuscarAlternativas2($conmy, $preid) {
    try {
      $stmt = $conmy->prepare("SELECT id, preid, descripcion FROM tblchkalternativas WHERE preid=:PreId");
      $stmt->execute(array(':PreId'=>$preid));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }

  function FnBuscarChecklists($conmy, $checklist) {
    try {
      $checklists=array('data'=>array(), 'pag'=>0);
      $query = "";

      if(!empty($checklist->Nombre)){
        $query = " and nombre like '%".$checklist->Nombre."%'";
      }else{
        if(!empty($checklist->Equipo)){$query .=" and equid=".$checklist->Equipo;}
        $query.=" and fecha between '".$checklist->FechaInicial."' and '".$checklist->FechaFinal."'";
      }

      $query.=" limit ".$checklist->Pagina.", 2";

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
        throw new Exception($e->getMessage().$msg);
    }
  }

  function FnEliminarAlternativa($conmy, $id) {
    try {
      $stmt = $conmy->prepare("DELETE FROM tblchkalternativas WHERE id =:Id");
      $params = array(':Id' => $id);
      $result = $stmt->execute($params);
      if($stmt->rowCount()==0){
        throw new Exception('Cambios no realizados.');
      }
      return $result;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnRegistrarObservacion($conmy, $observacion) {
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

  function FnModificarObservacion($conmy, $observacion) {
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

  function FnBuscarObservaciones($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, chkid, descripcion, archivo, estado FROM tblchkobservaciones WHERE chkid=:ChkId");
      $stmt->execute(array(':ChkId'=>$id));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }

  function FnBuscarObservacion($conmy, $id) {
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

  function FnEliminarObservacion($conmy, $id) {
    try {
      $stmt = $conmy->prepare("DELETE FROM tblchkobservaciones WHERE id=:Id");
      $params = array(':Id' => $id);
      $result = $stmt->execute($params);
      if($stmt->rowCount()==0){
        throw new Exception('Error al eliminar Observacion.');
      }
      return $result;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnRegistrarArchivoObservacion($conmy, $observacion) {
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

  function FnEliminarArchivoObservacion($conmy, $id) {
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

  // BUSCAR SUPERVISORES
  function FnBuscarSupervisores($comy) {
    try {
      $stmt = $comy->prepare("SELECT idsupervisor, idcliente, supervisor FROM cli_supervisores WHERE idcliente = 1");
      $stmt->execute(); 
      $supervisores = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $supervisores;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }
  }

  function FnBuscarTablaActividades($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, preid, chkid, descripcion, respuesta, observaciones, archivo, estado FROM tblchkactividades WHERE chkid=:ChkId");
      $stmt->execute(array(':ChkId'=>$id));
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultados;
    } catch (PDOException $ex) {
      return null;
    }
  }

  function FnBuscarTablaActividad($conmy, $id) {
    try {
      $stmt = $conmy->prepare("SELECT id, preid, chkid, descripcion, respuesta, observaciones, archivo, estado FROM tblchkactividades WHERE id=:Id");
      $stmt->execute(array(':Id' => $id));
      $actividad = new stdClass();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $actividad->Id = $row['id'];
          $actividad->PreId = $row['preid'];
          $actividad->ChkId = $row['chkid'];
          $actividad->Descripcion = $row['descripcion'];
          $actividad->Respuesta = $row['respuesta'];
          $actividad->Observaciones = $row['observaciones'];
          $actividad->Archivo = $row['archivo'];
          $actividad->Estado = $row['estado'];
        }
        return $actividad;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
  }

  function FnModificarTablaActividad($conmy, $actividad) {
    try {
        $stmt = $conmy->prepare("UPDATE tblchkactividades SET descripcion = :Descripcion, respuesta = :Respuesta, observaciones = :Observaciones, archivo = :Archivo, actualizacion = :Actualizacion WHERE id =:Id");
        $params = array(
            ':Descripcion' => $actividad->Descripcion,
            ':Respuesta' => $actividad->Respuesta,
            ':Observaciones' => $actividad->Observaciones,
            ':Archivo' => $actividad->Archivo,
            ':Actualizacion' => $actividad->Usuario,
            ':Id' => $actividad->Id
        );
        $result = $stmt->execute($params);
        
        // Comprobar si se realizó alguna actualización
        if ($stmt->rowCount() == 0) {
            throw new Exception('Cambios no realizados.');
        }
        return $result;
    } catch (PDOException $ex) {
        error_log($ex->getMessage()); // Log del error
        throw new Exception('Error en la base de datos: ' . $ex->getMessage());
    }
  }
?>