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
  if(id > 0){
    window.location.href='/checklist/EditarCheckListDatos.php?id='+id;
  }
  return false;
}

function FnListarChecklists(){
  window.location.href='/checklist/CheckLists.php';
  return false;
}

document.addEventListener("DOMContentLoaded", () => {
  const $boton = document.querySelector("#btnCrearPdf");
  $boton.addEventListener("click", () => {
    const $elementoParaConvertir = document.body; // Elemento a convertir
    const $carrusel = document.querySelector("#miCarrusel"); // Cambia esto al selector correcto
    const $imagenes = document.querySelectorAll(".imagen"); // Cambia esto al selector correcto

    // Ocultar el carrusel
    $carrusel.style.display = "none";
    
    // Mostrar las imágenes
    $imagenes.forEach(img => img.style.display = "block");

    html2pdf()
      .set({
        margin: 0.5,
        filename: 'CheckList.pdf',
        image: {
          type: 'jpeg',
          quality: 1
        },
        html2canvas: {
          scale: 4,
          letterRendering: true,
        },
        jsPDF: {
          unit: "in",
          format: "a4",
          orientation: 'portrait',
          putOnlyUsedFonts: true,
          floatPrecision: 16,
          pageSize: 'A4',
          header: function (data) {
            return {
              text: 'Encabezado de la Página', // Encabezado personalizado
              align: 'center',
              margin: [0, 10, 0, 0], // márgenes
            };
          },
          footer: function (data) {
            return {
              text: `Página ${data.pageNumber}`, // Pie de página personalizado
              align: 'center',
              margin: [0, 0, 0, 10], // márgenes
            };
          }
        }
      })
      .from($elementoParaConvertir)
      .save()
      .catch(err => console.log(err))
      .finally(() => {
        // Restaurar el estado original
        $carrusel.style.display = ""; // Mostrar el carrusel nuevamente
        $imagenes.forEach(img => img.style.display = ""); // Restaurar el estado original de las imágenes
        console.log('Guardado');
      });
  });
});






