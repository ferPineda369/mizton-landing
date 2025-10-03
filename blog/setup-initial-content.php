<?php
/**
 * Script para crear contenido inicial del blog
 * Ejecutar una sola vez para poblar el blog con posts de ejemplo
 */

require_once 'config/blog-config.php';
require_once 'includes/blog-functions.php';

echo "<h2>🚀 Configuración Inicial del Blog Mizton</h2>";

try {
    $db = getBlogDB();
    
    // Verificar si ya hay posts
    $stmt = $db->query("SELECT COUNT(*) FROM blog_posts");
    $existingPosts = $stmt->fetchColumn();
    
    if ($existingPosts > 0) {
        echo "<p>⚠️ Ya existen {$existingPosts} posts en la base de datos.</p>";
        echo "<p><a href='?force=1'>Forzar recreación de contenido</a> | <a href='index.php'>Ir al Blog</a></p>";
        
        if (!isset($_GET['force'])) {
            exit;
        } else {
            // Limpiar posts existentes
            $db->exec("DELETE FROM blog_posts");
            echo "<p>🗑️ Posts anteriores eliminados.</p>";
        }
    }
    
    // Posts de ejemplo con contenido completo
    $samplePosts = [
        [
            'title' => 'El Futuro de la Tokenización RWA: Transformando Activos Reales en el Blockchain',
            'content' => '
                <p>La tokenización de activos del mundo real (RWA - Real World Assets) representa una de las innovaciones más prometedoras en el ecosistema blockchain. Esta tecnología está revolucionando la forma en que invertimos, comercializamos y gestionamos activos tradicionales.</p>
                
                <h2>¿Qué es la Tokenización RWA?</h2>
                <p>La tokenización RWA es el proceso de convertir activos físicos o financieros tradicionales en tokens digitales en una blockchain. Estos activos pueden incluir:</p>
                
                <ul>
                    <li><strong>Bienes raíces:</strong> Propiedades residenciales y comerciales</li>
                    <li><strong>Commodities:</strong> Oro, plata, petróleo y otros recursos naturales</li>
                    <li><strong>Arte y coleccionables:</strong> Obras de arte, vinos, automóviles clásicos</li>
                    <li><strong>Instrumentos financieros:</strong> Bonos, acciones, fondos de inversión</li>
                </ul>
                
                <h2>Beneficios Revolucionarios</h2>
                
                <h3>1. Democratización del Acceso</h3>
                <p>La tokenización permite fraccionar activos de alto valor, haciendo posible que inversores con menor capital puedan participar en mercados antes exclusivos. Por ejemplo, en lugar de necesitar $1 millón para comprar una propiedad completa, puedes invertir $1,000 en tokens que representan una fracción de esa propiedad.</p>
                
                <h3>2. Liquidez Mejorada</h3>
                <p>Los activos tradicionalmente ilíquidos, como bienes raíces o arte, pueden comercializarse 24/7 en mercados digitales globales. Esto elimina las barreras geográficas y temporales del comercio tradicional.</p>
                
                <h3>3. Transparencia y Seguridad</h3>
                <p>La blockchain proporciona un registro inmutable y transparente de la propiedad y las transacciones. Cada token está respaldado por el activo real y su historial es completamente auditable.</p>
                
                <h3>4. Costos Reducidos</h3>
                <p>Al eliminar intermediarios tradicionales como bancos, corredores y notarios, la tokenización reduce significativamente los costos de transacción y gestión.</p>
                
                <h2>Casos de Uso Actuales</h2>
                
                <blockquote>
                    "La tokenización no es solo una tendencia tecnológica, es una transformación fundamental de cómo entendemos la propiedad y el valor en la era digital."
                </blockquote>
                
                <h3>Bienes Raíces</h3>
                <p>Plataformas como RealT y Fundrise ya están tokenizando propiedades, permitiendo a los inversores comprar fracciones de inmuebles y recibir rentas proporcionales.</p>
                
                <h3>Commodities</h3>
                <p>El oro tokenizado (como PAXG) permite a los inversores poseer oro físico sin los costos de almacenamiento y seguridad tradicionales.</p>
                
                <h3>Arte y Coleccionables</h3>
                <p>Masterworks y otras plataformas están democratizando la inversión en arte de alta gama mediante la tokenización de obras maestras.</p>
                
                <h2>El Papel de Mizton</h2>
                <p>En Mizton, estamos construyendo la infraestructura necesaria para hacer la tokenización RWA accesible para todos. Nuestra plataforma combina:</p>
                
                <ul>
                    <li>Tecnología blockchain de vanguardia</li>
                    <li>Cumplimiento regulatorio robusto</li>
                    <li>Interfaz intuitiva para usuarios no técnicos</li>
                    <li>Ecosistema de partners estratégicos</li>
                </ul>
                
                <h2>Desafíos y Oportunidades</h2>
                
                <h3>Regulación</h3>
                <p>El marco regulatorio para RWA está evolucionando rápidamente. Es crucial trabajar con reguladores para establecer estándares claros que protejan a los inversores mientras fomentan la innovación.</p>
                
                <h3>Adopción Masiva</h3>
                <p>La educación y la experiencia de usuario son clave para la adopción masiva. Debemos hacer que la tokenización sea tan simple como usar una aplicación bancaria tradicional.</p>
                
                <h2>El Futuro es Ahora</h2>
                <p>La tokenización RWA no es una promesa futura, es una realidad presente que está transformando los mercados financieros globales. Las instituciones tradicionales están adoptando esta tecnología, y los inversores individuales tienen la oportunidad de participar en esta revolución.</p>
                
                <p>En Mizton, creemos que el futuro de las finanzas es descentralizado, transparente y accesible para todos. La tokenización RWA es el puente que conecta el mundo financiero tradicional con el ecosistema blockchain del mañana.</p>
                
                <p><em>¿Estás listo para ser parte de esta transformación? Únete a Mizton y descubre cómo la tokenización puede revolucionar tu portafolio de inversiones.</em></p>
            ',
            'category' => 'rwa',
            'tags' => '["tokenización", "RWA", "blockchain", "inversiones", "bienes raíces", "fintech"]',
            'featured' => 1,
            'image' => 'assets/images/rwa-tokenization.jpg'
        ],
        [
            'title' => 'Blockchain: La Tecnología que Está Redefiniendo las Finanzas Globales',
            'content' => '
                <p>La tecnología blockchain ha evolucionado mucho más allá de ser simplemente la base de las criptomonedas. Hoy en día, está transformando sectores enteros de la economía global, especialmente el sector financiero.</p>
                
                <h2>Más Allá de Bitcoin</h2>
                <p>Aunque Bitcoin introdujo al mundo el concepto de blockchain, las aplicaciones actuales van mucho más allá de las criptomonedas:</p>
                
                <h3>Contratos Inteligentes</h3>
                <p>Los smart contracts automatizan acuerdos complejos sin necesidad de intermediarios. Ethereum pionero esta tecnología, permitiendo que los contratos se ejecuten automáticamente cuando se cumplen condiciones predefinidas.</p>
                
                <h3>DeFi (Finanzas Descentralizadas)</h3>
                <p>El ecosistema DeFi ha creado un sistema financiero paralelo que opera 24/7, sin fronteras geográficas y con mayor transparencia que el sistema tradicional.</p>
                
                <h3>NFTs y Propiedad Digital</h3>
                <p>Los tokens no fungibles han revolucionado la forma en que entendemos la propiedad digital, desde arte hasta música y contenido multimedia.</p>
                
                <h2>Impacto en las Finanzas Tradicionales</h2>
                
                <blockquote>
                    "Blockchain no es solo una tecnología, es una nueva forma de organizar la confianza en la era digital."
                </blockquote>
                
                <h3>Pagos Internacionales</h3>
                <p>Las transferencias internacionales que antes tomaban días y costaban decenas de dólares, ahora pueden completarse en minutos por centavos usando blockchain.</p>
                
                <h3>Inclusión Financiera</h3>
                <p>Blockchain está llevando servicios financieros a los 1.7 mil millones de personas no bancarizadas en el mundo, proporcionando acceso a servicios básicos como ahorro, crédito y seguros.</p>
                
                <h3>Transparencia y Auditabilidad</h3>
                <p>Cada transacción en blockchain es inmutable y auditable, proporcionando un nivel de transparencia sin precedentes en las operaciones financieras.</p>
                
                <h2>Casos de Uso Empresariales</h2>
                
                <h3>Cadena de Suministro</h3>
                <p>Empresas como Walmart y Maersk usan blockchain para rastrear productos desde su origen hasta el consumidor final, garantizando autenticidad y calidad.</p>
                
                <h3>Identidad Digital</h3>
                <p>Los sistemas de identidad basados en blockchain permiten a los usuarios controlar completamente sus datos personales, eliminando la dependencia de terceros.</p>
                
                <h3>Votación Electrónica</h3>
                <p>Varios países están explorando sistemas de votación basados en blockchain para garantizar elecciones transparentes y a prueba de manipulación.</p>
                
                <h2>Desafíos Actuales</h2>
                
                <h3>Escalabilidad</h3>
                <p>Las redes blockchain actuales enfrentan limitaciones de velocidad y capacidad. Soluciones como Lightning Network y sharding están abordando estos desafíos.</p>
                
                <h3>Consumo Energético</h3>
                <p>El mecanismo de consenso Proof of Work consume mucha energía. La transición a Proof of Stake y otras alternativas más eficientes está en curso.</p>
                
                <h3>Regulación</h3>
                <p>Los marcos regulatorios están evolucionando para equilibrar la innovación con la protección del consumidor y la estabilidad financiera.</p>
                
                <h2>El Futuro de Blockchain</h2>
                <p>Estamos apenas en los primeros días de la revolución blockchain. Las próximas innovaciones incluyen:</p>
                
                <ul>
                    <li><strong>Interoperabilidad:</strong> Diferentes blockchains trabajando juntas</li>
                    <li><strong>Web3:</strong> Una internet descentralizada y propiedad de los usuarios</li>
                    <li><strong>CBDCs:</strong> Monedas digitales de bancos centrales</li>
                    <li><strong>DAO:</strong> Organizaciones autónomas descentralizadas</li>
                </ul>
                
                <h2>Mizton y el Ecosistema Blockchain</h2>
                <p>En Mizton, aprovechamos el poder de blockchain para crear soluciones financieras innovadoras que benefician a nuestros usuarios. Nuestra plataforma integra las mejores prácticas de seguridad, escalabilidad y experiencia de usuario.</p>
                
                <p>La revolución blockchain está aquí, y las oportunidades son infinitas para aquellos que se adapten y adopten esta tecnología transformadora.</p>
            ',
            'category' => 'blockchain',
            'tags' => '["blockchain", "fintech", "criptomonedas", "DeFi", "smart contracts"]',
            'featured' => 0,
            'image' => 'assets/images/blockchain-tech.jpg'
        ],
        [
            'title' => 'Fintech 2024: Las Tendencias que Definirán el Futuro Financiero',
            'content' => '
                <p>El sector fintech continúa su evolución acelerada, transformando la manera en que interactuamos con el dinero y los servicios financieros. El 2024 marca un punto de inflexión donde la innovación tecnológica se encuentra con las necesidades reales de los usuarios.</p>
                
                <h2>Tendencias Dominantes en 2024</h2>
                
                <h3>1. Inteligencia Artificial y Machine Learning</h3>
                <p>La IA está revolucionando todos los aspectos de las fintech:</p>
                
                <ul>
                    <li><strong>Análisis de Riesgo:</strong> Algoritmos que evalúan creditworthiness en tiempo real</li>
                    <li><strong>Detección de Fraude:</strong> Sistemas que identifican patrones sospechosos instantáneamente</li>
                    <li><strong>Asesoramiento Personalizado:</strong> Robo-advisors que adaptan estrategias de inversión</li>
                    <li><strong>Atención al Cliente:</strong> Chatbots inteligentes disponibles 24/7</li>
                </ul>
                
                <h3>2. Banca Embebida (Embedded Finance)</h3>
                <p>Los servicios financieros se están integrando directamente en plataformas no financieras. Ejemplos incluyen:</p>
                
                <blockquote>
                    "El futuro de las fintech no está en crear más aplicaciones bancarias, sino en hacer que los servicios financieros sean invisibles y omnipresentes."
                </blockquote>
                
                <ul>
                    <li>Pagos integrados en e-commerce</li>
                    <li>Préstamos instantáneos en plataformas de ride-sharing</li>
                    <li>Seguros automáticos en aplicaciones de viajes</li>
                    <li>Inversiones micro en apps de ahorro</li>
                </ul>
                
                <h3>3. Pagos Digitales y Monedas Digitales</h3>
                <p>La digitalización de los pagos alcanza nuevos niveles:</p>
                
                <h4>CBDCs (Central Bank Digital Currencies)</h4>
                <p>Los bancos centrales están lanzando versiones digitales de sus monedas nacionales, combinando la estabilidad de las monedas fiduciarias con la eficiencia de la tecnología blockchain.</p>
                
                <h4>Stablecoins</h4>
                <p>Las monedas estables están ganando tracción como medio de pago internacional, ofreciendo la velocidad de las criptomonedas con la estabilidad de las monedas tradicionales.</p>
                
                <h4>Pagos Instantáneos</h4>
                <p>Sistemas como PIX en Brasil y UPI en India están siendo replicados globalmente, permitiendo transferencias instantáneas y gratuitas.</p>
                
                <h2>Innovaciones Emergentes</h2>
                
                <h3>Super Apps Financieras</h3>
                <p>Siguiendo el modelo de WeChat Pay y Alipay, las super apps integran múltiples servicios financieros en una sola plataforma:</p>
                
                <ul>
                    <li>Pagos y transferencias</li>
                    <li>Inversiones y ahorros</li>
                    <li>Préstamos y créditos</li>
                    <li>Seguros y protección</li>
                    <li>Servicios de lifestyle</li>
                </ul>
                
                <h3>Fintech Verde</h3>
                <p>La sostenibilidad se convierte en un factor clave:</p>
                
                <ul>
                    <li><strong>Carbon Tracking:</strong> Apps que rastrean la huella de carbono de las transacciones</li>
                    <li><strong>Green Investments:</strong> Plataformas especializadas en inversiones ESG</li>
                    <li><strong>Sustainable Banking:</strong> Bancos digitales con misión ambiental</li>
                </ul>
                
                <h3>Fintech Social</h3>
                <p>Las redes sociales y las finanzas se fusionan:</p>
                
                <ul>
                    <li>Inversiones colaborativas y copy trading</li>
                    <li>Préstamos peer-to-peer mejorados</li>
                    <li>Educación financiera gamificada</li>
                    <li>Comunidades de inversión temáticas</li>
                </ul>
                
                <h2>Desafíos y Oportunidades</h2>
                
                <h3>Regulación Adaptativa</h3>
                <p>Los reguladores están desarrollando marcos más flexibles que permiten la innovación mientras protegen a los consumidores. El concepto de "regulatory sandbox" se está expandiendo globalmente.</p>
                
                <h3>Ciberseguridad</h3>
                <p>Con el aumento de los servicios digitales, la seguridad se vuelve crítica:</p>
                
                <ul>
                    <li>Autenticación biométrica avanzada</li>
                    <li>Zero-trust security models</li>
                    <li>Encriptación cuántica</li>
                    <li>Blockchain para seguridad de datos</li>
                </ul>
                
                <h3>Inclusión Financiera</h3>
                <p>Las fintech están democratizando el acceso a servicios financieros:</p>
                
                <ul>
                    <li>Microcréditos basados en datos alternativos</li>
                    <li>Cuentas bancarias sin requisitos mínimos</li>
                    <li>Educación financiera personalizada</li>
                    <li>Servicios en múltiples idiomas y culturas</li>
                </ul>
                
                <h2>El Papel de Mizton en el Ecosistema Fintech</h2>
                <p>Mizton está a la vanguardia de estas tendencias, integrando:</p>
                
                <ul>
                    <li><strong>IA Avanzada:</strong> Para personalización y análisis predictivo</li>
                    <li><strong>Blockchain Nativa:</strong> Para transparencia y seguridad</li>
                    <li><strong>Diseño Centrado en el Usuario:</strong> Para una experiencia intuitiva</li>
                    <li><strong>Ecosistema Abierto:</strong> Para integración con partners estratégicos</li>
                </ul>
                
                <h2>Mirando Hacia el Futuro</h2>
                <p>El 2024 será recordado como el año en que las fintech maduraron de startups disruptivas a pilares fundamentales del sistema financiero global. Las empresas que sobrevivan y prosperen serán aquellas que pongan al usuario en el centro, abracen la innovación responsable y construyan ecosistemas sostenibles.</p>
                
                <p>En Mizton, estamos construyendo no solo para el presente, sino para el futuro financiero que todos merecemos: más inclusivo, más eficiente y más humano.</p>
            ',
            'category' => 'fintech',
            'tags' => '["fintech", "innovación", "pagos digitales", "IA", "banca digital"]',
            'featured' => 0,
            'image' => 'assets/images/fintech-trends.jpg'
        ]
    ];
    
    echo "<h3>📝 Creando Posts de Ejemplo...</h3>";
    
    foreach ($samplePosts as $index => $post) {
        $slug = generateSlug($post['title']);
        $excerpt = substr(strip_tags($post['content']), 0, 150) . '...';
        $readTime = calculateReadTime($post['content']);
        
        $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, featured, read_time, image, status, published_at, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW(), 'Mizton Team')");
        
        $result = $stmt->execute([
            $post['title'],
            $slug,
            $excerpt,
            $post['content'],
            $post['category'],
            $post['tags'],
            $post['featured'],
            $readTime,
            $post['image']
        ]);
        
        if ($result) {
            echo "<p>✅ Post " . ($index + 1) . ": " . htmlspecialchars($post['title']) . "</p>";
        } else {
            echo "<p>❌ Error creando post " . ($index + 1) . "</p>";
        }
    }
    
    // Verificar posts creados
    $stmt = $db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
    $totalPosts = $stmt->fetchColumn();
    
    echo "<h3>🎉 Configuración Completada</h3>";
    echo "<p><strong>Posts creados:</strong> {$totalPosts}</p>";
    echo "<p><strong>Blog listo:</strong> <a href='index.php' target='_blank'>Ver Blog</a></p>";
    echo "<p><strong>Panel Admin:</strong> <a href='admin/' target='_blank'>Administrar</a></p>";
    
    // Generar sitemap
    generateBlogSitemap();
    echo "<p>✅ Sitemap generado</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
