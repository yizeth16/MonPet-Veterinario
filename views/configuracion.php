<?php

require_once __DIR__ . '/../controllers/configuracion_vet_controller.php';

function e($valor, $fallback = 'No disponible') {
    if ($valor === null || trim((string)$valor) === '') {
        return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$idActivo = (int)($idPerfil ?? $idVeterinario ?? 0);

$urlVolver = 'perfil_veterinario.php' . ($idActivo > 0 ? '?id=' . urlencode((string)$idActivo) : '');
$temaInicial = (($configuracion['tema'] ?? 'claro') === 'oscuro') ? 'oscuro' : 'claro';
$idiomaInicial = (($configuracion['idioma'] ?? 'es') === 'en') ? 'en' : 'es';
$huboPost = ($_SERVER['REQUEST_METHOD'] === 'POST') ? '1' : '0';
?>

<!DOCTYPE html>
<html lang="<?= e($idiomaInicial, 'es') ?>" data-tema="<?= e($temaInicial, 'claro') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - MonVet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rancho&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/configuracion_veterinario.css">
    <link rel="stylesheet" href="../assets/css/tema.css">
</head>
<body
    data-perfil-id="<?= $idActivo ?>"
    data-tema="<?= e($temaInicial, 'claro') ?>"
    data-tema-server="<?= e($temaInicial, 'claro') ?>"
    data-idioma-server="<?= e($idiomaInicial, 'es') ?>"
    data-hubo-post="<?= e($huboPost, '0') ?>">

    <header class="topbar">
        <div class="marca-logo">
            <div class="circulo-logo">
                <i class="fa-solid fa-paw icono-huella" aria-hidden="true"></i>
            </div>
            <div class="texto-logo">
                <span class="titulo-logo">MonVet</span>
                <span class="subtitulo-logo" data-i18n="lema">Amor que cambia vidas</span>
            </div>
        </div>

        <div class="acciones-topbar">
            <a href="<?= e($urlVolver, 'perfil_veterinario.php') ?>" class="btn-volver">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span data-i18n="volver_inicio">Volver al inicio</span>
            </a>
        </div>
    </header>

    <main class="perfil-container">
        <section class="banner-configuracion">
            <div class="banner-texto">
                <h1><i class="fa-solid fa-gears" aria-hidden="true"></i> <span data-i18n="titulo_config">Configuración</span></h1>
                <p data-i18n="subtitulo_config">Personaliza tus preferencias en MonPet</p>
            </div>
        </section>

        <?php if ($mensajeEstado !== ''): ?>
            <div class="alerta-estado"><?= e($mensajeEstado) ?></div>
        <?php endif; ?>

        <form method="post" action="" class="panel-ajustes" id="form-configuracion">
            <?php if ($idActivo > 0): ?>
                <input type="hidden" name="id" value="<?= $idActivo ?>">
            <?php endif; ?>

            <div class="tarjeta-ajuste tarjeta-preferencias">
                <h2 class="titulo-seccion" data-i18n="preferencias">Preferencias</h2>
                <div class="grid-preferencias">
                    <div>
                        <p class="etiqueta-campo" data-i18n="tema_interfaz">Tema de la interfaz:</p>
                        <div class="selector-temas">
                            <?php $esClaro = ($temaInicial !== 'oscuro'); ?>
                            <label class="opcion-tema <?= $esClaro ? 'activo' : '' ?>" data-tema-opcion="claro">
                                <input type="radio" name="tema" value="claro" <?= $esClaro ? 'checked' : '' ?> hidden>
                                <?php if ($esClaro): ?>
                                    <span class="badge-check"><i class="fa-solid fa-check"></i></span>
                                <?php endif; ?>
                                <i class="fa-regular fa-sun icono-tema"></i>
                                <span data-i18n="tema_claro">Claro</span>
                            </label>

                            <label class="opcion-tema <?= !$esClaro ? 'activo' : '' ?>" data-tema-opcion="oscuro">
                                <input type="radio" name="tema" value="oscuro" <?= !$esClaro ? 'checked' : '' ?> hidden>
                                <?php if (!$esClaro): ?>
                                    <span class="badge-check"><i class="fa-solid fa-check"></i></span>
                                <?php endif; ?>
                                <i class="fa-solid fa-moon icono-tema"></i>
                                <span data-i18n="tema_oscuro">Oscuro</span>
                            </label>
                        </div>
                    </div>

                    <div class="campos-seleccion">
                        <div class="campo-grupo">
                            <label for="idioma" data-i18n="idioma">Idioma:</label>
                            <select id="idioma" name="idioma" class="campo-select">
                                <option value="es" <?= $idiomaInicial === 'es' ? 'selected' : '' ?>>Español</option>
                                <option value="en" <?= $idiomaInicial === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>

                        <div class="campo-grupo">
                            <label for="zona_horaria" data-i18n="zona_horaria">Zona horaria</label>
                            <select id="zona_horaria" name="zona_horaria" class="campo-select">
                                <option value="America/Bogota" <?= $configuracion['zona_horaria'] === 'America/Bogota' ? 'selected' : '' ?>>(GTM-) Bogotá, Lima, Quito</option>
                                <option value="America/Mexico_City" <?= $configuracion['zona_horaria'] === 'America/Mexico_City' ? 'selected' : '' ?>>(GTM-) Ciudad de México</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-inferior">
                <div class="tarjeta-ajuste">
                    <div class="encabezado-tarjeta">
                        <h2 class="titulo-seccion"><i class="fa-solid fa-bell" aria-hidden="true"></i> <span data-i18n="notificaciones">Notificaciones</span></h2>
                        <p data-i18n="subtitulo_notif">Recibe alertas importantes sobre MonPet</p>
                    </div>

                    <ul class="lista-notificaciones">
                        <li>
                            <span class="item-titulo">
                                <i class="fa-solid fa-paw" aria-hidden="true"></i>
                                <span data-i18n="notif_1">Nuevas solicitudes de revisión médica</span>
                            </span>
                            <label class="interruptor">
                                <input type="checkbox" name="notif_solicitudes" <?= $configuracion['notif_solicitudes'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </li>
                        <li>
                            <span class="item-titulo">
                                <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                <span data-i18n="notif_2">Recordatorio de jornadas y campañas</span>
                            </span>
                            <label class="interruptor">
                                <input type="checkbox" name="notif_jornadas" <?= $configuracion['notif_jornadas'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </li>
                        <li>
                            <span class="item-titulo">
                                <i class="fa-solid fa-comment-dots" aria-hidden="true"></i>
                                <span data-i18n="notif_3">Mensajes de usuarios</span>
                            </span>
                            <label class="interruptor">
                                <input type="checkbox" name="notif_mensajes" <?= $configuracion['notif_mensajes'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </li>
                        <li>
                            <span class="item-titulo">
                                <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                                <span data-i18n="notif_4">Actualizaciones del sistema</span>
                            </span>
                            <label class="interruptor">
                                <input type="checkbox" name="notif_actualizaciones" <?= $configuracion['notif_actualizaciones'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </li>
                    </ul>
                </div>

                <div class="tarjeta-ajuste">
                    <div class="encabezado-tarjeta">
                        <h2 class="titulo-seccion" data-i18n="seguridad">Seguridad</h2>
                    </div>

                    <ul class="lista-seguridad">
                        <li>
                            <div class="seguridad-info">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                <div>
                                    <strong data-i18n="seg_pass_titulo">Cambiar contraseña</strong>
                                    <p data-i18n="seg_pass_desc">Mantén tu cuenta segura con una nueva contraseña.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-seguridad" data-i18n="btn_cambiar">Cambiar</button>
                        </li>
                        <li>
                            <div class="seguridad-info">
                                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                                <div>
                                    <strong data-i18n="seg_verif_titulo">Verificar identidad</strong>
                                    <p data-i18n="seg_verif_desc">Confirma tu identidad con tu correo electrónico.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-seguridad" data-i18n="btn_verificar">Verificar</button>
                        </li>
                        <li>
                            <div class="seguridad-info">
                                <i class="fa-solid fa-fingerprint" aria-hidden="true"></i>
                                <div>
                                    <strong data-i18n="seg_logout_titulo">Cerrar sesión</strong>
                                    <p data-i18n="seg_logout_desc">Sal de tu cuenta de forma segura.</p>
                                </div>
                            </div>
                            <a href="logout.php" class="btn-cerrar-sesion" data-i18n="btn_cerrar_sesion">Cerrar sesión</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="acciones-guardar">
                <button type="submit" class="btn-guardar-cambios" data-i18n="btn_guardar">Guardar cambios</button>
            </div>
        </form>
    </main>

    <footer class="pie-pagina">
        <div class="franja-superior"></div>
        <div class="franja-inferior"></div>
    </footer>

    <script src="../assets/js/preferencias_globales.js"></script>
</body>
</html>