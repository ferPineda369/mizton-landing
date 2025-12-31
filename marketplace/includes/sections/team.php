<?php
/**
 * Sección Team - Equipo del proyecto
 */

$sectionData = $section['section_data'] ?? [];

// Obtener miembros del equipo
$teamMembers = getProjectTeam($project['id']);

if (empty($teamMembers)) {
    return; // No mostrar sección si no hay equipo
}
?>

<section class="section section-team">
    <div class="section-container">
        <div class="section-title">
            <h2><?php echo htmlspecialchars($section['section_title'] ?? 'Nuestro Equipo'); ?></h2>
            <?php if (!empty($section['section_subtitle'])): ?>
                <p class="subtitle"><?php echo htmlspecialchars($section['section_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="team-grid">
            <?php foreach ($teamMembers as $member): ?>
                <div class="team-member">
                    <?php if (!empty($member['member_photo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($member['member_photo_url']); ?>" 
                             alt="<?php echo htmlspecialchars($member['member_name']); ?>"
                             class="member-photo">
                    <?php else: ?>
                        <div class="member-photo" style="background: var(--bg-light); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--text-color);"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="member-info">
                        <h3 class="member-name"><?php echo htmlspecialchars($member['member_name']); ?></h3>
                        <p class="member-role"><?php echo htmlspecialchars($member['member_role']); ?></p>
                        
                        <?php if (!empty($member['member_bio'])): ?>
                            <p class="member-bio"><?php echo htmlspecialchars($member['member_bio']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($member['social_links'])): ?>
                            <div class="member-social">
                                <?php if (!empty($member['social_links']['linkedin'])): ?>
                                    <a href="<?php echo htmlspecialchars($member['social_links']['linkedin']); ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($member['social_links']['twitter'])): ?>
                                    <a href="<?php echo htmlspecialchars($member['social_links']['twitter']); ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-twitter"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($member['social_links']['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($member['social_links']['email']); ?>">
                                        <i class="bi bi-envelope"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
