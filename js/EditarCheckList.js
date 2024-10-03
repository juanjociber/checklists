const vgLoader = document.querySelector('.container-loader-full');
window.onload = function() {
  //document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
  vgLoader.classList.add('loader-full-hidden');
};

let currentCard;
let imgDibujo;
const FnHabilitarDibujo = (button) => {
  const card = button.closest('.card');
  if (!card) {
    console.error("No se encontró un elemento con la clase 'card'");
    return;
  }
  currentCard = card;
  const img = card.querySelector('img');

  if (!img) {
    return;
  }
  const canvas = document.getElementById('canvasDibujo');
  const ctx = canvas.getContext('2d');

  // Establecer tamaño del canvas a 370x370px
  const canvasSize = 410;
  canvas.width = canvasSize;
  canvas.height = canvasSize;

  imgDibujo = new Image();
  imgDibujo.src = img.src;
  imgDibujo.onload = () => {
    // Asegúrate de que la imagen se escale para llenar todo el canvas manteniendo las proporciones
    const aspectRatio = imgDibujo.width / imgDibujo.height;
    let drawWidth, drawHeight;
    if (aspectRatio > 1) { // Ancho mayor que alto
      drawWidth = canvasSize;
      drawHeight = canvasSize / aspectRatio;
    } else {
      drawHeight = canvasSize;
      drawWidth = canvasSize * aspectRatio;
    }
    // Aquí asegúrate de dibujar la imagen en toda el área del canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(imgDibujo, 0, 0, canvas.width, canvas.height);
  };

  const colorPicker = document.getElementById('colorPickerDibujo');
  ctx.strokeStyle = colorPicker.value;
  ctx.lineWidth = 2;

  colorPicker.oninput = () => {
    ctx.strokeStyle = colorPicker.value;
  };

  let isDrawing = false;

  const startDrawing = (x, y) => {
    isDrawing = true;
    ctx.beginPath();
    ctx.moveTo(x, y);
  };

  const draw = (x, y) => {
    if (isDrawing) {
      ctx.lineTo(x, y);
      ctx.stroke();
    }
  };

  const endDrawing = () => {
    isDrawing = false;
    ctx.beginPath();
  };

  // Función para obtener las coordenadas correctas
  const getMousePosition = (event) => {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width; 
    const scaleY = canvas.height / rect.height;
    return {
      x: (event.clientX - rect.left) * scaleX,
      y: (event.clientY - rect.top) * scaleY,
    };
  };

  // Mouse events
  canvas.onmousedown = (e) => {
    const pos = getMousePosition(e);
    startDrawing(pos.x, pos.y);
  };
  canvas.onmousemove = (e) => {
    const pos = getMousePosition(e);
    draw(pos.x, pos.y);
  };
  canvas.onmouseup = endDrawing;

  // Touch events
  canvas.ontouchstart = (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const pos = getMousePosition(touch);
    startDrawing(pos.x, pos.y);
  };
  canvas.ontouchmove = (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const pos = getMousePosition(touch);
    draw(pos.x, pos.y);
  };
  canvas.ontouchend = endDrawing;

  const modal = new bootstrap.Modal(document.getElementById('modalAgregarCanva'));
  modal.show();

  const btnLimpiar = document.getElementById('btnLimpiarCanvas');
  btnLimpiar.onclick = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(imgDibujo, 0, 0, canvas.width, canvas.height);
  };
}

const FnGuardarDibujo = () => {
  if (!currentCard) {
    return;
  }
  const canvas = document.getElementById('canvasDibujo');
  const ctx = canvas.getContext('2d');
  const img = currentCard.querySelector('img');

  if (!img) {
    return;
  }

  const tempCanvas = document.createElement('canvas');
  const tempCtx = tempCanvas.getContext('2d');

  tempCanvas.width = imgDibujo.width;
  tempCanvas.height = imgDibujo.height;

  tempCtx.drawImage(imgDibujo, 0, 0, tempCanvas.width, tempCanvas.height);
  tempCtx.drawImage(canvas, 0, 0, tempCanvas.width, tempCanvas.height);

  const combinedImageUrl = tempCanvas.toDataURL();
  img.src = combinedImageUrl;

  const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarCanva'));
  modal.hide();
  tempCanvas.remove();
}

// FUNCIÓN PARA CONVERTIR IMAGEN A BASE64 CON CANVAS
function convertirImagenA_Base64(imagen) {
  const canvas = document.createElement('canvas');
  const context = canvas.getContext('2d');
  if (!imagen.complete || imagen.naturalWidth === 0) {
    //console.error('No se cargó imagen');
    return '';
  }
  canvas.width = imagen.naturalWidth;
  canvas.height = imagen.naturalHeight;
  context.drawImage(imagen, 0, 0);
  const base64Image = canvas.toDataURL('image/jpeg');
  return base64Image;
}


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


