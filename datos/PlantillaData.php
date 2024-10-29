<?php
    /* ================================
        TABLA: tblchkplantillas 
       ================================
    */
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

  /*  ================================
      TABLA: tblchkpreguntas 
      ================================
  */
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

    function FnBuscarPlantillaPregunta($conmy, $id) {
      try {
        $stmt = $conmy->prepare("SELECT id, plaid, descripcion, estado FROM tblchkpreguntas WHERE id=:Id");
        $stmt->execute(array(':Id' => $id));
        $plantillaPregunta = new stdClass();
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $plantillaPregunta->Id = $row['id'];
            $plantillaPregunta->Plaid = $row['plaid'];
            $plantillaPregunta->Descripcion = $row['descripcion'];
            $plantillaPregunta->Estado = $row['estado'];
    
          }
          return $plantillaPregunta;
      } catch (PDOException $e) {
          throw new Exception($e->getMessage());
      }
    }

  
    function FnBuscarPlantillaPreguntas1($conmy, $plaid) {
      try {
          $datos=array();
          $stmt = $conmy->prepare("select id, plaid, descripcion FROM tblchkpreguntas WHERE plaid=:PlaId;");
          $stmt->execute(array(':PlaId'=>$plaid));
          while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
              $datos[]=array(
                  'id'=>$row['id'],
                  'plaid'=>$row['plaid'],                        
                  'pregunta'=>$row['descripcion']
              );
          }          
          return $datos;
      } catch (PDOException $ex) {
          throw new Exception($ex->getMessage());
      } catch (Exception $ex) {
          throw new Exception($ex->getMessage());
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

  /*  ================================
      TABLA: tblchkalternativas 
      ================================
  */
  function FnRegistrarPlantillaAlternativa($conmy, $alternativa) {
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

  function FnBuscarPlantillaPreguntasAlternativas($conmy, $preguntas) {
    try {
        $datos=array();
        $query=implode(',', $preguntas);
        $stmt = $conmy->prepare("select id, preid, descripcion FROM tblchkalternativas WHERE preid in($query);");
        $stmt->execute();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[]=array(
                'id'=>$row['id'],
                'preid'=>$row['preid'],                        
                'respuesta'=>$row['descripcion']
            );
        }        
        return $datos;
    } catch (PDOException $ex) {
        throw new Exception($ex->getMessage());
    } catch (Exception $ex) {
        throw new Exception($ex->getMessage());
    }
  }

  function FnEliminarPlantillaAlternativa($conmy, $id) {
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


?>