<?php
/**
 * Sección About - Descripción del proyecto
 */

$sectionData = $section['section_data'] ?? [];
$content = $sectionData['content'] ?? $project['long_description'] ?? $project['description'];
?>

<section class="section section-about">
    <div class="section-container">
        <div class="section-title">
            <h2><?php echo htmlspecialchars($section['section_title'] ?? 'Sobre el Proyecto'); ?></h2>
            <?php if (!empty($section['section_subtitle'])): ?>
                <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="section-content">
            <div class="about-content">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</section>