function obtenerImagenes(arrayImagenes) {
  const resultados = {};
  arrayImagenes.forEach(elemento => {
    const imagen = document.querySelector(elemento);
    const nombrePropiedad = elemento.replace('#', ''); 
    if (imagen) {
      const base64Image = convertirImagenA_Base64(imagen);
      resultados[nombrePropiedad] = base64Image;
    } else {
      console.error(`No se encontró imagen: ${elemento}`);
      resultados[nombrePropiedad] = ''; 
    }
  });
  return resultados;
}

function FnAgregarDatosChecklist() {
  try {
    vgLoader.classList.remove('loader-full-hidden');
    // IMAGENES
    const arrayImagenes = ['#imagen1', '#imagen2', '#imagen3', '#imagen4'];
    const imagenes = obtenerImagenes(arrayImagenes); 
    // PREGUNTAS Y RESPUESTAS
    const respuestas = [];
    const arregloRespuestas = [];
    document.querySelectorAll('.actividades > .col-12.mb-2').forEach((actividadContainer) => {
      const preguntaDescripcion = actividadContainer.querySelector('.pregunta');
      if (!preguntaDescripcion) {
        return;
      }
      const descripcion = preguntaDescripcion.innerText;
      const alternativas = actividadContainer.querySelector('#contenedorAlternativas');

      if (!alternativas) {
        return;
      }   
      alternativas.querySelectorAll('.form-check').forEach((item) => {
        const input = item.querySelector('input[type="radio"]');
        const preidInput = item.querySelector('input[type="hidden"]');
        const respuestaLabel = item.querySelector('label');
        estado = item.querySelector('#txtEstado').value;

        if (input && preidInput && respuestaLabel) {
          const preid = preidInput.value;
          const respuesta = respuestaLabel.innerText;
          // CONTEO DE ALTERNATIVAS
          arregloRespuestas.push({input, respuesta});
          if (input.checked) {
            respuestas.push({
              Id: input.id,
              Respuesta: respuesta,
              Preid: preid,
              Descripcion: descripcion,
              Estado: estado
            });
          }
        } else {
          console.warn('Datos incompletos en una pregunta, saltando...');
        }
      });
    });
    // JSON FINAL
    const data = {
      Id: document.getElementById('txtIdChecklist').value,
      imagen1: imagenes.imagen1,
      imagen2: imagenes.imagen2,
      imagen3: imagenes.imagen3,
      imagen4: imagenes.imagen4,
      respuestas
    };
    //console.log(data); 
    //console.log('respuestas: ',arregloRespuestas.length);  
    // ENVIAR DATOS AL SERVIDOR (IMAGENES + RESPUESTAS)
    if(arregloRespuestas.length > 0){
      fetch('/checklist/insert/AgregarChecklist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(datos => {
        // console.log(datos);
        if (!datos.res) {
          throw new Error(datos.msg);
        }
        // Ocultar loader después de un pequeño retraso
        setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
        // console.log(arregloRespuestas.length);
        // Mostrar mensaje de éxito
        return Swal.fire({
          title: "Aviso",
          text: datos.msg,
          icon: "success",
          timer: 2000
        }).then( setTimeout(() => { location.reload(); }, 1000));
      })
      .catch(error => {
        setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
        Swal.fire({
          title: "Error",
          text: error.message,
          icon: "error",
          timer: 2000
        });
      });
    }
    else{
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      // Swal.fire({
      //   title: "Aviso",
      //   text: "No existe respuestas para enviar al servidor",
      //   icon: "info",
      //   timer: 2000
      // });
      // const boton = document.querySelector('#guardarDataEquipo');
      // if (arregloRespuestas.length === 0) {
      //   boton.setAttribute('disabled', 'true'); 
      // } 
    }
  } catch (error) {
    setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    Swal.fire({
      title: "Error",
      text: error.message,
      icon: "error",
      timer: 2000
    });
  }
}

/** MODAL BUSCAR-MODIFICAR ACTIVIDAD */
async function FnModalModificarActividad(actividad) {
  console.log(actividad.getAttribute('dataId'));
  try {
    document.getElementById('txtIdChkActividad').value = actividad.getAttribute('dataId');
    document.getElementById('txtPreid').value = actividad.getAttribute('dataPreId');
    document.getElementById('txtDescripcion').value = actividad.getAttribute('dataDescripcion');
    document.getElementById('txtObservacion').value = actividad.getAttribute('dataObservacion');
    document.getElementById('txtRespuesta').value=actividad.getAttribute('dataRespuesta');
    const formData = new FormData();
    formData.append('preid', document.getElementById('txtPreid').value);
    console.log('Datos enviados:', Object.fromEntries(formData.entries()));
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
      console.log('Datos enviados:', Object.fromEntries(formData.entries()));

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
      setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
      setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
      document.getElementById('msjModicarActividad').innerHTML = `<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error.message}</div>`;
  }
};

function FnListarChecklists(){
  window.location.href='/checklist/CheckLists.php';
  return false;
}

function FnResumenChecklist(){
  id = document.getElementById('txtIdChecklist').value;
  console.log(id);
  if(id > 0){
      window.location.href='/checklist/CheckList.php?id='+id;
  }
  return false;
}




