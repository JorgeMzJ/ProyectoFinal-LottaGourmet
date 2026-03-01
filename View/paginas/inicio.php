<?php
// View/paginas/inicio.php
// Este archivo ahora tiene acceso a la variable $listaOfertas
?>

<main class="contenido-principal">
    <h1>Bienvenido a Lotta Gourmet</h1>
    
    <?php 
    // 2. Incluimos el carrusel que mencionaste
    include 'View/plantillas/carrusel.php'; 
    ?>

    <!-- Sección de Promociones (mostrar hasta 3) -->
    <?php if (isset($carruselOfertas) && count($carruselOfertas) > 0): ?>
    <h2 class="section-title">Promociones</h2>
    <div class="menu-contenedor">
        <?php
        $totalPromociones = count($carruselOfertas);
        $mostrar = array_slice($carruselOfertas, 0, 3);
        
        foreach ($mostrar as $oferta) {
            $id_producto = $oferta['id_producto'] ?? ($oferta['id'] ?? null);
            $nombre = $oferta['nombre'] ?? '';
            $descripcion = $oferta['descripcion'] ?? '';
            $precio = isset($oferta['precio']) ? (float)$oferta['precio'] : 0.0;
            $imagen = $oferta['imagen'] ?? 'placeholder.jpg';
            $precio_oferta = !empty($oferta['precio_oferta']) ? (float)$oferta['precio_oferta'] : round($precio * 0.80, 2);
        ?>
            <div class="producto-tarjeta" data-id="<?php echo $id_producto; ?>" data-nombre="<?php echo htmlspecialchars($nombre); ?>" data-precio="<?php echo htmlspecialchars(number_format($precio_oferta,2,'.','')); ?>" data-precio-original="<?php echo htmlspecialchars(number_format($precio,2,'.','')); ?>">
                <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars($nombre); ?>" style="width: 100%; height: 300px; object-fit: cover;">
                <div class="producto-info">
                    <h3><?php echo htmlspecialchars($nombre); ?></h3>
                    <p><?php echo htmlspecialchars($descripcion); ?></p>
                </div>
                <div class="producto-footer">
                    <div class="precio-wrap">
                        <span class="precio-original">$<?php echo number_format($precio,2); ?></span>
                        <span class="producto-precio">$<?php echo number_format($precio_oferta,2); ?></span>
                    </div>
                    <a href="#" class="btn-pedir">Agregar</a>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <?php if ($totalPromociones > 3): ?>
    <div style="text-align: center; margin: 20px 0 40px;">
        <a href="<?php echo BASE_URL; ?>promociones" class="btn-ver-mas">Ver todas las promociones (<?php echo $totalPromociones; ?>)</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <h2 class="section-title">Más Vendidos</h2>

    <div class="menu-contenedor">
        <?php
        if (isset($masVendidos) && count($masVendidos) > 0) {
            foreach ($masVendidos as $fila) {
                extract($fila);
        ?>
                <div class="producto-tarjeta">
                    <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($imagen ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($nombre); ?>" style="width: 100%; height: 300px; object-fit: cover;">
                    <div class="producto-info">
                        <h3><?php echo htmlspecialchars($nombre); ?></h3>
                        <p><?php echo htmlspecialchars($descripcion); ?></p>
                    </div>

                    <div class="producto-footer">
                        <span class="producto-precio">$<?php echo htmlspecialchars($precio); ?></span>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p class='no-productos'>No hay productos más vendidos marcados.</p>";
        }
        ?>
    </div>

    <h2>Otros productos</h2>

    <div class="menu-contenedor">
        <?php
        if (isset($otrosProductos) && count($otrosProductos) > 0) {
            foreach ($otrosProductos as $fila) {
                extract($fila);
        ?>
                <div class="producto-tarjeta">
                    <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($imagen ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($nombre); ?>" style="width: 100%; height: 300px; object-fit: cover;">
                    <div class="producto-info">
                        <h3><?php echo htmlspecialchars($nombre); ?></h3>
                        <p><?php echo htmlspecialchars($descripcion); ?></p>
                    </div>

                    <div class="producto-footer">
                        <span class="producto-precio">$<?php echo htmlspecialchars($precio); ?></span>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p class='no-productos'>No hay otros productos para mostrar.</p>";
        }
        ?>
    </div>
</main>