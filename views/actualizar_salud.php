<?php
require_once 'conexion_ejemplo.php';

// IDs de prueba (en producción vendrán por $_GET['mascota_id'] y $_SESSION['veterinario_id'])
$mascota_id = isset($_GET['mascota_id']) ? intval($_GET['mascota_id']) : 1;
$veterinario_id = 1;

// 1. Consultar datos de la veterinaria
$sqlVet = "SELECT nombre FROM veterinario WHERE veterinario_id = ?";
$stmtVet = $conexion->prepare($sqlVet);
$stmtVet->bind_param("i", $veterinario_id);
$stmtVet->execute();
$veterinaria = $stmtVet->get_result()->fetch_assoc();
$nombreVet = $veterinaria['nombre'] ?? 'Dra. Valentina Rojas';

// 2. Consultar datos de la mascota + especie + refugio
$sqlMascota = "SELECT m.mascota_id, m.nombre, m.fecha_nacimiento, m.foto,
                      e.nombre AS especie, r.nombre AS refugio
               FROM mascota m
               LEFT JOIN raza ra ON m.raza_id = ra.raza_id
               LEFT JOIN especie e ON ra.id_especie = e.id_especie
               LEFT JOIN refugio r ON m.nit = r.nit
               WHERE m.mascota_id = ?";
$stmtM = $conexion->prepare($sqlMascota);
$stmtM->bind_param("i", $mascota_id);
$stmtM->execute();
$mascota = $stmtM->get_result()->fetch_assoc();

// Calcular edad en años o meses
$edadTexto = "Sin registro";
if (!empty($mascota['fecha_nacimiento'])) {
    $nacimiento = new DateTime($mascota['fecha_nacimiento']);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($nacimiento);
    $edadTexto = ($diferencia->y > 0) ? $diferencia->y . " años" : $diferencia->m . " meses";
}

// 3. Consultar historial médico de la mascota
$sqlHistorial = "SELECT consulta_veterinaria_id, fecha_consulta, estado_salud, peso_actual,
                        condiciones_especiales, diagnostico, observaciones,
                        ruta_archivo, nombre_archivo_original
                 FROM consulta_veterinaria
                 WHERE mascota_id = ?
                 ORDER BY fecha_consulta DESC";
$stmtH = $conexion->prepare($sqlHistorial);
$stmtH->bind_param("i", $mascota_id);
$stmtH->execute();
$resultadoHistorial = $stmtH->get_result();

$historial = [];
$archivos = [];
$ultimoPeso = "24";

while ($fila = $resultadoHistorial->fetch_assoc()) {
    $historial[] = $fila;
    if (!empty($fila['ruta_archivo']) && !empty($fila['nombre_archivo_original'])) {
        $archivos[] = $fila;
    }
}

