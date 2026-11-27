document.addEventListener('DOMContentLoaded', () => {
    // 1. Contador de caracteres en tiempo real para la descripción
    const textareaDescripcion = document.getElementById('descripcionAtencion');
    const contadorTexto = document.getElementById('contadorTexto');

    const actualizarContador = () => {
        contadorTexto.textContent = textareaDescripcion.value.length;
    };

    actualizarContador();
    textareaDescripcion.addEventListener('input', actualizarContador);

    // 2. Funcionalidad de arrastrar y soltar archivos (Dropzone)
    const zonaArrastre = document.getElementById('zonaArrastre');
    const inputArchivo = document.getElementById('inputArchivo');
    const nombreArchivoSubido = document.getElementById('nombreArchivoSubido');

    // Abrir selector al hacer clic en la zona
    zonaArrastre.addEventListener('click', () => inputArchivo.click());

    // Función para restaurar el estilo original de la zona de arrastre
    const restaurarZonaArrastre = () => {
        nombreArchivoSubido.textContent = 'Selecciona un archivo o arrástralo aquí';
        zonaArrastre.style.borderColor = '#8ec7c0';
        zonaArrastre.style.backgroundColor = '#f2f9f8';
    };

    // Validar y mostrar nombre del archivo seleccionado
    const manejarArchivo = (archivo) => {
        if (!archivo) return;

        const tamanoMaximo = 5 * 1024 * 1024; // 5 MB en bytes
        if (archivo.size > tamanoMaximo) {
            alert('El archivo supera el límite máximo permitido de 5 MB.');
            inputArchivo.value = '';
            restaurarZonaArrastre();
            return;
        }

        nombreArchivoSubido.textContent = `Archivo listo: ${archivo.name}`;
        zonaArrastre.style.borderColor = '#287c73';
        zonaArrastre.style.backgroundColor = '#d6ece9';
    };

    inputArchivo.addEventListener('change', (e) => {
        manejarArchivo(e.target.files[0]);
    });

    // Eventos Drag & Drop
    zonaArrastre.addEventListener('dragover', (e) => {
        e.preventDefault();
        zonaArrastre.classList.add('arrastrando');
    });

    zonaArrastre.addEventListener('dragleave', () => {
        zonaArrastre.classList.remove('arrastrando');
    });

    zonaArrastre.addEventListener('drop', (e) => {
        e.preventDefault();
        zonaArrastre.classList.remove('arrastrando');
        if (e.dataTransfer.files.length > 0) {
            inputArchivo.files = e.dataTransfer.files;
            manejarArchivo(e.dataTransfer.files[0]);
        }
    });

    // 3. Envío del formulario mediante Fetch a PHP
    const formulario = document.getElementById('formActualizarSalud');
    const lineaTiempo = document.querySelector('.linea-tiempo');
    const listaArchivos = document.querySelector('.lista-archivos');

    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btnGuardar = formulario.querySelector('.btn-guardar');
        const textoOriginalBtn = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="ph ph-spinner"></i> Guardando...';

        const datosFormulario = new FormData(formulario);

        try {
            const respuesta = await fetch('guardar_salud.php', {
                method: 'POST',
                body: datosFormulario
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                const fechaRaw = datosFormulario.get('fecha_revision');
                const estadoSalud = datosFormulario.get('estado_salud');
                const [anio, mes, dia] = fechaRaw.split('-');
                const fechaFormateada = `${dia}/${mes}/${anio}`;

                // Agregar visualmente al historial de salud
                const nuevoItem = document.createElement('li');
                nuevoItem.className = 'item-tiempo';
                nuevoItem.innerHTML = `
                    <span class="punto-tiempo"></span>
                    <div class="detalle-tiempo">
                        <span class="fecha">${fechaFormateada}</span>
                        <p>Control veterinario (${estadoSalud})</p>
                    </div>
                    <span class="estado-badge completado">Registrado</span>
                `;
                lineaTiempo.prepend(nuevoItem);

                // Si se subió un archivo, agregarlo al widget "Últimos archivos"
                if (resultado.archivo_subido) {
                    const esPdf = resultado.archivo_subido.toLowerCase().endsWith('.pdf');
                    const nuevoDoc = document.createElement('li');
                    nuevoDoc.className = 'item-archivo';
                    nuevoDoc.innerHTML = `
                        <div class="icono-doc ${esPdf ? 'pdf' : 'img'}">
                            <i class="ph ${esPdf ? 'ph-file-pdf' : 'ph-image'}"></i>
                        </div>
                        <div class="info-doc">
                            <strong>${resultado.archivo_subido}</strong>
                            <span>${fechaFormateada} • Nuevo</span>
                        </div>
                    `;
                    listaArchivos.prepend(nuevoDoc);
                }

                alert(resultado.mensaje);
            } else {
                alert('Error: ' + resultado.mensaje);
            }
        } catch (error) {
            console.error('Error en la petición:', error);
            alert('No se pudo conectar con el servidor PHP.');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginalBtn;
        }
    });

    // 4. Botón cancelar (restaura formulario y estilos de la zona de archivo)
    document.getElementById('btnCancelar').addEventListener('click', () => {
        if (confirm('¿Deseas descartar los cambios realizados en la consulta?')) {
            formulario.reset();
            actualizarContador();
            restaurarZonaArrastre();
        }
    });
});