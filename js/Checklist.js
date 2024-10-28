const vgLoader = document.querySelector('.container-loader-full');

window.onload = function() {
  document.getElementById('MenuCheckLists').classList.add('menu-activo','fw-bold');
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
      const response = await fetch('/checklists/update/FinalizarCheckList.php', {
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
    window.location.href='/checklists/EditarCheckListDatos.php?id='+id;
  }
  return false;
}

function FnListarChecklists(){
  window.location.href='/checklists/CheckLists.php';
  return false;
}

document.addEventListener("DOMContentLoaded", () => {
  const $boton = document.querySelector("#btnCrearPdf");
  $boton.addEventListener("click", () => {
    const $elementoParaConvertir = document.body; 
    const elementosNoPdf = document.querySelectorAll('.no-pdf');
    elementosNoPdf.forEach(el => el.style.display = 'none');

    const isMobile = window.innerWidth < 768;
    const margins = isMobile ? [1, 0, 0.5, 0] : [1, 0.2, 0.5, 0.2];

    const opt = {
      margin: margins,
      filename: 'CheckList.pdf',
      image: { type: 'jpeg', quality: 1 },
      html2canvas: { 
        scale: 2, // Ajusta este valor para mejorar la resolución
        letterRendering: true,
        useCORS: true // Habilita CORS si usas imágenes externas
      },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    const fechaActual = new Date().toLocaleDateString(); 
    const numeroChecklist = 'CHK-01'; 
    const nombreEmpresa = 'GPEM S.A.C'; 
    const direccion = 'Av. Los Incas, Patio Norte Metropolitano, Comas 15313'; 
    const telefono = 'Teléfono: (01) 7130628 / 7130629';
    const equipo = document.querySelector('#txtEquNombre').textContent; 

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
          pdf.setTextColor(100, 100, 100);

          const headerYPosition = 0.5; 
          const logoX = 0.1; 
          const titleX = pdf.internal.pageSize.getWidth() / 2; 
          const dateX = pdf.internal.pageSize.getWidth() - 0.2; 

          // Logo
          pdf.addImage(logoUrl, 'JPEG', logoX, headerYPosition - 0.2, 1.5, 0.5, null, 'FAST'); 

          // Título
          pdf.text(`CheckList de ${equipo}`, titleX, headerYPosition, { align: 'center' });

          // Fecha y número de checklist
          pdf.setFontSize(10);
          pdf.text(`Fecha Actual: ${fechaActual}`, dateX, headerYPosition, { align: 'right' }); 
          pdf.text(`CheckList: ${numeroChecklist}`, dateX, headerYPosition + 0.2, { align: 'right' }); 
          
          // NÚMERO DE PÁGINA Y PIE DE PÁGINA
          const pageNumberYPosition = pdf.internal.pageSize.getHeight() - 0.75; 
          pdf.setTextColor(100, 100, 100); 
          pdf.text(`Página ${i} de ${totalPages}`, pdf.internal.pageSize.getWidth() / 2, pageNumberYPosition, { align: 'center' });

          // INFORMACIÓN DE EMPRESA
          const footerYPosition = pageNumberYPosition + 0.25; 
          pdf.setFontSize(10); 
          const footerText1 = `${direccion} - ${nombreEmpresa}`;
          const footerText2 = telefono;
          const footerYPosition1 = footerYPosition + 0.2; 
          const footerYPosition2 = footerYPosition1 + 0.15; 
          
          pdf.text(footerText1, pdf.internal.pageSize.getWidth() / 2, footerYPosition1, { align: 'center' });
          pdf.text(footerText2, pdf.internal.pageSize.getWidth() / 2, footerYPosition2, { align: 'center' });
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



























