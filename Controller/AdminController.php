<?php
// Controller/AdminController.php
class AdminController {
    private function soloAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) {
            header('Location: ' . BASE_URL . 'menu');
            exit;
        }
    }

    public function panel() {
        $this->soloAdmin();
        // Cargar productos con bajo stock
        require_once 'Config/Database.php';
        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        $umbral = 3; // umbral de alerta
        $stmt = $db->prepare('SELECT id_producto, nombre, stock FROM productos WHERE stock <= :umbral ORDER BY stock ASC, nombre ASC');
        $stmt->bindValue(':umbral', $umbral, PDO::PARAM_INT);
        $stmt->execute();
        $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/admin_panel.php';
        require_once 'View/plantillas/footer.php';
    }

    public function compras() {
        $this->soloAdmin();
        require_once 'Config/Database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        
        // Obtener todas las compras normales
        $queryCompras = "SELECT 
                            c.id_compra as id,
                            'compra' as tipo_venta,
                            c.fecha, 
                            c.total, 
                            c.nombre_cliente, 
                            c.email_cliente,
                            NULL as tipoEvento,
                            NULL as fechaEvento,
                            NULL as notas,
                            0 as prioridad
                         FROM compras c";
        $stmtCompras = $db->prepare($queryCompras);
        $stmtCompras->execute();
        $comprasNormales = $stmtCompras->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener todos los pedidos especiales (eventos)
        $queryPedidos = "SELECT 
                            p.id_pedido as id,
                            'evento' as tipo_venta,
                            p.fecha,
                            0 as total,
                            u.nombre as nombre_cliente,
                            u.email as email_cliente,
                            p.tipoEvento,
                            p.fechaEvento,
                            p.notas,
                            CASE 
                                WHEN DATEDIFF(p.fechaEvento, CURDATE()) <= 3 THEN 3
                                WHEN DATEDIFF(p.fechaEvento, CURDATE()) <= 7 THEN 2
                                ELSE 1
                            END as prioridad
                         FROM pedidos p
                         JOIN usuarios u ON p.id_usuario = u.id";
        $stmtPedidos = $db->prepare($queryPedidos);
        $stmtPedidos->execute();
        $pedidosEspeciales = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular el total para pedidos especiales
        foreach ($pedidosEspeciales as &$pedido) {
            $queryTotal = "SELECT SUM(cantidad * precio_unitario) as total 
                          FROM detalle_pedidos 
                          WHERE id_pedido = :id_pedido";
            $stmtTotal = $db->prepare($queryTotal);
            $stmtTotal->bindValue(':id_pedido', $pedido['id'], PDO::PARAM_INT);
            $stmtTotal->execute();
            $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $pedido['total'] = $resultTotal['total'] ?? 0;
        }
        
        // Combinar compras y pedidos
        $todasLasVentas = array_merge($comprasNormales, $pedidosEspeciales);
        
        // Ordenar por prioridad (mayor a menor) y luego por fecha (más reciente primero)
        usort($todasLasVentas, function($a, $b) {
            if ($a['prioridad'] != $b['prioridad']) {
                return $b['prioridad'] - $a['prioridad']; // Mayor prioridad primero
            }
            return strtotime($b['fecha']) - strtotime($a['fecha']); // Más reciente primero
        });
        
        // Para cada venta, obtener los detalles (productos)
        foreach ($todasLasVentas as &$venta) {
            if ($venta['tipo_venta'] === 'compra') {
                $queryDetalles = "SELECT cd.cantidad, cd.precio_unitario, p.nombre 
                                 FROM compra_detalles cd 
                                 JOIN productos p ON cd.id_producto = p.id_producto 
                                 WHERE cd.id_compra = :id";
            } else {
                $queryDetalles = "SELECT dp.cantidad, dp.precio_unitario, p.nombre 
                                 FROM detalle_pedidos dp 
                                 JOIN productos p ON dp.id_producto = p.id_producto 
                                 WHERE dp.id_pedido = :id";
            }
            $stmtDetalles = $db->prepare($queryDetalles);
            $stmtDetalles->bindValue(':id', $venta['id'], PDO::PARAM_INT);
            $stmtDetalles->execute();
            $venta['detalles'] = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $compras = $todasLasVentas;
        
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/admin_compras.php';
        require_once 'View/plantillas/footer.php';
    }

    public function restock() {
        $this->soloAdmin();
        require_once 'Config/Database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        
        // Calcular ventas de los últimos 30 días por producto
        $query = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.stock,
                    COALESCE(SUM(cd.cantidad), 0) as total_vendido_compras,
                    COALESCE(SUM(dp.cantidad), 0) as total_vendido_pedidos,
                    (COALESCE(SUM(cd.cantidad), 0) + COALESCE(SUM(dp.cantidad), 0)) as total_vendido
                FROM productos p
                LEFT JOIN compra_detalles cd ON p.id_producto = cd.id_producto
                LEFT JOIN compras c ON cd.id_compra = c.id_compra AND c.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                LEFT JOIN detalle_pedidos dp ON p.id_producto = dp.id_producto
                LEFT JOIN pedidos pe ON dp.id_pedido = pe.id_pedido AND pe.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                WHERE (en_promocion = 0 OR en_promocion IS NULL)
                GROUP BY p.id_producto, p.nombre, p.stock
                ORDER BY total_vendido DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular métricas de restock
        foreach ($productos as &$producto) {
            $ventasPor30Dias = $producto['total_vendido'];
            $ventasPorSemana = $ventasPor30Dias / 4.29; // 30 días / 4.29 semanas
            $ventasPorDia = $ventasPor30Dias / 30;
            
            // Calcular días hasta agotar stock actual
            if ($ventasPorDia > 0) {
                $diasRestantes = $producto['stock'] / $ventasPorDia;
            } else {
                $diasRestantes = 999; // Stock suficiente si no hay ventas
            }
            
            // Sugerencia de restock: stock para 2 semanas basado en promedio
            $restockSugerido = ceil($ventasPorSemana * 2);
            
            // Si el stock actual es suficiente para más de 14 días, no sugerir restock
            if ($diasRestantes > 14) {
                $restockSugerido = 0;
            }
            
            $producto['ventas_semana'] = round($ventasPorSemana, 1);
            $producto['dias_restantes'] = round($diasRestantes, 1);
            $producto['restock_sugerido'] = $restockSugerido;
            $producto['nivel_urgencia'] = $diasRestantes < 7 ? 'alto' : ($diasRestantes < 14 ? 'medio' : 'bajo');
        }
        
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/admin_restock.php';
        require_once 'View/plantillas/footer.php';
    }

    public function graficas() {
        $this->soloAdmin();
        require_once 'Config/Database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        
        // Generar array con los últimos 7 días (incluyendo hoy)
        $ultimos7Dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $ultimos7Dias[$fecha] = 0; // Inicializar con 0 ventas
        }
        
        // Obtener ventas de los últimos 7 días (compras + pedidos)
        $query = "SELECT 
                    DATE(fecha) as dia,
                    SUM(total) as total_dia
                  FROM (
                    SELECT fecha, total FROM compras WHERE DATE(fecha) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    UNION ALL
                    SELECT p.fecha, COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) as total
                    FROM pedidos p
                    LEFT JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
                    WHERE DATE(p.fecha) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    GROUP BY p.id_pedido, p.fecha
                  ) AS ventas
                  GROUP BY DATE(fecha)
                  ORDER BY dia ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $ventasDiarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Llenar los días con ventas reales
        foreach ($ventasDiarias as $venta) {
            $ultimos7Dias[$venta['dia']] = floatval($venta['total_dia']);
        }
        
        // Calcular porcentajes de cambio día a día
        $datosGrafica = [];
        $ventaAnterior = null;
        
        foreach ($ultimos7Dias as $dia => $total) {
            $cambio = 0;
            $porcentajeCambio = 0;
            
            if ($ventaAnterior !== null && $ventaAnterior > 0) {
                $cambio = $total - $ventaAnterior;
                $porcentajeCambio = (($total - $ventaAnterior) / $ventaAnterior) * 100;
            }
            
            $datosGrafica[] = [
                'dia' => $dia,
                'dia_formato' => date('D d', strtotime($dia)),
                'total' => $total,
                'cambio' => $cambio,
                'porcentaje' => $porcentajeCambio
            ];
            
            $ventaAnterior = $total;
        }
        
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/admin_graficas.php';
        require_once 'View/plantillas/footer.php';
    }

    // Gestión de productos (CRUD)
    public function productos() {
        $this->soloAdmin();
        require_once 'Config/Database.php';
        require_once 'Model/Producto.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");

        // Asegurar columnas para promociones
        try {
            $db->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS en_promocion TINYINT(1) DEFAULT 0");
            $db->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS precio_oferta DECIMAL(10,2) NULL");
        } catch (Exception $e) {
            // algunos motores no reconocen ADD COLUMN IF NOT EXISTS; it's ok to ignore
        }

        $producto = new Producto($db);
        $productos = $producto->obtenerTodosAdmin();

        // Si hay petición para editar, obtener el producto
        $edit = null;
        if (!empty($_GET['edit_id'])) {
            $edit = $producto->obtenerPorId($_GET['edit_id']);
        }

        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/admin_productos.php';
        require_once 'View/plantillas/footer.php';
    }

    public function guardarProducto() {
        $this->soloAdmin();
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_once 'Config/Database.php';
        require_once 'Model/Producto.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        $producto = new Producto($db);

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $en_promocion = (isset($_POST['en_promocion']) && $_POST['en_promocion'] == '1') ? 1 : 0;
        $precio_oferta = $_POST['precio_oferta'] ?? '';
        
        // Si no está en promoción, limpiar el precio de oferta
        if (!$en_promocion) {
            $precio_oferta = null;
        }

        // Validación servidor
        $errors = [];
        if ($nombre === '') $errors[] = 'El nombre es requerido.';
        if ($precio === '' || !is_numeric($precio) || $precio <= 0) $errors[] = 'El precio debe ser un número mayor a 0.';
        if ($stock === '' || !is_numeric($stock) || (int)$stock < 0) $errors[] = 'El stock debe ser un número entero 0 o mayor.';
        if ($en_promocion && $precio_oferta !== '' && (!is_numeric($precio_oferta) || $precio_oferta <= 0)) $errors[] = 'El precio de oferta debe ser un número mayor a 0.';

        // Validar imagen si fue subida
        if (!empty($_FILES['imagen']['name'])) {
            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $maxBytes = 2 * 1024 * 1024; // 2MB
            if (!in_array($ext, $allowed)) $errors[] = 'Tipo de imagen no permitido. Usa jpg, png o gif.';
            if ($_FILES['imagen']['size'] > $maxBytes) $errors[] = 'La imagen es demasiado grande (máx 2MB).';
            // Verificar MIME y dimensiones reales
            $info = @getimagesize($_FILES['imagen']['tmp_name']);
            if ($info === false) {
                $errors[] = 'Archivo subido no es una imagen válida.';
            } else {
                $mime = $info['mime'] ?? '';
                $allowedMimes = ['image/jpeg','image/png','image/gif'];
                if (!in_array($mime, $allowedMimes)) $errors[] = 'Tipo de imagen no permitido (MIME).';
                $maxDim = 4000;
                if ($info[0] > $maxDim || $info[1] > $maxDim) $errors[] = 'Dimensiones de imagen demasiado grandes (máx 4000x4000).';
            }
        }

        if (!empty($errors)) {
            $_SESSION['producto_errors'] = $errors;
            $_SESSION['producto_old'] = [
                'nombre'=>$nombre,'descripcion'=>$descripcion,'precio'=>$precio,'stock'=>$stock,'en_promocion'=>$en_promocion,'precio_oferta'=>$precio_oferta
            ];
            header('Location: ' . BASE_URL . 'admin/productos');
            exit;
        }

        // Manejar imagen
        $imagenNombre = null;
        if (!empty($_FILES['imagen']['name'])) {
            $orig = $_FILES['imagen']['name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $imagenNombre = time() . '_' . $safeBase . '.' . $ext;
            $imgDir = realpath(__DIR__ . '/../Public/img') ?: dirname(__DIR__, 1) . '/Public/img';
            $dest = $imgDir . '/' . $imagenNombre;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                // fallback
                $dest = $imgDir . '/' . $imagenNombre;
                @move_uploaded_file($_FILES['imagen']['tmp_name'], $dest);
            }
        }

        $data = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'imagen' => $imagenNombre,
            'en_promocion' => $en_promocion,
            'precio_oferta' => $precio_oferta !== '' ? $precio_oferta : null
        ];

        $producto->crear($data);
        $_SESSION['producto_success'] = 'Producto agregado correctamente.';
        header('Location: ' . BASE_URL . 'admin/productos');
        exit;
    }

    public function actualizarProducto() {
        $this->soloAdmin();
        require_once 'Config/Database.php';
        require_once 'Model/Producto.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        $producto = new Producto($db);

        $id = (int)($_POST['id_producto'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/productos');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $en_promocion = (isset($_POST['en_promocion']) && $_POST['en_promocion'] == '1') ? 1 : 0;
        $precio_oferta = $_POST['precio_oferta'] ?? '';
        
        // Si no está en promoción, limpiar el precio de oferta
        if (!$en_promocion) {
            $precio_oferta = null;
        }

        // Validación servidor
        $errors = [];
        if ($nombre === '') $errors[] = 'El nombre es requerido.';
        if ($precio === '' || !is_numeric($precio) || $precio <= 0) $errors[] = 'El precio debe ser un número mayor a 0.';
        if ($stock === '' || !is_numeric($stock) || (int)$stock < 0) $errors[] = 'El stock debe ser un número entero 0 o mayor.';
        if ($en_promocion && $precio_oferta !== '' && (!is_numeric($precio_oferta) || $precio_oferta <= 0)) $errors[] = 'El precio de oferta debe ser un número mayor a 0.';

        if (!empty($_FILES['imagen']['name'])) {
            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $maxBytes = 2 * 1024 * 1024; // 2MB
            if (!in_array($ext, $allowed)) $errors[] = 'Tipo de imagen no permitido. Usa jpg, png o gif.';
            if ($_FILES['imagen']['size'] > $maxBytes) $errors[] = 'La imagen es demasiado grande (máx 2MB).';
            $info = @getimagesize($_FILES['imagen']['tmp_name']);
            if ($info === false) {
                $errors[] = 'Archivo subido no es una imagen válida.';
            } else {
                $mime = $info['mime'] ?? '';
                $allowedMimes = ['image/jpeg','image/png','image/gif'];
                if (!in_array($mime, $allowedMimes)) $errors[] = 'Tipo de imagen no permitido (MIME).';
                $maxDim = 4000;
                if ($info[0] > $maxDim || $info[1] > $maxDim) $errors[] = 'Dimensiones de imagen demasiado grandes (máx 4000x4000).';
            }
        }

        if (!empty($errors)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['producto_errors'] = $errors;
            $_SESSION['producto_old'] = [
                'nombre'=>$nombre,'descripcion'=>$descripcion,'precio'=>$precio,'stock'=>$stock,'en_promocion'=>$en_promocion,'precio_oferta'=>$precio_oferta
            ];
            header('Location: ' . BASE_URL . 'admin/productos?edit_id=' . $id);
            exit;
        }

        $imagenNombre = null;
        if (!empty($_FILES['imagen']['name'])) {
            $orig = $_FILES['imagen']['name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $imagenNombre = time() . '_' . $safeBase . '.' . $ext;
            $imgDir = realpath(__DIR__ . '/../Public/img') ?: dirname(__DIR__, 1) . '/Public/img';
            $dest = $imgDir . '/' . $imagenNombre;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $dest = $imgDir . '/' . $imagenNombre;
                @move_uploaded_file($_FILES['imagen']['tmp_name'], $dest);
            }
            // eliminar imagen antigua si existe
            $old = $producto->obtenerPorId($id);
            if (!empty($old['imagen'])) {
                $oldPath = $imgDir . '/' . $old['imagen'];
                if (file_exists($oldPath)) @unlink($oldPath);
            }
        }

        $data = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'en_promocion' => $en_promocion,
            'precio_oferta' => $precio_oferta !== '' ? $precio_oferta : null,
            'imagen' => $imagenNombre
        ];

        $producto->actualizar($id, $data);
        header('Location: ' . BASE_URL . 'admin/productos');
        exit;
    }

    public function eliminarProducto() {
        $this->soloAdmin();
        require_once 'Config/Database.php';
        require_once 'Model/Producto.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        $producto = new Producto($db);

        $id = (int)($_POST['id_producto'] ?? 0);
        if ($id > 0) {
            // opcional: eliminar imagen física
            $p = $producto->obtenerPorId($id);
            if (!empty($p['imagen'])) {
                $path1 = __DIR__ . '/../Public/img/' . $p['imagen'];
                $path2 = dirname(__DIR__, 1) . '/Public/img/' . $p['imagen'];
                if (file_exists($path1)) @unlink($path1);
                if (file_exists($path2)) @unlink($path2);
            }
            $producto->eliminar($id);
        }
        header('Location: ' . BASE_URL . 'admin/productos');
        exit;
    }

    public function bulkRestock() {
        $this->soloAdmin();
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        require_once 'Config/Database.php';
        
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        
        if ($cantidad <= 0) {
            $_SESSION['producto_errors'] = ['La cantidad debe ser mayor a 0'];
            header('Location: ' . BASE_URL . 'admin/productos');
            exit;
        }
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            $db->exec("SET NAMES 'utf8mb4'");
            
            // Actualizar stock de todos los productos
            $query = "UPDATE productos SET stock = stock + :cantidad";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->execute();
            
            $affectedRows = $stmt->rowCount();
            $_SESSION['producto_success'] = "Se agregaron {$cantidad} unidades al stock de {$affectedRows} productos.";
        } catch (Exception $e) {
            error_log("Error en bulkRestock: " . $e->getMessage());
            $_SESSION['producto_errors'] = ['Error al actualizar el stock. Intenta nuevamente.'];
        }
        
        header('Location: ' . BASE_URL . 'admin/productos');
        exit;
    }
}
