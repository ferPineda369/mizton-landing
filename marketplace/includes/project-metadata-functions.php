<?php
/**
 * Funciones para Gestión de Metadata de Proyectos
 * Sistema EAV para campos personalizados por tipo de proyecto
 */

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../config/project-types-config.php';

// ==================== METADATA ====================

/**
 * Guardar metadata de un proyecto
 */
function saveProjectMetadata($projectId, $metaKey, $metaValue, $metaType = 'text') {
    $db = getMarketplaceDB();
    
    $sql = "INSERT INTO tbl_marketplace_project_metadata 
            (project_id, meta_key, meta_value, meta_type) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            meta_value = VALUES(meta_value),
            meta_type = VALUES(meta_type),
            updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $db->prepare($sql);
    return $stmt->execute([$projectId, $metaKey, $metaValue, $metaType]);
}

/**
 * Guardar múltiples metadatos de un proyecto
 */
function saveProjectMetadataBulk($projectId, $metadataArray) {
    $db = getMarketplaceDB();
    $db->beginTransaction();
    
    try {
        foreach ($metadataArray as $key => $data) {
            $value = $data['value'] ?? $data;
            $type = $data['type'] ?? 'text';
            saveProjectMetadata($projectId, $key, $value, $type);
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error saving project metadata: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener metadata de un proyecto
 */
function getProjectMetadata($projectId, $metaKey = null) {
    $db = getMarketplaceDB();
    
    if ($metaKey) {
        $stmt = $db->prepare("
            SELECT meta_value, meta_type 
            FROM tbl_marketplace_project_metadata 
            WHERE project_id = ? AND meta_key = ?
        ");
        $stmt->execute([$projectId, $metaKey]);
        $result = $stmt->fetch();
        return $result ? $result['meta_value'] : null;
    } else {
        $stmt = $db->prepare("
            SELECT meta_key, meta_value, meta_type 
            FROM tbl_marketplace_project_metadata 
            WHERE project_id = ?
        ");
        $stmt->execute([$projectId]);
        $metadata = [];
        while ($row = $stmt->fetch()) {
            $metadata[$row['meta_key']] = [
                'value' => $row['meta_value'],
                'type' => $row['meta_type']
            ];
        }
        return $metadata;
    }
}

/**
 * Eliminar metadata de un proyecto
 */
function deleteProjectMetadata($projectId, $metaKey = null) {
    $db = getMarketplaceDB();
    
    if ($metaKey) {
        $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_metadata WHERE project_id = ? AND meta_key = ?");
        return $stmt->execute([$projectId, $metaKey]);
    } else {
        $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_metadata WHERE project_id = ?");
        return $stmt->execute([$projectId]);
    }
}

// ==================== SECCIONES ====================

/**
 * Crear sección de proyecto
 */
function createProjectSection($projectId, $sectionType, $sectionTitle, $sectionData, $sectionOrder = 0) {
    $db = getMarketplaceDB();
    
    $sql = "INSERT INTO tbl_marketplace_project_sections 
            (project_id, section_type, section_title, section_data, section_order) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $jsonData = is_array($sectionData) ? json_encode($sectionData) : $sectionData;
    
    return $stmt->execute([$projectId, $sectionType, $sectionTitle, $jsonData, $sectionOrder]);
}

/**
 * Actualizar sección de proyecto
 */
function updateProjectSection($sectionId, $sectionTitle, $sectionData, $sectionOrder = null) {
    $db = getMarketplaceDB();
    
    $sql = "UPDATE tbl_marketplace_project_sections 
            SET section_title = ?, section_data = ?";
    
    $params = [$sectionTitle];
    $jsonData = is_array($sectionData) ? json_encode($sectionData) : $sectionData;
    $params[] = $jsonData;
    
    if ($sectionOrder !== null) {
        $sql .= ", section_order = ?";
        $params[] = $sectionOrder;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $sectionId;
    
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Obtener secciones de un proyecto
 */
function getProjectSections($projectId, $activeOnly = true) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM tbl_marketplace_project_sections WHERE project_id = ?";
    if ($activeOnly) {
        $sql .= " AND is_active = TRUE";
    }
    $sql .= " ORDER BY section_order ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$projectId]);
    
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decodificar JSON data
    foreach ($sections as &$section) {
        $section['section_data'] = json_decode($section['section_data'], true);
    }
    
    return $sections;
}

/**
 * Eliminar sección de proyecto
 */
function deleteProjectSection($sectionId) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_sections WHERE id = ?");
    return $stmt->execute([$sectionId]);
}

/**
 * Reordenar secciones de un proyecto
 */
function reorderProjectSections($projectId, $sectionIdsInOrder) {
    $db = getMarketplaceDB();
    $db->beginTransaction();
    
    try {
        $order = 1;
        foreach ($sectionIdsInOrder as $sectionId) {
            $stmt = $db->prepare("
                UPDATE tbl_marketplace_project_sections 
                SET section_order = ? 
                WHERE id = ? AND project_id = ?
            ");
            $stmt->execute([$order, $sectionId, $projectId]);
            $order++;
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error reordering sections: " . $e->getMessage());
        return false;
    }
}

// ==================== MEDIA ====================

/**
 * Guardar archivo de media
 */
function saveProjectMedia($projectId, $mediaType, $mediaCategory, $fileData, $metadata = []) {
    $db = getMarketplaceDB();
    
    $sql = "INSERT INTO tbl_marketplace_project_media 
            (project_id, media_type, media_category, file_name, file_path, file_url, 
             file_size, mime_type, title, description, alt_text, display_order, 
             is_featured, metadata, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    
    return $stmt->execute([
        $projectId,
        $mediaType,
        $mediaCategory,
        $fileData['file_name'],
        $fileData['file_path'],
        $fileData['file_url'],
        $fileData['file_size'] ?? null,
        $fileData['mime_type'] ?? null,
        $fileData['title'] ?? null,
        $fileData['description'] ?? null,
        $fileData['alt_text'] ?? null,
        $fileData['display_order'] ?? 0,
        $fileData['is_featured'] ?? false,
        json_encode($metadata),
        $_SESSION['user_id'] ?? null
    ]);
}

/**
 * Obtener media de un proyecto
 */
function getProjectMedia($projectId, $mediaType = null, $mediaCategory = null) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM tbl_marketplace_project_media WHERE project_id = ?";
    $params = [$projectId];
    
    if ($mediaType) {
        $sql .= " AND media_type = ?";
        $params[] = $mediaType;
    }
    
    if ($mediaCategory) {
        $sql .= " AND media_category = ?";
        $params[] = $mediaCategory;
    }
    
    $sql .= " ORDER BY display_order ASC, uploaded_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decodificar metadata JSON
    foreach ($media as &$item) {
        $item['metadata'] = json_decode($item['metadata'], true);
    }
    
    return $media;
}

/**
 * Eliminar archivo de media
 */
function deleteProjectMedia($mediaId) {
    $db = getMarketplaceDB();
    
    // Obtener info del archivo para eliminarlo físicamente
    $stmt = $db->prepare("SELECT file_path FROM tbl_marketplace_project_media WHERE id = ?");
    $stmt->execute([$mediaId]);
    $media = $stmt->fetch();
    
    if ($media && file_exists($media['file_path'])) {
        unlink($media['file_path']);
    }
    
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_media WHERE id = ?");
    return $stmt->execute([$mediaId]);
}

// ==================== EQUIPO ====================

/**
 * Agregar miembro del equipo
 */
function addTeamMember($projectId, $memberData) {
    $db = getMarketplaceDB();
    
    $sql = "INSERT INTO tbl_marketplace_project_team 
            (project_id, member_name, member_role, member_bio, member_photo_url, 
             social_links, display_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    
    return $stmt->execute([
        $projectId,
        $memberData['name'],
        $memberData['role'],
        $memberData['bio'] ?? null,
        $memberData['photo_url'] ?? null,
        json_encode($memberData['social_links'] ?? []),
        $memberData['display_order'] ?? 0
    ]);
}

/**
 * Obtener equipo de un proyecto
 */
function getProjectTeam($projectId, $activeOnly = true) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM tbl_marketplace_project_team WHERE project_id = ?";
    if ($activeOnly) {
        $sql .= " AND is_active = TRUE";
    }
    $sql .= " ORDER BY display_order ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$projectId]);
    
    $team = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($team as &$member) {
        $member['social_links'] = json_decode($member['social_links'], true);
    }
    
    return $team;
}

/**
 * Eliminar miembro del equipo
 */
function deleteTeamMember($memberId) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_team WHERE id = ?");
    return $stmt->execute([$memberId]);
}

// ==================== FAQ ====================

/**
 * Agregar pregunta FAQ
 */
function addProjectFAQ($projectId, $question, $answer, $displayOrder = 0) {
    $db = getMarketplaceDB();
    
    $sql = "INSERT INTO tbl_marketplace_project_faq 
            (project_id, question, answer, display_order) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    return $stmt->execute([$projectId, $question, $answer, $displayOrder]);
}

/**
 * Obtener FAQs de un proyecto
 */
function getProjectFAQs($projectId, $activeOnly = true) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM tbl_marketplace_project_faq WHERE project_id = ?";
    if ($activeOnly) {
        $sql .= " AND is_active = TRUE";
    }
    $sql .= " ORDER BY display_order ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$projectId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Eliminar FAQ
 */
function deleteProjectFAQ($faqId) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_faq WHERE id = ?");
    return $stmt->execute([$faqId]);
}

// ==================== TESTIMONIOS ====================

/**
 * Agregar testimonio
 */
function addProjectTestimonial($projectId, $testimonialData) {
    $db = getMarketplaceDB();
    
    $sql = "INSERT INTO tbl_marketplace_project_testimonials 
            (project_id, author_name, author_role, author_photo_url, 
             testimonial_text, rating, display_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    
    return $stmt->execute([
        $projectId,
        $testimonialData['author_name'],
        $testimonialData['author_role'] ?? null,
        $testimonialData['author_photo_url'] ?? null,
        $testimonialData['testimonial_text'],
        $testimonialData['rating'] ?? null,
        $testimonialData['display_order'] ?? 0
    ]);
}

/**
 * Obtener testimonios de un proyecto
 */
function getProjectTestimonials($projectId, $activeOnly = true) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM tbl_marketplace_project_testimonials WHERE project_id = ?";
    if ($activeOnly) {
        $sql .= " AND is_active = TRUE";
    }
    $sql .= " ORDER BY display_order ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$projectId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Eliminar testimonio
 */
function deleteProjectTestimonial($testimonialId) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_project_testimonials WHERE id = ?");
    return $stmt->execute([$testimonialId]);
}

// ==================== UTILIDADES ====================

/**
 * NOTA: La función getCompleteProject() está definida en marketplace-functions.php
 * para evitar duplicación y conflictos. Esa versión es más flexible y maneja
 * tanto IDs como códigos de proyecto, además de manejar tablas opcionales.
 */

/**
 * Crear secciones por defecto para un tipo de proyecto
 */
function createDefaultSections($projectId, $projectType) {
    $defaultSections = getProjectTypeDefaultSections($projectType);
    
    $order = 1;
    foreach ($defaultSections as $sectionType) {
        $title = ucfirst(str_replace('_', ' ', $sectionType));
        createProjectSection($projectId, $sectionType, $title, [], $order);
        $order++;
    }
    
    return true;
}
