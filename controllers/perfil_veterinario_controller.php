<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexion a la base de datos
require_once __DIR__ . '/../configuracion/conexion.php';

if (!isset($conexion) && isset($conn)) {
    $conexion = $conn;
}

$veterinarioId = isset($_SESSION['veterinario_id'])
    ? (int) $_SESSION['veterinario_id']
    : (isset($_GET['id']) ? (int) $_GET['id'] : 1);

// 1. Datos principales del veterinario
$sqlVet = "SELECT
                veterinario_id,
                nombre,
                email,
                telefono,
                tarjeta_profesional,
                ciudad,
                foto,
                sobre_mi,
                titulo,
                universidad,
                horario_lv,
                horario_sabado,
                horario_domingo
           FROM veterinario
           WHERE veterinario_id = ?
           LIMIT 1";

$stmtVet = $conexion->prepare($sqlVet);
$stmtVet->bind_param("i", $veterinarioId);
$stmtVet->execute();
$veterinario = $stmtVet->get_result()->fetch_assoc();
$stmtVet->close();

if (!$veterinario) {
    $veterinario = [
        'veterinario_id' => $veterinarioId,
        'nombre' => 'Veterinario sin registro',
        'email' => 'sin-correo@monpet.local',
        'telefono' => 'No registrado',
        'tarjeta_profesional' => 'Sin registro',
        'ciudad' => 'Ubicación no registrada',
        'foto' => '',
        'sobre_mi' => 'Aún no hay información registrada para este perfil.',
        'titulo' => 'Médico veterinario',
        'universidad' => 'No registrada',
        'horario_lv' => 'Por definir',
        'horario_sabado' => 'Por definir',
        'horario_domingo' => 'Cerrado'
    ];
}

// 2. Especialidades del veterinario
$sqlEsp = "SELECT e.nombre
           FROM veterinario_especialidad ve
           INNER JOIN especialidad e ON e.especialidad_id = ve.especialidad_id
           WHERE ve.veterinario_id = ?
           ORDER BY e.nombre ASC";

$stmtEsp = $conexion->prepare($sqlEsp);
$stmtEsp->bind_param("i", $veterinarioId);
$stmtEsp->execute();
$resEsp = $stmtEsp->get_result();

$especialidades = [];
while ($row = $resEsp->fetch_assoc()) {
    $especialidades[] = $row['nombre'];
}
$stmtEsp->close();

$textoEspecialidad = !empty($especialidades)
    ? implode(' · ', $especialidades)
    : 'Medicina veterinaria general';

// 3. Métricas reales desde consulta_veterinaria y mascota
$sqlMetricas = "SELECT
                    (SELECT COUNT(DISTINCT mascota_id)
                     FROM consulta_veterinaria
                     WHERE veterinario_id = ?) AS mascotas_consultadas,
                    (SELECT COUNT(*)
                     FROM consulta_veterinaria
                     WHERE veterinario_id = ?) AS total_consultas,
                    (SELECT COUNT(*)
                     FROM mascota
                     WHERE veterinario_id = ?) AS mascotas_asignadas";

$stmtMet = $conexion->prepare($sqlMetricas);
$stmtMet->bind_param("iii", $veterinarioId, $veterinarioId, $veterinarioId);
$stmtMet->execute();
$metricas = $stmtMet->get_result()->fetch_assoc() ?? [
    'mascotas_consultadas' => 0,
    'total_consultas' => 0,
    'mascotas_asignadas' => 0
];
$stmtMet->close();

// 4. Actividad reciente (últimas 5 consultas)
$sqlActividad = "SELECT
                    cv.consulta_veterinaria_id,
                    cv.fecha_consulta,
                    cv.diagnostico,
                    m.nombre AS nombre_mascota
                 FROM consulta_veterinaria cv
                 INNER JOIN mascota m ON m.mascota_id = cv.mascota_id
                 WHERE cv.veterinario_id = ?
                 ORDER BY cv.fecha_consulta DESC
                 LIMIT 5";

$stmtAct = $conexion->prepare($sqlActividad);
$stmtAct->bind_param("i", $veterinarioId);
$stmtAct->execute();
$actividadReciente = $stmtAct->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtAct->close();