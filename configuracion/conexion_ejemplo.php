<?php

$host = "Localhost";
$usuario = "root";
$password = "";          
$base_datos = "monpet";
$puerto = 3306;          

$conn = new mysqli($host, $usuario, $password, $base_datos, $puerto);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión con la base de datos: " . $conn->connect_error);
}

// Asegurar soporte para tildes y caracteres especiales (ñ, á, é, í, ó, ú)
$conn->set_charset("utf8mb4");
?>
