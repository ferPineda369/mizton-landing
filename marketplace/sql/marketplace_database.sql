-- ============================================================================
-- MIZTON MARKETPLACE - BASE DE DATOS
-- Sistema de Showcase de Proyectos Tokenizados
-- Versión: 1.0
-- Fecha: 2025-12-26
-- ============================================================================

-- Tabla principal de proyectos tokenizados
CREATE TABLE IF NOT EXISTS tbl_marketplace_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Información básica
    project_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Código único del proyecto (ej: LIBRO1, FIT1)',
    name VARCHAR(255) NOT NULL COMMENT 'Nombre del proyecto',
    slug VARCHAR(255) UNIQUE NOT NULL COMMENT 'URL-friendly slug',
    category ENUM(
        'inmobiliario', 
        'energia', 
        'arte', 
        'agropecuario', 
        'industrial', 
        'minero', 
        'farmaceutico', 
        'gubernamental',
        'deportivo', 
        'cinematografia', 
        'musical', 
        'editorial',
        'tecnologia',
        'otro'
    ) NOT NULL COMMENT 'Categoría del proyecto',
    
    description TEXT COMMENT 'Descripción completa del proyecto',
    short_description VARCHAR(500) COMMENT 'Descripción corta para cards',
    
    -- Imágenes
    logo_url VARCHAR(500) COMMENT 'URL del logo del proyecto',
    main_image_url VARCHAR(500) COMMENT 'Imagen principal/hero',
    gallery_images JSON COMMENT 'Array de URLs de imágenes adicionales',
    
    -- Estado del proyecto
    status ENUM(
        'desarrollo',
        'preventa', 
        'activo', 
        'financiado', 
        'completado', 
        'pausado',
        'cerrado'
    ) DEFAULT 'desarrollo' COMMENT 'Estado actual del proyecto',
    
    -- Información Blockchain
    contract_address VARCHAR(100) COMMENT 'Dirección del smart contract',
    blockchain_network VARCHAR(50) COMMENT 'Red blockchain (BSC, Ethereum, Polygon, etc)',
    token_symbol VARCHAR(10) COMMENT 'Símbolo del token (ej: FIT, BOOK)',
    block_explorer_url VARCHAR(500) COMMENT 'URL del explorador de bloques',
    
    -- Enlaces externos (sitio dedicado del proyecto)
    website_url VARCHAR(500) COMMENT 'Sitio web principal del proyecto',
    dashboard_url VARCHAR(500) COMMENT 'Dashboard de inversión del proyecto',
    whitepaper_url VARCHAR(500) COMMENT 'URL del whitepaper',
    pitch_deck_url VARCHAR(500) COMMENT 'URL del pitch deck',
    
    -- Redes sociales
    twitter_url VARCHAR(255),
    telegram_url VARCHAR(255),
    discord_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    instagram_url VARCHAR(255),
    
    -- Integración API (para sincronización de datos)
    api_endpoint VARCHAR(500) COMMENT 'Endpoint API del proyecto para obtener datos',
    api_key VARCHAR(255) COMMENT 'API Key si requiere autenticación',
    api_secret VARCHAR(255) COMMENT 'API Secret encriptado',
    update_method ENUM('api_pull', 'webhook', 'manual', 'blockchain') DEFAULT 'manual' COMMENT 'Método de actualización de datos',
    update_frequency INT DEFAULT 5 COMMENT 'Frecuencia de actualización en minutos',
    last_sync_attempt TIMESTAMP NULL COMMENT 'Último intento de sincronización',
    last_successful_sync TIMESTAMP NULL COMMENT 'Última sincronización exitosa',
    sync_status ENUM('never', 'success', 'failed', 'partial') DEFAULT 'never',
    
    -- Datos cacheados (JSON con toda la información del proyecto)
    cached_data JSON COMMENT 'Datos completos del proyecto en formato JSON estándar',
    
    -- Datos financieros básicos (extraídos del cached_data para queries rápidas)
    token_price_usd DECIMAL(15, 6) COMMENT 'Precio actual del token en USD',
    market_cap DECIMAL(20, 2) COMMENT 'Capitalización de mercado',
    total_supply BIGINT COMMENT 'Supply total de tokens',
    circulating_supply BIGINT COMMENT 'Supply en circulación',
    funding_goal DECIMAL(20, 2) COMMENT 'Meta de financiamiento',
    funding_raised DECIMAL(20, 2) COMMENT 'Monto recaudado',
    funding_percentage DECIMAL(5, 2) COMMENT 'Porcentaje de financiamiento',
    apy_percentage DECIMAL(8, 2) COMMENT 'APY/ROI proyectado',
    holders_count INT COMMENT 'Número de holders/inversionistas',
    
    -- Fechas importantes
    presale_start_date DATE COMMENT 'Inicio de preventa',
    presale_end_date DATE COMMENT 'Fin de preventa',
    launch_date DATE COMMENT 'Fecha de lanzamiento',
    
    -- Destacados y ordenamiento
    featured BOOLEAN DEFAULT FALSE COMMENT 'Proyecto destacado en homepage',
    featured_order INT DEFAULT 0 COMMENT 'Orden de proyectos destacados',
    display_order INT DEFAULT 0 COMMENT 'Orden general de visualización',
    
    -- Control de visibilidad
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Proyecto visible en marketplace',
    is_coming_soon BOOLEAN DEFAULT FALSE COMMENT 'Mostrar como "Próximamente"',
    requires_kyc BOOLEAN DEFAULT FALSE COMMENT 'Requiere KYC para invertir',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT COMMENT 'ID del admin que creó el proyecto',
    
    -- Índices para optimización
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_featured (featured, featured_order),
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order),
    INDEX idx_funding (funding_percentage DESC),
    INDEX idx_sync_status (sync_status, last_sync_attempt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tabla de categorías (gestión dinámica de categorías)
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_key VARCHAR(50) UNIQUE NOT NULL COMMENT 'Clave única de categoría',
    category_name VARCHAR(100) NOT NULL COMMENT 'Nombre visible de la categoría',
    category_name_plural VARCHAR(100) COMMENT 'Nombre en plural',
    category_icon VARCHAR(50) COMMENT 'Clase de icono (ej: bi-building)',
    category_color VARCHAR(20) COMMENT 'Color hex para la categoría',
    description TEXT COMMENT 'Descripción de la categoría',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar categorías iniciales
INSERT INTO tbl_marketplace_categories (category_key, category_name, category_name_plural, category_icon, category_color, description, display_order) VALUES
('inmobiliario', 'Inmobiliario', 'Inmobiliarios', 'bi-building', '#3498db', 'Proyectos de tokenización de bienes raíces', 1),
('energia', 'Energía', 'Energía', 'bi-lightning-charge', '#f39c12', 'Proyectos de energía renovable y sostenible', 2),
('editorial', 'Editorial', 'Editoriales', 'bi-book', '#9b59b6', 'Libros, publicaciones y contenido editorial', 3),
('arte', 'Arte', 'Arte', 'bi-palette', '#e74c3c', 'Arte, coleccionables y NFTs', 4),
('musical', 'Musical', 'Musicales', 'bi-music-note-beamed', '#1abc9c', 'Proyectos musicales, giras y producción', 5),
('cinematografia', 'Cinematografía', 'Cinematográficos', 'bi-film', '#34495e', 'Películas, series y producción audiovisual', 6),
('deportivo', 'Deportivo', 'Deportivos', 'bi-trophy', '#27ae60', 'Eventos deportivos y desarrollo de talentos', 7),
('agropecuario', 'Agropecuario', 'Agropecuarios', 'bi-tree', '#16a085', 'Agricultura, ganadería y proyectos del campo', 8),
('industrial', 'Industrial', 'Industriales', 'bi-gear', '#7f8c8d', 'Manufactura, fábricas y producción industrial', 9),
('tecnologia', 'Tecnología', 'Tecnológicos', 'bi-cpu', '#3498db', 'Startups tecnológicas y desarrollo de software', 10),
('minero', 'Minero', 'Mineros', 'bi-gem', '#95a5a6', 'Minería y extracción de recursos', 11),
('farmaceutico', 'Farmacéutico', 'Farmacéuticos', 'bi-capsule', '#e67e22', 'Investigación farmacéutica y desarrollo de medicamentos', 12),
('gubernamental', 'Gubernamental', 'Gubernamentales', 'bi-bank', '#2c3e50', 'Proyectos de tokenización gubernamental', 13),
('otro', 'Otro', 'Otros', 'bi-grid', '#95a5a6', 'Otros proyectos de tokenización', 99);

-- ============================================================================
-- Tabla de logs de sincronización
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_sync_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    sync_method ENUM('api_pull', 'webhook', 'manual', 'blockchain') NOT NULL,
    status ENUM('success', 'failed', 'partial') NOT NULL,
    
    -- Datos de la sincronización
    request_url VARCHAR(500),
    response_code INT,
    response_data JSON COMMENT 'Respuesta completa del API',
    error_message TEXT,
    execution_time_ms INT COMMENT 'Tiempo de ejecución en milisegundos',
    
    -- Metadata
    sync_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    user_agent VARCHAR(255),
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_timestamp (sync_timestamp DESC),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tabla de webhooks recibidos
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_webhooks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    
    -- Datos del webhook
    payload JSON NOT NULL COMMENT 'Payload completo recibido',
    signature VARCHAR(255) COMMENT 'Firma de seguridad del webhook',
    event_type VARCHAR(100) COMMENT 'Tipo de evento (update, funding_change, etc)',
    
    -- Procesamiento
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    processing_error TEXT,
    
    -- Metadata
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    user_agent VARCHAR(255),
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_processed (processed),
    INDEX idx_received (received_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tabla de documentos de proyectos
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    
    document_name VARCHAR(255) NOT NULL,
    document_type ENUM('whitepaper', 'pitch_deck', 'contract', 'audit', 'report', 'legal', 'other') NOT NULL,
    document_url VARCHAR(500) NOT NULL,
    file_size INT COMMENT 'Tamaño en bytes',
    file_format VARCHAR(20) COMMENT 'pdf, docx, etc',
    
    description TEXT,
    is_public BOOLEAN DEFAULT TRUE COMMENT 'Visible públicamente o solo para inversionistas',
    display_order INT DEFAULT 0,
    
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tabla de milestones/hitos del proyecto
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_milestones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    
    milestone_name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    progress_percentage DECIMAL(5, 2) DEFAULT 0,
    
    target_date DATE,
    completed_date DATE,
    
    display_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tabla de configuración del marketplace
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_config (
    config_key VARCHAR(100) PRIMARY KEY,
    config_value TEXT,
    config_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Accesible desde frontend',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones iniciales
INSERT INTO tbl_marketplace_config (config_key, config_value, config_type, description, is_public) VALUES
('marketplace_enabled', 'true', 'boolean', 'Marketplace activo o en mantenimiento', true),
('default_sync_frequency', '5', 'number', 'Frecuencia de sincronización por defecto (minutos)', false),
('featured_projects_limit', '6', 'number', 'Número máximo de proyectos destacados', true),
('require_login_to_view', 'false', 'boolean', 'Requiere login para ver proyectos', true),
('maintenance_mode', 'false', 'boolean', 'Modo mantenimiento del marketplace', true),
('contact_email', 'marketplace@mizton.cat', 'string', 'Email de contacto del marketplace', true),
('webhook_secret', '', 'string', 'Secret para validar webhooks', false);

-- ============================================================================
-- Tabla de estadísticas del marketplace (opcional - para analytics)
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_marketplace_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    
    stat_date DATE NOT NULL,
    
    -- Métricas del día
    views_count INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    click_throughs INT DEFAULT 0 COMMENT 'Clicks al sitio del proyecto',
    
    -- Snapshot de datos financieros
    token_price_snapshot DECIMAL(15, 6),
    market_cap_snapshot DECIMAL(20, 2),
    funding_percentage_snapshot DECIMAL(5, 2),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES tbl_marketplace_projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_date (project_id, stat_date),
    INDEX idx_date (stat_date DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Vistas útiles para queries comunes
-- ============================================================================

-- Vista de proyectos activos con información completa
CREATE OR REPLACE VIEW vw_marketplace_active_projects AS
SELECT 
    p.*,
    c.category_name,
    c.category_icon,
    c.category_color,
    (SELECT COUNT(*) FROM tbl_marketplace_documents WHERE project_id = p.id AND is_public = TRUE) as public_documents_count,
    (SELECT COUNT(*) FROM tbl_marketplace_milestones WHERE project_id = p.id) as milestones_count,
    (SELECT COUNT(*) FROM tbl_marketplace_milestones WHERE project_id = p.id AND status = 'completed') as completed_milestones_count
FROM tbl_marketplace_projects p
LEFT JOIN tbl_marketplace_categories c ON p.category = c.category_key
WHERE p.is_active = TRUE
ORDER BY p.featured DESC, p.featured_order ASC, p.display_order ASC;

-- Vista de estadísticas de sincronización
CREATE OR REPLACE VIEW vw_marketplace_sync_status AS
SELECT 
    p.id,
    p.project_code,
    p.name,
    p.update_method,
    p.sync_status,
    p.last_successful_sync,
    p.last_sync_attempt,
    TIMESTAMPDIFF(MINUTE, p.last_successful_sync, NOW()) as minutes_since_last_sync,
    (SELECT COUNT(*) FROM tbl_marketplace_sync_log WHERE project_id = p.id AND status = 'failed' AND sync_timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY)) as failed_syncs_24h
FROM tbl_marketplace_projects p
WHERE p.update_method != 'manual';

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
