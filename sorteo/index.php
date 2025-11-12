<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo Mizton - Participa y Gana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/sorteo.css" rel="stylesheet">
</head>
<body>
    <!-- Header con título y contador -->
    <header class="hero-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h1 class="main-title">🎉 LLEVA LA NAVIDAD A PAHUATA 🎉</h1>
                    <p class="subtitle">¡Participa y apoya a esta comunidad!</p>
                    
                    <!-- Contador Regresivo -->
                    <div class="countdown-container">
                        <h3 class="countdown-title">Tiempo restante para el sorteo:</h3>
                        <div class="countdown-timer" id="countdown">
                            <div class="time-unit">
                                <span class="time-number" id="days">00</span>
                                <span class="time-label">Días</span>
                            </div>
                            <div class="time-unit">
                                <span class="time-number" id="hours">00</span>
                                <span class="time-label">Horas</span>
                            </div>
                            <div class="time-unit">
                                <span class="time-number" id="minutes">00</span>
                                <span class="time-label">Minutos</span>
                            </div>
                            <div class="time-unit">
                                <span class="time-number" id="seconds">00</span>
                                <span class="time-label">Segundos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sección de Reglas -->
    <section class="rules-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="rules-card">
                        <h2 class="rules-title"><i class="fas fa-list-ul"></i> Reglas del Sorteo</h2>
                        <div class="rules-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="rules-list">
                                        <li><i class="fas fa-check-circle"></i> Selecciona un número del 1 al 100</li>
                                        <li><i class="fas fa-check-circle"></i> Completa tus datos personales</li>
                                        <li><i class="fas fa-check-circle"></i> Realiza el pago de participación</li>
                                        <li><i class="fas fa-check-circle"></i> Confirma tu pago en el tiempo límite</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="rules-list">
                                        <li><i class="fas fa-clock"></i> Tienes 15 minutos para confirmar el pago</li>
                                        <li><i class="fas fa-trophy"></i> El sorteo se realizará el 28 de noviembre</li>
                                        <li><i class="fas fa-gift"></i> Premios increíbles te esperan</li>
                                        <li><i class="fas fa-handshake"></i> Sorteo transparente y justo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Grid de Números -->
    <section class="numbers-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title">Selecciona tu número de la suerte</h2>
                    <div class="numbers-grid" id="numbersGrid">
                        <!-- Los números se cargarán dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Registro -->
    <div class="modal fade" id="registrationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Participación - Número <span id="selectedNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registrationForm">
                        <input type="hidden" id="numberInput" name="number">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="fullName" name="fullName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Información de Pago</h6>
                            <p><strong>Cuenta:</strong> 1234567890</p>
                            <p><strong>Banco:</strong> Banco Ejemplo</p>
                            <p><strong>Titular:</strong> Mizton Sorteos</p>
                            <p><strong>Monto:</strong> $50.00 MXN</p>
                            <p><strong>Concepto:</strong> Sorteo Número <span class="payment-number"></span></p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> 
                            <strong>Tiempo límite:</strong> 
                            <span id="reservationTimer">15:00</span> minutos para confirmar el pago
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Confirmar Participación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sorteo.js"></script>
</body>
</html>
