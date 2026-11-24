<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutasConexion = [
    __DIR__ . '/../config/conexion_ejemplo.php',
    __DIR__ . '/../configuracion/conexion_ejemplo.php',
    __DIR__ . '/../conexion_ejemplo.php',
];

foreach ($rutasConexion as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        break;
    }
}

$idPerfil = (int)(
    $_SESSION['id_perfil']
    ?? $_SESSION['id_veterinario']
    ?? $_SESSION['id']
    ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? 1
);

$temaActual = $_SESSION['tema'] ?? 'claro';
$idiomaActual = $_SESSION['idioma'] ?? 'es';

if ($idPerfil > 0 && isset($conn) && ($conn instanceof mysqli)) {
    $stmt = $conn->prepare("SELECT tema, idioma FROM configuracion_usuario WHERE id_perfil = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $idPerfil);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($fila = $res->fetch_assoc()) {
            $temaActual = ($fila['tema'] === 'oscuro') ? 'oscuro' : 'claro';
            $idiomaActual = ($fila['idioma'] === 'en') ? 'en' : 'es';
        }
        $stmt->close();
    }
}