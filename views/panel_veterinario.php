<?php
// 1. Incluir la conexión real a la base de datos
require_once '../configuracion/conexion_ejemplo.php';

// 2. Simulación de sesión
$nombre_vet = "Dra. Laura Aroca";
$nombre_corto = "Dra. Laura";
$rol_vet = "Veterinaria";

// 3. CONSULTAS PARA LAS 4 TARJETAS DE ESTADÍSTICAS (KPIs)
// Contar mascotas en refugio
$sql_mascotas = "SELECT COUNT(*) AS total FROM mascota WHERE estado = 'En refugio'";
$total_mascotas = $conn->query($sql_mascotas)->fetch_assoc()['total'];

// Contar citas próximas
$sql_citas = "SELECT COUNT(*) AS total FROM consulta_veterinaria WHERE fecha_consulta >= NOW()";
$total_citas = $conn->query($sql_citas)->fetch_assoc()['total'];

// Contar reportes médicos
$sql_reportes = "SELECT COUNT(*) AS total FROM consulta_veterinaria";
$total_reportes = $conn->query($sql_reportes)->fetch_assoc()['total'];

// Contar adopciones de este mes
$sql_adopciones = "SELECT COUNT(*) AS total FROM solicitud_adopcion WHERE MONTH(fecha_solicitud) = MONTH(CURDATE()) AND YEAR(fecha_solicitud) = YEAR(CURDATE())";
$adopciones_mes = $conn->query($sql_adopciones)->fetch_assoc()['total'];

// 4. CONSULTA PARA LA TABLA DE PRÓXIMAS CITAS (Trae las 4 más cercanas)
$sql_lista_citas = "SELECT c.consulta_veterinaria_id AS id_cita, c.fecha_consulta AS fecha_hora, c.diagnostico AS motivo, m.nombre AS nombre_mascota, m.foto
                    FROM consulta_veterinaria c
                    INNER JOIN mascota m ON c.mascota_id = m.mascota_id
                    WHERE c.fecha_consulta >= NOW()
                    ORDER BY c.fecha_consulta ASC
                    LIMIT 4";
$resultado_citas = $conn->query($sql_lista_citas);

