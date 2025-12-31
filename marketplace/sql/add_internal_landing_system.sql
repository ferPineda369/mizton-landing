-- =====================================================
-- Migración: Sistema de Landing Pages Internas
-- Fecha: 2025-12-30
-- Descripción: Agrega soporte para proyectos sin sitio web propio
-- =====================================================

-- 1. Agregar campos a la tabla principal de proyectos
ALTER TABLE tbl_marketplace_projects
ADD COLUMN has_internal_landing BOOLEAN DEFAULT FALSE AFTER website_url,
ADD COLUMN project_type VARCHAR(50) DEFAULT 'general' AFTER has_internal_landing,
ADD COLUMN long_description TEXT AFTER description,
ADD INDEX idx_project_type (project_type),
ADD INDEX idx_internal_landing (has_internal_landing);

-- 2. Tabla de Metadatos Flexibles (EAV)
CREATE TABLE IF NOT EXISTS tbl_marketplace_project_metadata (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    meta_key VARCHAR(100) NOT NULL,
    meta_value TEXT,
    meta_type ENUM('text', 'number', 'date', 'json', 'url', 'boolean') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_meta (project_id, meta_key),
    INDEX idx_project_key (project_id, meta_key),
    INDEX idx_meta_key (meta_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Secciones de Contenido
CREATE TABLE IF NOT EXISTS tbl_marketplace_project_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    section_type VARCHAR(50) NOT NULL,
    section_title VARCHAR(200),
    section_subtitle VARCHAR(300),
    section_order INT DEFAULT 0,
    section_data JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project_order (project_id, section_order),
    INDEX idx_project_type (project_id, section_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Media (Imágenes, Videos, Documentos)
CREATE TABLE IF NOT EXISTS tbl_marketplace_project_media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    media_type ENUM('image', 'video', 'document', 'audio') NOT NULL,
    media_category VARCHAR(50),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_size INT COMMENT 'Tamaño en bytes',
    mime_type VARCHAR(100),
    title VARCHAR(200),
    description TEXT,
    alt_text VARCHAR(255),
    display_order INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    metadata JSON COMMENT 'Dimensiones, duración, etc.',
    uploaded_by INT COMMENT 'ID del usuario que subió',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project_type (project_id, media_type),
    INDEX idx_project_category (project_id, media_category),
    INDEX idx_media_type (media_type),
    INDEX idx_featured (is_featured),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de Miembros del Equipo
CREATE TABLE IF NOT EXISTS tbl_marketplace_project_team (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    member_name VARCHAR(200) NOT NULL,
    member_role VARCHAR(100) NOT NULL,
    member_bio TEXT,
    member_photo_url VARCHAR(500),
    social_links JSON COMMENT 'LinkedIn, Twitter, etc.',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project_order (project_id, display_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de FAQ
CREATE TABLE IF NOT EXISTS tbl_marketplace_project_faq (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project_order (project_id, display_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla de Testimonios
CREATE TABLE IF NOT EXISTS tbl_marketplace_project_testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    author_name VARCHAR(200) NOT NULL,
    author_role VARCHAR(100),
    author_photo_url VARCHAR(500),
    testimonial_text TEXT NOT NULL,
    rating DECIMAL(2,1) COMMENT 'Rating de 0.0 a 5.0',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project_order (project_id, display_order),
    INDEX idx_active (is_active),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos de ejemplo para testing
-- =====================================================

-- Comentar estas líneas en producción si no se necesitan datos de prueba

/*
-- Ejemplo: Proyecto de libro
UPDATE tbl_marketplace_projects 
SET 
    has_internal_landing = TRUE,
    project_type = 'book',
    long_description = '<h2>Sobre el Libro</h2><p>Una obra revolucionaria que explica en detalle el proceso de tokenización de activos reales...</p>'
WHERE project_code = 'LIBRO1';

-- Metadatos del libro
INSERT INTO tbl_marketplace_project_metadata (project_id, meta_key, meta_value, meta_type) VALUES
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'book_isbn', '978-1234567890', 'text'),
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'book_pages', '320', 'number'),
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'book_genre', 'Finanzas/Educación', 'text'),
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'book_author', 'Fernando Pineda', 'text'),
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'book_language', 'es', 'text');

-- Sección Hero
INSERT INTO tbl_marketplace_project_sections (project_id, section_type, section_title, section_subtitle, section_order, section_data) VALUES
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'hero', 'El Libro de la Tokenización', 'La guía definitiva para entender la tokenización de activos reales', 1, 
'{"cta_text": "Invertir en este proyecto", "cta_link": "#invest", "background_image": "/marketplace/projects/LIBRO1/images/hero.jpg"}');

-- Sección About
INSERT INTO tbl_marketplace_project_sections (project_id, section_type, section_title, section_order, section_data, is_active) VALUES
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'about', 'Sobre el Libro', 2, 
'{"content": "<p>Una obra revolucionaria que explica el futuro de las finanzas...</p>"}', TRUE);

-- Miembro del equipo (Autor)
INSERT INTO tbl_marketplace_project_team (project_id, member_name, member_role, member_bio, display_order) VALUES
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), 'Fernando Pineda', 'Autor', 
'Experto en tokenización de activos reales con más de 10 años de experiencia en fintech...', 1);

-- FAQ
INSERT INTO tbl_marketplace_project_faq (project_id, question, answer, display_order) VALUES
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), '¿Cuándo estará disponible el libro?', 
'El libro estará disponible en formato digital e impreso a partir de junio 2025.', 1),
((SELECT id FROM tbl_marketplace_projects WHERE project_code = 'LIBRO1'), '¿En qué idiomas estará disponible?', 
'Inicialmente en español, con traducciones a inglés y portugués previstas para el segundo semestre de 2025.', 2);
*/

-- =====================================================
-- Verificación de la migración
-- =====================================================

-- Verificar que las tablas se crearon correctamente
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN (
    'tbl_marketplace_project_metadata',
    'tbl_marketplace_project_sections',
    'tbl_marketplace_project_media',
    'tbl_marketplace_project_team',
    'tbl_marketplace_project_faq',
    'tbl_marketplace_project_testimonials'
)
ORDER BY TABLE_NAME;

-- Verificar columnas agregadas a tbl_marketplace_projects
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_marketplace_projects'
AND COLUMN_NAME IN ('has_internal_landing', 'project_type', 'long_description')
ORDER BY ORDINAL_POSITION;
