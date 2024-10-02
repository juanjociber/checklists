
function FnEditarChecklist(id){
  console.log(id);
  document.getElementById('txtIdPlantilla').value = id;
  if(id > 0){
    window.location.href='/checklist/EditarChecklistDatos.php?id='+id;
  }
  return false;
}

function FnListarChecklists(){
  window.location.href='/checklist/Checklists.php';
  return false;
}





