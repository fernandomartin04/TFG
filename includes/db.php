<?php
// Habilitar errores detallados (puedes quitarlo en producción)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Parámetros de conexión
$servername = 'db5017984678.hosting-data.io';
$username   = 'dbu657666';
$password   = 'MegustaelMineclaft25';
$dbname     = 'dbs14302116';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Establecer codificación para compatibilidad completa (incluye emojis, etc.)
$conn->set_charset("utf8mb4");

// Ya está conectada la BD. No mostramos mensajes aquí para no romper cabeceras HTML.
?>
