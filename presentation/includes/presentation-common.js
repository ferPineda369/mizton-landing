/* ==========================================================================
   MIZTON PRESENTATIONS - FUNCIONES COMUNES
   Para usar en: blockchain, kimen, smartContract, y futuras presentaciones
   ========================================================================== */

/* --------------------------------------------------------------------------
   FLOATING MENU - Inicialización
   -------------------------------------------------------------------------- */
function initFloatingMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const menuDropdown = document.getElementById('menu-dropdown');
    
    if (!menuToggle || !menuDropdown) return;
    
    // Toggle menu on button click
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        menuToggle.classList.toggle('active');
        menuDropdown.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!menuToggle.contains(e.target) && !menuDropdown.contains(e.target)) {
            menuToggle.classList.remove('active');
            menuDropdown.classList.remove('active');
        }
    });
    
    // Close menu when pressing Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            menuToggle.classList.remove('active');
            menuDropdown.classList.remove('active');
        }
    });
}

/* --------------------------------------------------------------------------
   TOGGLE CONTROLES DE NAVEGACIÓN
   -------------------------------------------------------------------------- */
function toggleNavControls() {
    const navControls = document.querySelector('.nav-controls');
    const toggleCheckbox = document.getElementById('nav-toggle');
    const menuText = document.querySelector('#nav-toggle-item .menu-text');
    
    if (!navControls || !toggleCheckbox) return;
    
    if (toggleCheckbox.checked) {
        navControls.classList.remove('hidden');
        if (menuText) menuText.textContent = 'Mostrar controles';
    } else {
        navControls.classList.add('hidden');
        if (menuText) menuText.textContent = 'Ocultar controles';
    }
}

/* --------------------------------------------------------------------------
   DETECTAR PRESENTACIÓN ACTUAL Y MARCAR MENÚ
   -------------------------------------------------------------------------- */
function markActiveMenu() {
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.menu-item[data-menu]');
    
    menuItems.forEach(item => {
        const menuName = item.getAttribute('data-menu');
        if (currentPath.includes('/' + menuName + '/') || currentPath.includes('/' + menuName)) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

/* --------------------------------------------------------------------------
   INICIALIZAR MENÚ DESPUÉS DE CARGAR INCLUDE
   -------------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que el menú se cargue (si se usa fetch)
    setTimeout(function() {
        initFloatingMenu();
        markActiveMenu();
    }, 100);
});
