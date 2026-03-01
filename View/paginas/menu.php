<?php
// View/paginas/menu.php
// Este archivo es "requerido" por ProductosController
// por lo que tiene acceso a la variable $listaProductos
?>

<main class="contenido-principal">
    <div class="menu-header">
        <div>
            <h2>Nuestro Menú</h2>
            <?php if (!empty($_GET['q'])): ?>
                <p>Resultados para: <strong><?php echo htmlspecialchars($_GET['q']); ?></strong></p>
            <?php else: ?>
                <p>Descubre todas nuestras delicias.</p>
            <?php endif; ?>
        </div>

        <div class="menu-search">
            <form action="<?php echo BASE_URL; ?>menu" method="get">
                <input type="text" name="q" placeholder="Buscar productos..." aria-label="Buscar" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button id="verCarritoBtn" type="button" class="btn-ver-carrito">Ver Carrito</button>
            </form>
        </div>
    </div>

    <div class="menu-contenedor">
        <?php
        // Verificamos si hay productos para mostrar
        if (isset($listaProductos) && count($listaProductos) > 0) {
            // Iteramos sobre los productos
            foreach ($listaProductos as $fila) {
                $id = $fila['id_producto'] ?? 0;
                $nombre = $fila['nombre'] ?? '';
                $descripcion = $fila['descripcion'] ?? '';
                $precio = $fila['precio'] ?? '';
                $imagen = $fila['imagen'] ?? 'placeholder.jpg';
                $en_promocion = $fila['en_promocion'] ?? 0;
                $precio_oferta = $fila['precio_oferta'] ?? null;
                $stock = $fila['stock'] ?? 0;
        ?>
                <div class="producto-tarjeta<?php echo ((int)$stock <= 0) ? ' agotado' : ''; ?>" data-id="<?php echo $id; ?>" data-nombre="<?php echo htmlspecialchars($nombre); ?>" data-precio="<?php echo htmlspecialchars($en_promocion ? ($precio_oferta ?? $precio) : $precio); ?>" data-stock="<?php echo (int)$stock; ?>"<?php if ($en_promocion && $precio_oferta !== null && $precio_oferta !== ''): ?> data-precio-original="<?php echo htmlspecialchars($precio); ?>"<?php endif; ?>>
                    <img src="<?php echo BASE_URL; ?>Public/img/<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars($nombre); ?>" style="width: 100%; height: 300px; object-fit: cover;">
                    <div class="producto-info">
                        <h3>
                            <?php echo htmlspecialchars($nombre); ?>
                            <?php if ($en_promocion && $precio_oferta !== null && $precio_oferta !== ''): ?>
                                <span class="badge-oferta">Oferta</span>
                            <?php endif; ?>
                        </h3>
                        <p><?php echo htmlspecialchars($descripcion); ?></p>
                    </div>

                    <div class="producto-footer">
                        <div class="precio-y-stock">
                        <span class="producto-precio">
                            <?php if ($en_promocion && $precio_oferta !== null && $precio_oferta !== ''): ?>
                                <span class="precio-oferta">$<?php echo htmlspecialchars($precio_oferta); ?></span>
                                <span class="precio-original">$<?php echo htmlspecialchars($precio); ?></span>
                            <?php else: ?>
                                $<?php echo htmlspecialchars($precio); ?>
                            <?php endif; ?>
                        </span>
                        <?php if ((int)$stock > 0 && (int)$stock <= 5): ?>
                            <div class="stock-label debajo-precio"><?php echo (string)"Por agotarse"; ?></div>
                        <?php endif; ?>
                        </div>
                        <div class="stock-row">
                                    <?php if ((int)$stock <= 0): ?>
                                        <span class="agotado-badge">Agotado</span>
                                    <?php else: ?>
                                        <a href="#" class="btn-pedir btn-pedir-grande">Agregar</a>
                                    <?php endif; ?>
                                </div>
                    </div>
                </div>

        <?php
            } // Fin del foreach
        } else {
            // Mensaje si no hay productos en la BD
            echo "<p class='no-productos'>Aún no tenemos productos en el menú. ¡Vuelve pronto!</p>";
        }
        ?>
    </div>
</main>
<script>window.BASE_URL = '<?php echo BASE_URL; ?>';</script>
<script src="<?php echo BASE_URL; ?>Public/js/menu-search.js?v=1.0"></script>