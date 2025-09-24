# 🤖 GUÍA PARA ACTIVAR IA EN EL CHAT DE MIZTON

## 📋 ESTADO ACTUAL
- ✅ Chat funcional con FAQ estáticas
- ✅ Captura de leads y emails
- ✅ Integración con sistema de referidos
- ✅ Estructura preparada para IA

## 🚀 PASOS PARA ACTIVAR IA

### PASO 1: Obtener API Key de OpenAI
1. Ir a [OpenAI Platform](https://platform.openai.com/)
2. Crear cuenta o iniciar sesión
3. Ir a API Keys y crear nueva key
4. Copiar la key (empieza con `sk-...`)

### PASO 2: Configurar Variables de Entorno
En el archivo `.env` del VPS, agregar:
```bash
# IA Configuration
OPENAI_API_KEY=sk-tu-api-key-aqui
AI_ENABLED=true
AI_MODEL=gpt-3.5-turbo
AI_MAX_TOKENS=300
AI_TEMPERATURE=0.7
```

### PASO 3: Verificar Funcionamiento
1. Probar chat en landing page
2. Verificar logs en tabla `ai_usage_logs`
3. Monitorear respuestas de IA vs FAQ

## 📊 MONITOREO Y MÉTRICAS

### Consultas SQL Útiles:
```sql
-- Ver uso de IA vs FAQ
SELECT model_used, COUNT(*) as total 
FROM ai_usage_logs 
GROUP BY model_used;

-- Ver conversaciones recientes
SELECT session_id, user_message, ai_response, model_used, created_at 
FROM ai_usage_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- Ver leads capturados por chat
SELECT email, referral_code, status, created_at 
FROM chat_leads 
ORDER BY created_at DESC 
LIMIT 20;

-- Ver escalamientos a humanos
SELECT el.email, el.contact_method, el.created_at,
       JSON_EXTRACT(el.contact_info, '$.message') as escalation_message
FROM escalation_logs el 
ORDER BY el.created_at DESC 
LIMIT 10;
```

## 🔧 CONFIGURACIÓN AVANZADA

### Modelos Disponibles:
- `gpt-3.5-turbo` (Recomendado - Económico y rápido)
- `gpt-4` (Más inteligente pero más costoso)
- `gpt-4-turbo` (Balance entre costo y calidad)

### Ajustar Temperatura:
- `0.1-0.3`: Respuestas más consistentes y conservadoras
- `0.7`: Balance (recomendado)
- `0.9-1.0`: Respuestas más creativas pero menos predecibles

## 🛡️ SEGURIDAD Y LÍMITES

### Protecciones Implementadas:
- ✅ Prompt del sistema que limita respuestas a temas de Mizton
- ✅ Fallback automático a FAQ si IA falla
- ✅ Logging de todas las interacciones
- ✅ Límite de tokens para controlar costos

### Monitorear Costos:
- Revisar dashboard de OpenAI regularmente
- Establecer límites de gasto mensual
- Monitorear uso por sesión

## 🔄 ROLLBACK A FAQ

Si necesitas desactivar la IA temporalmente:
```bash
# En .env
AI_ENABLED=false
```

El sistema automáticamente volverá a usar FAQ estáticas.

## 📈 MEJORAS FUTURAS

### Fase 3 - IA Avanzada:
- [ ] Base de conocimiento vectorial
- [ ] Integración con documentos PDF de Mizton
- [ ] Análisis de sentimientos
- [ ] Escalamiento inteligente a humanos
- [ ] Personalización por tipo de usuario

### Integraciones Adicionales:
- [ ] WhatsApp Business API
- [ ] Telegram Bot
- [ ] Integración con CRM
- [ ] Dashboard de métricas en tiempo real
