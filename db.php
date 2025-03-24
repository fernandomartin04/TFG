<?php
$servername = 'sql202.infinityfree.com';   
$username = 'if0_38587556';   
$password = "RsjmrY76PsqHBT";   
$dbname = 'if0_38587556_tfg_fer';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {                                             
    die("❌ Conexión fallida: " . $conn->connect_error);     
}

// Establecer codificación para caracteres especiales
$conn->set_charset("utf8"); 

// Mensaje para pruebas
echo "✅ Conexión exitosa a la base de datos";
?>
