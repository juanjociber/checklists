function FnAgregarDatosChecklist() {
    try {
      const arrayImagenes = ['#imagen1', '#imagen2', '#imagen3', '#imagen4'];
  
      const imagenes = obtenerImagenes(arrayImagenes); 
  
      const respuestas = Array.from(document.querySelectorAll('.actividades > .col-12.mb-2'))
        .map((actividadContainer) => {
          const preguntaDescripcion = actividadContainer.querySelector('.pregunta')?.innerText;
          if (!preguntaDescripcion) return null;
  
          const alternativas = Array.from(actividadContainer.querySelectorAll('#contenedorAlternativas .form-check'))
            .map(item => {
              const input = item.querySelector('input[type="radio"]');
              const preidInput = item.querySelector('input[type="hidden"]');
              const respuestaLabel = item.querySelector('label')?.innerText;
              const estado = item.querySelector('#txtEstado')?.value;
  
              if (input?.checked && preidInput && respuestaLabel) {
                return {
                  Id: input.id,                // ID del input
                  Respuesta: respuestaLabel,   // Texto de la respuesta
                  Preid: preidInput.value,     // Valor oculto
                  Descripcion: preguntaDescripcion, // Pregunta asociada
                  Estado: estado || 'N/A'      // Estado, si existe, sino 'N/A'
                };
              }
              return null;
            })
            .filter(Boolean); // Filtramos las alternativas no seleccionadas
  
          return alternativas.length > 0 ? alternativas : null;
        })
        .flat()
        .filter(Boolean); // Filtramos las respuestas vacías
  
      // Estructura final del objeto 'data' para enviar
      const data = {
        Id: document.getElementById('txtIdChecklist').value, // ID del checklist
        Imagenes: imagenes, // Incluimos las imágenes
        Respuestas: respuestas // Aquí están las respuestas obtenidas
      };
  
      console.log(data); // Para verificar que se esté estructurando bien
  
      // Enviamos los datos mediante Fetch a tu controlador PHP
      fetch('/checklist/insert/AgregarChecklist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data) // Convertimos los datos a JSON
      })
      .then(response => response.json())
      .then(datos => {
        if (!datos.res) throw new Error(datos.msg);
        setTimeout(() => { vgLoader.classList.add('loader-full-hidden'); }, 300);
        Swal.fire({
          title: "Aviso",
          text: datos.msg,
          icon: "success",
          timer: 2000
        }).then(() => {
          // setTimeout(() => { location.reload(); }, 1000);
        });
      })
      .catch(error => {
        handleFetchError(error); // Manejo de errores
      });
    } catch (error) {
      handleFetchError(error); // Manejo de errores
    }
  }
  