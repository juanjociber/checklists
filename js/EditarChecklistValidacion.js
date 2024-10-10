// const vgLoader = document.querySelector('.container-loader-full');
window.onload = function() {
  //document.getElementById('MenuInformes').classList.add('menu-activo','fw-bold');
  // vgLoader.classList.add('loader-full-hidden');
};
// Función para limpiar el canvas
function FnEliminarFirma(tipo) {
  const canvasId = tipo === 1 ? 'canvasEmpFirma' : 'canvasCliFirma';
  const canvas = document.getElementById(canvasId);
  const context = canvas.getContext('2d');
  context.clearRect(0, 0, canvas.width, canvas.height);
}

/** GUARDAR FIRMA */
async function FnAgregarFirma(tipo) {
  try {
    // vgLoader.classList.remove('loader-full-hidden');
    const canvasId = tipo === 'emp' ? 'canvasEmpFirma' : 'canvasCliFirma';
    const canvas = document.getElementById(canvasId);
    const dataURL = canvas.toDataURL('image/jpeg'); 
    const formData = new FormData();
    formData.append('id', document.getElementById('txtIdChecklist').value);
    formData.append('archivo', dataURL);
    formData.append('tipo', tipo); 
    console.log('Datos enviados:', Object.fromEntries(formData.entries()));

    const response = await fetch('/checklist/insert/AgregarCheckListFirma.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`${response.status} ${response.statusText}`);
    }
    // setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    const datos = await response.json(); 
    console.log(datos);

    if (!datos.res) {
      throw new Error(datos.msg);
    }
    Swal.fire({
      icon: 'success',
      title: 'Éxito',
      text: datos.msg,
    });
    setTimeout(() => { location.reload(); }, 1000);
  } catch (error) {
    // setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message,
    });
  }
}

// INICIAR CANVAS
function iniciarCanvas() {
  const canvases = ['canvasEmpFirma', 'canvasCliFirma'];
  canvases.forEach(canvasId => {
    const canvas = document.getElementById(canvasId);
    const context = canvas.getContext('2d');

    // Ajustar el tamaño del canvas para que sea preciso
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;

    // Establecer el color de fondo y el color del trazo
    context.fillStyle = 'white'; // Color de fondo
    context.fillRect(0, 0, canvas.width, canvas.height); // Rellenar el canvas con el color de fondo
    context.strokeStyle = 'black'; // Color del trazo
    context.lineWidth = 2; // Ancho del trazo

    let drawing = false;
    function getMousePos(canvas, event) {
      const rect = canvas.getBoundingClientRect();
      return {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top
      };
    }
    canvas.addEventListener('mousedown', (event) => {
      drawing = true;
      context.beginPath();
      const pos = getMousePos(canvas, event);
      context.moveTo(pos.x, pos.y);
    });
    canvas.addEventListener('mousemove', (event) => {
      if (!drawing) return;
      const pos = getMousePos(canvas, event);
      context.lineTo(pos.x, pos.y);
      context.stroke();
    });
    canvas.addEventListener('mouseup', () => {
      drawing = false;
      context.closePath();
    });

    canvas.addEventListener('mouseleave', () => {
      drawing = false;
      context.closePath();
    });
    // Soporte para dispositivos táctiles
    canvas.addEventListener('touchstart', (event) => {
      event.preventDefault();
      drawing = true;
      context.beginPath();
      const pos = getMousePos(canvas, event.touches[0]);
      context.moveTo(pos.x, pos.y);
    });
    canvas.addEventListener('touchmove', (event) => {
      event.preventDefault();
      if (!drawing) return;
      const pos = getMousePos(canvas, event.touches[0]);
      context.lineTo(pos.x, pos.y);
      context.stroke();
    });
    canvas.addEventListener('touchend', () => {
      drawing = false;
      context.closePath();
    });
    canvas.addEventListener('touchcancel', () => {
      drawing = false;
      context.closePath();
    });
  });
}

window.onload = iniciarCanvas;

function FnListarChecklists(){
  window.location.href='/checklist/CheckLists.php';
  return false;
}

function FnResumenChecklist(){
  id = document.getElementById('txtIdChecklist').value;
  if(id > 0){
      window.location.href='/checklist/CheckList.php?id='+id;
  }
  return false;
}





