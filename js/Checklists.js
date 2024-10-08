var EquId = 0;
var Nombre = '';
var FechaInicial = '';
var FechaFinal = '';
var PaginasTotal = 0;
var PaginaActual = 0;

const vgLoader = document.querySelector('.container-loader-full');

window.onload = function() {
    document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
    vgLoader.classList.add('loader-full-hidden');
};

$(document).ready(function() {
  $('#cbActivo1').select2({
    width: 'resolve', //Personalizar el alto del select, aplicar estilo.
    ajax: {
      delay: 450, //Tiempo de demora para buscar
      url: '/gesman/search/ListarActivos.php',
      type: 'POST',
      dataType: 'json',
      data: function (params) {
        return {
          nombre: params.term // parametros a enviar al server. params.term captura lo que se escribe en el input
        };
      },
      processResults: function (data) {
        return {
          results: data.data //Retornar el json obtenido
        }
      },
      cache: true
    },
    placeholder: 'Seleccionar',
    // allowClear: true, // Permite borrar la selección
    minimumInputLength:1 //Caracteres minimos para buscar
  });
});

async function FnBuscarChecklists(){
    vgLoader.classList.remove('loader-full-hidden');
    try {
        Nombre = document.getElementById('txtChecklist').value;
        EquId = document.getElementById('cbActivo1').value;
        FechaInicial = document.getElementById('dtpFechaInicial').value;
        FechaFinal = document.getElementById('dtpFechaFinal').value;
        PaginasTotal = 0;
        PaginaActual = 0;
        await FnBuscarChecklist2();
    } catch (ex) {
        console.log(ex.message);
    } finally {
        setTimeout(function () { vgLoader.classList.add('loader-full-hidden'); }, 500);
    }
}

async function FnBuscarChecklist2(){
  try {
    const formData = new FormData();
    formData.append('nombre', Nombre);
    formData.append('equipo', EquId);
    formData.append('fechainicial', FechaInicial);
    formData.append('fechafinal', FechaFinal);
    formData.append('pagina', PaginasTotal);
    console.log('Datos enviados:', Object.fromEntries(formData.entries()));

    const response = await fetch('/checklist/search/BuscarCheckLists.php', {
        method:'POST',
        body: formData
    });/*.then(response=>response.text()).then((response)=>{console.log(response)}).catch(err=>console.log(err));*/

    if (!response.ok) { throw new Error(`${response.status} ${response.statusText}`);}
    const datos = await response.json();
    console.log(datos);
    if (!datos.res) { throw new Error(`${datos.msg}`); }

    document.getElementById('tblChecklists').innerHTML = '';
    let estado = '';
    datos.data.forEach(item => {
      console.log(item);
        switch (item.estado){
          case 1:
              estado='<span class="badge bg-danger">Anulado</span>';
          break;
          case 2:
              estado='<span class="badge bg-primary">Abierto</span>';
          break;
          case 3:
              estado='<span class="badge bg-success">Cerrado</span>';
          break;
          default:
              estado='<span class="badge bg-light text-dark">Unknown</span>';
        }
        document.getElementById('tblChecklists').innerHTML +=`
        <div class="col-12">
          <div class="divselect border-bottom border-1 mb-2 px-1" onclick="FnChecklist(${item.id}); return false;">
            <div class="div d-flex justify-content-between">
              <p class="m-0"><span class="fw-bold">${item.nombre}</span> <span class="text-secondary" style="font-size: 13px;">${item.fecha}</span></p><p class="m-0">${estado}</p>
            </div>
            <div class="div">${item.equnombre}</div>
          </div>
        </div>`;
    });
    FnPaginacion(datos.pag);
  } catch (ex) {
      throw ex;
  }
}

function FnPaginacion(cantidad) {
  try {
    PaginaActual += 1;
    if (cantidad == 2) {
      PaginasTotal += 2;
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
    await FnBuscarChecklist2();
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
    PaginasTotal = 0
    PaginaActual = 0
    await FnBuscarChecklist2()
  } catch (ex) {
      document.getElementById("btnPrimero").classList.add('d-none');
      showToast(ex.message, 'bg-danger');
  } finally {
      setTimeout(function () { vgLoader.classList.add('loader-full-hidden'); }, 500);
  }
}

function FnChecklist(id){
  console.log(id);
  if(id > 0){
    window.location.href='/checklist/CheckList.php?id='+id;
  }
  return false;
}



