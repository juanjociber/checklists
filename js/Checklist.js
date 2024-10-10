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
    const $elementoParaConvertir = document.body;

    // Aplicar estilos específicos para PDF
    const originalStyles = document.head.innerHTML; // Guarda los estilos originales
    const pdfStyles = `
      <style>
        body {
          width: 210mm; /* Ancho A4 */
          height: 297mm; /* Alto A4 */
          margin: 0; /* Eliminar márgenes */
        }
        img {
          max-width: 100%; /* Asegurar que las imágenes no excedan el contenedor */
          height: auto; /* Mantener la proporción */
        }
      </style>
    `;
    
    // Añadir estilos para PDF
    document.head.insertAdjacentHTML('beforeend', pdfStyles);

    html2pdf()
      .set({
        margin: -1,
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
          unit: "mm", // Cambiar a mm para un mejor manejo de tamaños
          format: "a4",
          orientation: 'portrait',
          putOnlyUsedFonts: true,
          floatPrecision: 16,
        }
      })
      .from($elementoParaConvertir)
      .save()
      .catch(err => console.log(err))
      .finally(() => {
        // Restaurar estilos originales
        document.head.innerHTML = originalStyles; 
        console.log('Guardado');
      });
  });
});






