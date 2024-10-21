
const vgLoader = document.querySelector('.container-loader-full');
window.onload = function() {
  //document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
  vgLoader.classList.add('loader-full-hidden');
};

/**================================
 FUNCIONES PARA CARGA DE IMÁGENES
===================================* 
*/
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
  // console.log('Nombre del archivo:', file.name);
  // console.log('Tipo del archivo:', file.type);
  // console.log('Tamaño del archivo:', file.size, 'bytes');
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

/** ABRIR MODAL AGREGAR SILUETA */
const FnModalAgregarPlantillaImagen = (numImagen) => {
  const modalAgregarSilueta = new bootstrap.Modal(document.getElementById('modalAgregarSilueta'), {keyboard: false}).show();
  document.querySelector('#txtNumImagen').value = numImagen; 
  return false;
};

/** AGREGAR SILUETAS */
async function FnAgregarPlantillaImagen() {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', document.querySelector('#txtIdPlantilla').value);
    formData.append('numImagen', document.querySelector('#txtNumImagen').value); 
    var archivo;
    if (document.getElementById('canvas')) {
      archivo = document.querySelector("#canvas").toDataURL("image/jpeg");
    } else if (document.getElementById('fileImagen').files.length == 1) {
      archivo = document.getElementById('fileImagen').files[0];
    } else {
      throw new Error('No se reconoce el archivo');
    }
    formData.append('archivo', archivo);
    const response = await fetch('/checklists/insert/AgregarPlantillaImagen.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
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
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjAgregarImagen').innerHTML = `<div class="alert alert-danger m-0 p-1 text-center" role="alert">${error.message}</div>`;
      setTimeout(() => { location.reload(); }, 2000);
  }
}

/** ELIMINAR SILUETAS */
const FnEliminarPlantillaImagen = async (id, numImagen) => {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', id);
    formData.append('numImagen', numImagen); 
    const response = await fetch('/checklists/delete/EliminarPlantillaImagen.php', {
      method: 'POST',
      body: formData,
    });
    const datos = await response.json();
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    if (datos.res) {
      Swal.fire({
        title: "Aviso",
        text: datos.msg,
        icon: "success",
        timer: 2000
      });
      setTimeout(() => { location.reload(); }, 1000);
    } else {
      Swal.fire({
        title: "Información",
        text: datos.msg,
        icon: "error",
        timer: 2000
      });
    }
  } catch (error) {
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    await Swal.fire({
      title: "Información",
      text: `${error.message}`,
      icon: "error",
      timer: 2000
    });
  }
};

/** ABRIR MODAL AGREGAR PREGUNTA */
const FnModalAgregarPlantillaPregunta = () => {
  const modalAgregarPlantillaPregunta = new bootstrap.Modal(document.getElementById('modalAgregarPlantillaPregunta'), {keyboard: false}).show();
  return false;
}

/** AGREGAR PREGUNTA */
const FnAgregarPlantillaPregunta = async () => {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('plaid',document.querySelector('#txtIdPlantilla').value);
    formData.append('descripcion', document.querySelector('#txtDescripcion').value);
    const response = await fetch("/checklists/insert/AgregarPlantillaPregunta.php", {
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
    Swal.fire({
      title: "Aviso",
      text: datos.msg,
      icon: "success",
      timer: 2000
    });
    setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjAgregarActividad').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
      setTimeout(() => { location.reload(); }, 2000);
  }
};

/** ABRIR MODAL BUSCAR-MODIFICAR ACTIVIDAD */
const FnModalModificarPlantillaPregunta = async (id) => {
  document.querySelector('#txtIdActividad').value = id;
  const formData = new FormData();
  formData.append('id', id);
  try {
    const response = await fetch('/checklists/search/BuscarPlantillaPregunta.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) { 
      throw new Error(response.status + ' ' + response.statusText); 
    }
    const datos = await response.json();
    document.getElementById('txtDescripcion2').value = datos.data.Descripcion;
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
  const modalModificarPlantillaPregunta = new bootstrap.Modal(document.getElementById('modalModificarPlantillaPregunta'), {keyboard: false}).show();
  return false;
};

/** MODIFICAR PREGUNTA */
const FnModificarPlantillaPregunta = async () => {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', document.getElementById('txtIdActividad').value);
    formData.append('descripcion', document.getElementById('txtDescripcion2').value);
    const response = await fetch("/checklists/update/ModificarPlantillaPregunta.php", {
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
    Swal.fire({
      title: "Aviso",
      text: datos.msg,
      icon: "success",
      timer: 2000
    });
    setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjModificarActividad').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
      setTimeout(() => { location.reload(); }, 2000);
  }
};

/** ELIMINAR PREGUNTA */
const FnModalEliminarPlantillaPregunta = async (id) =>{
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', id);
    const response = await fetch('/checklists/delete/EliminarPlantillaPregunta.php', {
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
      setTimeout(() => { location.reload(); }, 1000);
    } else {
      await Swal.fire({
        title: "Error",
        text: datos.msg,
        icon: "error",
        timer: 2000,
      });
    }
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

/** ABRIR MODAL AGREGAR ALTERNATIVA */
const FnModalAgregarAlternativa = (id) => {
  document.querySelector('#txtIdActividad').value = id;
  const modalAgregarAlternativa=new bootstrap.Modal(document.getElementById('modalAgregarAlternativa'), {keyboard: false}).show();
  return false;
};

/** AGREGAR ALTERNATIVA */
const FnAgregarAlternativa = async () => {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('preid',document.querySelector('#txtIdActividad').value);
    formData.append('descripcion', document.getElementById('txtAlternativa').value);
    const response = await fetch("/checklists/insert/AgregarPlantillaAlternativa.php", {
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
    await Swal.fire({
      title: "Éxito",
      text: datos.msg,
      icon: "success",
      timer: 2000,
    });
    setTimeout(() => { location.reload(); }, 100);
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjAgregarAlternativa').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
      setTimeout(() => { location.reload(); }, 2000);
  }
}

async function FnEliminarAlternativa(id){
  try {
    vgLoader.classList.remove('loader-full-hidden');
    const formData = new FormData();
    formData.append('id', id);
    const response = await fetch('/checklists/delete/EliminarPlantillaAlternativa.php', {
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
}

/** RESUMEN DE PLANTILLA */
function FnResumenPlantilla(){
  id = document.getElementById('txtIdPlantilla').value;
  if(id > 0){
    window.location.href='/checklists/admin/Plantilla.php?id='+id;
  }
  return false;
}

/** LISTAR PLANTILLAS */
function FnListarPlantillas(){
  window.location.href='/checklists/admin/Plantillas.php';
  return false;
}