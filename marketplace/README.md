# Mizton Marketplace - Sistema de Showcase de Proyectos Tokenizados

## 🎯 Descripción

Portal informativo que conecta usuarios con proyectos de tokenización de activos reales (RWA). El marketplace actúa como un hub centralizado que muestra información actualizada de proyectos independientes.

## ✨ Características

- 📊 **Showcase Visual**: Grid de proyectos con información clave
- 🔄 **Sincronización Automática**: API Pull, Webhooks y Blockchain
- 📱 **Responsive Design**: Optimizado para móviles y desktop
- 🎨 **14 Categorías**: Inmobiliario, Energía, Arte, Editorial, etc.
- 📈 **Analytics Integrado**: Vistas, clicks y estadísticas
- 🛠️ **Panel Admin**: Gestión completa de proyectos
- 🔌 **SDK de Integración**: Template para proyectos externos

## 🚀 Instalación Rápida

### 1. Crear Base de Datos

```bash
mysql -u root -p < sql/marketplace_database.sql
```

### 2. Configurar Cron Job

```bash
crontab -e
```

Agregar:
```
*/5 * * * * /usr/bin/php /path/to/marketplace/cron/sync-projects.php >> /var/log/marketplace-sync.log 2>&1
```

### 3. Acceder al Marketplace

- **Público**: https://mizton.cat/marketplace/
- **Admin**: https://mizton.cat/marketplace/admin/ (requiere `admin=1`)

## 📁 Estructura del Proyecto

```
/marketplace/
├── index.php                    # Vista pública (listado)
├── project.php                  # Vista detalle de proyecto
├── /admin/                      # Panel de administración
│   ├── index.php               # Dashboard admin
│   ├── projects.php            # Gestión de proyectos
│   └── sync-status.php         # Estado de sincronización
├── /api/                        # APIs REST
│   ├── get-projects.php        # Obtener proyectos
│   ├── webhook-receiver.php    # Receptor de webhooks
│   └── record-analytics.php    # Registrar analytics
├── /config/                     # Configuración
│   ├── database.php            # Conexión BD
│   └── marketplace-config.php  # Configuración general
├── /includes/                   # Funciones PHP
│   ├── marketplace-functions.php
│   └── sync-functions.php
├── /assets/                     # Frontend
│   ├── css/marketplace.css
│   └── js/marketplace.js
├── /cron/                       # Tareas programadas
│   └── sync-projects.php       # Sincronización automática
├── /sdk/                        # SDK de integración
│   └── integration-template.php
├── /sql/                        # Scripts SQL
│   └── marketplace_database.sql
└── /docs/                       # Documentación
    └── MARKETPLACE_DOCUMENTATION.md
```

## 🔌 Integración de Proyectos Externos

### Opción 1: API Pull (Recomendado)

1. Copiar template: `/sdk/integration-template.php`
2. Implementar funciones de datos
3. Exponer endpoint: `https://tu-proyecto.com/api/marketplace-data`
4. Registrar en Admin Marketplace

### Opción 2: Webhook (Tiempo Real)

```php
$data = getProjectData();
$data['project_code'] = 'LIBRO1';
$payload = json_encode($data);
$signature = hash_hmac('sha256', $payload, API_SECRET);

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://mizton.cat/marketplace/api/webhook-receiver.php',
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Signature: ' . $signature
    ]
]);
```

## 📊 Formato JSON Estándar

```json
{
  "project_info": {
    "name": "Nombre del Proyecto",
    "category": "editorial",
    "description": "...",
    "status": "activo"
  },
  "blockchain": {
    "contract_address": "0x...",
    "network": "BSC",
    "token_symbol": "BOOK",
    "total_supply": 100000,
    "token_price_usd": 1.00
  },
  "financials": {
    "funding_goal": 100000,
    "raised": 50000,
    "funding_percentage": 50.0,
    "apy_staking": 8.5
  },
  "participation": {
    "holders_count": 250,
    "min_investment": 100,
    "tokens_available": 50000
  },
  "links": {
    "website": "https://...",
    "dashboard": "https://..."
  }
}
```

