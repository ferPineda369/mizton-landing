<?php
/**
 * Configuración de Tipos de Proyecto - Marketplace Mizton
 * Define los campos específicos, secciones y categorías de media para cada tipo de proyecto
 */

$PROJECT_TYPES = [
    'general' => [
        'name' => 'Proyecto General',
        'icon' => 'bi-folder',
        'color' => '#6c757d',
        'description' => 'Proyecto estándar sin campos específicos',
        'metadata_fields' => [],
        'default_sections' => ['hero', 'about', 'milestones', 'invest'],
        'media_categories' => ['hero', 'gallery']
    ],
    
    'book' => [
        'name' => 'Obra Literaria',
        'icon' => 'bi-book',
        'color' => '#8B4513',
        'description' => 'Libros, novelas, ensayos, poesía, etc.',
        'metadata_fields' => [
            'book_isbn' => [
                'label' => 'ISBN',
                'type' => 'text',
                'required' => false,
                'placeholder' => '978-1234567890',
                'help' => 'Número Internacional Normalizado del Libro'
            ],
            'book_pages' => [
                'label' => 'Número de Páginas',
                'type' => 'number',
                'required' => false,
                'min' => 1,
                'placeholder' => '320'
            ],
            'book_genre' => [
                'label' => 'Género Literario',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Ficción, No ficción, Poesía, etc.'
            ],
            'book_author' => [
                'label' => 'Autor(es)',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Nombre del autor'
            ],
            'book_language' => [
                'label' => 'Idioma',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'es' => 'Español',
                    'en' => 'Inglés',
                    'pt' => 'Portugués',
                    'fr' => 'Francés',
                    'de' => 'Alemán',
                    'it' => 'Italiano'
                ]
            ],
            'book_publisher' => [
                'label' => 'Editorial',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Nombre de la editorial'
            ],
            'book_publication_date' => [
                'label' => 'Fecha de Publicación',
                'type' => 'date',
                'required' => false
            ],
            'book_preview_url' => [
                'label' => 'URL de Vista Previa',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://...',
                'help' => 'Enlace a muestra o primeras páginas'
            ],
            'book_format' => [
                'label' => 'Formato',
                'type' => 'multiselect',
                'required' => false,
                'options' => ['Impreso', 'Digital', 'Audiolibro']
            ]
        ],
        'default_sections' => ['hero', 'about', 'author', 'preview', 'gallery', 'reviews', 'faq', 'milestones', 'invest'],
        'media_categories' => ['cover', 'pages', 'author_photo', 'gallery', 'promotional']
    ],
    
    'music_video' => [
        'name' => 'Video Musical',
        'icon' => 'bi-music-note-beamed',
        'color' => '#FF1493',
        'description' => 'Producción de videos musicales',
        'metadata_fields' => [
            'music_artist' => [
                'label' => 'Artista',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Nombre del artista o banda'
            ],
            'music_song_title' => [
                'label' => 'Título de la Canción',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Nombre de la canción'
            ],
            'music_genre' => [
                'label' => 'Género Musical',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Rock, Pop, Hip-Hop, etc.'
            ],
            'music_duration' => [
                'label' => 'Duración',
                'type' => 'text',
                'required' => false,
                'placeholder' => '4:30'
            ],
            'music_director' => [
                'label' => 'Director del Video',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Nombre del director'
            ],
            'music_producer' => [
                'label' => 'Productor Musical',
                'type' => 'text',
                'required' => false
            ],
            'music_youtube_url' => [
                'label' => 'URL YouTube',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://youtube.com/...'
            ],
            'music_spotify_url' => [
                'label' => 'URL Spotify',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://open.spotify.com/...'
            ],
            'music_release_date' => [
                'label' => 'Fecha de Lanzamiento',
                'type' => 'date',
                'required' => false
            ]
        ],
        'default_sections' => ['hero', 'about', 'artist', 'video', 'team', 'behind_scenes', 'milestones', 'invest'],
        'media_categories' => ['poster', 'behind_scenes', 'artist_photo', 'stills', 'promotional']
    ],
    
    'concert_tour' => [
        'name' => 'Gira de Conciertos',
        'icon' => 'bi-mic',
        'color' => '#9370DB',
        'description' => 'Tours y giras musicales',
        'metadata_fields' => [
            'tour_artist' => [
                'label' => 'Artista',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Nombre del artista o banda'
            ],
            'tour_name' => [
                'label' => 'Nombre de la Gira',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'World Tour 2025'
            ],
            'tour_cities' => [
                'label' => 'Ciudades',
                'type' => 'json',
                'required' => false,
                'placeholder' => '["CDMX", "Guadalajara", "Monterrey"]',
                'help' => 'Array JSON de ciudades'
            ],
            'tour_venues' => [
                'label' => 'Número de Venues',
                'type' => 'number',
                'required' => false,
                'min' => 1
            ],
            'tour_start_date' => [
                'label' => 'Fecha de Inicio',
                'type' => 'date',
                'required' => true
            ],
            'tour_end_date' => [
                'label' => 'Fecha de Fin',
                'type' => 'date',
                'required' => false
            ],
            'tour_expected_attendance' => [
                'label' => 'Asistencia Esperada',
                'type' => 'number',
                'required' => false,
                'placeholder' => '50000',
                'help' => 'Número total de asistentes esperados'
            ],
            'tour_countries' => [
                'label' => 'Países',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'México, USA, Colombia'
            ]
        ],
        'default_sections' => ['hero', 'about', 'artist', 'dates', 'venues', 'team', 'gallery', 'invest'],
        'media_categories' => ['artist_photo', 'venue_photos', 'past_concerts', 'promotional']
    ],
    
    'theater' => [
        'name' => 'Obra de Teatro',
        'icon' => 'bi-mask',
        'color' => '#DC143C',
        'description' => 'Producciones teatrales y escénicas',
        'metadata_fields' => [
            'theater_title' => [
                'label' => 'Título de la Obra',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Nombre de la obra'
            ],
            'theater_playwright' => [
                'label' => 'Dramaturgo',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Autor de la obra'
            ],
            'theater_director' => [
                'label' => 'Director',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Director de la puesta en escena'
            ],
            'theater_genre' => [
                'label' => 'Género',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Drama, Comedia, Musical, etc.'
            ],
            'theater_duration' => [
                'label' => 'Duración',
                'type' => 'text',
                'required' => false,
                'placeholder' => '2 horas'
            ],
            'theater_venue' => [
                'label' => 'Teatro/Venue',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Nombre del teatro'
            ],
            'theater_premiere_date' => [
                'label' => 'Fecha de Estreno',
                'type' => 'date',
                'required' => false
            ],
            'theater_run_dates' => [
                'label' => 'Temporada',
                'type' => 'json',
                'required' => false,
                'placeholder' => '{"start": "2025-06-01", "end": "2025-08-31"}',
                'help' => 'Fechas de la temporada en formato JSON'
            ],
            'theater_cast_size' => [
                'label' => 'Tamaño del Elenco',
                'type' => 'number',
                'required' => false,
                'min' => 1
            ]
        ],
        'default_sections' => ['hero', 'synopsis', 'cast', 'crew', 'gallery', 'dates', 'reviews', 'invest'],
        'media_categories' => ['poster', 'rehearsal', 'cast_photos', 'set_design', 'promotional']
    ],
    
    'local_business' => [
        'name' => 'Negocio Local',
        'icon' => 'bi-shop',
        'color' => '#20B2AA',
        'description' => 'Negocios físicos locales (restaurantes, tiendas, etc.)',
        'metadata_fields' => [
            'business_type' => [
                'label' => 'Tipo de Negocio',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Restaurante, Tienda, Cafetería, etc.'
            ],
            'business_location' => [
                'label' => 'Ubicación',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Ciudad, Estado, País'
            ],
            'business_address' => [
                'label' => 'Dirección',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Calle, número, colonia'
            ],
            'business_opening_date' => [
                'label' => 'Fecha de Apertura',
                'type' => 'date',
                'required' => false
            ],
            'business_employees' => [
                'label' => 'Número de Empleados',
                'type' => 'number',
                'required' => false,
                'min' => 1
            ],
            'business_hours' => [
                'label' => 'Horario',
                'type' => 'json',
                'required' => false,
                'placeholder' => '{"lun-vie": "9:00-18:00", "sab": "10:00-14:00"}',
                'help' => 'Horario de atención en formato JSON'
            ],
            'business_capacity' => [
                'label' => 'Capacidad',
                'type' => 'number',
                'required' => false,
                'help' => 'Capacidad de clientes/comensales'
            ],
            'business_phone' => [
                'label' => 'Teléfono',
                'type' => 'text',
                'required' => false,
                'placeholder' => '+52 123 456 7890'
            ]
        ],
        'default_sections' => ['hero', 'about', 'location', 'products', 'team', 'gallery', 'financials', 'invest'],
        'media_categories' => ['storefront', 'interior', 'products', 'team', 'menu', 'promotional']
    ],
    
    'real_estate' => [
        'name' => 'Bienes Raíces',
        'icon' => 'bi-building',
        'color' => '#4682B4',
        'description' => 'Proyectos inmobiliarios',
        'metadata_fields' => [
            'property_type' => [
                'label' => 'Tipo de Propiedad',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'residential' => 'Residencial',
                    'commercial' => 'Comercial',
                    'industrial' => 'Industrial',
                    'land' => 'Terreno',
                    'mixed' => 'Uso Mixto'
                ]
            ],
            'property_location' => [
                'label' => 'Ubicación',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Ciudad, Estado, País'
            ],
            'property_size' => [
                'label' => 'Tamaño (m²)',
                'type' => 'number',
                'required' => false,
                'min' => 1,
                'placeholder' => '150'
            ],
            'property_units' => [
                'label' => 'Número de Unidades',
                'type' => 'number',
                'required' => false,
                'min' => 1,
                'help' => 'Departamentos, locales, etc.'
            ],
            'property_completion_date' => [
                'label' => 'Fecha de Entrega',
                'type' => 'date',
                'required' => false
            ],
            'property_amenities' => [
                'label' => 'Amenidades',
                'type' => 'json',
                'required' => false,
                'placeholder' => '["Alberca", "Gym", "Seguridad 24/7"]',
                'help' => 'Array JSON de amenidades'
            ]
        ],
        'default_sections' => ['hero', 'about', 'location', 'features', 'gallery', 'floor_plans', 'financials', 'invest'],
        'media_categories' => ['exterior', 'interior', 'amenities', 'location', 'floor_plans', 'renders']
    ],
    
    'film' => [
        'name' => 'Película/Documental',
        'icon' => 'bi-film',
        'color' => '#FFD700',
        'description' => 'Producción cinematográfica',
        'metadata_fields' => [
            'film_title' => [
                'label' => 'Título',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Título de la película'
            ],
            'film_type' => [
                'label' => 'Tipo',
                'type' => 'select',
                'required' => true,
                'options' => [
                    'feature' => 'Largometraje',
                    'short' => 'Cortometraje',
                    'documentary' => 'Documental',
                    'series' => 'Serie'
                ]
            ],
            'film_genre' => [
                'label' => 'Género',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Drama, Comedia, Acción, etc.'
            ],
            'film_director' => [
                'label' => 'Director',
                'type' => 'text',
                'required' => true
            ],
            'film_duration' => [
                'label' => 'Duración',
                'type' => 'text',
                'required' => false,
                'placeholder' => '120 minutos'
            ],
            'film_release_date' => [
                'label' => 'Fecha de Estreno',
                'type' => 'date',
                'required' => false
            ],
            'film_language' => [
                'label' => 'Idioma',
                'type' => 'text',
                'required' => false
            ],
            'film_trailer_url' => [
                'label' => 'URL del Trailer',
                'type' => 'url',
                'required' => false,
                'placeholder' => 'https://youtube.com/...'
            ]
        ],
        'default_sections' => ['hero', 'synopsis', 'cast', 'crew', 'trailer', 'gallery', 'festivals', 'invest'],
        'media_categories' => ['poster', 'stills', 'behind_scenes', 'cast_photos', 'promotional']
    ]
];

