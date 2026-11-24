<?php
// =========================================================================
// 1. CONEXIÓN A LA BASE DE DATOS
// =========================================================================
require_once '../configuracion/conexion_ejemplo.php';

// =========================================================================
// 2. CAPTURAR FILTROS DESDE LA URL (GET)
// =========================================================================
// Si el usuario seleccionó un filtro, lo tomamos; si no, usamos valores por defecto
$filtro_dias = isset($_GET['dias']) ? $_GET['dias'] : 'todos';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Construimos las condiciones WHERE dinamicamente segun los filtros elegidos
$condiciones = [];
if ($filtro_dias === '7') {
    $condiciones[] = "fecha_evento >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filtro_dias === '30') {
    $condiciones[] = "fecha_evento >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

if ($filtro_tipo !== 'todos' && $filtro_tipo !== '') {
    $tipo_seguro = $conn->real_escape_string($filtro_tipo);
    $condiciones[] = "categoria = '$tipo_seguro'";
}

$where_sql = count($condiciones) > 0 ? 'WHERE ' . implode(' AND ', $condiciones) : '';

// La actividad se construye con registros disponibles en el esquema del proyecto.
$sql_eventos = "SELECT * FROM (
    SELECT 'Salud' AS categoria,
           CONCAT('Consulta veterinaria de ', m.nombre) AS titulo,
           COALESCE(NULLIF(c.diagnostico, ''), NULLIF(c.observaciones, ''), 'Consulta veterinaria registrada') AS descripcion,
           c.fecha_consulta AS fecha_evento
    FROM consulta_veterinaria c
    INNER JOIN mascota m ON c.mascota_id = m.mascota_id
    UNION ALL
    SELECT 'Mascota' AS categoria,
           CONCAT('Ingreso de mascota: ', m.nombre) AS titulo,
           COALESCE(NULLIF(m.descripcion, ''), 'Mascota registrada en el refugio') AS descripcion,
           CAST(m.fecha_ingreso AS DATETIME) AS fecha_evento
    FROM mascota m
    WHERE m.fecha_ingreso IS NOT NULL
    UNION ALL
    SELECT 'Adopcion' AS categoria,
           'Solicitud de adopcion' AS titulo,
           CONCAT('Estado: ', s.estado) AS descripcion,
           s.fecha_solicitud AS fecha_evento
    FROM solicitud_adopcion s
    UNION ALL
    SELECT 'Aporte' AS categoria,
           'Aporte voluntario registrado' AS titulo,
           CONCAT('Cantidad: ', a.cantidad) AS descripcion,
           CAST(a.fecha_aporte AS DATETIME) AS fecha_evento
    FROM aporte_voluntario a
    WHERE a.fecha_aporte IS NOT NULL
) AS eventos $where_sql
ORDER BY fecha_evento DESC";
$resultado_eventos = $conn->query($sql_eventos);

