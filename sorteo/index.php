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
                                        <li><i class="fas fa-users"></i> <strong>Requisito:</strong> Únete al grupo de WhatsApp</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="rules-list">
                                        <li><i class="fas fa-clock"></i> 15 minutos reservado para confirmar tu pago</li>
                                        <li><i class="fas fa-calendar"></i> El sorteo se realizará el 28 de noviembre</li>
                                        <li><i class="fas fa-trophy"></i> 2 Ganadores posibles → <button class="btn btn-link p-0 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#prizeModal" style="color: var(--christmas-gold);">Premio</button> ←</li>
                                        <li><i class="fas fa-handshake"></i> Sorteo en vivo transparente y justo</li>
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

    <!-- Modal del Premio -->
    <div class="modal fade" id="prizeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-gift"></i> Premio del Sorteo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="text-primary">🏆 Colágeno Duché 🏆</h3>
                        <h5 class="text-muted">(Colágeno Hidrolizado)</h5>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Descripción del Producto</h6>
                                <p>Bebida proteínica en polvo a base de colágeno puro saborizado que se disuelve fácilmente en agua o jugo. Contiene Aminoácidos Esenciales.</p>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-star"></i> Beneficios</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> <strong>PIEL:</strong> La favorece con brillo, tonicidad, suavidad y la humecta.</li>
                                        <li><i class="fas fa-check text-success"></i> <strong>UÑAS:</strong> Por sus aminoácidos, favorecen el crecimiento y dureza.</li>
                                        <li><i class="fas fa-check text-success"></i> <strong>PESTAÑAS:</strong> Posee propiedades que nutren y fortalecen, haciéndolas crecer largas y abundantes.</li>
                                        <li><i class="fas fa-check text-success"></i> <strong>CABELLO:</strong> Gracias al colágeno lucirá abundante y espectacular.</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-warning">
                                            <h6 class="mb-0"><i class="fas fa-utensils"></i> Uso Recomendado</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Sabor:</strong> Natural</p>
                                            <p><strong>Cantidad:</strong> Toma diariamente 1 cucharada sopera disuelta en un vaso de agua.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-box"></i> Contenido</h6>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="text-center text-primary">300 g</h4>
                                            <p class="text-center text-muted">Producto completo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

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
                        <input type="hidden" id="selectedNumbers" name="selectedNumbers">
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Números seleccionados:</strong> <span id="selectedNumbersList"></span>
                        </div>
                        
                        <div class="alert alert-danger" id="blockingTimer" style="display: none;">
                            <i class="fas fa-lock"></i> 
                            <strong>Números bloqueados temporalmente:</strong> 
                            <span id="blockingTimeLeft">2:00</span> restantes para completar el registro
                        </div>
                        
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required>
                            <div class="form-text">Este nombre aparecerá en todos los números seleccionados</div>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-whatsapp"></i> 
                            <strong>¡Importante!</strong> Al confirmar tu participación se abrirá automáticamente el grupo de WhatsApp. Es <strong>REQUISITO</strong> unirse para participar en el evento en vivo.
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Información de Pago</h6>
                            <p><strong>CLABE:</strong> 638180000187197364</p>
                            <p><strong>Banco:</strong> NU MEXICO</p>
                            <p><strong>Titular:</strong> Ileana Pineda Calderón</p>
                            <p><strong>Monto por boleto:</strong> $25.00 MXN</p>
                            <p><strong>Total a pagar:</strong> $<span id="totalAmount">25.00</span> MXN</p>
                            <p><strong>Concepto:</strong> Sorteo Número(s) <span class="payment-number"></span></p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> 
                            <strong>Tiempo restante:</strong> 
                            <span id="reservationTimer">15:00</span> para confirmar el pago
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
