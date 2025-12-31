<?php
/**
 * Sección Gallery - Galería de imágenes del proyecto
 */

$sectionData = $section['section_data'] ?? [];

// Obtener imágenes de la galería
$galleryImages = [];
if (!empty($sectionData['images'])) {
    $galleryImages = $sectionData['images'];
} else {
    // Obtener de la tabla de media
    $galleryMedia = getProjectMedia($project['id'], 'image', 'gallery');
    foreach ($galleryMedia as $media) {
        $galleryImages[] = [
            'url' => $media['file_url'],
            'title' => $media['title'] ?? '',
            'alt' => $media['alt_text'] ?? $media['title'] ?? ''
        ];
    }
}

if (empty($galleryImages)) {
    return; // No mostrar sección si no hay imágenes
}
?>

<section class="section section-gallery">
    <div class="section-container">
        <div class="section-title">
            <h2><?php echo htmlspecialchars($section['section_title'] ?? 'Galería'); ?></h2>
            <?php if (!empty($section['section_subtitle'])): ?>
                <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="gallery-grid">
            <?php foreach ($galleryImages as $image): ?>
                <div class="gallery-item" data-image="<?php echo htmlspecialchars($image['url']); ?>">
                    <img src="<?php echo htmlspecialchars($image['url']); ?>" 
                         alt="<?php echo htmlspecialchars($image['alt'] ?? ''); ?>"
                         loading="lazy">
                    <div class="gallery-overlay">
                        <i class="bi bi-zoom-in"></i>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" style="display: none;">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-content" id="lightbox-img">
    <div class="lightbox-caption" id="lightbox-caption"></div>
</div>