/**
 * Obtener configuración de un tipo de proyecto
 */
function getProjectTypeConfig($projectType) {
    global $PROJECT_TYPES;
    return $PROJECT_TYPES[$projectType] ?? $PROJECT_TYPES['general'];
}

/**
 * Obtener todos los tipos de proyecto disponibles
 */
function getAllProjectTypes() {
    global $PROJECT_TYPES;
    return $PROJECT_TYPES;
}

/**
 * Validar que un tipo de proyecto existe
 */
function isValidProjectType($projectType) {
    global $PROJECT_TYPES;
    return isset($PROJECT_TYPES[$projectType]);
}

/**
 * Obtener campos de metadata para un tipo de proyecto
 */
function getProjectTypeMetadataFields($projectType) {
    $config = getProjectTypeConfig($projectType);
    return $config['metadata_fields'] ?? [];
}

/**
 * Obtener secciones por defecto para un tipo de proyecto
 */
function getProjectTypeDefaultSections($projectType) {
    $config = getProjectTypeConfig($projectType);
    return $config['default_sections'] ?? [];
}

/**
 * Obtener categorías de media para un tipo de proyecto
 */
function getProjectTypeMediaCategories($projectType) {
    $config = getProjectTypeConfig($projectType);
    return $config['media_categories'] ?? [];
}
