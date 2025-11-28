# Sistema de Sorteo Mizton 2025

## Descripción
Sistema completo de rifa/sorteo con 100 números de participación, reserva temporal y confirmación de pagos.

## Características Principales

### 🎯 Funcionalidades del Usuario
- **Grid de 100 números** con estados visuales (disponible, reservado, confirmado)
- **Contador regresivo** hasta el 13 de diciembre de 2025
- **Formulario de registro** con validación completa
- **Sistema de reserva temporal** de 15 minutos
- **Información de pago** automática con datos bancarios
- **Diseño responsive** para móviles y desktop

### 🔧 Funcionalidades Administrativas
- **Panel de administración** con estadísticas en tiempo real
- **Confirmación manual de pagos** por número
- **Log de transacciones** completo
- **Limpieza automática** de reservas expiradas
- **Estadísticas de recaudación**

### 🛡️ Seguridad y Validaciones
- **Validación de datos** en frontend y backend
- **Prevención de números duplicados** por email
- **Transacciones atómicas** en base de datos
- **Log de actividades** con IP y user agent
- **Limpieza automática** de reservas expiradas

## Estructura de Archivos

```
sorteo/
├── index.php                 # Página principal
├── config/
│   └── database.php          # Configuración de BD
├── api/
│   ├── get_numbers.php       # Obtener estado de números
│   ├── register_number.php   # Reservar número
│   └── confirm_payment.php   # Confirmar pago (admin)
├── admin/
│   └── index.php            # Panel administrativo
├── assets/
│   ├── css/
│   │   └── sorteo.css       # Estilos principales
│   └── js/
│       └── sorteo.js        # JavaScript principal
├── .htaccess               # Configuración Apache
└── README.md              # Este archivo
```

## Base de Datos

### Tabla: `sorteo_numbers`
- `id` - ID único
- `number_value` - Número del 1 al 100
- `status` - Estado: available, reserved, confirmed
- `participant_name` - Nombre del participante
- `participant_email` - Email del participante
- `reserved_at` - Fecha de reserva
- `confirmed_at` - Fecha de confirmación
- `reservation_expires_at` - Fecha de expiración de reserva

### Tabla: `sorteo_transactions`
- `id` - ID único
- `number_value` - Número involucrado
- `participant_name` - Nombre del participante
- `participant_email` - Email del participante
- `action` - Acción: reserved, confirmed, expired, cancelled
- `ip_address` - IP del usuario
- `user_agent` - Navegador del usuario
- `created_at` - Fecha de la transacción

## Configuración

### 1. Base de Datos
El sistema utiliza la misma configuración de base de datos que la landing principal de Mizton. Las tablas se crean automáticamente al acceder por primera vez.

### 2. Datos de Pago
Editar en `index.php` la sección de información de pago:
```html
<p><strong>Cuenta:</strong> 1234567890</p>
<p><strong>Banco:</strong> Banco Ejemplo</p>
<p><strong>Titular:</strong> Mizton Sorteos</p>
<p><strong>Monto:</strong> $50.00 MXN</p>
```

### 3. Fecha del Sorteo
La fecha está configurada para el 13 de diciembre de 2025. Para cambiarla, editar en `assets/js/sorteo.js`:
```javascript
this.targetDate = new Date('2025-12-13T23:59:59').getTime();
```

### 4. Contraseñas de Administración
- **Panel Admin:** `mizton_admin_2025`
- **API Admin:** `mizton_sorteo_2025`

**⚠️ IMPORTANTE:** Cambiar estas contraseñas antes de subir a producción.

## URLs de Acceso

### Producción (VPS)
- **Sorteo:** https://mizton.cat/sorteo/
- **Admin:** https://mizton.cat/sorteo/admin/

### Desarrollo Local
- **Sorteo:** http://localhost/landing/sorteo/
- **Admin:** http://localhost/landing/sorteo/admin/

## Flujo de Uso

### Para Participantes
1. Acceder a `mizton.cat/sorteo/`
2. Ver contador regresivo y reglas
3. Seleccionar número disponible (verde)
4. Llenar formulario con nombre y email
5. Ver información de pago y tiempo límite (15 min)
6. Realizar transferencia bancaria
7. Esperar confirmación del administrador

### Para Administradores
1. Acceder a `mizton.cat/sorteo/admin/`
2. Ingresar contraseña: `mizton_admin_2025`
3. Ver estadísticas y números reservados
4. Confirmar pagos recibidos
5. Monitorear transacciones

## Características Técnicas

### Estados de Números
- **Verde (available):** Disponible para selección
- **Amarillo (reserved):** Reservado temporalmente (15 min)
- **Rojo (confirmed):** Confirmado y pagado

### Temporizadores
- **Contador principal:** Hasta 13 dic 2025, 23:59:59
- **Reserva temporal:** 15 minutos por número
- **Auto-refresh:** Panel admin cada 30 segundos
- **Limpieza automática:** Reservas expiradas

### Validaciones
- **Nombre:** Mínimo 3 caracteres, solo letras
- **Email:** Formato válido y único por sorteo
- **Número:** Del 1 al 100, disponible al momento
- **Duplicados:** Un email = un número máximo

### Responsive Design
- **Desktop:** Grid 10x10 números
- **Tablet:** Grid adaptativo
- **Móvil:** Grid 8 columnas, menús colapsables

## Personalización

### Colores y Branding
Los colores principales están definidos en `assets/css/sorteo.css`:
```css
:root {
    --primary-color: #2E8B57;    /* Verde Mizton */
    --secondary-color: #3CB371;   /* Verde claro */
    --accent-color: #FFD700;      /* Dorado */
}
```

### Reglas del Sorteo
Las reglas se pueden editar directamente en `index.php` en la sección `.rules-content`.

### Información de Pago
Actualizar los datos bancarios en el modal de registro dentro de `index.php`.

## Mantenimiento

### Limpieza Automática
El sistema limpia automáticamente las reservas expiradas cada vez que:
- Se carga la página principal
- Se accede a las APIs
- Se accede al panel admin

### Logs
Los errores se registran en el log de PHP del servidor. Para debugging, verificar:
- `/var/log/apache2/error.log` (Linux)
- `C:\xampp\apache\logs\error.log` (Windows)

### Backup
Respaldar regularmente las tablas:
- `sorteo_numbers`
- `sorteo_transactions`

## Soporte

Para soporte técnico o modificaciones, contactar al desarrollador del sistema.

---

**Desarrollado para Mizton - Sistema de Sorteos 2025**