// Resumen basado en tablas definidas en monpet.sql
$total_mascotas = $conn->query("SELECT COUNT(DISTINCT mascota_id) AS total FROM consulta_veterinaria")->fetch_assoc()['total'];
$total_solicitudes = $conn->query("SELECT COUNT(*) AS total FROM solicitud_adopcion")->fetch_assoc()['total'];
$total_consultas = $conn->query("SELECT COUNT(*) AS total FROM consulta_veterinaria")->fetch_assoc()['total'];
$total_aportes = $conn->query("SELECT COUNT(*) AS total FROM aporte_voluntario")->fetch_assoc()['total'];
function obtenerIconoCategoria($categoria) {
    switch ($categoria) {
        case 'Salud':        return '<i class="fa-solid fa-square-plus"></i>';
        case 'Jornada':      return '<i class="fa-solid fa-calendar-days"></i>';
        case 'Mascota':      return '<i class="fa-solid fa-paw"></i>';
        case 'Notificación': return '<i class="fa-solid fa-bell"></i>';
        case 'Reporte':      return '<i class="fa-solid fa-chart-line"></i>';
        case 'Campaña':      return '<i class="fa-solid fa-flag"></i>';
        default:             return '<i class="fa-solid fa-list"></i>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonPet - Actividad Reciente</title>
    <link rel="stylesheet" href="../assets/css/estilo_actividad_reciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

    <!-- =====================================================================
         1. BARRA SUPERIOR (ENCABEZADO)
         ===================================================================== -->
    <header class="encabezado-superior">
        <div class="marca-logo">
            <img src="../img/logo-huella.png" alt="Logo MonPet" class="icono-logo">
            <div class="texto-logo">
                <span class="titulo-marca">MonPet</span>
                <p class="eslogan-marca">Amor que cambia vidas</p>
            </div>
        </div>

        <div class="contenedor-buscador">
            <form action="../buscar.php" method="GET" class="formulario-busqueda">
                <span class="icono-buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="search" name="busqueda" class="campo-busqueda" placeholder="Buscar mascota, historia o refugio...">
            </form>
        </div>

        <div class="acciones-superiores">
            <button type="button" class="boton-notificaciones" aria-label="Notificaciones">
                <i class="fa-solid fa-bell"></i>
            </button>
            <div class="separador-vertical"></div>
            <a href="panel_veterinario.php" class="boton-volver">← Volver al inicio</a>
        </div>
    </header>


    <!-- =====================================================================
         2. CONTENEDOR PRINCIPAL
         ===================================================================== -->
    <main class="contenedor-actividad">

        <!-- SECCIÓN A: BANNER SUPERIOR -->
        <section class="banner-actividad">
            <div class="ilustracion-banner">
                <!-- Puedes reemplazar este bloque por <img src="../img/portapapeles-huella.png"> -->
                <div class="icono-portapapeles">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <i class="fa-solid fa-paw"></i>
                </div>
            </div>
            <div class="texto-banner">
                <h1 class="titulo-banner">Actividad reciente</h1>
                <p class="subtitulo-banner">Consulta las últimas acciones y eventos de tu cuenta.</p>
            </div>
        </section>


        <!-- SECCIÓN B: BARRA DE FILTROS -->
        <!-- Al cambiar cualquier select, el formulario se envía automáticamente por GET -->
        <form action="actividad_reciente.php" method="GET" class="barra-filtros" id="form-filtros">
            <div class="grupo-selectores">
                <!-- Filtro 1: Últimos 7 días -->
                <div class="caja-filtro <?php echo ($filtro_dias == '7') ? 'filtro-activo' : ''; ?>">
                    <span><i class="fa-solid fa-calendar-days"></i></span>
                    <select name="dias" onchange="document.getElementById('form-filtros').submit()" class="select-filtro">
                        <option value="todos" <?php echo ($filtro_dias == 'todos') ? 'selected' : ''; ?>>Todos los tiempos</option>
                        <option value="7" <?php echo ($filtro_dias == '7') ? 'selected' : ''; ?>>Últimos 7 días</option>
                        <option value="30" <?php echo ($filtro_dias == '30') ? 'selected' : ''; ?>>Últimos 30 días</option>
                    </select>
                </div>

                <!-- Filtro 2: Tipo de evento -->
                <div class="caja-filtro <?php echo ($filtro_tipo != 'todos') ? 'filtro-activo' : ''; ?>">
                    <span><i class="fa-solid fa-tag"></i></span>
                    <select name="tipo" onchange="document.getElementById('form-filtros').submit()" class="select-filtro">
                        <option value="todos" <?php echo ($filtro_tipo == 'todos') ? 'selected' : ''; ?>>Todos los eventos</option>
                        <option value="Salud" <?php echo ($filtro_tipo == 'Salud') ? 'selected' : ''; ?>>Salud</option>
                        <option value="Mascota" <?php echo ($filtro_tipo == 'Mascota') ? 'selected' : ''; ?>>Mascota</option>
                        <option value="Adopcion" <?php echo ($filtro_tipo == 'Adopcion') ? 'selected' : ''; ?>>Adopci&oacute;n</option>
                        <option value="Aporte" <?php echo ($filtro_tipo == 'Aporte') ? 'selected' : ''; ?>>Aporte</option>
                    </select>
                </div>
            </div>

            <!-- Botón Limpiar filtros (recarga la página sin parámetros GET) -->
            <a href="actividad_reciente.php" class="boton-limpiar">
                <i class="fa-solid fa-trash-can"></i> Limpiar filtros
            </a>
        </form>


        <!-- SECCIÓN C: CUADRÍCULA DE CONTENIDO (RESUMEN IZQUIERDO + LÍNEA DE TIEMPO DERECHA) -->
        <div class="cuadricula-contenido">

            <!-- COLUMNA IZQUIERDA: Resumen de actividad -->
            <aside class="panel-resumen">
                <h2 class="titulo-resumen"><i class="fa-solid fa-chart-line"></i> Resumen de actividad</h2>

                <div class="lista-tarjetas-resumen">
                    <!-- Tarjeta 1 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_mascotas; ?></span>
                            <p class="etiqueta-resumen">Mascotas atendidas</p>
                        </div>
                    </div>

                    <!-- Tarjeta 2 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_solicitudes; ?></span>
                            <p class="etiqueta-resumen">Solicitudes de adopci&oacute;n</p>
                        </div>
                    </div>

                    <!-- Tarjeta 3 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_consultas; ?></span>
                            <p class="etiqueta-resumen">Consultas m&eacute;dicas</p>
                        </div>
                    </div>

                    <!-- Tarjeta 4 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_aportes; ?></span>
                            <p class="etiqueta-resumen">Aportes voluntarios</p>
                        </div>
                    </div>

                    <!-- Tarjeta 5: Motivacional -->
                    <div class="tarjeta-resumen tarjeta-felicitacion">
                        <div class="icono-resumen">
                            <i class="fa-solid fa-thumbs-up"></i>
                        </div>
                        <div class="datos-resumen">
                            <span class="titulo-felicitacion">¡Buen trabajo!</span>
                            <p class="etiqueta-resumen">Sigues cuidando a las mascotas de MonPet</p>
                        </div>
                    </div>
                </div>
            </aside>


            <!-- COLUMNA DERECHA: Línea de tiempo (Timeline) -->
            <section class="panel-linea-tiempo">
                <div class="contenedor-timeline">

                    <?php 
                    if ($resultado_eventos && $resultado_eventos->num_rows > 0): 
                        while ($evento = $resultado_eventos->fetch_assoc()): 
                    ?>
                        <!-- Cada fila de la línea de tiempo -->
                        <article class="item-timeline">
                            <!-- Punto circular sobre la línea vertical izquierda -->
                            <div class="punto-timeline"></div>

                            <!-- Tarjeta del evento -->
                            <div class="tarjeta-evento">
                                <div class="icono-evento">
                                    <?php echo obtenerIconoCategoria($evento['categoria']); ?>
                                </div>

                                <div class="detalle-evento">
                                    <h3 class="titulo-evento"><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                                    <p class="descripcion-evento"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                </div>

                                <span class="etiqueta-tipo-evento">
                                    <?php echo htmlspecialchars($evento['categoria']); ?>
                                </span>

                                <div class="fecha-evento">
                                    <?php echo date("d/m/Y, g:i A", strtotime($evento['fecha_evento'])); ?>
                                </div>
                            </div>
                        </article>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <p class="mensaje-vacio">No se encontraron eventos con los filtros seleccionados.</p>
                    <?php endif; ?>

                </div>
            </section>

        </div>

    </main>

    <!-- =====================================================================
         3. PIE DE PÁGINA
         ===================================================================== -->
    <footer class="pie-pagina"></footer>

</body>
</html>