// Tomar el peso más reciente registrado si existe
if (!empty($historial) && !empty($historial[0]['peso_actual'])) {
    $ultimoPeso = floatval($historial[0]['peso_actual']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonPet - Actualizar Salud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="estilos_salud.css">
</head>
<body>

    <!-- ==================== BARRA DE NAVEGACIÓN SUPERIOR ==================== -->
    <header class="barra-superior">
        <div class="contenedor-logo">
            <div class="icono-marca"><i class="ph-fill ph-paw-print"></i></div>
            <div class="texto-marca">
                <h1>MonPet</h1>
                <span>Amor que cambia vidas <i class="ph-fill ph-heart"></i></span>
            </div>
        </div>

        <nav class="menu-principal">
            <a href="#" class="enlace-nav"><i class="ph ph-house"></i> Inicio</a>
            <a href="#" class="enlace-nav"><i class="ph ph-paw-print"></i> Adopción <i class="ph ph-caret-down"></i></a>
            <a href="#" class="enlace-nav"><i class="ph ph-flag"></i> Reportes <i class="ph ph-caret-down"></i></a>
            <a href="#" class="enlace-nav"><i class="ph ph-shield-check"></i> Refugios</a>
            <a href="#" class="enlace-nav activo"><i class="ph ph-stethoscope"></i> Veterinarios</a>
            <a href="#" class="enlace-nav"><i class="ph ph-heart"></i> Nosotros</a>
        </nav>

        <div class="acciones-usuario">
            <button class="btn-notificaciones" aria-label="Notificaciones">
                <i class="ph ph-bell"></i>
                <span class="insignia-notificacion">2</span>
            </button>
            <div class="perfil-mini">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Valentina&top=longHair&hairColor=black&clothesColor=white" alt="Veterinaria">
                <div class="info-mini">
                    <strong><?= htmlspecialchars($nombreVet) ?> <i class="ph ph-caret-down"></i></strong>
                    <span>Veterinaria <i class="ph ph-caret-down"></i></span>
                </div>
            </div>
        </div>
    </header>

    <!-- ==================== CONTENEDOR PRINCIPAL ==================== -->
    <div class="layout-dashboard">
        
        <!-- BARRA LATERAL IZQUIERDA -->
        <aside class="barra-lateral">
            <div class="tarjeta-perfil-lateral">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Valentina&top=longHair&hairColor=black&clothesColor=white" alt="Veterinaria">
                <div class="datos-perfil">
                    <h3><?= htmlspecialchars($nombreVet) ?> <i class="ph-fill ph-heart"></i></h3>
                    <span>Veterinaria <i class="ph ph-caret-down"></i></span>
                </div>
            </div>

            <nav class="menu-lateral">
                <a href="#" class="item-lateral"><i class="ph ph-house"></i> Panel Veterinario</a>
                <a href="#" class="item-lateral"><i class="ph ph-paw-print"></i> Mis Mascotas</a>
                <a href="#" class="item-lateral"><i class="ph ph-file-plus"></i> Reportes Médicos</a>
                <a href="#" class="item-lateral activo"><i class="ph ph-heartbeat"></i> Actualizar Salud</a>
                <a href="#" class="item-lateral"><i class="ph ph-hand-heart"></i> Jornadas y Campañas</a>
                <a href="#" class="item-lateral"><i class="ph ph-user"></i> Perfil Profesional</a>
                <a href="#" class="item-lateral"><i class="ph ph-gear"></i> Configuración</a>
            </nav>

            <div class="tarjeta-soporte">
                <div class="icono-soporte"><i class="ph-fill ph-shield-check"></i></div>
                <div class="texto-soporte">
                    <h4>¿Necesitas ayuda?</h4>
                    <p>Estamos aquí para ayudarte.</p>
                    <a href="#">Contactar soporte <i class="ph ph-arrow-up-right"></i></a>
                </div>
                <i class="ph-fill ph-paw-print marca-agua-huella"></i>
            </div>
        </aside>

        <!-- ÁREA DE CONTENIDO DERECHA -->
        <main class="contenido-principal">
            <nav class="migas-pan">
                <a href="#"><i class="ph ph-house"></i> Inicio</a>
                <span>&gt;</span>
                <a href="#">Veterinarios</a>
                <span>&gt;</span>
                <span class="actual">Actualizar salud</span>
            </nav>

            <section class="banner-encabezado">
                <div class="banner-info">
                    <div class="icono-pulso"><i class="ph ph-activity"></i></div>
                    <div>
                        <h2>Actualizar salud</h2>
                        <p>Registra el estado de salud y condiciones especiales de la mascota.</p>
                    </div>
                </div>
                <div class="banner-mascotas">
                    <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=300&q=80" alt="Perro" class="img-perro">
                    <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=250&q=80" alt="Gato" class="img-gato">
                </div>
            </section>

            <div class="grid-seccion-salud">
                <!-- COLUMNA IZQUIERDA -->
                <div class="columna-formulario">
                    
                    <!-- Tarjeta resumen de la mascota cargada desde BD -->
                    <article class="tarjeta tarjeta-mascota">
                        <div class="info-mascota-izq">
                            <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=200&q=80" alt="<?= htmlspecialchars($mascota['nombre'] ?? 'Mascota') ?>" class="foto-mascota">
                            <div class="detalles-mascota">
                                <div class="nombre-especie">
                                    <h3><?= htmlspecialchars($mascota['nombre'] ?? 'Mascota no encontrada') ?></h3>
                                    <span class="etiqueta-especie"><i class="ph-fill ph-paw-print"></i> <?= htmlspecialchars($mascota['especie'] ?? 'Sin especie') ?></span>
                                </div>
                                <div class="metadatos-mascota">
                                    <span><i class="ph ph-calendar-blank"></i> <strong>Edad:</strong> <?= $edadTexto ?></span>
                                    <span><i class="ph ph-scales"></i> <strong>Peso:</strong> <?= $ultimoPeso ?> kg</span>
                                    <span><i class="ph ph-house-line"></i> <strong>Refugio:</strong> <?= htmlspecialchars($mascota['refugio'] ?? 'Sin refugio') ?></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-secundario-outline" id="btnVerFicha">
                            <i class="ph ph-eye"></i> Ver ficha completa
                        </button>
                    </article>

                    <!-- Formulario -->
                    <form class="tarjeta tarjeta-formulario" id="formActualizarSalud" enctype="multipart/form-data">
                        <input type="hidden" name="mascota_id" value="<?= $mascota_id ?>">
                        <input type="hidden" name="veterinario_id" value="<?= $veterinario_id ?>">

                        <div class="titulo-seccion">
                            <i class="ph ph-stethoscope"></i>
                            <h3>Información de la consulta</h3>
                        </div>

                        <div class="grid-campos">
                            <div class="grupo-columna">
                                <div class="campo-formulario">
                                    <label for="fechaRevision">Fecha de la revisión <span class="requerido">*</span></label>
                                    <div class="input-con-icono">
                                        <i class="ph ph-calendar"></i>
                                        <input type="date" id="fechaRevision" name="fecha_revision" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="campo-formulario">
                                    <label for="estadoSalud">Estado de salud <span class="requerido">*</span></label>
                                    <div class="select-personalizado">
                                        <select id="estadoSalud" name="estado_salud" required>
                                            <option value="Bueno" selected>Bueno</option>
                                            <option value="Estable">Estable</option>
                                            <option value="En observación">En observación</option>
                                            <option value="Crítico">Crítico</option>
                                        </select>
                                        <i class="ph ph-caret-down"></i>
                                    </div>
                                </div>

                                <div class="campo-formulario">
                                    <label for="temperatura">Temperatura corporal (°C)</label>
                                    <div class="input-con-icono">
                                        <i class="ph ph-thermometer"></i>
                                        <input type="number" step="0.1" id="temperatura" name="temperatura" value="38.5">
                                    </div>
                                </div>

                                <div class="campo-formulario">
                                    <label for="pesoActual">Peso actual (kg)</label>
                                    <div class="input-con-icono">
                                        <i class="ph ph-scales"></i>
                                        <input type="number" step="0.1" id="pesoActual" name="peso_actual" value="<?= $ultimoPeso ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="grupo-columna">
                                <h4 class="subtitulo-bloque">Signos vitales</h4>
                                
                                <div class="fila-signos">
                                    <div class="campo-formulario">
                                        <label for="frecCardiaca">Frecuencia cardíaca (lpm)</label>
                                        <div class="input-vital">
                                            <i class="ph ph-heart"></i>
                                            <input type="number" id="frecCardiaca" name="frecuencia_cardiaca" value="85">
                                        </div>
                                    </div>

                                    <div class="campo-formulario">
                                        <label for="estadoGeneral">Estado general</label>
                                        <div class="select-personalizado">
                                            <select id="estadoGeneral" name="estado_general">
                                                <option value="Activo y alerta" selected>Activo y alerta</option>
                                                <option value="Decaído">Decaído</option>
                                                <option value="Somnoliento">Somnoliento</option>
                                            </select>
                                            <i class="ph ph-caret-down"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="campo-formulario">
                                    <label for="frecRespiratoria">Frecuencia respiratoria (rpm)</label>
                                    <div class="input-vital mitad-ancho">
                                        <i class="ph ph-wind"></i>
                                        <input type="number" id="frecRespiratoria" name="frecuencia_respiratoria" value="18">
                                    </div>
                                </div>

                                <h4 class="subtitulo-bloque margen-superior">Condiciones especiales</h4>
                                <div class="lista-checkbox">
                                    <label class="checkbox-personalizado">
                                        <input type="checkbox" name="condiciones[]" value="En tratamiento" checked>
                                        <span class="caja-check"><i class="ph-bold ph-check"></i></span>
                                        En tratamiento
                                    </label>
                                    <label class="checkbox-personalizado">
                                        <input type="checkbox" name="condiciones[]" value="Requiere dieta especial">
                                        <span class="caja-check"><i class="ph-bold ph-check"></i></span>
                                        Requiere dieta especial
                                    </label>
                                    <label class="checkbox-personalizado">
                                        <input type="checkbox" name="condiciones[]" value="Otra">
                                        <span class="caja-check"><i class="ph-bold ph-check"></i></span>
                                        Otra
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid-inferior-form">
                            <div class="campo-formulario">
                                <label for="descripcionAtencion">Descripción de la atención <span class="requerido">*</span></label>
                                <textarea id="descripcionAtencion" name="descripcion" rows="4" maxlength="500" required placeholder="Escribe aquí las observaciones médicas de la revisión..."></textarea>
                                <div class="contador-caracteres"><span id="contadorTexto">0</span>/500</div>
                            </div>

                            <div class="campo-formulario">
                                <label>Adjuntar archivos <span class="texto-opcional">(opcional)</span></label>
                                <div class="zona-arrastre" id="zonaArrastre">
                                    <input type="file" id="inputArchivo" name="archivo_adjunto" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" hidden>
                                    <div class="icono-nube"><i class="ph ph-cloud-arrow-up"></i></div>
                                    <p class="texto-principal-drop" id="nombreArchivoSubido">Selecciona un archivo o arrástralo aquí</p>
                                    <span class="texto-ayuda-drop">Imágenes, PDF o documentos (máx. 5 MB)</span>
                                </div>
                            </div>
                        </div>

                        <div class="acciones-formulario">
                            <button type="button" class="btn-cancelar" id="btnCancelar"><i class="ph ph-x"></i> Cancelar</button>
                            <button type="submit" class="btn-guardar"><i class="ph ph-floppy-disk"></i> Guardar actualización</button>
                        </div>
                    </form>
                </div>

                <!-- COLUMNA DERECHA (Widgets dinámicos desde MySQL) -->
                <aside class="columna-widgets">
                    
                    <!-- Widget 1: Historial de salud real -->
                    <div class="tarjeta widget-historial">
                        <div class="titulo-widget">
                            <i class="ph-fill ph-calendar-plus"></i>
                            <h3>Historial de salud</h3>
                        </div>

                        <ul class="linea-tiempo">
                            <?php if (empty($historial)): ?>
                                <li class="item-tiempo"><p style="font-size:13px;">Aún no hay consultas registradas.</p></li>
                            <?php else: ?>
                                <?php foreach ($historial as $reg): 
                                    $fechaFmt = date('d/m/Y', strtotime($reg['fecha_consulta']));
                                    $enTratamiento = strpos($reg['condiciones_especiales'] ?? '', 'En tratamiento') !== false;
                                ?>
                                <li class="item-tiempo">
                                    <span class="punto-tiempo"></span>
                                    <div class="detalle-tiempo">
                                        <span class="fecha"><?= $fechaFmt ?></span>
                                        <p><?= htmlspecialchars($reg['diagnostico'] ?: 'Control veterinario') ?></p>
                                    </div>
                                    <span class="estado-badge <?= $enTratamiento ? 'tratamiento' : 'completado' ?>">
                                        <?= $enTratamiento ? 'En tratamiento' : 'Completado' ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Widget 2: Últimos archivos reales -->
                    <div class="tarjeta widget-archivos">
                        <div class="cabecera-widget-flex">
                            <div class="titulo-widget">
                                <i class="ph ph-file-text"></i>
                                <h3>Últimos archivos</h3>
                            </div>
                        </div>

                        <ul class="lista-archivos">
                            <?php if (empty($archivos)): ?>
                                <li class="item-archivo"><span style="font-size:12px;">Sin archivos adjuntos todavía.</span></li>
                            <?php else: ?>
                                <?php foreach (array_slice($archivos, 0, 4) as $doc): 
                                    $ext = strtolower(pathinfo($doc['nombre_archivo_original'], PATHINFO_EXTENSION));
                                    $esPdf = ($ext === 'pdf');
                                    $fechaDoc = date('d/m/Y', strtotime($doc['fecha_consulta']));
                                ?>
                                <li class="item-archivo">
                                    <div class="icono-doc <?= $esPdf ? 'pdf' : 'img' ?>">
                                        <i class="ph <?= $esPdf ? 'ph-file-pdf' : 'ph-image' ?>"></i>
                                    </div>
                                    <div class="info-doc">
                                        <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>" target="_blank" style="text-decoration:none; color:inherit;">
                                            <strong><?= htmlspecialchars($doc['nombre_archivo_original']) ?></strong>
                                        </a>
                                        <span><?= $fechaDoc ?></span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Widget 3: Tarjeta Recuerda -->
                    <div class="tarjeta-recuerda">
                        <div class="titulo-recuerda">
                            <i class="ph-fill ph-paw-print"></i>
                            <h4>Recuerda</h4>
                        </div>
                        <p>Mantener al día la salud de tu mascota ayuda a prevenir enfermedades y mejora su calidad de vida.</p>
                        <i class="ph-fill ph-paw-print huella-decorativa"></i>
                    </div>

                </aside>
            </div>
        </main>
    </div>

    <footer class="pie-pagina">
        <div class="contenedor-footer">
            <div class="footer-marca">
                <div class="icono-marca-blanco"><i class="ph-fill ph-paw-print"></i></div>
                <div>
                    <h4>MonPet</h4>
                    <span>Amor que cambia vidas <i class="ph-fill ph-heart"></i></span>
                </div>
            </div>
            <div class="footer-enlaces">
                <a href="#">Términos y condiciones</a>
                <a href="#">Política de privacidad</a>
                <a href="#">Aviso legal</a>
            </div>
            <div class="footer-copyright">
                <span>© 2026 MonPet. Todos los derechos reservados.</span>
            </div>
        </div>
    </footer>

    <script src="logica_salud.js"></script>
</body>
</html>