<?php
// No renderizar el sidebar cuando estemos en la ruta admin
$requestPath = $_SERVER['REQUEST_URI'];
if (strpos($requestPath, '/admin') !== false) {
    return;
}
?>
<aside class="sidebar">
    <!-- Logo de la tienda en la parte superior del sidebar -->
    <div class="sidebar-section sidebar-logo">
        <a href="<?php echo BASE_URL; ?>">
            <img src="<?php echo BASE_URL; ?>Public/img/logo.png" alt="Lotta Gourmet Logo" />
        </a>
    </div>
    
    <div class="sidebar-section sidebar-contact">
        <h4>Contacto</h4>
        <p>Tel: <a href="tel:+521234567890">(123) 456-7890</a><br>
        Email: <a href="mailto:info@lottagourmet.example">info@lottagourmet.example</a></p>
    </div>

    <div class="sidebar-section sidebar-social">
        <h4>Síguenos</h4>
        <p>
            <a href="https://1000logos.net/wp-content/uploads/2016/11/Facebook-Logo.png" aria-label="Facebook">Facebook</a> •
            <a href="https://1000logos.net/wp-content/uploads/2017/02/Instagram-Logo.png" aria-label="Instagram">Instagram</a>
        </p>
    </div>
</aside>