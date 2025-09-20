# Mizton Landing Page

Landing page oficial de Mizton con sistema de referidos integrado.

## Configuración

### 1. Variables de Entorno

Copia el archivo de ejemplo y configura las variables:

```bash
cp .env.example .env
```

Edita el archivo `.env` con los valores correctos:

```bash
# Configuración de Base de Datos
DB_HOST=localhost
DB_NAME=mizton_db
DB_USER=mizton_user
DB_PASS=tu_contraseña_real

# Configuración de Entorno
ENVIRONMENT=production
DEBUG=false

# Configuración de Zona Horaria
TIMEZONE=-06:00

# URLs del Sistema
PANEL_URL=https://panel.mizton.cat
LANDING_URL=https://mizton.cat

# WhatsApp por Defecto (si no hay referido)
DEFAULT_WHATSAPP=5212226536090
```

### 2. Permisos de Archivos

```bash
chmod 644 .env
chown www-data:www-data .env
```

### 3. Estructura de Archivos

- `index.html` - Página principal
- `script.js` - JavaScript principal con manejo de referidos
- `api/referral-info.php` - API para información de referidos
- `database.php` - Funciones de base de datos
- `bootstrap-landing.php` - Bootstrap simplificado (evita conflictos con panel)

## Funcionalidades

### Sistema de Referidos

La landing page detecta automáticamente códigos de referido desde:
- URL: `?ref=codigo123`
- Hash: `#ref=codigo123`

### Integración WhatsApp

Los botones de WhatsApp se actualizan automáticamente con:
1. Número del referidor (si existe y tiene `landing_preference = 1`)
2. Número por defecto configurado en `.env`

### API Endpoints

- `GET /api/referral-info.php?ref=codigo` - Información del referido

## Deployment

El deployment se realiza automáticamente via webhook de GitHub.

### Manual Deployment

```bash
cd /usr/local/lsws/Example/html
git pull origin main
```

## Debugging

Para habilitar el modo debug, cambia en `.env`:

```
DEBUG=true
```

Los logs se escriben en el error log del servidor web.
