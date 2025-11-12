# Configuración de Base de Datos - Sorteo Mizton

## Error Actual
El sistema está mostrando "Error interno del servidor" porque no puede conectarse a la base de datos.

## Pasos para Solucionar

### 1. Verificar XAMPP
- Asegúrate de que Apache y MySQL estén ejecutándose en XAMPP
- Abre phpMyAdmin en: http://localhost/phpmyadmin

### 2. Configurar Base de Datos
Edita el archivo: `D:\xampp\htdocs\landing\sorteo\config\database.php`

Cambia estas líneas según tu configuración:
```php
$host = 'localhost';           // Normalmente localhost
$dbname = 'mizton_db';         // Nombre de tu base de datos
$username = 'root';            // Usuario de MySQL (normalmente root)
$password = '';                // Contraseña de MySQL (vacía por defecto en XAMPP)
```

### 3. Crear Base de Datos
Si no existe la base de datos `mizton_db`, créala:
1. Abre phpMyAdmin
2. Clic en "Nueva" 
3. Nombre: `mizton_db`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Clic en "Crear"

### 4. Probar Conexión
Visita: http://localhost/landing/sorteo/test-db.php

Deberías ver:
```json
{
  "success": true,
  "message": "Conexión exitosa a la base de datos"
}
```

### 5. Probar API
Visita: http://localhost/landing/sorteo/api/get_numbers_simple.php

Deberías ver:
```json
{
  "success": true,
  "numbers": [...],
  "stats": {...}
}
```

## Configuraciones Alternativas

### Para Producción (VPS)
```php
$host = 'localhost';
$dbname = 'nombre_bd_real';
$username = 'usuario_bd';
$password = 'contraseña_segura';
```

### Para Desarrollo Local con Contraseña
```php
$host = 'localhost';
$dbname = 'mizton_db';
$username = 'root';
$password = 'tu_contraseña_mysql';
```

## Archivos Importantes

### APIs Simplificadas (Funcionando)
- `api/get_numbers_simple.php` - Obtener números
- `api/register_number_simple.php` - Registrar números

### APIs Completas (Con bloqueo temporal)
- `api/get_numbers.php` - Versión completa
- `api/register_number.php` - Versión completa
- `api/block_numbers.php` - Manejo de bloqueos

## Funcionalidades

### Actualmente Funcionando
✅ Selección múltiple de números
✅ Cálculo automático de total ($25 x números)
✅ Timer de 2 minutos (solo visual)
✅ Formulario simplificado (solo nombre)
✅ Información de pago actualizada
✅ Efectos navideños con copos de nieve

### Pendiente de Activar
⏳ Bloqueo temporal en servidor (2 minutos)
⏳ Sincronización entre usuarios
⏳ WhatsApp automático

## Solución de Problemas

### Error: "Access denied for user"
- Verifica usuario y contraseña en `config/database.php`
- Asegúrate de que MySQL esté ejecutándose

### Error: "Unknown database"
- Crea la base de datos `mizton_db` en phpMyAdmin
- O cambia `$dbname` por una base existente

### Error: "Table doesn't exist"
- Las tablas se crean automáticamente
- Si persiste, ejecuta manualmente las consultas SQL del archivo

## Contacto
Una vez configurada la base de datos, el sistema debería funcionar completamente.
