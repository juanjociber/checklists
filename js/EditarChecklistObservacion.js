const vgLoader = document.querySelector('.container-loader-full');
window.onload = function() {
  //document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
  vgLoader.classList.add('loader-full-hidden');
};

const FnModalAgregarObservacion = () => {
  const modalAgregarObservacion = new bootstrap.Modal(document.getElementById('modalAgregarObservacion'), {keyboard: false}).show();
  return false;
}

/** FUNCIÓN BUSCAR OBSERVACION */
const FnModalModificarObservacion = async (id) => {
  document.querySelector('#txtIdChecklistObs').value = id;
  const formData = new FormData();
  formData.append('id', id);
  try {
    const response = await fetch('/checklists/search/BuscarCheckListObservacion.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) { 
      throw new Error(response.status + ' ' + response.statusText); 
    }
    const datos = await response.json();
    //console.log(datos);
    document.getElementById('txtObservacion2').value = datos.data.Descripcion;
    if (!datos.res) { 
      throw new Error(datos.msg); 
    }
  } 
  catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error,
      timer: 1000
    });
  }
  const modalModificarObservacion = new bootstrap.Modal(document.getElementById('modalModificarObservacion'), {keyboard: false}).show();
  return false;
};

/** FUNCIÓN AGREGAR OBSERVACION */
const FnAgregarObservacion = async () => {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('chkid', document.getElementById('txtIdChecklist').value);
    formData.append('descripcion', document.getElementById('txtObservacion').value);
  
    const response = await fetch("/checklists/insert/AgregarCheckListObservacion.php", {
        method: "POST",
        body: formData
    });
    if (!response.ok) {
      throw new Error(`${response.status} ${response.statusText}`);
    }
    const datos = await response.json();
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    if (!datos.res) {
      throw new Error(datos.msg);
    }
    setTimeout(() => { location.reload(); }, 100);
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjAgregarObservacion').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
      setTimeout(() => { location.reload(); }, 2000);
  }
};

/** FUNCIÓN MODIFCAR OBSERVACION */
const FnModificarObservacion = async () => {
  try {
    const formData = new FormData();
    formData.append('id', document.getElementById('txtIdChecklistObs').value);
    formData.append('descripcion', document.getElementById('txtObservacion2').value);
  
    const response = await fetch("/checklists/update/ModificarCheckListObservacion.php", {
        method: "POST",
        body: formData
    });
    if (!response.ok) {
      throw new Error(`${response.status} ${response.statusText}`);
    }
    const datos = await response.json();
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    if (!datos.res) {
      throw new Error(datos.msg);
    }
    setTimeout(() => { location.reload(); }, 100);
  } catch (error) {
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    document.getElementById('msjAgregarObservacion2').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
    setTimeout(() => { location.reload(); }, 2000);  }
};

/** FUNCIÓN ELIMINAR OBSERVACION */
const FnModalEliminarObservacion = async (id) =>{
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', id);
    formData.append('chkid', document.querySelector('#txtIdChecklist').value);
    console.log('Datos enviados:', Object.fromEntries(formData.entries()));

    const response = await fetch('/checklists/delete/EliminarCheckListObservacion.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
    }
    const result = await response.json();
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    if (result.res) {
      await Swal.fire({
        title: "Éxito",
        text: result.msg,
        icon: "success",
        timer: 2000,
      });
    } else {
      await Swal.fire({
        title: "Error",
        text: result.msg,
        icon: "error",
        timer: 2000,
      });
    }
    setTimeout(() => { location.reload(); }, 100);
  } catch (error) {
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    await Swal.fire({
      title: "Error",
      text: error.message,
      icon: "error",
      timer: 1000
    });
  }
};

/** VARIABLES Y FUNCIONES PARA CARGA DE ARCHIVO IMAGEN*/
const MAX_WIDTH = 1080;
const MAX_HEIGHT = 720;
const MIME_TYPE = "image/jpeg";
const QUALITY = 0.7;

