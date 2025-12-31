<?php
/**
 * Footer del Marketplace
 */
?>

<footer class="marketplace-footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Mizton</h3>
                <p>Tokenización de activos reales para democratizar la inversión.</p>
            </div>
            
            <div class="footer-section">
                <h4>Enlaces</h4>
                <ul>
                    <li><a href="/marketplace/">Proyectos</a></li>
                    <li><a href="/news/">Blog</a></li>
                    <li><a href="https://panel.mizton.cat/">Panel</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Legal</h4>
                <ul>
                    <li><a href="/privacy">Privacidad</a></li>
                    <li><a href="/terms">Términos</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Síguenos</h4>
                <div class="footer-social">
                    <a href="#" target="_blank"><i class="bi bi-twitter"></i></a>
                    <a href="#" target="_blank"><i class="bi bi-linkedin"></i></a>
                    <a href="#" target="_blank"><i class="bi bi-telegram"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Mizton. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<style>
.marketplace-footer {
    background: var(--text-dark);
    color: white;
    padding: 60px 0 20px;
    margin-top: 80px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h3,
.footer-section h4 {
    margin-bottom: 20px;
    color: white;
}

.footer-section p {
    color: rgba(255,255,255,0.7);
    line-height: 1.6;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section ul li a:hover {
    color: var(--primary-color);
}

.footer-social {
    display: flex;
    gap: 15px;
}

.footer-social a {
    color: rgba(255,255,255,0.7);
    font-size: 1.5rem;
    transition: color 0.3s ease;
}

.footer-social a:hover {
    color: var(--primary-color);
}

.footer-bottom {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.5);
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
</style>
