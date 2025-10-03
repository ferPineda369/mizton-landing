/**
 * Scripts del Panel de Administración - Mizton Blog
 * Funcionalidades interactivas y dinámicas
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAdmin();
});

function initializeAdmin() {
    console.log('🔧 Admin Panel inicializado');
    
    // Inicializar componentes
    initSectionNavigation();
    initFormValidation();
    initTableActions();
    initAutoSave();
    
    // Mostrar sección por defecto
    showSection('dashboard');
}

// Navegación entre secciones
function initSectionNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const sectionId = this.getAttribute('href').substring(1);
                showSection(sectionId);
                
                // Actualizar navegación activa
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}

// Mostrar sección específica
function showSection(sectionId) {
    // Ocultar todas las secciones
    const sections = document.querySelectorAll('.admin-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostrar sección seleccionada
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
        
        // Actualizar título de página
        const titles = {
            'dashboard': 'Dashboard',
            'posts': 'Gestionar Posts',
            'new-post': 'Crear Nuevo Post',
            'newsletter': 'Newsletter'
        };
        
        const pageTitle = document.getElementById('page-title');
        if (pageTitle && titles[sectionId]) {
            pageTitle.textContent = titles[sectionId];
        }
    }
}

// Validación de formularios
function initFormValidation() {
    const postForm = document.querySelector('.post-form');
    
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            if (!validatePostForm()) {
                e.preventDefault();
                showNotification('Por favor, completa todos los campos requeridos.', 'error');
            }
        });
        
        // Auto-generar slug desde título
        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.addEventListener('input', function() {
                generateSlugPreview(this.value);
            });
        }
        
        // Auto-generar extracto desde contenido
        const contentTextarea = document.getElementById('content');
        const excerptTextarea = document.getElementById('excerpt');
        
        if (contentTextarea && excerptTextarea) {
            contentTextarea.addEventListener('input', function() {
                if (!excerptTextarea.value.trim()) {
                    const plainText = stripHtml(this.value);
                    const excerpt = plainText.substring(0, 150) + (plainText.length > 150 ? '...' : '');
                    excerptTextarea.value = excerpt;
                }
            });
        }
    }
}

// Validar formulario de post
function validatePostForm() {
    const requiredFields = ['title', 'content', 'category'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && !field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else if (field) {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Generar preview del slug
function generateSlugPreview(title) {
    const slug = title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .trim('-');
    
    // Mostrar preview del slug (si existe elemento)
    const slugPreview = document.getElementById('slug-preview');
    if (slugPreview) {
        slugPreview.textContent = slug || 'slug-del-post';
    }
}

// Quitar HTML tags
function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

// Acciones de tabla
function initTableActions() {
    // Delegación de eventos para botones dinámicos
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-danger')) {
            const postId = e.target.closest('.btn-danger').getAttribute('data-post-id');
            if (postId) {
                confirmDeletePost(postId);
            }
        }
        
        if (e.target.closest('.btn-secondary')) {
            const postId = e.target.closest('.btn-secondary').getAttribute('data-post-id');
            if (postId) {
                editPost(postId);
            }
        }
    });
}

// Editar post
function editPost(postId) {
    // Cargar datos del post vía AJAX
    fetch(`api/get-post.php?id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.post);
                showSection('new-post');
            } else {
                showNotification('Error al cargar el post', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
}

// Poblar formulario para edición
function populateEditForm(post) {
    const form = document.querySelector('.post-form');
    if (!form) return;
    
    // Cambiar acción del formulario
    const actionInput = form.querySelector('input[name="action"]');
    if (actionInput) {
        actionInput.value = 'update_post';
    }
    
    // Agregar campo de ID si no existe
    let postIdInput = form.querySelector('input[name="post_id"]');
    if (!postIdInput) {
        postIdInput = document.createElement('input');
        postIdInput.type = 'hidden';
        postIdInput.name = 'post_id';
        form.appendChild(postIdInput);
    }
    postIdInput.value = post.id;
    
    // Llenar campos
    const fields = ['title', 'category', 'excerpt', 'content', 'image', 'status'];
    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input && post[field]) {
            input.value = post[field];
        }
    });
    
    // Tags (convertir array a string)
    const tagsInput = document.getElementById('tags');
    if (tagsInput && post.tags) {
        tagsInput.value = Array.isArray(post.tags) ? post.tags.join(', ') : post.tags;
    }
    
    // Featured checkbox
    const featuredInput = document.getElementById('featured');
    if (featuredInput) {
        featuredInput.checked = post.featured == 1;
    }
    
    // Cambiar texto del botón
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Actualizar Post';
    }
}

// Confirmar eliminación de post
function confirmDeletePost(postId) {
    if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
        deletePost(postId);
    }
}

// Eliminar post
function deletePost(postId) {
    const formData = new FormData();
    formData.append('action', 'delete_post');
    formData.append('post_id', postId);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Recargar página para mostrar cambios
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar el post', 'error');
    });
}

// Auto-guardado de borradores
function initAutoSave() {
    const form = document.querySelector('.post-form');
    if (!form) return;
    
    let autoSaveTimer;
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                autoSaveDraft();
            }, 30000); // Auto-guardar cada 30 segundos
        });
    });
}

// Guardar borrador automáticamente
function autoSaveDraft() {
    const form = document.querySelector('.post-form');
    if (!form) return;
    
    const formData = new FormData(form);
    formData.set('action', 'auto_save_draft');
    
    fetch('api/auto-save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Borrador guardado automáticamente', 'info', 2000);
        }
    })
    .catch(error => {
        console.error('Error en auto-guardado:', error);
    });
}

// Limpiar formulario
function resetForm() {
    const form = document.querySelector('.post-form');
    if (!form) return;
    
    if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
        form.reset();
        
        // Restaurar acción original
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput) {
            actionInput.value = 'create_post';
        }
        
        // Remover campo de ID si existe
        const postIdInput = form.querySelector('input[name="post_id"]');
        if (postIdInput) {
            postIdInput.remove();
        }
        
        // Restaurar texto del botón
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Post';
        }
        
        showNotification('Formulario limpiado', 'info');
    }
}

// Sistema de notificaciones
function showNotification(message, type = 'info', duration = 4000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type]};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 350px;
        font-weight: 500;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remover
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Previsualización de imágenes
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.style.cssText = 'max-width: 200px; max-height: 200px; margin-top: 10px; border-radius: 8px;';
                input.parentNode.appendChild(preview);
            }
            preview.src = e.target.result;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Contador de palabras
function initWordCounter() {
    const contentTextarea = document.getElementById('content');
    if (!contentTextarea) return;
    
    const counter = document.createElement('div');
    counter.className = 'word-counter';
    counter.style.cssText = 'font-size: 0.875rem; color: #6c757d; margin-top: 0.5rem;';
    contentTextarea.parentNode.appendChild(counter);
    
    function updateCounter() {
        const text = stripHtml(contentTextarea.value);
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        const readTime = Math.ceil(words / 200); // 200 palabras por minuto
        
        counter.textContent = `${words} palabras • ~${readTime} min de lectura`;
    }
    
    contentTextarea.addEventListener('input', updateCounter);
    updateCounter(); // Inicial
}

// Búsqueda en tablas
function initTableSearch() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Buscar posts...';
    searchInput.className = 'table-search';
    searchInput.style.cssText = `
        width: 300px;
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 1rem;
    `;
    
    const postsTable = document.querySelector('.posts-table');
    if (postsTable) {
        postsTable.parentNode.insertBefore(searchInput, postsTable);
        
        searchInput.addEventListener('input', function() {
            filterTable(this.value, postsTable);
        });
    }
}

// Filtrar tabla
function filterTable(searchTerm, table) {
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// Exportar funciones globales
window.showSection = showSection;
window.editPost = editPost;
window.deletePost = deletePost;
window.resetForm = resetForm;
window.previewImage = previewImage;

// Inicializar componentes adicionales cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initWordCounter();
    initTableSearch();
});
