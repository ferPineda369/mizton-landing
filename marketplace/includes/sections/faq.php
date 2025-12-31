<?php
/**
 * Sección FAQ - Preguntas frecuentes
 */

$sectionData = $section['section_data'] ?? [];

// Obtener FAQs
$faqs = getProjectFAQs($project['id']);

if (empty($faqs)) {
    return; // No mostrar sección si no hay FAQs
}
?>

<section class="section section-faq">
    <div class="section-container">
        <div class="section-title">
            <h2><?php echo htmlspecialchars($section['section_title'] ?? 'Preguntas Frecuentes'); ?></h2>
            <?php if (!empty($section['section_subtitle'])): ?>
                <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="faq-list">
            <?php foreach ($faqs as $faq): ?>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo htmlspecialchars($faq['question']); ?></span>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
