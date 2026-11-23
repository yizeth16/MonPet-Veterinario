<?php
require_once '../database/conexion.php';

// Verificamos que el formulario se haya enviado por el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Recibir los datos usando los atributos "name" de los inputs del HTML
    $id_mascota = $_POST['id_mascota'];
    $observaciones = $_POST['observaciones'];
    $estado_salud = $_POST['estado_salud'];

    // 2. Los checkboxes llegan como un arreglo (array). Si marcaron alguno, los unimos con comas
    if (isset($_POST['condiciones'])) {
        $condiciones = implode(", ", $_POST['condiciones']); // Ej: "En tratamiento, Requiere dieta especial"
    } else {
        $condiciones = "Ninguna";
    }

    // 3. Preparar la consulta SQL segura (evita inyección SQL)
    $sql = "INSERT INTO historial_salud (id_mascota, estado_salud, observaciones, condiciones, fecha_registro) 
            VALUES (?, ?, ?, ?, NOW())";

    $sentencia = $conexion->prepare($sql);
    // "isss" significa: i = entero (id_mascota), s = texto (estado, observaciones, condiciones)
    $sentencia->bind_param("isss", $id_mascota, $estado_salud, $observaciones, $condiciones);

    // 4. Ejecutar y redirigir de vuelta al panel
    if ($sentencia->execute()) {
        // Redirige al panel enviando ?exito=1 por la URL para mostrar el mensaje verde
        header("Location: panel_veterinario.php?exito=1");
        exit();
    } else {
        echo "Error al guardar la actualización: " . $conexion->error;
    }

    $sentencia->close();
    $conexion->close();
}
?>