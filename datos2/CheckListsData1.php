<?php
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

    function FnBuscarCheckList($conmy, $cliid, $id) {
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

    function FnBuscarCheckListPreguntas($conmy, $chkid) {
        try {
            $datos=array();
            $stmt = $conmy->prepare("select id, preid, descripcion, respuesta FROM tblchkactividades WHERE chkid=:ChkId;");
            $stmt->execute(array(':ChkId'=>$chkid));

            while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
                $datos[]=array(
                    'id'=>$row['id'],
                    'preid'=>$row['preid'],                        
                    'pregunta'=>$row['descripcion'],
                    'respuesta'=>$row['respuesta']
                );
            }
            return $datos;
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    function FnBuscarPlantillaPreguntas($conmy, $plaid) {
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