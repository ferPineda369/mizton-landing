<?php
/**
 * Sección Genérica - Para tipos de sección sin template específico
 */

$sectionData = $section['section_data'] ?? [];
$content = $sectionData['content'] ?? '';

// Determinar estilo de fondo alternando
$sectionIndex = $sectionIndex ?? 0;
$bgClass = ($sectionIndex % 2 == 0) ? 'section-white' : 'section-light';
?>

<section class="section section-generic <?php echo $bgClass; ?>">
    <div class="section-container">
        <?php if (!empty($section['section_title'])): ?>
            <div class="section-title">
                <h2><?php echo htmlspecialchars($section['section_title']); ?></h2>
                <?php if (!empty($section['section_subtitle'])): ?>
                    <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($content)): ?>
            <div class="section-content">
                <div class="generic-content">
                    <?php echo $content; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="section-content" style="text-align: center; padding: 40px 20px;">
                <p style="color: var(--text-color);">Contenido en preparación</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.section-white {
    background: white;
}

.section-light {
    background: var(--bg-light);
}

.generic-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text-color);
}

.generic-content h3 {
    color: var(--primary-color);
    margin: 30px 0 15px;
}

.generic-content p {
    margin-bottom: 20px;
}

.generic-content ul,
.generic-content ol {
    margin-bottom: 20px;
    padding-left: 30px;
}

.generic-content li {
    margin-bottom: 10px;
}
</style>
