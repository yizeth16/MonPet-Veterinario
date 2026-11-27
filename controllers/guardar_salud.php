<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion_ejemplo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
    exit;
}

// 1. Llaves foráneas obligatorias en tu tabla consulta_veterinaria
// En producción estos IDs vendrán de $_SESSION['veterinario_id'] y $_POST['mascota_id']
$mascota_id = isset($_POST['mascota_id']) ? intval($_POST['mascota_id']) : 1;
$veterinario_id = isset($_POST['veterinario_id']) ? intval($_POST['veterinario_id']) : 1;

// 2. Datos del formulario
$fecha_raw = $_POST['fecha_revision'] ?? '';
// Tu columna fecha_consulta es DATETIME, agregamos la hora actual del servidor
$fecha_consulta = !empty($fecha_raw) ? $fecha_raw . ' ' . date('H:i:s') : '';

$estado_salud = trim($_POST['estado_salud'] ?? '');
$observaciones = trim($_POST['descripcion'] ?? ''); // Mapeado a 'observaciones' en tu tabla

$temperatura = ($_POST['temperatura'] !== '') ? floatval($_POST['temperatura']) : null;
$peso_actual = ($_POST['peso_actual'] !== '') ? floatval($_POST['peso_actual']) : null;
$frecuencia_cardiaca = ($_POST['frecuencia_cardiaca'] !== '') ? intval($_POST['frecuencia_cardiaca']) : null;
$frecuencia_respiratoria = ($_POST['frecuencia_respiratoria'] !== '') ? intval($_POST['frecuencia_respiratoria']) : null;
$estado_general = trim($_POST['estado_general'] ?? 'Activo y alerta');

$condiciones_array = $_POST['condiciones'] ?? [];
$condiciones_especiales = !empty($condiciones_array) ? implode(', ', $condiciones_array) : 'Ninguna';

// Resumen automático para tu columna 'diagnostico'
$diagnostico = "Estado: $estado_salud | General: $estado_general";

if (empty($fecha_consulta) || empty($estado_salud) || empty($observaciones)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Por favor completa todos los campos obligatorios (*)."
    ]);
    exit;
}

// 3. Manejo del archivo adjunto
$ruta_archivo_bd = null;
$nombre_original_bd = null;

if (isset($_FILES['archivo_adjunto']) && $_FILES['archivo_adjunto']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['archivo_adjunto'];
    $tamano_maximo = 5 * 1024 * 1024; // 5 MB
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

    $nombre_original_bd = basename($archivo['name']);
    $extension = strtolower(pathinfo($nombre_original_bd, PATHINFO_EXTENSION));

    if ($archivo['size'] > $tamano_maximo) {
        echo json_encode(["exito" => false, "mensaje" => "El archivo supera el límite de 5 MB."]);
        exit;
    }

    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode(["exito" => false, "mensaje" => "Formato de archivo no permitido."]);
        exit;
    }

    $carpeta_destino = __DIR__ . '/uploads/consultas/';
    if (!is_dir($carpeta_destino)) {
        mkdir($carpeta_destino, 0755, true);
    }

    $nombre_unico = uniqid('consulta_') . '_' . time() . '.' . $extension;
    $ruta_completa = $carpeta_destino . $nombre_unico;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        $ruta_archivo_bd = 'uploads/consultas/' . $nombre_unico;
    } else {
        echo json_encode(["exito" => false, "mensaje" => "No se pudo guardar el archivo en el servidor."]);
        exit;
    }
}

// 4. Insertar en tu tabla consulta_veterinaria
$sql = "INSERT INTO consulta_veterinaria (
            mascota_id, veterinario_id, fecha_consulta, estado_salud,
            temperatura, peso_actual, frecuencia_cardiaca, frecuencia_respiratoria,
            estado_general, condiciones_especiales, diagnostico, observaciones,
            ruta_archivo, nombre_archivo_original
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode(["exito" => false, "mensaje" => "Error en la consulta SQL: " . $conexion->error]);
    exit;
}

// Tipos: i=int, s=string, d=double
$stmt->bind_param(
    "iissddiissssss",
    $mascota_id,
    $veterinario_id,
    $fecha_consulta,
    $estado_salud,
    $temperatura,
    $peso_actual,
    $frecuencia_cardiaca,
    $frecuencia_respiratoria,
    $estado_general,
    $condiciones_especiales,
    $diagnostico,
    $observaciones,
    $ruta_archivo_bd,
    $nombre_original_bd
);

if ($stmt->execute()) {
    echo json_encode([
        "exito" => true,
        "mensaje" => "¡Consulta registrada exitosamente en MonPet!",
        "consulta_veterinaria_id" => $stmt->insert_id,
        "archivo_subido" => $nombre_original_bd
    ]);
} else {
    // Si falla por llave foránea (ej. no existe mascota_id=1 o veterinario_id=1), avisará claramente
    echo json_encode([
        "exito" => false,
        "mensaje" => "Error al guardar (verifica que existan la mascota y el veterinario): " . $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>