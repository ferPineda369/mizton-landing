# Documentación Completa - Mizton Marketplace

## 📋 Índice

1. [Visión General](#visión-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Instalación](#instalación)
4. [Estructura de Base de Datos](#estructura-de-base-de-datos)
5. [API de Integración](#api-de-integración)
6. [Panel de Administración](#panel-de-administración)
7. [Sistema de Sincronización](#sistema-de-sincronización)
8. [SDK para Proyectos Externos](#sdk-para-proyectos-externos)
9. [Mantenimiento](#mantenimiento)

---

## 🎯 Visión General

El **Mizton Marketplace** es un sistema de showcase (escaparate) para proyectos de tokenización de activos reales (RWA). No es un marketplace de inversión directa, sino un portal informativo que conecta a usuarios con proyectos tokenizados independientes.

### Características Principales

- ✅ **Showcase de Proyectos**: Listado visual de proyectos tokenizados
- ✅ **Múltiples Categorías**: Inmobiliario, Energía, Arte, Editorial, etc.
- ✅ **Sincronización Automática**: API Pull, Webhooks y lectura Blockchain
- ✅ **Analytics Integrado**: Seguimiento de vistas y clicks
- ✅ **Panel Admin Completo**: Gestión de proyectos y configuración
- ✅ **Responsive Design**: Optimizado para móviles y desktop

### Flujo de Usuario

```
Usuario → Marketplace → Ve Proyectos → Click "Ver Más" → Sitio Dedicado del Proyecto
```

---

## 🏗️ Arquitectura del Sistema

### Componentes Principales

```
/marketplace/
├── index.php                    # Vista pública (listado)
├── project.php                  # Vista detalle de proyecto
├── /admin/                      # Panel de administración
├── /api/                        # APIs REST
├── /config/                     # Configuración
├── /includes/                   # Funciones PHP
├── /assets/                     # CSS, JS, imágenes
├── /cron/                       # Tareas programadas
├── /sdk/                        # SDK de integración
└── /docs/                       # Documentación
```

### Arquitectura Híbrida de Sincronización

```
┌─────────────────────────────────────────┐
│      MIZTON MARKETPLACE (Hub)           │
│  - Cache de datos (actualizado c/5min) │
│  - Webhook receiver (tiempo real)      │
│  - Fallback a lectura blockchain       │
└─────────────────────────────────────────┘
              ↓ ↓ ↓
    ┌─────────┴─────────┴─────────┐
    ↓                   ↓          ↓
┌─────────┐      ┌─────────┐  ┌─────────┐
│Proyecto1│      │Proyecto2│  │Proyecto3│
│  API    │      │  API    │  │  API    │
└─────────┘      └─────────┘  └─────────┘
```

**Métodos de Actualización:**

1. **API Pull**: Cron cada 5 minutos consulta endpoint del proyecto
2. **Webhook**: Proyecto envía actualizaciones en tiempo real
3. **Blockchain**: Lectura directa del smart contract (futuro)
4. **Manual**: Actualización desde panel admin

---

## 📦 Instalación

### Paso 1: Crear Base de Datos

```bash
mysql -u root -p < /marketplace/sql/marketplace_database.sql
```

### Paso 2: Configurar Permisos

```bash
chmod 755 /marketplace
chmod 644 /marketplace/config/*.php
chmod 755 /marketplace/cron/*.php
```

### Paso 3: Configurar Cron Job

Agregar a crontab para sincronización automática:

```bash
*/5 * * * * /usr/bin/php /path/to/marketplace/cron/sync-projects.php >> /var/log/marketplace-sync.log 2>&1
```

### Paso 4: Verificar Instalación

Acceder a: `https://mizton.cat/marketplace/`

---

## 🗄️ Estructura de Base de Datos

### Tablas Principales

#### `tbl_marketplace_projects`
Almacena información de todos los proyectos.

**Campos Clave:**
- `project_code`: Código único (ej: LIBRO1, FIT1)
- `slug`: URL-friendly para SEO
- `category`: Categoría del proyecto
- `status`: Estado actual
- `cached_data`: JSON con datos completos
- `update_method`: api_pull, webhook, manual, blockchain

#### `tbl_marketplace_categories`
Categorías disponibles con iconos y colores.

#### `tbl_marketplace_sync_log`
Registro de todas las sincronizaciones.

#### `tbl_marketplace_webhooks`
Webhooks recibidos de proyectos.

#### `tbl_marketplace_stats`
Estadísticas de vistas y clicks.

### Vistas SQL

#### `vw_marketplace_active_projects`
Proyectos activos con información completa.

#### `vw_marketplace_sync_status`
Estado de sincronización de todos los proyectos.

---

## 🔌 API de Integración

### Formato JSON Estándar

Todos los proyectos deben exponer un endpoint que retorne:

```json
{
  "project_info": {
    "name": "Nombre del Proyecto",
    "category": "editorial",
    "description": "Descripción completa...",
    "short_description": "Descripción corta",
    "logo": "https://...",
    "main_image": "https://...",
    "status": "activo"
  },
  "blockchain": {
    "contract_address": "0x...",
    "network": "BSC",
    "token_symbol": "BOOK",
    "total_supply": 100000,
    "circulating_supply": 50000,
    "token_price_usd": 1.00,
    "market_cap": 50000
  },
  "financials": {
    "funding_goal": 100000,
    "raised": 50000,
    "funding_percentage": 50.0,
    "apy_staking": 8.5,
    "roi_projected": 300,
    "total_value_locked": 25000
  },
  "participation": {
    "holders_count": 250,
    "min_investment": 100,
    "max_investment": 10000,
    "tokens_available": 50000,
    "presale_start": "2025-01-01",
    "presale_end": "2025-03-31"
  },
  "milestones": [
    {
      "name": "Financiamiento",
      "description": "...",
      "status": "in_progress",
      "percentage": 50,
      "target_date": "2025-03-31"
    }
  ],
  "links": {
    "website": "https://...",
    "dashboard": "https://...",
    "whitepaper": "https://...",
    "twitter": "https://...",
    "telegram": "https://..."
  },
  "last_updated": "2025-12-26T17:42:00Z"
}
```

### Categorías Disponibles

- `inmobiliario`: Proyectos inmobiliarios
- `energia`: Energía renovable
- `editorial`: Libros y publicaciones
- `arte`: Arte y coleccionables
- `musical`: Proyectos musicales
- `cinematografia`: Películas y series
- `deportivo`: Eventos y talentos deportivos
- `agropecuario`: Agricultura y ganadería
- `industrial`: Manufactura y producción
- `tecnologia`: Startups tecnológicas
- `minero`: Minería y recursos
- `farmaceutico`: Investigación farmacéutica
- `gubernamental`: Proyectos gubernamentales
- `otro`: Otros proyectos

### Estados de Proyecto

- `desarrollo`: En desarrollo
- `preventa`: En preventa
- `activo`: Activo y funcionando
- `financiado`: Meta de financiamiento alcanzada
- `completado`: Proyecto completado
- `pausado`: Temporalmente pausado
- `cerrado`: Cerrado permanentemente

---

## 👨‍💼 Panel de Administración

### Acceso

URL: `https://mizton.cat/marketplace/admin/`

**Requisitos:** `$_SESSION['admin'] == 1`

### Funcionalidades

#### Gestión de Proyectos
- ✅ Crear nuevo proyecto
- ✅ Editar proyecto existente
- ✅ Eliminar proyecto
- ✅ Cambiar estado y visibilidad
- ✅ Marcar como destacado

#### Configuración de Sincronización
- ✅ Configurar método de actualización
- ✅ Establecer frecuencia de sincronización
- ✅ Configurar API endpoint y credenciales
- ✅ Probar sincronización manual

#### Analytics
- ✅ Vistas por proyecto
- ✅ Click-through rate
- ✅ Proyectos más populares
- ✅ Estadísticas de financiamiento

#### Gestión de Categorías
- ✅ Crear/editar categorías
- ✅ Asignar iconos y colores
- ✅ Ordenar categorías

---

## 🔄 Sistema de Sincronización

### API Pull (Recomendado)

**Configuración:**
1. Proyecto expone endpoint: `https://proyecto.com/api/marketplace-data`
2. Admin configura URL en Mizton Marketplace
3. Cron ejecuta cada X minutos
4. Datos se cachean en `cached_data` (JSON)

**Ventajas:**
- ✅ Simple de implementar
- ✅ Control total desde Mizton
- ✅ Fácil debugging

**Código de Ejemplo:**

```php
// En el proyecto externo
<?php
header('Content-Type: application/json');
echo json_encode(getProjectData());
```

### Webhook (Tiempo Real)

**Configuración:**
1. Admin configura API Secret en Mizton
2. Proyecto envía POST a `https://mizton.cat/marketplace/api/webhook-receiver.php`
3. Incluir firma HMAC en header `X-Signature`
4. Datos se actualizan inmediatamente

**Ventajas:**
- ✅ Actualizaciones en tiempo real
- ✅ Sin polling constante
- ✅ Eficiente

**Código de Ejemplo:**

```php
// En el proyecto externo
$data = getProjectData();
$data['project_code'] = 'LIBRO1';
$payload = json_encode($data);
$signature = hash_hmac('sha256', $payload, API_SECRET);

$ch = curl_init('https://mizton.cat/marketplace/api/webhook-receiver.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Signature: ' . $signature
    ]
]);
curl_exec($ch);
```

### Blockchain (Futuro)

Lectura directa del smart contract usando Web3.php.

---

## 🛠️ SDK para Proyectos Externos

### Archivo Template

Ubicación: `/marketplace/sdk/integration-template.php`

### Pasos de Integración

1. **Copiar Template**
   ```bash
   cp /marketplace/sdk/integration-template.php /tu-proyecto/api/marketplace-data.php
   ```

2. **Configurar Constantes**
   ```php
   define('PROJECT_CODE', 'LIBRO1');
   define('API_SECRET', 'tu-secret-key');
   ```

3. **Implementar Funciones**
   - `getProjectInfo()`
   - `getBlockchainData()`
   - `getFinancialData()`
   - `getParticipationData()`
   - `getMilestones()`
   - `getProjectLinks()`

4. **Exponer Endpoint**
   ```
   https://tu-proyecto.com/api/marketplace-data.php
   ```

5. **Registrar en Mizton Admin**
   - Ir a Admin → Proyectos → Nuevo
   - Ingresar URL del endpoint
   - Configurar método de actualización
   - Guardar

### Ejemplo Completo

Ver: `/marketplace/sdk/integration-template.php`

---

## 🔧 Mantenimiento

### Logs de Sincronización

```bash
# Ver logs del cron
tail -f /var/log/marketplace-sync.log

# Ver logs de webhooks
SELECT * FROM tbl_marketplace_webhooks 
WHERE received_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY received_at DESC;

# Ver logs de sincronización
SELECT * FROM tbl_marketplace_sync_log 
WHERE sync_timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY sync_timestamp DESC;
```

### Limpiar Logs Antiguos

```php
// Ejecutar manualmente o vía cron
require_once 'includes/sync-functions.php';
$deleted = cleanOldSyncLogs(30); // Mantener últimos 30 días
echo "Eliminados: $deleted registros";
```

### Verificar Estado de Sincronización

```sql
SELECT * FROM vw_marketplace_sync_status;
```

### Backup de Base de Datos

```bash
mysqldump -u root -p mizton_db \
  tbl_marketplace_projects \
  tbl_marketplace_categories \
  tbl_marketplace_sync_log \
  tbl_marketplace_webhooks \
  tbl_marketplace_stats \
  > marketplace_backup_$(date +%Y%m%d).sql
```

### Optimización

```sql
-- Optimizar tablas
OPTIMIZE TABLE tbl_marketplace_projects;
OPTIMIZE TABLE tbl_marketplace_sync_log;
OPTIMIZE TABLE tbl_marketplace_stats;

-- Analizar queries lentas
EXPLAIN SELECT * FROM vw_marketplace_active_projects;
```

---

## 📊 Métricas y Analytics

### Consultas Útiles

```sql
-- Proyectos más vistos (últimos 30 días)
SELECT 
    p.name,
    SUM(s.views_count) as total_views,
    SUM(s.click_throughs) as total_clicks,
    (SUM(s.click_throughs) / SUM(s.views_count) * 100) as ctr
FROM tbl_marketplace_projects p
JOIN tbl_marketplace_stats s ON p.id = s.project_id
WHERE s.stat_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY p.id
ORDER BY total_views DESC
LIMIT 10;

-- Proyectos por categoría
SELECT 
    category,
    COUNT(*) as count,
    AVG(funding_percentage) as avg_funding
FROM tbl_marketplace_projects
WHERE is_active = TRUE
GROUP BY category;

-- Estado de sincronización
SELECT 
    sync_status,
    COUNT(*) as count
FROM tbl_marketplace_projects
WHERE update_method != 'manual'
GROUP BY sync_status;
```

---

## 🚀 Roadmap Futuro

### Fase 2
- [ ] Lectura directa de blockchain (Web3.php)
- [ ] Sistema de notificaciones para nuevos proyectos
- [ ] Comparador de proyectos (lado a lado)
- [ ] Calculadora de ROI

### Fase 3
- [ ] Wishlist de proyectos favoritos
- [ ] Alertas personalizadas
- [ ] Gráficas de performance histórica
- [ ] API pública para terceros

### Fase 4
- [ ] Certificados NFT de inversión
- [ ] Marketplace secundario (reventa de tokens)
- [ ] Sistema de reputación de proyectos
- [ ] Integración con wallets

---

## 📞 Soporte

**Email:** marketplace@mizton.cat

**Documentación:** https://mizton.cat/marketplace/docs/

**GitHub:** (Privado - ferPineda369/panel-php)

---

## 📝 Changelog

### v1.0.0 (2025-12-26)
- ✅ Lanzamiento inicial
- ✅ Sistema de showcase completo
- ✅ Sincronización API Pull y Webhooks
- ✅ Panel admin funcional
- ✅ SDK de integración
- ✅ Analytics básico
- ✅ Responsive design

---

**Última actualización:** 26 de diciembre de 2025
