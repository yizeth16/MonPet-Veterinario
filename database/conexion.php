<?php
// Datos de conexión (en XAMPP por defecto el usuario es 'root' y la contraseña va vacía)
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "monpet_db"; // Cambia esto por el nombre real de tu base de datos

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

// Verificar si hubo algún error al conectar
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Configurar tildes y eñes (UTF-8)
$conexion->set_charset("utf8mb4");
?>