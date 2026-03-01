<style>
    /* Estilos para el carrusel */
    .carrusel-contenedor {
        position: relative; /* Necesario para posicionar las flechas */
        max-width: 1200px; /* Ancho máximo para el carrusel */
        height: 420px; /* Altura del carrusel */
        margin: 20px auto; /* Centra el carrusel y le da margen */
        overflow: hidden; /* Oculta las imágenes que están fuera de vista */
        border-radius: 8px;
    }

    .carrusel-slides {
        display: flex; /* Para que las imágenes se coloquen una al lado de la otra */
        transition: transform 0.5s ease-in-out; /* Animación para el deslizamiento */
    }

    .carrusel-slide {
        min-width: 100%; /* Cada slide ocupa el 100% del contenedor */
        box-sizing: border-box; /* Incluye padding y borde en el ancho */
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative; /* para ventanas flotantes sobre la imagen */
    }

    .carrusel-slide img {
        width: 100%; /* La imagen ocupa todo el ancho del slide */
        height: 420px; /* Altura fija para todas las imágenes */
        object-fit: cover; /* Asegura que la imagen cubra el área, recortando si es necesario */
        display: block; /* Elimina espacio extra debajo de la imagen */
    }

    /* Estilos para las flechas de navegación */
    .carrusel-btn {
        position: absolute; /* Posiciona las flechas sobre el carrusel */
        top: 50%;
        transform: translateY(-50%); /* Centra verticalmente */
        background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente */
        color: white;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        font-size: 1.5em;
        z-index: 10; /* Asegura que estén por encima de las imágenes */
        border-radius: 50%; /* Hace que los botones sean circulares */
        line-height: 1; /* Para centrar el icono de la flecha */
    }

    .carrusel-btn-prev {
        left: 10px;
    }

    .carrusel-btn-next {
        right: 10px;
    }

    .carrusel-btn:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    /* Ventana flotante por slide (oculta por defecto, aparece al hover) */
    .slide-info {
        position: absolute;
        left: 24px;
        bottom: 24px;
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 12px 16px;
        border-radius: 8px;
        max-width: 60%;
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 220ms ease, transform 220ms ease;
        z-index: 8;
    }
    .slide-info h4 { margin: 0 0 6px 0; font-size: 1.05em; }
    .slide-info p { margin: 0; font-size: 0.95em; color: rgba(255,255,255,0.95); }
    .carrusel-slide:hover .slide-info { opacity: 1; transform: translateY(0); }
</style>

<div class="carrusel-contenedor">
    <div class="carrusel-slides">
        <?php
        // Cargar imágenes de ofertas desde BD
        if (isset($carruselOfertas) && count($carruselOfertas) > 0) {
            foreach ($carruselOfertas as $oferta) {
        ?>
        <div class="carrusel-slide">
            <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($oferta['imagen'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($oferta['nombre']); ?>">
            <!-- Ventana flotante con info de la oferta -->
            <div class="slide-info" role="region" aria-label="Información de la imagen">
                <h4><?php echo htmlspecialchars($oferta['nombre'] ?? ''); ?></h4>
                <p><?php echo htmlspecialchars($oferta['descripcion'] ?? ''); ?></p>
            </div>
        </div>
        <?php
            }
        } else {
            // Fallback si no hay ofertas
        ?>
        <div class="carrusel-slide">
            <img src="<?php echo BASE_URL; ?>Public/img/carlota_fresa.jpg" alt="Carlota de Fresa">
            <div class="slide-info"><h4>Carlota de Fresa</h4><p>Deliciosa carlota con fresas frescas y crema.</p></div>
        </div>
        <div class="carrusel-slide">
            <img src="<?php echo BASE_URL; ?>Public/img/pastel_chocolate.jpg" alt="Pastel de Chocolate">
            <div class="slide-info"><h4>Pastel de Chocolate</h4><p>Clásico pastel de chocolate, ideal para celebraciones.</p></div>
        </div>
        <?php } ?>
    </div>

    <button class="carrusel-btn carrusel-btn-prev">&#10094;</button>
    <button class="carrusel-btn carrusel-btn-next">&#10095;</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const slides = document.querySelector('.carrusel-slides');
        const slideElements = document.querySelectorAll('.carrusel-slide');
        const prevBtn = document.querySelector('.carrusel-btn-prev');
        const nextBtn = document.querySelector('.carrusel-btn-next');
        let currentIndex = 0;
        const totalSlides = slideElements.length;

        function updateCarrusel() {
            const offset = -currentIndex * 100; // Mueve el contenedor de slides en porcentajes
            slides.style.transform = `translateX(${offset}%)`;
        }

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex === 0) ? totalSlides - 1 : currentIndex - 1;
            updateCarrusel();
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex === totalSlides - 1) ? 0 : currentIndex + 1;
            updateCarrusel();
        });

        // Opcional: Carrusel automático
        setInterval(() => {     
            currentIndex = (currentIndex === totalSlides - 1) ? 0 : currentIndex + 1;
            updateCarrusel();
        }, 5000); // Cambia cada 5 segundos
    });
</script>