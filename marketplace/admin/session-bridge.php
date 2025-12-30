<?php
/**
 * Session Bridge - Forzar uso de la sesión del panel
 * Este archivo se asegura de que el marketplace use la misma sesión que el panel
 */

// Obtener el session ID de la cookie PHPSESSID
$sessionId = $_COOKIE['PHPSESSID'] ?? null;

if ($sessionId) {
    // Forzar el uso del session ID existente
    session_id($sessionId);
}

// Configurar parámetros de sesión antes de iniciar
session_name('PHPSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Iniciar sesión con el ID existente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Verificar que tenemos los datos de sesión
error_log('Marketplace Session Bridge - Session ID: ' . session_id());
error_log('Marketplace Session Bridge - User ID: ' . ($_SESSION['user_id'] ?? 'NO SET'));
error_log('Marketplace Session Bridge - Admin: ' . ($_SESSION['admin'] ?? 'NO SET'));
