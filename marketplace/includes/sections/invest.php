<?php
/**
 * Sección Invest - Llamado a la acción para invertir
 */

$sectionData = $section['section_data'] ?? [];

// Configurar CTA según estado de autenticación
if ($isUserLoggedIn) {
    $ctaText = $sectionData['cta_text'] ?? 'Invertir Ahora';
    $ctaLink = '/marketplace/marketplace-reserve.php?project_id=' . $project['id'];
} else {
    $ctaText = 'Iniciar Sesión para Invertir';
    $reserveUrl = '/marketplace/marketplace-reserve.php?project_id=' . $project['id'];
    $ctaLink = 'https://panel.mizton.cat/login.php?redirect=' . urlencode($reserveUrl);
}
?>

<section id="section-invest" class="section section-invest">
    <div class="section-container">
        <div class="section-title">
            <h2><?php echo htmlspecialchars($section['section_title'] ?? '¿Listo para Invertir?'); ?></h2>
            <?php if (!empty($section['section_subtitle'])): ?>
                <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="invest-stats">
            <div class="invest-stat">
                <span class="invest-stat-value">$<?php echo number_format($project['token_price_usd'], 2); ?></span>
                <span class="invest-stat-label">Precio por Token</span>
            </div>
            
            <div class="invest-stat">
                <span class="invest-stat-value">$<?php echo number_format($project['funding_goal'], 0); ?></span>
                <span class="invest-stat-label">Meta de Financiamiento</span>
            </div>
            
            <div class="invest-stat">
                <span class="invest-stat-value"><?php echo number_format($project['funding_percentage']); ?>%</span>
                <span class="invest-stat-label">Financiado</span>
            </div>
        </div>
        
        <div class="invest-cta">
            <?php if (!$isUserLoggedIn): ?>
                <p style="margin-bottom: 20px; color: #666; font-size: 0.95rem;">
                    <i class="bi bi-info-circle"></i> 
                    Necesitas tener una cuenta en Mizton para realizar reservas de tokens
                </p>
            <?php endif; ?>
            
            <a href="<?php echo htmlspecialchars($ctaLink); ?>" class="btn-invest">
                <?php echo htmlspecialchars($ctaText); ?>
            </a>
            
            <?php if (!$isUserLoggedIn): ?>
                <p style="margin-top: 15px; font-size: 0.9rem;">
                    ¿No tienes cuenta? 
                    <a href="https://panel.mizton.cat/register.php" style="color: #4CAF50; text-decoration: underline;">
                        Regístrate gratis aquí
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>
