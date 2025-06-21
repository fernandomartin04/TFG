<?php
session_start();
$_SESSION = [];                // Vacía todas las variables de sesión
session_unset();               // Elimina las variables de sesión
session_destroy();             // Destruye la sesión

// Redirige a la página de inicio 
header("Location: index.php");
exit();
