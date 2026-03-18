# KIMEN Token - Landing Page

Landing page para el primer libro tokenizado de la saga KIMEN: "Un Bello Mundo por Extrañar"

## 📁 Estructura del Proyecto

```
kimen/
├── index.html          # Página principal
├── styles.css          # Estilos y animaciones
├── script.js           # Funcionalidad JavaScript
├── assets/             # Imágenes y recursos
│   ├── README.md       # Guía de imágenes
│   └── portada-libro.jpg  # Portada del libro (agregar)
└── README.md           # Este archivo
```

## 🚀 Características

### Secciones Implementadas

1. **Hero Section** - Título principal con fondo espacial animado
2. **El Libro** - Información sobre la obra y características
3. **¿Cómo Funciona?** - 3 pasos simples para participar
4. **Tokenomics** - Distribución de 4,800 KIMEN tokens
5. **Beneficios** - Regalías, acceso exclusivo, NFTs, valor futuro
6. **Números Claros** - Tabla de escenarios de ganancias
7. **Roadmap** - Timeline 2026-2028
8. **Disclaimer Legal** - Información importante sobre el token
9. **CTA Final** - Llamado a la acción con countdown y stats
10. **Footer** - Enlaces y avisos legales

### Funcionalidades JavaScript

- ✅ Smooth scroll en navegación
- ✅ Countdown timer dinámico
- ✅ Gráfico de tokenomics (canvas)
- ✅ Animaciones on-scroll
- ✅ Efecto parallax en hero
- ✅ Efecto 3D en portada del libro
- ✅ Stats en tiempo real (simulados)

### Diseño

- 🎨 Paleta de colores: Dorado (#FFD700), Púrpura (#9B59B6), Azul (#3498DB)
- 🌟 Fondo espacial con estrellas animadas
- 📱 Completamente responsive
- ⚡ Animaciones suaves y modernas
- 🎯 CTAs destacados y efectivos

## 📝 Configuración

### 1. Agregar Imagen del Libro

Coloca la portada del libro en:
```
assets/portada-libro.jpg
```

**Especificaciones recomendadas:**
- Tamaño: 800x1200px (portrait)
- Formato: JPG o PNG
- Peso: < 500KB (optimizada para web)

### 2. Personalizar Countdown

Edita en `script.js` línea 14:
```javascript
targetDate.setDate(targetDate.getDate() + 30); // Cambiar días
```

### 3. Actualizar Stats en Vivo

Edita en `script.js` línea 120:
```javascript
const tokensSold = 234; // Actualizar con datos reales
```

### 4. Configurar Enlaces

Actualiza los siguientes enlaces en `index.html`:

- **WhatsApp**: Línea 434 - `https://wa.me/522226536090`
- **Redes sociales**: Líneas 442-445
- **Contratos BSC**: Línea 456
- **Whitepaper**: Agregar URL real

## 🔧 Próximas Integraciones

### Funcionalidades Pendientes

1. **Sistema de Compra**
   - Modal de compra de tokens
   - Integración con wallet (MetaMask/Trust Wallet)
   - Procesamiento de pagos

2. **Whitepaper**
   - PDF descargable
   - Versión web interactiva

3. **Capítulo 1 Gratis**
   - Sistema de descarga
   - Captura de email (newsletter)

4. **API de Stats**
   - Conexión a smart contract
   - Datos en tiempo real de ventas
   - Actualización automática de stats

5. **Analytics**
   - Google Analytics
   - Facebook Pixel (si aplica)
   - Tracking de conversiones

## 🌐 Deployment

### Local (XAMPP)

Accede a:
```
http://localhost/landing/marketplace/projects/kimen/
```

### Producción

1. Subir archivos al servidor
2. Configurar dominio/subdirectorio
3. Verificar rutas de assets
4. Probar en diferentes dispositivos

## 📱 Responsive Breakpoints

- **Desktop**: > 1024px
- **Tablet**: 768px - 1024px
- **Mobile**: < 768px
- **Small Mobile**: < 480px

## ⚠️ Notas Importantes

1. **Imagen de Portada**: Actualmente usa placeholder, agregar imagen real
2. **Enlaces Externos**: Verificar y actualizar todos los enlaces
3. **Legal**: Revisar disclaimer con asesor legal
4. **Smart Contract**: Agregar dirección del contrato cuando esté disponible
5. **Auditoría**: Agregar enlace a auditoría cuando esté lista

## 🎯 SEO Optimizado

- Meta tags configurados
- Títulos descriptivos
- Estructura semántica HTML5
- Alt text en imágenes (agregar cuando se suban)
- Schema markup preparado

## 📞 Soporte

Para modificaciones o soporte técnico, contactar al equipo de desarrollo de Mizton.

---

**KIMEN Token** - Un Bello Mundo por Extrañar Tokenizado  
© 2026 Todos los derechos reservados
