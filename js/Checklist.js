const vgLoader = document.querySelector('.container-loader-full');

window.onload = function() {
  document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
  vgLoader.classList.add('loader-full-hidden');
};

function FnModalFinalizarCheckList(){
  let modalFinalizarCheckList=new bootstrap.Modal(document.getElementById('modalFinalizarCheckList'), {
      keyboard: false
  });
  modalFinalizarCheckList.show();
}

async function FnFinalizarCheckList(){
  vgLoader.classList.remove('loader-full-hidden');
  try {
      const formData = new FormData();
      formData.append('id', document.getElementById('idCheckList').value);
      const response = await fetch('/checklist/update/FinalizarCheckList.php', {
          method:'POST',
          body: formData
      });

      if(!response.ok){throw new Error(`${response.status} ${response.statusText}`);}
      const datos = await response.json();
      if(!datos.res){throw new Error(datos.msg);}        
      setTimeout(function(){location.reload();},500);
  } catch (ex) {
      showToast(ex.message, 'bg-danger');
      setTimeout(function () { vgLoader.classList.add('loader-full-hidden'); }, 500);
  }
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





