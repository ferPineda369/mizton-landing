<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo Pahuata - Participa y Gana</title>
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
                                        <li><i class="fas fa-check-circle"></i> Confirma tu pago en el tiempo límite. <a href="#" class="text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#paymentDataModal">→Datos←</a></li>
                                        <li><i class="fas fa-users"></i> <strong>Requisito: </strong> Unirse al grupo de WhatsApp</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="rules-list">
                                        <li><i class="fas fa-clock"></i> 30 minutos reservado para confirmar tu pago</li>
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
                    
                    <!-- Enlaces de Consulta -->
                    <div class="row mb-4">
                        <div class="col-md-6 text-center mb-3">
                            <button type="button" 
                                    class="btn btn-info btn-lg" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#consultaModal">
                                <i class="fas fa-search"></i> Consulta tus boletos comprados aquí
                            </button>
                        </div>
                    </div>
                    
                    <div class="numbers-grid" id="numbersGrid">
                        <!-- Los números se cargarán dinámicamente -->
                    </div>
                    
                    <!-- Temporizador de Reserva -->
                    <div class="alert alert-success mt-4" id="reservationAlert" style="display: none;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1"><i class="fas fa-clock"></i> ¡Números Reservados!</h5>
                                <p class="mb-0">Tienes <strong><span id="reservationTimer">30:00</span></strong> para confirmar tu pago</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#paymentDataModal">
                                    <i class="fas fa-credit-card"></i> Ver Datos de Pago
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información sobre Pahuata y Mecánica -->
                    <div class="row mt-5">
                        <div class="col-12 text-center">
                            <p class="mb-3">
                                <a href="#" class="text-decoration-none fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#pahuataModal">
                                    → Conoce un poco más de Pahuata y de nuestra misión con esta comunidad ←
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Mecánica del Sorteo -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Mecánica del Sorteo</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-users text-success"></i> Se mantendrá informados a los participantes a través del grupo de WhatsApp creado para tal fin</li>
                                        <li class="mb-2"><i class="fas fa-lock text-warning"></i> Al concluir el evento, dicho grupo se cerrará</li>
                                        <li class="mb-2"><i class="fas fa-video text-primary"></i> El día y hora señalada para el sorteo, se realizará un "en vivo" a través de Google Meet o Zoom elegido 1 día antes a través de votación</li>
                                        <li class="mb-2"><i class="fas fa-random text-info"></i> Se transmitirá en vivo donde se mostrarán todos los números participantes y se eligirán a los 2 afortunados del evento</li>
                                        <li class="mb-2"><i class="fas fa-map-marker-alt text-danger"></i> El regalo será entregado de forma presencial en la ciudad de Puebla, Pue.</li>
                                        <li class="mb-0"><i class="fas fa-handshake text-secondary"></i> Si el ganador(a) se encontrara fuera de la ciudad, se acordará la mejor forma de hacer la entrega</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
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
                        
                        <!-- Imagen del Premio -->
                        <div class="mt-3 mb-3">
                            <img src="premio.jpg" alt="Colágeno Duché - Premio del Sorteo" 
                                 class="img-fluid rounded shadow" 
                                 style="max-height: 300px; object-fit: contain;">
                        </div>
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
                            <strong>Números apartados temporalmente:</strong> 
                            <span id="blockingTimeLeft">2:00</span> restantes para completar el registro
                        </div>
                        
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required>
                            <div class="form-text">Este nombre aparecerá en todos los números seleccionados</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label">Número Celular *</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required 
                                   pattern="[0-9]{10}" maxlength="10" placeholder="##########">
                            <div class="form-text">10 dígitos sin espacios - identificación de tus boletos</div>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fab fa-whatsapp"></i> 
                            <strong>¡Importante!</strong> Al confirmar tu participación se abrirá automáticamente el grupo de WhatsApp. Es <strong>REQUISITO</strong> unirse para participar en el evento en vivo.
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Información de Pago</h6>
                            <div class="mb-2">
                                <strong>CLABE:</strong>
                                <div class="input-group input-group-sm mt-1">
                                    <input type="text" class="form-control" value="012180015545193401" readonly id="clabeInput">
                                    <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('clabeInput')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <p><strong>Banco:</strong> BBVA</p>
                            <p><strong>Titular:</strong> Ileana Pineda Calderón</p>
                            <p><strong>Monto por boleto:</strong> $25.00 MXN</p>
                            <p><strong>Total a pagar:</strong> $<span id="totalAmount">25.00</span> MXN</p>
                            <div class="mb-2">
                                <strong>Concepto:</strong>
                                <div class="input-group input-group-sm mt-1">
                                    <input type="text" class="form-control" value="Apoyo a Pahuata" readonly id="conceptoRegistro">
                                    <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('conceptoRegistro')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" onclick="submitRegistration()">
                                <i class="fas fa-check"></i> Confirmar Participación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Datos de Pago -->
    <div class="modal fade" id="paymentDataModal" tabindex="-1" aria-labelledby="paymentDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="paymentDataModalLabel">
                        <i class="fas fa-credit-card"></i> Datos para Depósito Bancario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Realiza tu depósito con estos datos exactos y conserva tu comprobante.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-success mb-3">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-university"></i> Información Bancaria
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">CLABE:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="012180015545193401" readonly id="clabeInputModal">
                                            <button class="btn btn-outline-success" type="button" onclick="copyToClipboard('clabeInputModal')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Banco:</label>
                                        <input type="text" class="form-control" value="BBVA" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titular:</label>
                                        <input type="text" class="form-control" value="Ileana Pineda Calderón" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-warning mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <i class="fas fa-dollar-sign"></i> Información de Pago
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Monto por boleto:</label>
                                        <div class="h4 text-success">$25.00 MXN</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Concepto sugerido:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="Apoyo a Pahuata" readonly id="conceptoInput">
                                            <button class="btn btn-outline-warning" type="button" onclick="copyToClipboard('conceptoInput')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Incluye tu número celular al concepto</div>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Recuerda:</strong> Tienes 15 minutos para realizar el pago después de reservar tus números.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Pasos para el pago:</h6>
                        <ol class="mb-0">
                            <li>Copia la CLABE y realiza la transferencia desde tu banco</li>
                            <li>Usa el concepto sugerido para identificar tu pago</li>
                            <li>Conserva tu comprobante de transferencia</li>
                            <li>El pago se confirmará automáticamente en unos minutos</li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Consulta de Boletos -->
    <div class="modal fade" id="consultaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-search"></i> Consulta tus Boletos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="consultaForm">
                        <div class="mb-3">
                            <label for="consultaPhone" class="form-label">Número Celular *</label>
                            <input type="tel" class="form-control" id="consultaPhone" name="consultaPhone" 
                                   pattern="[0-9]{10}" maxlength="10" placeholder="##########" required>
                            <div class="form-text">Ingresa el número celular con el que compraste tus boletos</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> Buscar Mis Boletos
                            </button>
                        </div>
                    </form>
                    
                    <!-- Resultados de la consulta -->
                    <div id="consultaResultados" class="mt-4" style="display: none;">
                        <hr>
                        <h6><i class="fas fa-ticket-alt"></i> Tus Boletos:</h6>
                        <div id="boletosEncontrados"></div>
                    </div>
                    
                    <!-- Loading -->
                    <div id="consultaLoading" class="text-center mt-4" style="display: none;">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Buscando...</span>
                        </div>
                        <p class="mt-2">Buscando tus boletos...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pahuata -->
    <div class="modal fade" id="pahuataModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-mountain"></i> Conoce Pahuata</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h6 class="text-muted">Pahuata: aldea en Municipio de Xochitlán de Vicente Suárez, Estado de Puebla.</h6>
                    </div>
                    
                    <p class="lead">¡Hola! Permítanme presentarles a Pahuata, una comunidad súper importante en la Sierra Norte de Puebla.</p>
                    
                    <p>Es un lugar pequeño (es el cuarto pueblo más grande de su municipio), rodeado de montañas y con una rica cultura indígena; muchos de sus habitantes son hablantes de náhuatl.</p>
                    
                    <p>La gente aquí es trabajadora, se dedican principalmente a la agricultura, produciendo café, guayaba y frijol.</p>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Desafíos que Enfrenta</h6>
                        <p class="mb-0">Pero Pahuata enfrenta grandes desafíos: la mayoría de las familias viven en un ambiente rural con pobreza y alta marginación. Esto se traduce en carencias serias de acceso a comida nutritiva, afectando la salud y causando rezago educativo (el promedio de escolaridad es muy bajo).</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-hands-helping"></i> Por Qué Es Importante Ayudar</h6>
                        <p class="mb-0">Por eso, llevar víveres y apoyo educativo es vital. No solo es una ayuda, es un acto de empatía y amor para que los niños tengan un futuro más brillante, combatir la desnutrición y asegurar que la lengua y costumbres ancestrales no se pierdan.</p>
                    </div>
                    
                    <p class="fw-bold">Apoyar a Pahuata significa darle a sus productores las herramientas para que salgan adelante.</p>
                    
                    <div class="text-center mt-4">
                        <p class="h5 text-success">Gracias por tu apoyo <i class="fas fa-heart text-danger"></i></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sorteo.js"></script>
    
    <script>
        // Función para copiar al portapapeles (API moderna)
        async function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const textToCopy = element.value;
            
            try {
                // Intentar usar la API moderna del portapapeles
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(textToCopy);
                    console.log('✅ Copiado con navigator.clipboard');
                } else {
                    // Fallback para navegadores más antiguos
                    element.select();
                    element.setSelectionRange(0, 99999);
                    document.execCommand('copy');
                    console.log('✅ Copiado con execCommand (fallback)');
                }
                
                // Mostrar feedback visual
                const button = element.nextElementSibling || element.parentElement.querySelector('button');
                if (button) {
                    const originalHTML = button.innerHTML;
                    const originalClasses = button.className;
                    
                    button.innerHTML = '<i class="fas fa-check"></i> Copiado';
                    button.className = 'btn btn-success';
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.className = originalClasses;
                    }, 2000);
                }
                
            } catch (err) {
                console.error('❌ Error al copiar:', err);
                
                // Mostrar error visual
                const button = element.nextElementSibling || element.parentElement.querySelector('button');
                if (button) {
                    const originalHTML = button.innerHTML;
                    const originalClasses = button.className;
                    
                    button.innerHTML = '<i class="fas fa-times"></i> Error';
                    button.className = 'btn btn-danger';
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.className = originalClasses;
                    }, 2000);
                }
                
                // Fallback manual: seleccionar texto para que el usuario pueda copiarlo
                element.select();
                element.setSelectionRange(0, 99999);
                alert('No se pudo copiar automáticamente. El texto está seleccionado, usa Ctrl+C para copiarlo.');
            }
        }
    </script>
</body>
</html>
