<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDF Reader</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
</head>
<body>
  <!-- Contenedor para el visor de PDF -->
  <div id="pdfViewer"></div>

  <!-- Input para cargar el archivo PDF -->
  <input type="file" id="inputPDF" accept=".pdf">

  <!-- Contenedor para mostrar el resultado -->
  <div id="resultado"></div>

  <script>
    // Función para extraer texto del PDF entre dos palabras específicas
    function extraerTextoEntrePalabras(pdfUrl, palabraInicio, palabraFin) {
      // Carga el archivo PDF
      pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        // Variable para almacenar el texto extraído
        let textoExtraido = "";

        // Itera sobre cada página del PDF
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
          pdf.getPage(pageNum).then(function(page) {
            // Extrae el texto de la página
            page.getTextContent().then(function(textContent) {
              // Itera sobre cada elemento de texto en la página
              textContent.items.forEach(function(item) {
                // Si encontramos la palabra de inicio, comenzamos a capturar texto
                if (item.str.includes(palabraInicio)) {
                  // Establece un indicador para comenzar a capturar texto
                  let capturandoTexto = true;
                  // Agrega el texto de inicio al texto extraído
                  textoExtraido += item.str.replace(palabraInicio, "") + " ";
                  
                  // Continúa iterando a través de los elementos de texto hasta encontrar la palabra de fin
                  for (let i = textContent.items.indexOf(item) + 1; i < textContent.items.length; i++) {
                    const nextItem = textContent.items[i];
                    // Si encontramos la palabra de fin, detenemos la captura de texto
                    if (nextItem.str.includes(palabraFin)) {
                      capturandoTexto = false;
                      break;
                    }
                    // Agrega el texto al texto extraído
                    if (capturandoTexto) {
                      textoExtraido += nextItem.str + " ";
                    }
                  }
                }
              });
              // Evalúa el texto extraído y muestra el resultado
              mostrarResultado(textoExtraido.trim());
            });
          });
        }
      });
    }

    // Función para mostrar el resultado según el texto extraído
    function mostrarResultado(textoExtraido) {
      // Evalúa si el texto contiene "POSITIVO" o "NEGATIVO" y muestra el resultado correspondiente
      if (textoExtraido.includes("POSITIVO")) {
        window.location.href="hola.php"
      } else if (textoExtraido.includes("NEGATIVO")) {
        document.getElementById("resultado").textContent = "Según la información proporcionada por el SAT, hay problemas identificados en tu situación fiscal que requieren atención.";
      } else {
        document.getElementById("resultado").textContent = "No se encontró ninguna coincidencia por favor sube una opinion valida";
      }
    }

    // Manejador de eventos para cuando se selecciona un archivo
    document.getElementById("inputPDF").addEventListener("change", function(event) {
      const archivo = event.target.files[0];
      if (archivo) {
        // Crea un objeto URL para el archivo seleccionado
        const archivoURL = URL.createObjectURL(archivo);
        // Llama a la función para extraer texto del PDF entre las palabras específicas
        extraerTextoEntrePalabras(archivoURL, "Clave R.F.C", "Folio", "Denominacion", "Razon Social", "Sello Digital");
      }
    });
  </script>
</body>
</html>