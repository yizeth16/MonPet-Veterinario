<?php
// =========================================================================
// 1. CONEXIÓN A LA BASE DE DATOS
// =========================================================================
require_once '../database/conexion.php';

// =========================================================================
// 2. CAPTURAR FILTROS DESDE LA URL (GET)
// =========================================================================
// Si el usuario seleccionó un filtro, lo tomamos; si no, usamos valores por defecto
$filtro_dias = isset($_GET['dias']) ? $_GET['dias'] : 'todos';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Construimos las condiciones WHERE dinámicamente según los filtros elegidos
$condiciones = [];

if ($filtro_dias === '7') {
    $condiciones[] = "fecha_evento >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filtro_dias === '30') {
    $condiciones[] = "fecha_evento >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

if ($filtro_tipo !== 'todos' && $filtro_tipo !== '') {
    // Escapamos el texto por seguridad antes de meterlo al SQL
    $tipo_seguro = $conexion->real_escape_string($filtro_tipo);
    $condiciones[] = "categoria = '$tipo_seguro'";
}

// Unimos las condiciones con AND si existe alguna
$where_sql = "";
if (count($condiciones) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $condiciones);
}

// =========================================================================
// 3. CONSULTA DE LA LÍNEA DE TIEMPO (TIMELINE)
// =========================================================================
$sql_eventos = "SELECT * FROM actividad_reciente $where_sql ORDER BY fecha_evento DESC";
$resultado_eventos = $conexion->query($sql_eventos);

// =========================================================================
// 4. CONSULTAS PARA LA COLUMNA IZQUIERDA ("RESUMEN DE ACTIVIDAD")
// =========================================================================
$total_mascotas = $conexion->query("SELECT COUNT(*) AS total FROM mascotas")->fetch_assoc()['total'];
$total_jornadas = $conexion->query("SELECT COUNT(*) AS total FROM actividad_reciente WHERE categoria IN ('Jornada', 'Campaña')")->fetch_assoc()['total'];
$total_reportes = $conexion->query("SELECT COUNT(*) AS total FROM historial_salud")->fetch_assoc()['total'];
$total_notificaciones = $conexion->query("SELECT COUNT(*) AS total FROM actividad_reciente WHERE categoria = 'Notificación'")->fetch_assoc()['total'];

// Función auxiliar para asignar el emoji/icono según la categoría del evento
function obtenerIconoCategoria($categoria) {
    switch ($categoria) {
        case 'Salud':        return '➕';
        case 'Jornada':      return '📅';
        case 'Mascota':      return '🐾';
        case 'Notificación': return '🔔';
        case 'Reporte':      return '📡';
        case 'Campaña':      return '✅';
        default:             return '📋';
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
                <span class="icono-buscar">🔍</span>
                <input type="search" name="busqueda" class="campo-busqueda" placeholder="Buscar mascota, historia o refugio...">
            </form>
        </div>

        <div class="acciones-superiores">
            <button type="button" class="boton-notificaciones" aria-label="Notificaciones">🔔</button>
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
                <div class="icono-portapapeles">📋🐾</div>
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
                    <span>📅</span>
                    <select name="dias" onchange="document.getElementById('form-filtros').submit()" class="select-filtro">
                        <option value="todos" <?php echo ($filtro_dias == 'todos') ? 'selected' : ''; ?>>Todos los tiempos</option>
                        <option value="7" <?php echo ($filtro_dias == '7') ? 'selected' : ''; ?>>Últimos 7 días</option>
                        <option value="30" <?php echo ($filtro_dias == '30') ? 'selected' : ''; ?>>Últimos 30 días</option>
                    </select>
                </div>

                <!-- Filtro 2: Tipo de evento -->
                <div class="caja-filtro <?php echo ($filtro_tipo != 'todos') ? 'filtro-activo' : ''; ?>">
                    <span>🏷️</span>
                    <select name="tipo" onchange="document.getElementById('form-filtros').submit()" class="select-filtro">
                        <option value="todos" <?php echo ($filtro_tipo == 'todos') ? 'selected' : ''; ?>>Todos los eventos</option>
                        <option value="Salud" <?php echo ($filtro_tipo == 'Salud') ? 'selected' : ''; ?>>Salud</option>
                        <option value="Jornada" <?php echo ($filtro_tipo == 'Jornada') ? 'selected' : ''; ?>>Jornada</option>
                        <option value="Mascota" <?php echo ($filtro_tipo == 'Mascota') ? 'selected' : ''; ?>>Mascota</option>
                        <option value="Notificación" <?php echo ($filtro_tipo == 'Notificación') ? 'selected' : ''; ?>>Notificación</option>
                        <option value="Reporte" <?php echo ($filtro_tipo == 'Reporte') ? 'selected' : ''; ?>>Reporte</option>
                        <option value="Campaña" <?php echo ($filtro_tipo == 'Campaña') ? 'selected' : ''; ?>>Campaña</option>
                    </select>
                </div>
            </div>

            <!-- Botón Limpiar filtros (recarga la página sin parámetros GET) -->
            <a href="actividad_reciente.php" class="boton-limpiar">
                🗑️ Limpiar filtros
            </a>
        </form>


        <!-- SECCIÓN C: CUADRÍCULA DE CONTENIDO (RESUMEN IZQUIERDO + LÍNEA DE TIEMPO DERECHA) -->
        <div class="cuadricula-contenido">

            <!-- COLUMNA IZQUIERDA: Resumen de actividad -->
            <aside class="panel-resumen">
                <h2 class="titulo-resumen">📊 Resumen de actividad</h2>

                <div class="lista-tarjetas-resumen">
                    <!-- Tarjeta 1 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">🐾</div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_mascotas; ?></span>
                            <p class="etiqueta-resumen">Mascotas atendidas</p>
                        </div>
                    </div>

                    <!-- Tarjeta 2 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">📅</div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_jornadas; ?></span>
                            <p class="etiqueta-resumen">Jornadas y campañas</p>
                        </div>
                    </div>

                    <!-- Tarjeta 3 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">➕</div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_reportes; ?></span>
                            <p class="etiqueta-resumen">Reportes médicos</p>
                        </div>
                    </div>

                    <!-- Tarjeta 4 -->
                    <div class="tarjeta-resumen">
                        <div class="icono-resumen">🔔</div>
                        <div class="datos-resumen">
                            <span class="numero-resumen"><?php echo $total_notificaciones; ?></span>
                            <p class="etiqueta-resumen">Notificaciones</p>
                        </div>
                    </div>

                    <!-- Tarjeta 5: Motivacional -->
                    <div class="tarjeta-resumen tarjeta-felicitacion">
                        <div class="icono-resumen">👍</div>
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