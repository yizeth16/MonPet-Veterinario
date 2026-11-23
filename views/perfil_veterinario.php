<?php
require_once __DIR__ . '/../controllers/perfil_veterinario_controller.php';

function e($valor, $fallback = 'No disponible') {
    if ($valor === null || trim((string)$valor) === '') {
        return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Profesional - MonPet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/perfil_veterinario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rancho&display=swap" rel="stylesheet">
</head>
<body>
   <header class="topbar">
        <div class="marca-logo">
            <div class="circulo-logo">
                <i class="fa-solid fa-paw icono-huella" aria-hidden="true"></i>
            </div>
            <div class="texto-logo">
                <span class="titulo-logo">MonPet</span>
                <span class="subtitulo-logo">Amor que cambia vidas</span>
            </div>
        </div>

        <div class="acciones-topbar">
            <a href="panel_veterinario.php" class="btn-volver">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al inicio
            </a>
        </div>
    </header>

    <main class="perfil-container">
        <section class="banner-perfil">
            <h1><i class="fa-solid fa-id-badge"></i> Perfil Profesional</h1>
            <p>Gestiona tu información profesional y brinda un mejor cuidado a las mascotas</p>
        </section>

        <section class="tarjeta-principal">
            <div class="info-vet">
                <?php if (!empty($veterinario['foto'])): ?>
                    <img src="<?= e($veterinario['foto']) ?>" alt="Foto de <?= e($veterinario['nombre']) ?>" class="foto-vet">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= strtoupper(substr(e($veterinario['nombre'], 'V'), 0, 1)) ?></div>
                <?php endif; ?>

                <div>
                    <h2><?= e($veterinario['nombre']) ?></h2>
                    <span class="especialidad">
                        <i class="fa-solid fa-stethoscope"></i> <?= e($textoEspecialidad) ?>
                    </span>
                    <p><i class="fa-solid fa-envelope"></i> <?= e($veterinario['email']) ?></p>
                    <p><i class="fa-solid fa-phone"></i> <?= e($veterinario['telefono']) ?></p>
                    <p><i class="fa-solid fa-location-dot"></i> <?= e($veterinario['ciudad']) ?></p>
                    <a href="editar_perfil_veterinario.php?id=<?= (int)$veterinario['veterinario_id'] ?>" class="btn-editar">
                        <i class="fa-solid fa-pen-to-square"></i> Editar perfil
                    </a>
                </div>
            </div>

            <div class="metricas-grid">
                <div class="metrica-card">
                    <strong><?= (int)$metricas['mascotas_consultadas'] ?></strong>
                    <span><i class="fa-solid fa-paw"></i> Mascotas atendidas</span>
                </div>
                <div class="metrica-card">
                    <strong><?= (int)$metricas['total_consultas'] ?></strong>
                    <span><i class="fa-solid fa-clipboard-list"></i> Reportes / consultas</span>
                </div>
                <div class="metrica-card">
                    <strong><?= (int)$metricas['mascotas_asignadas'] ?></strong>
                    <span><i class="fa-solid fa-heart-pulse"></i> Mascotas asignadas</span>
                </div>
            </div>
        </section>

        <section class="grid-detalle">
            <article class="card-bloque">
                <h3><i class="fa-solid fa-user-doctor"></i> Sobre mí</h3>
                <p><?= e($veterinario['sobre_mi']) ?></p>
            </article>

            <article class="card-bloque">
                <h3><i class="fa-solid fa-clock"></i> Mi horario de atención</h3>
                <ul>
                    <li><i class="fa-regular fa-calendar-days"></i> <strong>Lunes - Viernes:</strong> <?= e($veterinario['horario_lv'], 'Por definir') ?></li>
                    <li><i class="fa-regular fa-calendar"></i> <strong>Sábados:</strong> <?= e($veterinario['horario_sabado'], 'Por definir') ?></li>
                    <li><i class="fa-regular fa-calendar-xmark"></i> <strong>Domingos:</strong> <?= e($veterinario['horario_domingo'], 'Cerrado') ?></li>
                </ul>
            </article>

            <article class="card-bloque">
                <h3><i class="fa-solid fa-graduation-cap"></i> Credenciales</h3>
                <ul>
                    <li><i class="fa-solid fa-award"></i> <strong>Título:</strong> <?= e($veterinario['titulo']) ?></li>
                    <li><i class="fa-solid fa-university"></i> <strong>Universidad:</strong> <?= e($veterinario['universidad']) ?></li>
                    <li><i class="fa-solid fa-id-card"></i> <strong>Registro profesional:</strong> <?= e($veterinario['tarjeta_profesional']) ?></li>
                </ul>
            </article>

            <article class="card-bloque">
                <h3><i class="fa-solid fa-notes-medical"></i> Actividad reciente</h3>
                <?php if (!empty($actividadReciente)): ?>
                    <ul>
                        <?php foreach ($actividadReciente as $act): ?>
                            <li>
                                <i class="fa-solid fa-stethoscope"></i>
                                <strong><?= e($act['nombre_mascota']) ?></strong> —
                                <?= e($act['diagnostico'], 'Consulta registrada') ?>
                                <small><i class="fa-regular fa-calendar-check"></i> <?= e($act['fecha_consulta']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><i class="fa-regular fa-folder-open"></i> No hay consultas recientes registradas aún.</p>
                <?php endif; ?>
            </article>
             <a href="configuracion.php" class="btn-configuracion">
                <i class="fa-solid fa-gear" aria-hidden="true"></i> Configuración
            </a>
        </section>
    </main>
</body>
</html>