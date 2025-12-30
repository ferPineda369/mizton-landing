<?php
/**
 * Middleware de Autenticación Admin para Marketplace
 * Verifica que el usuario tenga permisos de administrador
 */

// Usar el session bridge para forzar el uso de la sesión del panel
require_once __DIR__ . '/session-bridge.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: https://panel.mizton.cat/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verificar que el usuario sea administrador
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    http_response_code(403);
    die('
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Denegado - Mizton Marketplace</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: linear-gradient(135deg, #1B4332 0%, #40916C 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 16px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .error-icon {
                font-size: 64px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            h1 {
                color: #1B4332;
                margin-bottom: 16px;
                font-size: 28px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #40916C;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.3s;
            }
            .btn:hover {
                background: #1B4332;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>Acceso Denegado</h1>
            <p>No tienes permisos de administrador para acceder al panel de gestión del marketplace.</p>
            <p>Si crees que deberías tener acceso, contacta con el administrador del sistema.</p>
            <a href="https://panel.mizton.cat/" class="btn">
                <i class="bi bi-house"></i> Volver al Panel
            </a>
        </div>
    </body>
    </html>
    ');
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log('Error al generar token CSRF: ' . $e->getMessage());
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
    }
}

// Variables disponibles para las vistas admin
$admin_user_id = $_SESSION['user_id'];
$admin_user_name = $_SESSION['user_name'] ?? 'Admin';
$admin_user_email = $_SESSION['user_email'] ?? '';
$csrf_token = $_SESSION['csrf_token'];
