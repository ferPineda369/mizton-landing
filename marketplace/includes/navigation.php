<?php
/**
 * Navegación del Marketplace
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="marketplace-nav">
    <div class="nav-container">
        <a href="/" class="nav-logo">
            <img src="/logoB.gif" alt="Mizton" style="height: 40px;">
        </a>
        
        <div class="nav-links">
            <a href="/marketplace/" class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                Proyectos
            </a>
            <a href="/news/" class="nav-link">
                Blog
            </a>
            <a href="https://panel.mizton.cat/" class="nav-link nav-link-primary">
                Mi Panel
            </a>
        </div>
    </div>
</nav>

<style>
.marketplace-nav {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 15px 0;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-links {
    display: flex;
    gap: 30px;
    align-items: center;
}

.nav-link {
    color: var(--text-color);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.nav-link:hover,
.nav-link.active {
    color: var(--primary-color);
}

.nav-link-primary {
    background: var(--primary-color);
    color: white !important;
    padding: 10px 25px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.nav-link-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .nav-links {
        gap: 15px;
        font-size: 0.9rem;
    }
    
    .nav-link-primary {
        padding: 8px 20px;
    }
}
</style>
