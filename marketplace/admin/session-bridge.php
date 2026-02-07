<?php
/**
 * Session Bridge - Forzar uso de la sesión del panel
 * Este archivo se asegura de que el marketplace use la misma sesión que el panel
 */

// Configurar sesión ANTES de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    // Detectar dominio para compartir cookies entre subdominios
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $domain = '';
    
    // En producción, usar dominio base para compartir sesión entre subdominios
    // En localhost, NO configurar dominio para evitar problemas de redirección
    if (strpos($host, 'mizton.cat') !== false) {
        $domain = '.mizton.cat'; // El punto inicial permite compartir entre subdominios
    } elseif (strpos($host, 'publiaxion.com') !== false) {
        $domain = '.publiaxion.com';
    }
    // Si es localhost, $domain queda vacío (comportamiento por defecto de PHP)
    
    // Configurar parámetros de cookie ANTES de session_start()
    session_name('PHPSESSID');
    
    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    
    // Solo agregar dominio si no es localhost
    if (!empty($domain)) {
        $cookieParams['domain'] = $domain;
    }
    
    session_set_cookie_params($cookieParams);
    
    // Iniciar sesión (usará automáticamente la cookie PHPSESSID si existe)
    session_start();
}
