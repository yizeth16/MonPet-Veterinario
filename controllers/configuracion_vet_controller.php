<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutasConexion = [
    __DIR__ . '/../configuracion/conexion_ejemplo.php',
];

foreach ($rutasConexion as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        break;
    }
}

// Identificar el perfil activo
$idPerfil = (int)(
    $_SESSION['id_perfil']
    ?? $_SESSION['id_veterinario']
    ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? 1
);
$idVeterinario = $idPerfil;

// Valores por defecto para cualquier perfil nuevo
$configuracion = [
    'tema' => $_SESSION['tema'] ?? 'claro',
    'idioma' => $_SESSION['idioma'] ?? 'es',
    'zona_horaria' => 'America/Bogota',
    'notif_solicitudes' => 1,
    'notif_jornadas' => 1,
    'notif_mensajes' => 0,
    'notif_actualizaciones' => 1,
];

$mensajeEstado = '';
$errorEstado = '';

$usaPDO = isset($pdo) && ($pdo instanceof PDO);
$usaMySQLi = isset($conn) && ($conn instanceof mysqli);

if ($idPerfil > 0) {
    if ($usaPDO) {
        $stmt = $pdo->prepare("SELECT * FROM configuracion_usuario WHERE id_perfil = :id_perfil LIMIT 1");
        $stmt->execute([':id_perfil' => $idPerfil]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            $configuracion = array_merge($configuracion, $fila);
        }
    } elseif ($usaMySQLi) {
        $stmt = $conn->prepare("SELECT * FROM configuracion_usuario WHERE id_perfil = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $idPerfil);
            $stmt->execute();
            $res = $stmt->get_result();
            $fila = $res ? $res->fetch_assoc() : null;
            if ($fila) {
                $configuracion = array_merge($configuracion, $fila);
            }
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temaPost = (($_POST['tema'] ?? 'claro') === 'oscuro') ? 'oscuro' : 'claro';
    $idiomaPost = in_array(($_POST['idioma'] ?? 'es'), ['es', 'en'], true) ? $_POST['idioma'] : 'es';
    $zonasValidas = ['America/Bogota', 'America/Mexico_City'];
    $zonaPost = in_array(($_POST['zona_horaria'] ?? 'America/Bogota'), $zonasValidas, true)
        ? $_POST['zona_horaria']
        : 'America/Bogota';

    $notifSolicitudes = isset($_POST['notif_solicitudes']) ? 1 : 0;
    $notifJornadas = isset($_POST['notif_jornadas']) ? 1 : 0;
    $notifMensajes = isset($_POST['notif_mensajes']) ? 1 : 0;
    $notifActualizaciones = isset($_POST['notif_actualizaciones']) ? 1 : 0;

    // Actualizar arreglo y sesión para que la vista refleje el cambio de inmediato
    $configuracion = [
        'tema' => $temaPost,
        'idioma' => $idiomaPost,
        'zona_horaria' => $zonaPost,
        'notif_solicitudes' => $notifSolicitudes,
        'notif_jornadas' => $notifJornadas,
        'notif_mensajes' => $notifMensajes,
        'notif_actualizaciones' => $notifActualizaciones,
    ];

    $_SESSION['tema'] = $temaPost;
    $_SESSION['idioma'] = $idiomaPost;

    if ($idPerfil > 0) {
        if ($usaPDO) {
            $sqlGuardar = "INSERT INTO configuracion_usuario
                (id_perfil, tema, idioma, zona_horaria, notif_solicitudes, notif_jornadas, notif_mensajes, notif_actualizaciones)
                VALUES (:id_perfil, :tema, :idioma, :zona_horaria, :n_sol, :n_jor, :n_men, :n_act)
                ON DUPLICATE KEY UPDATE
                    tema = VALUES(tema),
                    idioma = VALUES(idioma),
                    zona_horaria = VALUES(zona_horaria),
                    notif_solicitudes = VALUES(notif_solicitudes),
                    notif_jornadas = VALUES(notif_jornadas),
                    notif_mensajes = VALUES(notif_mensajes),
                    notif_actualizaciones = VALUES(notif_actualizaciones)";
            $stmt = $pdo->prepare($sqlGuardar);
            $stmt->execute([
                ':id_perfil' => $idPerfil,
                ':tema' => $temaPost,
                ':idioma' => $idiomaPost,
                ':zona_horaria' => $zonaPost,
                ':n_sol' => $notifSolicitudes,
                ':n_jor' => $notifJornadas,
                ':n_men' => $notifMensajes,
                ':n_act' => $notifActualizaciones,
            ]);
            $mensajeEstado = ($idiomaPost === 'en')
                ? 'Changes saved for this profile.'
                : 'Cambios guardados para este perfil.';
        } elseif ($usaMySQLi) {
            $sqlGuardar = "INSERT INTO configuracion_usuario
                (id_perfil, tema, idioma, zona_horaria, notif_solicitudes, notif_jornadas, notif_mensajes, notif_actualizaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    tema = VALUES(tema),
                    idioma = VALUES(idioma),
                    zona_horaria = VALUES(zona_horaria),
                    notif_solicitudes = VALUES(notif_solicitudes),
                    notif_jornadas = VALUES(notif_jornadas),
                    notif_mensajes = VALUES(notif_mensajes),
                    notif_actualizaciones = VALUES(notif_actualizaciones)";
            $stmt = $conn->prepare($sqlGuardar);
            if ($stmt) {
                $stmt->bind_param(
                    'isssiiii',
                    $idPerfil,
                    $temaPost,
                    $idiomaPost,
                    $zonaPost,
                    $notifSolicitudes,
                    $notifJornadas,
                    $notifMensajes,
                    $notifActualizaciones
                );
                $stmt->execute();
                $stmt->close();
                $mensajeEstado = ($idiomaPost === 'en')
                    ? 'Changes saved for this profile.'
                    : 'Cambios guardados para este perfil.';
            }
        } else {
            $mensajeEstado = ($idiomaPost === 'en')
                ? 'Changes applied in session (no DB connection variable $pdo or $conn found).'
                : 'Cambios aplicados en sesión (no se encontró conexión $pdo ni $conn).';
        }
    } else {
        $mensajeEstado = ($idiomaPost === 'en')
            ? 'Changes applied in session for current profile.'
            : 'Cambios aplicados en sesión para el perfil actual.';
    }
}