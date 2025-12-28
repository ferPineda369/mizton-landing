/**
 * JavaScript del Marketplace Mizton
 */

(function() {
    'use strict';

    // Estado global del marketplace
    const MarketplaceState = {
        currentCategory: null,
        currentStatus: null,
        currentSearch: '',
        currentSort: 'default',
        currentPage: 1,
        projects: [],
        filteredProjects: []
    };

    /**
     * Inicializar marketplace
     */
    function initMarketplace() {
        setupEventListeners();
        loadProjects();
    }

    /**
     * Configurar event listeners
     */
    function setupEventListeners() {
        // Filtros
        const categoryFilter = document.getElementById('category-filter');
        const statusFilter = document.getElementById('status-filter');
        const sortFilter = document.getElementById('sort-filter');
        const searchInput = document.getElementById('search-input');
        const filterBtn = document.getElementById('apply-filters');

        if (categoryFilter) {
            categoryFilter.addEventListener('change', handleFilterChange);
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', handleFilterChange);
        }

        if (sortFilter) {
            sortFilter.addEventListener('change', handleFilterChange);
        }

        if (searchInput) {
            searchInput.addEventListener('input', debounce(handleSearchChange, 500));
        }

        if (filterBtn) {
            filterBtn.addEventListener('click', applyFilters);
        }

        // Categorías clickeables
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('click', function() {
                const category = this.dataset.category;
                selectCategory(category);
            });
        });

        // Tabs en vista detalle
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                switchTab(tabId);
            });
        });
    }

    /**
     * Cargar proyectos desde API
     */
    function loadProjects() {
        showLoading();

        fetch('/marketplace/api/get-projects.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    MarketplaceState.projects = data.projects;
                    MarketplaceState.filteredProjects = data.projects;
                    renderProjects(data.projects);
                } else {
                    showError(data.error || 'Error al cargar proyectos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error de conexión al cargar proyectos');
            })
            .finally(() => {
                hideLoading();
            });
    }

    /**
     * Renderizar proyectos en el grid
     */
    function renderProjects(projects) {
        const grid = document.getElementById('projects-grid');
        
        if (!grid) return;

        if (projects.length === 0) {
            grid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="bi bi-inbox"></i>
                    <h3>No se encontraron proyectos</h3>
                    <p>Intenta ajustar los filtros de búsqueda</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = projects.map(project => createProjectCard(project)).join('');

        // Agregar event listeners a los botones
        const viewButtons = grid.querySelectorAll('.btn-view-project');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const projectId = this.dataset.projectId;
                recordClickThrough(projectId);
            });
        });
    }

    /**
     * Crear HTML de card de proyecto
     */
    function createProjectCard(project) {
        const categoryInfo = getCategoryInfo(project.category);
        const statusInfo = getStatusInfo(project.status);
        const featuredClass = project.featured ? 'featured' : '';
        
        const imageUrl = project.main_image_url || '/marketplace/assets/images/placeholder-project.jpg';
        const fundingPercentage = project.funding_percentage || 0;
        const tokenPrice = formatCurrency(project.token_price_usd);
        const apy = project.apy_percentage ? formatPercentage(project.apy_percentage) : 'N/A';

        return `
            <div class="project-card ${featuredClass}" data-project-id="${project.id}">
                <img src="${imageUrl}" alt="${escapeHtml(project.name)}" class="project-image" 
                     onerror="this.src='/marketplace/assets/images/placeholder-project.jpg'">
                
                <div class="project-content">
                    <div class="project-header">
                        <h3 class="project-title">${escapeHtml(project.name)}</h3>
                        <span class="project-code">${escapeHtml(project.project_code)}</span>
                    </div>

                    <div class="project-category" style="background: ${categoryInfo.color}20; color: ${categoryInfo.color};">
                        <i class="bi ${categoryInfo.icon}"></i>
                        <span>${categoryInfo.name}</span>
                    </div>

                    <p class="project-description">${escapeHtml(project.short_description || project.description || '')}</p>

                    ${fundingPercentage > 0 ? `
                    <div class="funding-progress">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: ${Math.min(fundingPercentage, 100)}%"></div>
                        </div>
                        <div class="progress-info">
                            <span>${formatCurrency(project.funding_raised)} recaudado</span>
                            <span class="progress-percentage">${formatPercentage(fundingPercentage)}</span>
                        </div>
                    </div>
                    ` : ''}

                    <div class="project-stats">
                        <div class="stat-item">
                            <div class="stat-label">Precio Token</div>
                            <div class="stat-value highlight">${tokenPrice}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">APY/ROI</div>
                            <div class="stat-value">${apy}</div>
                        </div>
                    </div>

                    <div class="project-footer">
                        <span class="project-status status-${project.status}">
                            <i class="bi ${statusInfo.icon}"></i>
                            ${statusInfo.label}
                        </span>
                        <a href="/marketplace/project.php?slug=${project.slug}" 
                           class="btn-view-project" 
                           data-project-id="${project.id}">
                            Ver Más
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Manejar cambio de filtros
     */
    function handleFilterChange() {
        const category = document.getElementById('category-filter')?.value || '';
        const status = document.getElementById('status-filter')?.value || '';
        const sort = document.getElementById('sort-filter')?.value || 'default';

        MarketplaceState.currentCategory = category;
        MarketplaceState.currentStatus = status;
        MarketplaceState.currentSort = sort;

        applyFilters();
    }

    /**
     * Manejar cambio de búsqueda
     */
    function handleSearchChange(e) {
        MarketplaceState.currentSearch = e.target.value.toLowerCase();
        applyFilters();
    }

    /**
     * Aplicar filtros a los proyectos
     */
    function applyFilters() {
        let filtered = [...MarketplaceState.projects];

        // Filtrar por categoría
        if (MarketplaceState.currentCategory) {
            filtered = filtered.filter(p => p.category === MarketplaceState.currentCategory);
        }

        // Filtrar por estado
        if (MarketplaceState.currentStatus) {
            filtered = filtered.filter(p => p.status === MarketplaceState.currentStatus);
        }

        // Filtrar por búsqueda
        if (MarketplaceState.currentSearch) {
            filtered = filtered.filter(p => {
                const searchText = MarketplaceState.currentSearch;
                return p.name.toLowerCase().includes(searchText) ||
                       p.project_code.toLowerCase().includes(searchText) ||
                       (p.description && p.description.toLowerCase().includes(searchText));
            });
        }

        // Ordenar
        filtered = sortProjects(filtered, MarketplaceState.currentSort);

        MarketplaceState.filteredProjects = filtered;
        renderProjects(filtered);
    }

    /**
     * Ordenar proyectos
     */
    function sortProjects(projects, sortBy) {
        const sorted = [...projects];

        switch (sortBy) {
            case 'funding':
                return sorted.sort((a, b) => (b.funding_percentage || 0) - (a.funding_percentage || 0));
            case 'apy':
                return sorted.sort((a, b) => (b.apy_percentage || 0) - (a.apy_percentage || 0));
            case 'price_asc':
                return sorted.sort((a, b) => (a.token_price_usd || 0) - (b.token_price_usd || 0));
            case 'price_desc':
                return sorted.sort((a, b) => (b.token_price_usd || 0) - (a.token_price_usd || 0));
            case 'newest':
                return sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            default:
                return sorted.sort((a, b) => {
                    if (a.featured && !b.featured) return -1;
                    if (!a.featured && b.featured) return 1;
                    return (a.featured_order || 0) - (b.featured_order || 0);
                });
        }
    }

    /**
     * Seleccionar categoría
     */
    function selectCategory(category) {
        // Actualizar UI de categorías
        document.querySelectorAll('.category-card').forEach(card => {
            card.classList.remove('active');
        });

        const selectedCard = document.querySelector(`.category-card[data-category="${category}"]`);
        if (selectedCard) {
            selectedCard.classList.add('active');
        }

        // Actualizar filtro
        const categoryFilter = document.getElementById('category-filter');
        if (categoryFilter) {
            categoryFilter.value = category;
        }

        MarketplaceState.currentCategory = category;
        applyFilters();

        // Scroll a proyectos
        const projectsSection = document.getElementById('projects-section');
        if (projectsSection) {
            projectsSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Cambiar tab en vista detalle
     */
    function switchTab(tabId) {
        // Actualizar botones
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.tab-button[data-tab="${tabId}"]`)?.classList.add('active');

        // Actualizar contenido
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(tabId)?.classList.add('active');
    }

    /**
     * Registrar click-through al proyecto
     */
    function recordClickThrough(projectId) {
        fetch('/marketplace/api/record-analytics.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'click_through',
                project_id: projectId
            })
        }).catch(err => console.error('Error recording analytics:', err));
    }

    /**
     * Obtener información de categoría
     */
    function getCategoryInfo(category) {
        const categories = {
            'inmobiliario': { name: 'Inmobiliario', icon: 'bi-building', color: '#3498db' },
            'energia': { name: 'Energía', icon: 'bi-lightning-charge', color: '#f39c12' },
            'editorial': { name: 'Editorial', icon: 'bi-book', color: '#9b59b6' },
            'arte': { name: 'Arte', icon: 'bi-palette', color: '#e74c3c' },
            'musical': { name: 'Musical', icon: 'bi-music-note-beamed', color: '#1abc9c' },
            'cinematografia': { name: 'Cinematografía', icon: 'bi-film', color: '#34495e' },
            'deportivo': { name: 'Deportivo', icon: 'bi-trophy', color: '#27ae60' },
            'agropecuario': { name: 'Agropecuario', icon: 'bi-tree', color: '#16a085' },
            'industrial': { name: 'Industrial', icon: 'bi-gear', color: '#7f8c8d' },
            'tecnologia': { name: 'Tecnología', icon: 'bi-cpu', color: '#3498db' },
            'minero': { name: 'Minero', icon: 'bi-gem', color: '#95a5a6' },
            'farmaceutico': { name: 'Farmacéutico', icon: 'bi-capsule', color: '#e67e22' },
            'gubernamental': { name: 'Gubernamental', icon: 'bi-bank', color: '#2c3e50' },
            'otro': { name: 'Otro', icon: 'bi-grid', color: '#95a5a6' }
        };
        return categories[category] || categories['otro'];
    }

    /**
     * Obtener información de estado
     */
    function getStatusInfo(status) {
        const statuses = {
            'desarrollo': { label: 'En Desarrollo', icon: 'bi-gear' },
            'preventa': { label: 'Preventa', icon: 'bi-clock' },
            'activo': { label: 'Activo', icon: 'bi-check-circle' },
            'financiado': { label: 'Financiado', icon: 'bi-cash-stack' },
            'completado': { label: 'Completado', icon: 'bi-flag' },
            'pausado': { label: 'Pausado', icon: 'bi-pause-circle' },
            'cerrado': { label: 'Cerrado', icon: 'bi-x-circle' }
        };
        return statuses[status] || statuses['desarrollo'];
    }

    /**
     * Formatear moneda
     */
    function formatCurrency(amount) {
        if (!amount) return '$0.00';
        return '$' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 6
        });
    }

    /**
     * Formatear porcentaje
     */
    function formatPercentage(percentage) {
        if (!percentage) return '0%';
        return parseFloat(percentage).toFixed(2) + '%';
    }

    /**
     * Escapar HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Mostrar loading
     */
    function showLoading() {
        const grid = document.getElementById('projects-grid');
        if (grid) {
            grid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px;">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 20px; color: #666;">Cargando proyectos...</p>
                </div>
            `;
        }
    }

    /**
     * Ocultar loading
     */
    function hideLoading() {
        // El loading se oculta automáticamente al renderizar proyectos
    }

    /**
     * Mostrar error
     */
    function showError(message) {
        const grid = document.getElementById('projects-grid');
        if (grid) {
            grid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h3>Error</h3>
                    <p>${escapeHtml(message)}</p>
                </div>
            `;
        }
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMarketplace);
    } else {
        initMarketplace();
    }

    // Exponer funciones globales si es necesario
    window.Marketplace = {
        loadProjects,
        selectCategory,
        switchTab
    };

})();
