var PaginasTotal = 0;
var PaginaActual = 0;

const vgLoader = document.querySelector('.container-loader-full');

window.onload = function() {
    // document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
    vgLoader.classList.add('loader-full-hidden');
};

function FnModalAgregarPlantilla(){
  const modalAgregarOrden=new bootstrap.Modal(document.getElementById('modalAgregarPlantilla'), {keyboard: false}).show();
  return false;
}

async function FnAgregarPlantilla() {
  try {
    const formData = new FormData();
    formData.append('tipo', document.getElementById('txtTipo1').value);

    const response = await fetch("/checklist/insert/AgregarPlantilla.php", {
      method: "POST",
      body: formData
    });
    if (!response.ok) {
      throw new Error(`${response.status} ${response.statusText}`);
    }
    const datos = await response.json();
    if (!datos.res) {
      throw new Error(datos.msg);
    }
    Swal.fire({
      title: "Éxito",
      text: datos.msg,
      icon: "success",
      timer: 2000
    });
    setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
      document.getElementById('msjAgregarPlantilla').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
  }
}

async function FnBuscarPlantillas() {
  try {
    const formData = new FormData();
    formData.append('tipo', document.querySelector('#txtTipo').value);
    // formData.append('pagina', PaginasTotal);
    const response = await fetch('/checklist/search/BuscarPlantillas.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`${response.status} ${response.statusText}`);
    }
    const datos = await response.json();
    console.log(datos.data);
    if (!datos.res) {
      document.getElementById('tblPlantillas').innerHTML = `
        <div class="col-12">
          <div class="alert alert-danger" role="alert">
            ${datos.msg}
          </div>
        </div>`;
      return; 
    }
    document.getElementById('tblPlantillas').innerHTML = '';
    let estado = '';
    datos.data.forEach(item => {
      switch (parseInt(item.estado)) {
        case 1:
          estado = '<span class="badge bg-danger">Anulado</span>';
          break;
        case 2:
          estado = '<span class="badge bg-primary">Abierto</span>';
          break;
        case 3:
          estado = '<span class="badge bg-success">Cerrado</span>';
          break;
        default:
          estado = '<span class="badge bg-light text-dark">Unknown</span>';
      }
      document.getElementById('tblPlantillas').innerHTML += `
      <div class="col-12">
        <div class="divselect border-bottom border-secondary mb-2 px-1" onclick="FnPlantilla(${item.id}); return false;">
          <div class="div d-flex justify-content-between">
            <p class="m-0"><span class="fw-bold">${item.tipo}</span></p><p class="m-0">${estado}</p>
          </div>
        </div>
      </div>`;
    });

    // FnPaginacion(datos.pag);
  } catch (error) {
    document.getElementById('tblPlantillas').innerHTML = `
      <div class="col-12">
        <div class="alert alert-danger" role="alert">
          ${error.message}
        </div>
      </div>`;
  }
}

function FnPlantilla(id){
  if(id > 0){
    window.location.href=`/checklist/admin/EditarPlantilla.php?id=${id}`;
  }
  return false;
}

function FnPaginacion(cantidad) {
  try {
    PaginaActual += 1;
    if (cantidad == 15) {
      PaginasTotal += 15;
      document.getElementById("btnSiguiente").classList.remove('d-none');
    } else {
      document.getElementById("btnSiguiente").classList.add('d-none');
    }
    if (PaginaActual > 1) {
      document.getElementById("btnPrimero").classList.remove('d-none');
    } else {
      document.getElementById("btnPrimero").classList.add('d-none');
    }
  } catch (ex) {
      throw ex;
  }
}

async function FnBuscarSiguiente() {
  vgLoader.classList.remove('loader-full-hidden');
  try {
    await FnBuscarPlantillas();
  } catch (ex) {
    document.getElementById("btnSiguiente").classList.add('d-none');
    showToast(ex.message, 'bg-danger');
  } finally {
    setTimeout(function () { vgLoader.classList.add('loader-full-hidden'); }, 500);
  }
}

async function FnBuscarPrimero() {
  vgLoader.classList.remove('loader-full-hidden');
  try {
    PaginasTotal = 0;
    PaginaActual = 0;
    await FnBuscarPlantillas();
  } catch (ex) {
      document.getElementById("btnPrimero").classList.add('d-none');
      showToast(ex.message, 'bg-danger');
  } finally {
      setTimeout(function () { vgLoader.classList.add('loader-full-hidden'); }, 500);
  }
}