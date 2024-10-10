function FnModalFinalizarCheckList(){
  
}


function FnEditarChecklist(id){
  console.log(id);
  if(id > 0){
    window.location.href='/checklist/EditarCheckListDatos.php?id='+id;
  }
  return false;
}

function FnListarChecklists(){
  window.location.href='/checklist/CheckLists.php';
  return false;
}





