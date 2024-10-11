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
    // ELEMENTO QUE SE CONVERTIRA A PDF
    const $elementoParaConvertir = document.body; 
    // OCULTAR ELEMENTOS CON LA CLASE 'no-pdf'
    const elementosNoPdf = document.querySelectorAll('.no-pdf');
    elementosNoPdf.forEach(el => el.style.display = 'none');

    const opt = {
      margin: [1.2, -1, 0.5, -1],  
      filename: 'CheckList.pdf',
      image: { type: 'jpeg', quality: 1 },
      html2canvas: { scale: 4, letterRendering: true },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };
    const fechaActual = new Date().toLocaleDateString(); 
    const numeroChecklist = 'CHK-01'; 
    const nombreEmpresa = 'GPEM S.A.C'; 
    const direccion = 'Av. Los Incas, Comas 15313'; 
    const telefono = 'Teléfono: (01)7130628 / 7130629'; 

    html2pdf()
      .set(opt)
      .from($elementoParaConvertir)
      .toPdf()
      .get('pdf')
      .then(function (pdf) {
        const totalPages = pdf.internal.getNumberOfPages();
        const logoUrl = '/mycloud/logos/logo-gpem.png'; 
        for (let i = 1; i <= totalPages; i++) {
          pdf.setPage(i);
          pdf.setFontSize(12);
          // ENCABEZADO
          pdf.setTextColor(100, 100, 100);
          pdf.text('CHECKLIST DE "EQUIPO"', pdf.internal.pageSize.getWidth() / 2, 1, { align: 'center' });
          // LOGO
          pdf.addImage(logoUrl, 'JPEG', 0.1, 0.5, 1.5, 0.5, null, 'FAST'); 
          
          // FECHA - NOMBRE
          pdf.setFontSize(10);
          pdf.setTextColor(100, 100, 100); 
          const textYPosition = 0.5 + 0.5 / 2; 
          pdf.text(`Fecha Actual: ${fechaActual}`, pdf.internal.pageSize.getWidth() - 0.2, textYPosition, { align: 'right' }); 
          pdf.text(`Nombre: ${numeroChecklist}`, pdf.internal.pageSize.getWidth() - 0.7, textYPosition + 0.2, { align: 'right' }); 
          
          // NÚMERO DE PÁGINA Y PIE DE PÁGINA
          const pageNumberYPosition = pdf.internal.pageSize.getHeight() - 0.6; 
          pdf.setTextColor(100, 100, 100); 
          pdf.text(`Página ${i} de ${totalPages}`, pdf.internal.pageSize.getWidth() / 2, pageNumberYPosition, { align: 'center' });

          // INFORMACIÓN DE EMPRESA
          const footerYPosition = pageNumberYPosition + 0.14; 
          pdf.setTextColor(100, 100, 100); 
          pdf.text(nombreEmpresa, pdf.internal.pageSize.getWidth() / 2, footerYPosition + 0, { align: 'center' });
          pdf.text(telefono, pdf.internal.pageSize.getWidth() / 2, footerYPosition + 0.15, { align: 'center' });
          pdf.text(direccion, pdf.internal.pageSize.getWidth() / 2, footerYPosition + 0.3, { align: 'center' });
        }
      })
      .save()
      .then(() => {
        elementosNoPdf.forEach(el => el.style.display = '');
      })
      .catch(err => {
        console.log(err);
        elementosNoPdf.forEach(el => el.style.display = '');
      });
  });
});






















