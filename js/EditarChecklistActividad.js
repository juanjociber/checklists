const vgLoader = document.querySelector('.container-loader-full');
window.onload = function() {
  //document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
  vgLoader.classList.add('loader-full-hidden');
};

/**================================
 FUNCIONES PARA CARGA DE IMÁGENES
===================================*/
const MAX_WIDTH = 1080;
const MAX_HEIGHT = 720;
const MIME_TYPE = "image/jpeg";
const QUALITY = 0.7;

const $divImagen = document.getElementById("divImagen");

document.getElementById('fileImagen').addEventListener('change', function(event) {
  vgLoader.classList.remove('loader-full-hidden');
  
  const file = event.target.files[0];

  if (!isValidFileType(file)) {
      console.log('El archivo', file.name, 'Tipo de archivo no permitido.');
  }

  if (!isValidFileSize(file)) {
      console.log('El archivo', file.name, 'El tamaño del archivo excede los 3MB.');
  }

  while ($divImagen.firstChild) {
      $divImagen.removeChild($divImagen.firstChild);
  }

  if (file.type.startsWith('image/')) {
      displayImage(file);
  }

  console.log('Nombre del archivo:', file.name);
  console.log('Tipo del archivo:', file.type);
  console.log('Tamaño del archivo:', file.size, 'bytes');

  setTimeout(function() {
    vgLoader.classList.add('loader-full-hidden');
  }, 1000)
});

function isValidFileType(file) {
  const acceptedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
  return acceptedTypes.includes(file.type);
}

function isValidFileSize(file) {
  const maxSize = 3 * 1024 * 1024; // 4MB en bytes
  return file.size <= maxSize;
}

function displayImage(file) {
  const reader = new FileReader();
  reader.onload = function(event) {
    const imageUrl = event.target.result;
    const canvas = document.createElement('canvas');
    canvas.style.border = '1px solid black';

    $divImagen.appendChild(canvas);
    const context = canvas.getContext('2d');

    const image = new Image();
    image.onload = function() {
      const [newWidth, newHeight] = calculateSize(image, MAX_WIDTH, MAX_HEIGHT);
      canvas.width = newWidth;
      canvas.height = newHeight;
      canvas.id="canvas";
      context.drawImage(image, 0, 0, newWidth, newHeight);
      // Agregar texto como marca de agua
      context.strokeStyle = 'rgba(216, 216, 216, 0.7)';// color del texto (blanco con opacidad)
      context.font = '15px Verdana'; // fuente y tamaño del texto
      context.strokeText("GPEM SAC", 10, newHeight-10);// texto y posición

      canvas.toBlob(
        (blob) => {  
          displayInfo('Original: ', file);
          displayInfo('Comprimido: ', blob);
        },
        MIME_TYPE,
        QUALITY
      );
    };
    image.src = imageUrl;
  };
  reader.readAsDataURL(file);
}

function displayInfo(label, file) {
  const p = document.createElement('p');
  p.classList.add('text-secondary', 'm-0', 'fs-6');
  p.innerText = `${label} ${readableBytes(file.size)}`;
  $divImagen.append(p);
}

function readableBytes(bytes) {
  const i = Math.floor(Math.log(bytes) / Math.log(1024)),
  sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
}

function calculateSize(img, maxWidth, maxHeight) {
  let width = img.width;
  let height = img.height;
  // calculate the width and height, constraining the proportions
  if (width > height) {
    if (width > maxWidth) {
      height = Math.round((height * maxWidth) / width);
      width = maxWidth;
    }
  } 
  else {
    if (height > maxHeight) {
      width = Math.round((width * maxHeight) / height);
      height = maxHeight;
    }
  }
  return [width, height];
}

/** MODAL BUSCAR-MODIFICAR ACTIVIDAD */
async function FnModalModificarActividad(actividad) {
  try {
    document.getElementById('txtIdChkActividad').value = actividad.getAttribute('dataId');
    document.getElementById('txtPreid').value = actividad.getAttribute('dataPreId');
    document.getElementById('txtDescripcion').value = actividad.getAttribute('dataDescripcion');
    document.getElementById('txtObservacion').value = actividad.getAttribute('dataObservacion');
    document.getElementById('txtRespuesta').value=actividad.getAttribute('dataRespuesta');
    const formData = new FormData();
    formData.append('preid', document.getElementById('txtPreid').value);
    // console.log('Datos enviados:', Object.fromEntries(formData.entries()));
    const response = await fetch('/checklist/search/BuscarAlternativas.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) { 
      throw new Error(response.status + ' ' + response.statusText); 
    }
    const datos = await response.json();
    console.log('DATOS MODAL',datos);
    document.getElementById('tblAlternativas').innerHTML = '';
    datos.data.forEach(item => {
      let checked=''; 
      if(item.descripcion == document.getElementById('txtRespuesta').value){
        checked = 'checked'; 
      }
      document.getElementById('tblAlternativas').innerHTML += `
        <div class="form-check">
          <input class="form-check-input" type="radio" ${checked} name="respuestaRadio" id="chkRespuesta" value="${item.descripcion}">
          <label class="form-check-label" for="chkRespuesta">${item.descripcion}</label>
        </div>`;
    });
    if (!datos.res) { 
      throw new Error(datos.msg); 
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message,
    });
  }
  const modalModificarActividad = new bootstrap.Modal(document.getElementById('modalModificarActividad'), {keyboard: false}).show();
  return false;
}


/** MODIFICAR ACTIVIDAD */
const FnModificarActividad = async () => {
  try {
      vgLoader.classList.remove('loader-full-hidden');
      let archivo=null;
      if (document.getElementById('canvas')) {
          archivo = document.querySelector("#canvas").toDataURL("image/jpeg");
      } else if (document.getElementById('fileImagen').files.length === 1) {
          archivo = document.getElementById('fileImagen').files[0];
      }
      const respuestaSeleccionada = document.querySelector('input[name="respuestaRadio"]:checked');
      const formData = new FormData();
      formData.append('id', document.getElementById('txtIdChkActividad').value);
      formData.append('descripcion', document.getElementById('txtDescripcion').value);
      formData.append('respuesta', respuestaSeleccionada.value);
      formData.append('observaciones', document.getElementById('txtObservacion').value);
      formData.append('archivo', archivo || '');
      // console.log('Datos enviados:', Object.fromEntries(formData.entries()));

      const response = await fetch("/checklist/update/ModificarTablaActividad.php", {
          method: "POST",
          body: formData
      });
      if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
      }
      const datos = await response.json();
      // console.log(datos);
      if (!datos.res) {
          throw new Error(datos.msg);
      }
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      await Swal.fire({
          title: "Aviso",
          text: datos.msg,
          icon: "success",
          timer: 2000
      });
      setTimeout(() => { location.reload(); }, 100);
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjModicarActividad').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
  }
};

function FnListarChecklists(){
  window.location.href='/checklist/Checklists.php';
  return false;
}

function FnResumenChecklist(){
  id = document.getElementById('txtIdChecklist').value;
  console.log(id);
  if(id > 0){
      window.location.href='/checklist/Checklist.php?id='+id;
  }
  return false;
}