const $divImagen = document.getElementById("divImagen");
document.getElementById('fileImagen').addEventListener('change', function(event) {
  vgLoader.classList.remove('loader-full-hidden');
  
  const file = event.target.files[0];

  if (!isValidFileType(file)) {
      // console.log('El archivo', file.name, 'Tipo de archivo no permitido.');
  }
  if (!isValidFileSize(file)) {
      // console.log('El archivo', file.name, 'El tamaño del archivo excede los 3MB.');
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

/** FUNCIÓN MOSTRAR MODAL AGREGAR ARCHIVO */
function FnModalAgregarArchivo(id){
  console.log(id);
  document.querySelector('#txtIdChecklistObs').value=id;
  const modalAgregarArchivo = new bootstrap.Modal(document.getElementById('modalAgregarArchivo'), {keyboard: false}).show();
  return false;
}

/** FUNCIÓN AGREGAR ARCHIVO */
async function FnAgregarArchivo() {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    let archivo;
    if (document.getElementById('canvas')) {
      archivo = document.querySelector("#canvas").toDataURL("image/jpeg");

    } else if (document.getElementById('fileImagen').files.length == 1) {
      archivo = document.getElementById('fileImagen').files[0];
    } else {
      throw new Error('No se reconoce el archivo');
    }
    const formData = new FormData();
    formData.append('id', document.querySelector('#txtIdChecklistObs').value);
    formData.append('archivo', archivo);

    const response = await fetch('/checklists/insert/AgregarCheckListObservacionArchivo.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }
    const responseText = await response.text();
    const datos = JSON.parse(responseText);

    if (!datos.res) {
      throw new Error(datos.msg);
    }
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    Swal.fire({
      title: "Éxito",
      text: datos.msg,
      icon: "success",
      timer: 2000
    });
    setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    document.getElementById('msjAgregarArchivo').innerHTML = `<div class="alert alert-danger m-0 p-1 text-center" role="alert">${error.message}</div>`;
  }
}

/** FUNCIÓN ELIMINAR ARCHIVO */
async function FnEliminarArchivo(id){
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', id);
    const response = await fetch('/checklists/delete/EliminarCheckListObservacionArchivo.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
    }
    const datos = await response.json();
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    if (datos.res) {
      await Swal.fire({
        title: "Éxito",
        text: datos.msg,
        icon: "success",
        timer: 2000,
      });
    } else {
      await Swal.fire({
        title: "Error",
        text: datos.msg,
        icon: "error",
        timer: 2000,
      });
    }
    setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    await Swal.fire({
      title: "Error",
      text: error.message,
      icon: "error",
      timer: 1000
    });
  }
}

/** DECLARANDO VARIABLES */
const $video = document.querySelector("#video");
const $canvas = document.querySelector("#canvas1");
const $listaDeDispositivos = document.querySelector("#listaDeDispositivos");

let contenido; 
const tieneSoporteUserMedia = () => navigator.mediaDevices && navigator.mediaDevices.getUserMedia;
const obtenerDispositivos = () => navigator.mediaDevices.enumerateDevices();
const limpiarSelect = () => $listaDeDispositivos.innerHTML = ""; 

const llenarSelectConDispositivosDisponibles = async () => {
  limpiarSelect();
  const dispositivos = await obtenerDispositivos();
  const dispositivosDeVideo = dispositivos.filter(dispositivo => dispositivo.kind === "videoinput");

  if (dispositivosDeVideo.length > 0) {
    dispositivosDeVideo.forEach(dispositivo => {
      const option = document.createElement('option');
      option.value = dispositivo.deviceId;
      option.text = dispositivo.label || `Dispositivo ${dispositivo.deviceId}`;
      $listaDeDispositivos.appendChild(option);
    });
  } 
  // else {
  //   Swal.fire({
  //     title: "Error",
  //     text: "No se encontró dispositivo de cámara",
  //     icon: "error",
  //     timer: 2000
  //   });
  // }
};

/** FUNCIÓN MOSTRAR CAMARA */
async function mostrarContenido (idDeDispositivo){
  const modalAgregarArchivo = new bootstrap.Modal(document.getElementById('modalMostrarCamara'), {keyboard: false}).show();
  try {
    contenido = await navigator.mediaDevices.getUserMedia({
      video: { deviceId: idDeDispositivo ? { exact: idDeDispositivo } : undefined }
    });
    llenarSelectConDispositivosDisponibles();
    $video.srcObject = contenido;
    $video.style.display = 'block';
    $video.style.width = '100%'; 
    $video.style.height ='440px';
    $video.play();
  } catch (error) {
    Swal.fire({
      title: "Error",
      text: error.message,
      icon: "error",
      timer: 2000
    });
  }
};

/** FUNCIÓN PARA ABRIR CÁMARA */
async function FnAbrirCamara(id){
  document.querySelector('#txtIdChecklistObs').value=id;
  const dispositivos = await obtenerDispositivos();
  const dispositivosDeVideo = dispositivos.filter(dispositivo => dispositivo.kind === "videoinput");
  if (dispositivosDeVideo.length > 0) {
    mostrarContenido(dispositivosDeVideo[0].deviceId);
  } else {
    Swal.fire({
      title: "Error",
      text: "No existe dispositivos de cámara",
      icon: "info",
      timer: 2000
    });
  }
}

/** FUNCIÓN PARA TOMAR FOTO */
async function FnAgregarFoto(){
  if (!contenido) {
    Swal.fire({
      title: "Aviso",
      text: "Por favor, abrir la cámara primero",
      icon: "info",
      timer: 2000
    });
  }
  $video.pause();
  $canvas.width = $video.videoWidth;
  $canvas.height = $video.videoHeight;
  const contexto = $canvas.getContext("2d");
  contexto.drawImage($video, 0, 0, $canvas.width, $canvas.height);
  const archivo = $canvas.toDataURL('image/jpeg');
  
  const formData = new FormData();
  formData.append('id', document.querySelector('#txtIdChecklistObs').value);
  formData.append('archivo', archivo);
  console.log('Datos enviados:', Object.fromEntries(formData.entries()));

  const response = await fetch("/checklists/insert/AgregarArchivo.php", {
    method: "POST",
    body: formData
  });

  if (!response.ok) {
    throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
  }
  const datos = await response.json();
  if (!datos.res) {
    throw new Error(datos.msg);
  }
  setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
  Swal.fire({
    title: "Éxito",
    text: datos.msg,
    icon: "success",
    timer: 2000
  });
  setTimeout(() => { location.reload(); }, 1000);
  $video.play();
};

(async () => {
  if (!tieneSoporteUserMedia()) {
    Swal.fire({
      title: "Error",
      text: "Navegador no soporta caracerística de cámara.",
      icon: "error",
      timer: 2000
    });
  }
  // INICIALZAR DISPOSITIVO
  llenarSelectConDispositivosDisponibles();
})();

/**LISTAR CHEKCLITS */
function FnListarChecklists(){
  window.location.href='/checklists/CheckLists.php';
  return false;
}

/** MOSTRAR RESUMEN DE CHECKLIST */
function FnResumenChecklist(){
  id = document.getElementById('txtIdChecklist').value;
  if(id > 0){
    window.location.href='/checklists/checkList.php?id='+id;
  }
  return false;
}