## 🎨 Categorías Disponibles

| Categoría | Descripción | Icono |
|-----------|-------------|-------|
| `inmobiliario` | Bienes raíces | 🏢 |
| `energia` | Energía renovable | ⚡ |
| `editorial` | Libros y publicaciones | 📚 |
| `arte` | Arte y coleccionables | 🎨 |
| `musical` | Proyectos musicales | 🎵 |
| `cinematografia` | Películas y series | 🎬 |
| `deportivo` | Eventos deportivos | 🏆 |
| `agropecuario` | Agricultura | 🌾 |
| `industrial` | Manufactura | ⚙️ |
| `tecnologia` | Startups tech | 💻 |
| `minero` | Minería | 💎 |
| `farmaceutico` | Farmacéutica | 💊 |
| `gubernamental` | Proyectos gubernamentales | 🏛️ |
| `otro` | Otros | 📦 |

## 🔧 Configuración

### Variables de Entorno

Editar `/config/marketplace-config.php`:

```php
define('MARKETPLACE_NAME', 'Mizton Marketplace');
define('DEFAULT_SYNC_FREQUENCY', 5); // minutos
define('PROJECTS_PER_PAGE', 12);
define('FEATURED_PROJECTS_LIMIT', 6);
```

### Base de Datos

Usa la misma conexión que el panel principal de Mizton.

## 📈 Analytics

### Métricas Disponibles

- ✅ Vistas por proyecto
- ✅ Click-through rate
- ✅ Proyectos más populares
- ✅ Financiamiento total
- ✅ Estado de sincronización

### Consultas SQL Útiles

```sql
-- Proyectos más vistos (últimos 30 días)
SELECT p.name, SUM(s.views_count) as total_views
FROM tbl_marketplace_projects p
JOIN tbl_marketplace_stats s ON p.id = s.project_id
WHERE s.stat_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY p.id
ORDER BY total_views DESC
LIMIT 10;
```

## 🛠️ Mantenimiento

### Ver Logs de Sincronización

```bash
tail -f /var/log/marketplace-sync.log
```

### Limpiar Logs Antiguos

```php
require_once 'includes/sync-functions.php';
cleanOldSyncLogs(30); // Mantener últimos 30 días
```

### Backup

```bash
mysqldump -u root -p mizton_db \
  tbl_marketplace_projects \
  tbl_marketplace_categories \
  tbl_marketplace_sync_log \
  > marketplace_backup_$(date +%Y%m%d).sql
```

## 🚀 Roadmap

### Fase 1 (Completada) ✅
- [x] Sistema de showcase completo
- [x] Sincronización API Pull y Webhooks
- [x] Panel admin funcional
- [x] SDK de integración
- [x] Analytics básico

### Fase 2 (Próxima)
- [ ] Lectura directa de blockchain (Web3.php)
- [ ] Comparador de proyectos
- [ ] Calculadora de ROI
- [ ] Sistema de notificaciones

### Fase 3 (Futuro)
- [ ] Wishlist de proyectos
- [ ] Alertas personalizadas
- [ ] Gráficas históricas
- [ ] API pública

## 📚 Documentación

- **Completa**: `/docs/MARKETPLACE_DOCUMENTATION.md`
- **SDK**: `/sdk/integration-template.php`
- **SQL**: `/sql/marketplace_database.sql`

## 🤝 Contribuir

Este es un proyecto privado de Mizton. Para cambios:

1. Crear rama feature
2. Hacer cambios
3. Commit a GitHub (ferPineda369/panel-php)
4. Deployment automático a producción

## 📞 Soporte

- **Email**: marketplace@mizton.cat
- **Panel Admin**: https://mizton.cat/marketplace/admin/

## 📝 Changelog

### v1.0.0 (2025-12-26)
- Lanzamiento inicial
- Sistema completo de showcase
- Sincronización automática
- Panel admin funcional
- SDK de integración
- Documentación completa

---

**Desarrollado por Mizton** | © 2025 Todos los derechos reservados
