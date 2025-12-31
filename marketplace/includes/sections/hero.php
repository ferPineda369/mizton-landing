<?php
/**
 * Sección Hero - Portada principal del proyecto
 */

$sectionData = $section['section_data'] ?? [];
$backgroundImage = $sectionData['background_image'] ?? $project['main_image_url'] ?? '';
$ctaText = $sectionData['cta_text'] ?? 'Invertir Ahora';
$ctaLink = $sectionData['cta_link'] ?? '#invest';
$secondaryCta = $sectionData['secondary_cta'] ?? null;

// Stats para mostrar
$stats = [
    [
        'value' => '$' . number_format($project['token_price_usd'], 2),
        'label' => 'Precio por Token'
    ],
    [
        'value' => number_format($project['funding_percentage']) . '%',
        'label' => 'Financiamiento'
    ],
    [
        'value' => number_format($project['holders_count'] ?? 0),
        'label' => 'Inversores'
    ]
];
?>

<section class="section section-hero" style="background-image: url('<?php echo htmlspecialchars($backgroundImage); ?>');">
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($section['section_title'] ?? $project['name']); ?></h1>
        
        <?php if (!empty($section['section_subtitle'])): ?>
            <p class="hero-subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
        <?php endif; ?>
        
        <div class="hero-stats">
            <?php foreach ($stats as $stat): ?>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $stat['value']; ?></span>
                    <span class="stat-label"><?php echo $stat['label']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cta-buttons">
            <a href="<?php echo htmlspecialchars($ctaLink); ?>" class="btn-hero btn-hero-primary">
                <?php echo htmlspecialchars($ctaText); ?>
            </a>
            
            <?php if ($secondaryCta): ?>
                <a href="<?php echo htmlspecialchars($secondaryCta['link'] ?? '#'); ?>" class="btn-hero btn-hero-secondary">
                    <?php echo htmlspecialchars($secondaryCta['text'] ?? 'Más Información'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
