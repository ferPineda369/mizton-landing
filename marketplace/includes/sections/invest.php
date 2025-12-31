<?php
/**
 * Sección Invest - Llamado a la acción para invertir
 */

$sectionData = $section['section_data'] ?? [];
$ctaText = $sectionData['cta_text'] ?? 'Invertir en este Proyecto';
$ctaLink = $sectionData['cta_link'] ?? 'https://panel.mizton.cat/';
?>

<section class="section section-invest">
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
            <a href="<?php echo htmlspecialchars($ctaLink); ?>" class="btn-invest">
                <?php echo htmlspecialchars($ctaText); ?>
            </a>
        </div>
    </div>
</section>
