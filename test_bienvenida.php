<?php
// test_confirmacion.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "includes/enviar_correo.php";

$emailPrueba = 'tu-correo@example.com';  // Cambia por tu correo real para prueba
$nombrePrueba = 'Usuario Prueba';

$pedidoHTML = '
    <h1>Confirmación de Pedido</h1>
    <p>Hola ' . htmlspecialchars($nombrePrueba) . ',</p>
    <p>Gracias por tu compra. Este es un correo de prueba para confirmar que el sistema de confirmación funciona.</p>
';

if (enviarCorreoPedido($emailPrueba, $nombrePrueba, $pedidoHTML)) {
    echo "Correo de confirmación enviado correctamente a $emailPrueba";
} else {
    echo "Error al enviar correo de confirmación a $emailPrueba";
}