// 5. CONSULTA PARA EL SELECT DEL FORMULARIO (Todas las mascotas)
$sql_select_mascotas = "SELECT mascota_id, nombre, foto FROM mascota ORDER BY nombre ASC";
$resultado_mascotas = $conn->query($sql_select_mascotas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonPet - Panel Veterinario</title>
    <link rel="stylesheet" href="../assets/css/estilo_panel_veterinario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

    <!-- [PHP: VALIDACIÓN DE SESIÓN] Al inicio del archivo (antes del <!DOCTYPE html>) 
         necesitarás session_start() para verificar que el usuario haya iniciado sesión 
         y tenga el rol de "Veterinario". Si no, redirigir al login. -->

    <!-- =====================================================================
         1. BARRA SUPERIOR (ENCABEZADO / NAVBAR)
         ===================================================================== -->
    <header class="encabezado-superior">
        
        <div class="marca-logo">
            <img src="ruta/logo-huella.png" alt="Logo MonPet" class="icono-logo">
            <div class="texto-logo">
                <span class="titulo-marca">MonPet</span>
                <p class="eslogan-marca">Amor que cambia vidas</p>
            </div>
        </div>

        <!-- Barra de búsqueda central -->
        <div class="contenedor-buscador">
            <!-- [PHP: BUSCADOR] Este formulario enviará la palabra clave por GET (ej. buscar.php?busqueda=Buddy)
                 para hacer una consulta con LIKE en la base de datos de mascotas, historias o refugios. -->
            <form action="buscar.php" method="GET" class="formulario-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="busqueda" class="campo-busqueda" placeholder="Buscar mascota, historia o refugio...">
            </form>
        </div>

        <!-- Acciones derechas: Notificaciones y Perfil -->
        <div class="acciones-usuario">
            <!-- [PHP: NOTIFICACIONES] Opcional: Consultar si hay notificaciones sin leer para mostrar un punto rojo o contador -->
            <button type="button" class="boton-notificaciones" aria-label="Notificaciones">
                <i class="fa-solid fa-bell"></i>
            </button>

            <!-- [PHP: DATOS DEL USUARIO LOGUEADO] Imprimir desde $_SESSION la foto, 
                 el nombre completo ("Dra. Laura Aroca") y el rol ("Veterinaria"). -->
            <div class="perfil-usuario">
                <img src="ruta/avatar-doctora.jpg" alt="Foto de Dra. Laura Aroca" class="avatar-usuario">
                <div class="info-usuario">
                    <span class="nombre-usuario">Dra. Laura Aroca</span>
                    <span class="rol-usuario">Veterinaria</span>
                </div>
                <span class="flecha-desplegable">⌄</span>
            </div>
        </div>

    </header>


    <!-- =====================================================================
         2. CONTENEDOR GENERAL DEL DASHBOARD
         ===================================================================== -->
    <div class="contenedor-principal">

        <!-- =================================================================
             2.1 MENÚ LATERAL IZQUIERDO (SIDEBAR)
             ================================================================= -->
        <aside class="barra-lateral">
            <nav class="navegacion-principal">
                <ul class="lista-menu">
                    <!-- [PHP: NAVEGACIÓN] Cambiar los href="#..." por los archivos .php reales 
                         (ej. index.php, citas.php, historias.php) y usar una condición PHP 
                         para poner la clase 'activo' según la página actual. -->
                    <li class="item-menu activo">
                        <a href="index.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-house"></i></span>
                            <span class="texto-menu">Inicio</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="citas.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-calendar"></i>
                            </span>
                            <span class="texto-menu">Citas veterinarias</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="historias.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-file-lines"></i>
                            </span>
                            <span class="texto-menu">Historias clinicas</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="actualizar_salud.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-stethoscope"></i>
                            </span>
                            <span class="texto-menu">Actualizar salud</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="jornadas.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-hospital"></i>
                            </span>
                            <span class="texto-menu">Jornadas</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="mascotas.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-paw"></i>
                            </span>
                            <span class="texto-menu">Mascotas</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="reportes.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-chart-bar"></i>
                            </span>
                            <span class="texto-menu">Reportes</span>
                        </a>
                    </li>
                    <li class="item-menu">
                        <a href="mensajes.php" class="enlace-menu">
                            <span class="icono-menu">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <span class="texto-menu">Mensajes</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>


        <!-- =================================================================
             2.2 ÁREA DE CONTENIDO PRINCIPAL
             ================================================================= -->
        <main class="contenido-dashboard">

            <!-- SECCIÓN A: BANNER DE BIENVENIDA -->
            <section class="banner-bienvenida">
                <div class="texto-bienvenida">
                    <!-- [PHP: SALUDO DINÁMICO] Imprimir el nombre corto desde $_SESSION['nombre'] -->
                    <h1 class="titulo-bienvenida">¡Hola, Dra. Laura!</h1>
                    <p class="subtitulo-bienvenida">Gracias por ser parte de MonPet y ayudar a nuestras mascotas.</p>
                    <a href="/MonPet/MonPet-Veterinario/views/actividad_reciente.php" class="boton-actividades">Ver tus actividades →</a>
                </div>
                <div class="imagen-bienvenida">
                    <img src="ruta/perro-y-gato.jpg" alt="Perro y gato juntos" class="foto-banner">
                </div>
            </section>


            <!-- SECCIÓN B: TARJETAS DE ESTADÍSTICAS (KPIs) -->
            <!-- [PHP: CONSULTAS COUNT()] Aquí necesitas 4 consultas SQL con SELECT COUNT(*) 
                 para mostrar los totales reales en tiempo real en lugar de números fijos. -->
            <section class="seccion-estadisticas">
                <!-- Tarjeta 1: Total mascotas disponibles/en refugio -->
                <article class="tarjeta-estadistica">
                    <div class="icono-estadistica">
                        <i class="fa-solid fa-paw"></i>
                    </div>
                    <div class="datos-estadistica">
                        <span class="numero-estadistica"><?php echo $total_mascotas; ?></span>
                        <p class="etiqueta-estadistica">Mascotas en refugio</p>
                    </div>
                </article>

                <!-- Tarjeta 2: Total citas pendientes de esta veterinaria -->
                <article class="tarjeta-estadistica">
                    <div class="icono-estadistica">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="datos-estadistica">
                        <span class="numero-estadistica"><?php echo $total_citas; ?></span>
                        <p class="etiqueta-estadistica">Citas proximas</p>
                    </div>
                </article>

                <!-- Tarjeta 3: Total reportes médicos creados -->
                <article class="tarjeta-estadistica">
                    <div class="icono-estadistica">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <div class="datos-estadistica">
                        <span class="numero-estadistica"><?php echo $total_reportes; ?></span>
                        <p class="etiqueta-estadistica">Consultas medicas</p>
                    </div>
                </article>

                <!-- Tarjeta 4: Total adopciones filtradas por el mes actual (MONTH(fecha) = MONTH(CURRENT_DATE)) -->
                <article class="tarjeta-estadistica">
                    <div class="icono-estadistica">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div class="datos-estadistica">
                        <span class="numero-estadistica"><?php echo $adopciones_mes; ?></span>
                        <p class="etiqueta-estadistica">Solicitudes de adopcion este mes</p>
                    </div>
                </article>
            </section>


            <!-- SECCIÓN C: FILA MEDIA (Tabla de citas + Actualizaciones recientes) -->
            <div class="fila-intermedia">

                <!-- Columna Izquierda: Tabla de Mis próximas citas -->
                <section class="tarjeta-panel panel-citas">
                    <div class="cabecera-panel">
                        <h2 class="titulo-panel"><i class="fa-solid fa-clock"></i> Mis próximas citas</h2>
                        <a href="citas.php" class="boton-ver-todas">Ver todas →</a>
                    </div>

                    <table class="tabla-citas">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Mascota</th>
                                <th>Motivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- [PHP: BUCLE FOREACH / WHILE DE CITAS] 
                                 Hacer un SELECT con JOIN entre las tablas 'citas' y 'mascotas' (LIMIT 4), 
                                 ordenado por fecha ascendente. Recorrer los resultados para generar cada <tr>.
                                 En el botón "Ver →", pasar el ID de la cita por URL: detalle_cita.php?id=<?php echo $cita['id']; ?> -->
                            <tr>
                                <td class="fecha-cita">16/10/2026 9:00 am</td>
                                <td class="celda-mascota">
                                    <img src="ruta/buddy.jpg" alt="Buddy" class="miniatura-mascota">
                                    <span>Buddy</span>
                                </td>
                                <td>Chequeo general</td>
                                <td><a href="detalle_cita.php?id=1" class="boton-ver">Ver →</a></td>
                            </tr>
                            <tr>
                                <td class="fecha-cita">20/10/2026 11:00 am</td>
                                <td class="celda-mascota">
                                    <img src="ruta/michi.jpg" alt="Michi" class="miniatura-mascota">
                                    <span>Michi</span>
                                </td>
                                <td>Vacunación</td>
                                <td><a href="detalle_cita.php?id=2" class="boton-ver">Ver →</a></td>
                            </tr>
                            <tr>
                                <td class="fecha-cita">21/10/2026 2:00 pm</td>
                                <td class="celda-mascota">
                                    <img src="ruta/max.jpg" alt="Max" class="miniatura-mascota">
                                    <span>Max</span>
                                </td>
                                <td>Revisión medica</td>
                                <td><a href="detalle_cita.php?id=3" class="boton-ver">Ver →</a></td>
                            </tr>
                            <tr>
                                <td class="fecha-cita">21/10/2026 4:00 pm</td>
                                <td class="celda-mascota">
                                    <img src="ruta/luna.jpg" alt="Luna" class="miniatura-mascota">
                                    <span>Luna</span>
                                </td>
                                <td>Vacunación</td>
                                <td><a href="detalle_cita.php?id=4" class="boton-ver">Ver →</a></td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Columna Derecha: Actualizaciones recientes -->
                <section class="tarjeta-panel panel-actualizaciones">
                    <div class="cabecera-panel">
                        <h2 class="titulo-panel">Actualizaciones recientes</h2>
                        <a href="actualizaciones.php" class="boton-ver-todas">Ver todas →</a>
                    </div>

                    <!-- [PHP: BUCLE DE ACTIVIDAD / LOGS] 
                         Consultar los últimos 3 registros de una tabla de historial/bitácora 
                         (ORDER BY fecha_registro DESC LIMIT 3). 
                         Usar un condicional if/switch en PHP para asignar la clase CSS de la etiqueta 
                         ('etiqueta-salud' o 'etiqueta-refugio') según el tipo de evento. -->
                    <ul class="lista-actualizaciones">
                        <li class="item-actualizacion">
                            <img src="ruta/luna.jpg" alt="Luna" class="miniatura-actualizacion">
                            <div class="detalle-actualizacion">
                                <p class="texto-actualizacion">Se actualizó la salud de Luna</p>
                                <span class="tiempo-actualizacion">Hace una hora</span>
                            </div>
                            <span class="etiqueta-categoria etiqueta-salud">Salud</span>
                        </li>

                        <li class="item-actualizacion">
                            <img src="ruta/nueva-mascota.jpg" alt="Nueva mascota" class="miniatura-actualizacion">
                            <div class="detalle-actualizacion">
                                <p class="texto-actualizacion">Nueva mascota en el refugio</p>
                                <span class="tiempo-actualizacion">Hace dos horas</span>
                            </div>
                            <span class="etiqueta-categoria etiqueta-refugio">Refugio</span>
                        </li>

                        <li class="item-actualizacion">
                            <img src="ruta/max.jpg" alt="Max" class="miniatura-actualizacion">
                            <div class="detalle-actualizacion">
                                <p class="texto-actualizacion">Reporte médico de Max</p>
                                <span class="tiempo-actualizacion">Hace dos horas</span>
                            </div>
                            <span class="etiqueta-categoria etiqueta-salud">Salud</span>
                        </li>
                    </ul>
                </section>

            </div>


            <!-- SECCIÓN D: FORMULARIO INFERIOR (Actualizar salud de mascota) -->
            <section class="tarjeta-panel panel-formulario-salud">
                <h2 class="titulo-formulario">Actualizar salud de mascota</h2>

                <!-- [PHP: PROCESAMIENTO POST (INSERT / UPDATE)] 
                     El atributo action apunta al archivo controlador (ej. procesar_salud.php) que recibirá 
                     $_POST['id_mascota'], $_POST['observaciones'], $_POST['estado_salud'] y el array $_POST['condiciones'].
                     Ese archivo guardará el registro en la tabla de historias clínicas/salud, actualizará el estado 
                     de la mascota y redirigirá de vuelta con un mensaje de éxito. -->
                <form action="procesar_salud.php" method="POST" class="formulario-salud">
                    
                    <!-- Columna 1: Foto grande de la mascota -->
                    <div class="columna-foto-mascota">
                        <!-- Nota: Para que esta imagen cambie dinámicamente al elegir otra mascota en el <select> 
                             sin recargar la página, necesitarás un poco de JavaScript leyendo un atributo data-foto 
                             impreso por PHP en cada <option>. -->
                        <img src="ruta/buddy-grande.jpg" alt="Foto de Buddy" class="foto-mascota-seleccionada" id="img-mascota-preview">
                    </div>

                    <!-- Columna 2: Selector de mascota y Observaciones -->
                    <div class="columna-campos-principales">
                        <div class="grupo-campo">
                            <label for="select-mascota" class="etiqueta-campo">Selecciona la mascota</label>
                            <!-- [PHP: LLENAR SELECT DESDE BD] 
                                 Hacer un SELECT id, nombre, foto FROM mascotas y generar cada <option> con un foreach.
                                 El value debe ser el ID numérico de la mascota en la base de datos. -->
                            <select id="select-mascota" name="id_mascota" class="selector-campo" required>
                                <option value="1" data-foto="ruta/buddy-grande.jpg" selected>Buddy</option>
                                <option value="2" data-foto="ruta/michi-grande.jpg">Michi</option>
                                <option value="3" data-foto="ruta/max-grande.jpg">Max</option>
                                <option value="4" data-foto="ruta/luna-grande.jpg">Luna</option>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <label for="texto-observaciones" class="etiqueta-campo">Observaciones</label>
                            <textarea id="texto-observaciones" name="observaciones" rows="4" class="area-texto" placeholder="Describe el estado de la mascota...." required></textarea>
                        </div>
                    </div>

                    <!-- Columna 3: Estado de salud y Checkboxes -->
                    <div class="columna-campos-estado">
                        <div class="grupo-campo">
                            <label for="select-estado" class="etiqueta-campo">Estado de salud</label>
                            <select id="select-estado" name="estado_salud" class="selector-campo" required>
                                <option value="" disabled selected>Seleccione un estado</option>
                                <option value="saludable">Saludable</option>
                                <option value="observacion">En observación</option>
                                <option value="critico">Crítico</option>
                            </select>
                        </div>

                        <div class="grupo-campo">
                            <span class="etiqueta-campo">Condiciones especiales</span>
                            <!-- Nota: El name="condiciones[]" lleva corchetes para que PHP lo reciba como un arreglo (array) 
                                 si el veterinario marca más de una casilla al mismo tiempo. -->
                            <div class="lista-checkbox">
                                <label class="opcion-checkbox">
                                    <input type="checkbox" name="condiciones[]" value="tratamiento">
                                    <span>En tratamiento</span>
                                </label>
                                <label class="opcion-checkbox">
                                    <input type="checkbox" name="condiciones[]" value="dieta">
                                    <span>Requiere dieta especial</span>
                                </label>
                                <label class="opcion-checkbox">
                                    <input type="checkbox" name="condiciones[]" value="otra">
                                    <span>Otra</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <div class="contenedor-boton-guardar">
                        <button type="submit" class="boton-guardar">Guardar Actualización</button>
                    </div>

                </form>
            </section>

        </main>

    </div>


    <!-- =====================================================================
         3. PIE DE PÁGINA (FOOTER)
         ===================================================================== -->
    <footer class="pie-pagina">
    </footer>

</body>
</html>