    </div><!-- Cierre de main-container -->
    
    <footer class="footer-principal">
        <p>© <?php echo date('Y'); ?> Lotta Gourmet. Todos los derechos reservados.</p>
    </footer>

    <!-- Modal del Carrito (global) -->
    <div id="carritoModal" class="modal">
        <div class="modal-contenido">
            <span class="cerrar">&times;</span>
            <h2>Carrito de Compras</h2>
            <div id="carritoContenedor">
                <!-- Los productos del carrito se agregarán aquí -->
            </div>
            <p>Total: $<span id="carritoTotal">0.00</span></p>
            <button id="comprarBtn" class="btn-comprar">Comprar</button>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>Public/js/script.js?v=2.0"></script>
    <script src="<?php echo BASE_URL; ?>Public/js/carrito.js?v=1.0"></script>

</body>
</html>

<style>
    .footer-principal {
        width: 100%;
        background-color: #333;
        color: #f4f4f4;
        padding: 25px 0;
        text-align: center;
        margin-top: auto;
    }

    .footer-principal p {
        margin: 0;
        font-size: 0.9em;
    }
</style>