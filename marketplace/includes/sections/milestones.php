<?php
/**
 * Sección Milestones - Hitos del proyecto
 */

$sectionData = $section['section_data'] ?? [];

// Obtener milestones
$milestones = $project['milestones'] ?? [];

if (empty($milestones)) {
    return; // No mostrar sección si no hay milestones
}
?>

<section class="section section-milestones">
    <div class="section-container">
        <div class="section-title">
            <h2><?php echo htmlspecialchars($section['section_title'] ?? 'Roadmap'); ?></h2>
            <?php if (!empty($section['section_subtitle'])): ?>
                <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="timeline">
            <?php foreach ($milestones as $milestone): ?>
                <div class="milestone-item">
                    <div class="milestone-dot"></div>
                    <div class="milestone-content">
                        <div class="milestone-date">
                            <?php echo date('M Y', strtotime($milestone['target_date'])); ?>
                        </div>
                        <h3 class="milestone-title"><?php echo htmlspecialchars($milestone['milestone_name']); ?></h3>
                        <?php if (!empty($milestone['description'])): ?>
                            <p class="milestone-description"><?php echo htmlspecialchars($milestone['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($milestone['progress_percentage'] > 0): ?>
                            <div class="milestone-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $milestone['progress_percentage']; ?>%"></div>
                                </div>
                                <small style="color: var(--text-color); margin-top: 5px; display: block;">
                                    <?php echo $milestone['progress_percentage']; ?>% completado
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
