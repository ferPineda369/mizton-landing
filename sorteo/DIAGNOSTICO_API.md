# Diagnóstico de API Principal - Sorteo Mizton

## Problema Identificado
La API principal `api/get_numbers.php` no está funcionando y el sistema está usando la API de respaldo.

## Archivos de Diagnóstico Creados

### 1. Debug General
**URL:** `https://mizton.cat/sorteo/debug_api.php`

Este archivo prueba paso a paso:
- ✅ Configuración de base de datos
- ✅ Función cleanExpiredReservations
- ✅ Creación de tabla sorteo_temp_blocks
- ✅ Consulta principal con LEFT JOIN
- ✅ Tablas existentes

### 2. API Corregida
**URL:** `https://mizton.cat/sorteo/api/get_numbers_fixed.php`

Versión mejorada que:
- ✅ Maneja errores de tabla de bloqueos
- ✅ Funciona con o sin tabla sorteo_temp_blocks
- ✅ Proporciona información de debug
- ✅ No falla si hay problemas menores

### 3. Diagnóstico del Sistema
**URL:** `https://mizton.cat/sorteo/diagnostico.php`

Estado general del sistema.

## Pasos para Diagnosticar

### Paso 1: Verificar Debug General
Visita: `https://mizton.cat/sorteo/debug_api.php`

**Busca errores en:**
- `config_include` - Error al cargar configuración
- `clean_expired` - Error en función de limpieza
- `create_temp_blocks` - Error creando tabla de bloqueos
- `main_query` - Error en consulta principal

### Paso 2: Probar API Corregida
Visita: `https://mizton.cat/sorteo/api/get_numbers_fixed.php`

**Debe mostrar:**
```json
{
  "success": true,
  "numbers": [...],
  "has_blocking": true/false,
  "debug": "api_fixed_version"
}
```

### Paso 3: Verificar en el Sorteo
Visita: `https://mizton.cat/sorteo/`

**En la consola del navegador (F12) busca:**
- "Cargando números desde API..."
- "Respuesta recibida: 200 OK"
- "Datos recibidos: {success: true, ...}"

## Posibles Problemas y Soluciones

### Error 1: Tabla sorteo_temp_blocks
**Síntoma:** Error al crear tabla de bloqueos temporales
**Solución:** La API corregida funciona sin esta tabla

### Error 2: Permisos de sesión
**Síntoma:** Error con session_start()
**Solución:** Verificar permisos de carpeta temporal

### Error 3: LEFT JOIN fallando
**Síntoma:** Error en consulta principal
**Solución:** API corregida usa consulta condicional

### Error 4: Función cleanExpiredReservations
**Síntoma:** Error al limpiar reservas
**Solución:** Verificar que la función existe en config/database.php

## Resultados Esperados

### Si debug_api.php muestra todos SUCCESS:
- La API principal debería funcionar
- Problema puede ser en el LEFT JOIN o sesiones

### Si get_numbers_fixed.php funciona:
- Podemos usar esta versión como reemplazo
- Sistema funcionará con todas las características

### Si ambos fallan:
- Problema en configuración de base de datos
- Verificar credenciales y permisos

## Próximos Pasos

1. **Ejecutar diagnósticos** y reportar resultados
2. **Identificar error específico** en la API principal
3. **Aplicar solución** correspondiente
4. **Restaurar funcionalidad completa** del sistema

## Notas Técnicas

- La API corregida es más robusta y maneja errores mejor
- Funciona con o sin tabla de bloqueos temporales
- Proporciona información de debug útil
- Compatible con todas las funcionalidades existentes
