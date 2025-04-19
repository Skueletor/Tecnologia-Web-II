<?php

try {
    $conexion = new PDO("mysql:host=127.0.0.1:3306;dbname=tcweb2", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Activa errores
} catch (Exception $ex) {
    echo "Error de conexión: " . $ex->getMessage();
    exit;
}
?>
