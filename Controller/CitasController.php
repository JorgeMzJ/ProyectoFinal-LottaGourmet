<?php
class CitasController {

    /**
     * Muestra el formulario de citas/pedidos personalizados
     * Este método es llamado cuando la URL es /citas
     */
    public function formulario() {
        require_once 'Config/Database.php';
        require_once 'Model/Producto.php';
        require_once 'Model/Paquete.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        $productoModel = new Producto($db);
        $paqueteModel = new Paquete($db);
        
        $productos = $productoModel->obtenerTodos();
        $paquetes = $paqueteModel->obtenerTodos();

        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/citas.php';
        require_once 'View/plantillas/footer.php';
    }

    /**
     * Guarda los datos del formulario en la BD (pedidos, detalle_pedidos)
     */
    public function guardar() {
        require_once 'Config/App.php';
        // Conexión a BD
        require_once 'Config/Database.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");

        // Recoger y validar datos básicos
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $tipoEvento = trim($_POST['tipoEvento'] ?? 'Pedido');
        // Si eligieron 'Otro' y especificaron texto, usar la especificación
        if ($tipoEvento === 'Otro' && !empty($_POST['tipoEventoOtro'])) {
            $tipoEvento = trim($_POST['tipoEventoOtro']);
        }
        $fechaEvento = $_POST['fechaEvento'] ?? null; // optional

        // Productos: arrays product_id[] and cantidad[]
        $product_ids = $_POST['product_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];

        $errors = [];
        if ($nombre === '') $errors[] = 'El nombre es requerido.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
        if (empty($product_ids) || !is_array($product_ids)) $errors[] = 'Debes seleccionar al menos un producto.';

        // Normalizar productos seleccionados: pares id => cantidad
        $items = [];
        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = isset($cantidades[$i]) ? (int)$cantidades[$i] : 0;
            if ($pid > 0 && $qty > 0) {
                $items[] = ['id' => $pid, 'cantidad' => $qty];
            }
        }

        if (empty($items)) $errors[] = 'Debes indicar cantidad (>0) para al menos un producto.';

        if (!empty($errors)) {
            $_SESSION['citas_errors'] = $errors;
            $_SESSION['citas_old'] = $_POST;
            header('Location: ' . BASE_URL . 'citas');
            exit;
        }

        try {
            // Iniciar transacción
            $db->beginTransaction();

            // Verificar que el usuario esté logueado
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('Debe iniciar sesión para realizar un pedido');
            }
            $id_usuario = $_SESSION['usuario_id'];

            // Insertar pedido directamente con el usuario logueado
            $pstmt = $db->prepare('INSERT INTO pedidos (id_usuario, tipoEvento, fechaEvento) VALUES (:id_usuario, :tipoEvento, :fechaEvento)');
            $pstmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $pstmt->bindValue(':tipoEvento', $tipoEvento);
            if ($fechaEvento === '') $fechaEvento = null;
            $pstmt->bindValue(':fechaEvento', $fechaEvento);
            $pstmt->execute();
            $id_pedido = $db->lastInsertId();

            // 3) Insertar detalle_pedidos consultando precio de productos
            $getPrice = $db->prepare('SELECT precio FROM productos WHERE id_producto = :id LIMIT 1');
            $insDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');

            foreach ($items as $it) {
                $getPrice->bindValue(':id', $it['id'], PDO::PARAM_INT);
                $getPrice->execute();
                $prod = $getPrice->fetch(PDO::FETCH_ASSOC);
                $precio = $prod ? $prod['precio'] : 0;

                $insDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $insDetalle->bindValue(':id_producto', $it['id'], PDO::PARAM_INT);
                $insDetalle->bindValue(':cantidad', $it['cantidad'], PDO::PARAM_INT);
                $insDetalle->bindValue(':precio_unitario', $precio);
                $insDetalle->execute();
            }

            $db->commit();

            $_SESSION['citas_success'] = 'Pedido guardado correctamente. Nos pondremos en contacto contigo.';
            unset($_SESSION['citas_errors'], $_SESSION['citas_old']);
            header('Location: ' . BASE_URL . 'citas?success=1');
            exit;

        } catch (PDOException $e) {
            $db->rollBack();
            $_SESSION['citas_errors'] = ['Error al guardar el pedido: ' . $e->getMessage()];
            $_SESSION['citas_old'] = $_POST;
            header('Location: ' . BASE_URL . 'citas');
            exit;
        }
    }

    /**
     * Procesa la selección de un paquete de evento
     * URL: /citas/paquete/{id_paquete}
     */
    public function paquete($id_paquete) {
        require_once 'Config/App.php';
        require_once 'Config/Database.php';
        require_once 'Model/Paquete.php';

        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");
        $paqueteModel = new Paquete($db);

        // Obtener información del paquete con sus productos
        $paquete = $paqueteModel->obtenerPorId($id_paquete);

        if (!$paquete) {
            $_SESSION['citas_errors'] = ['El paquete seleccionado no existe.'];
            header('Location: ' . BASE_URL . 'citas');
            exit;
        }

        // Capturar fecha del evento si viene por GET
        $fechaEvento = $_GET['fecha'] ?? '';
        
        // Guardar el paquete en sesión para mostrarlo en la confirmación
        $_SESSION['paquete_seleccionado'] = [
            'id_paquete' => $paquete['id_paquete'],
            'nombre' => $paquete['nombre'],
            'tipo_evento' => $paquete['tipo_evento'],
            'descripcion' => $paquete['descripcion'],
            'precio' => $paquete['precio'],
            'cantidad_postres' => $paquete['cantidad_postres'],
            'productos' => $paquete['productos'] ?? []
        ];
        
        // Guardar la fecha en old para pre-llenar el formulario
        if ($fechaEvento) {
            $_SESSION['citas_old']['fechaEvento'] = $fechaEvento;
        }

        // Mostrar página de formulario de datos
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/formulario_paquete.php';
        require_once 'View/plantillas/footer.php';
        exit;
    }

    /**
     * Muestra el formulario para completar datos del cliente con paquete preseleccionado
     * URL: /citas/formulario-paquete
     */
    public function formularioPaquete() {
        if (!isset($_SESSION['paquete_seleccionado'])) {
            header('Location: ' . BASE_URL . 'citas');
            exit;
        }

        $paquete = $_SESSION['paquete_seleccionado'];
        
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/formulario_paquete.php';
        require_once 'View/plantillas/footer.php';
    }

    /**
     * Guarda el pedido de un paquete en la base de datos
     * URL: /citas/guardar-paquete (POST)
     */
    public function guardarPaquete() {
        require_once 'Config/App.php';
        require_once 'Config/Database.php';

        if (!isset($_SESSION['paquete_seleccionado'])) {
            header('Location: ' . BASE_URL . 'citas');
            exit;
        }

        $paquete = $_SESSION['paquete_seleccionado'];
        $database = new Database();
        $db = $database->getConnection();
        $db->exec("SET NAMES 'utf8mb4'");

        // Obtener datos del usuario desde la sesión
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $nombre = $_SESSION['usuario_nombre'] ?? '';
        $email = $_SESSION['usuario_email'] ?? '';
        $telefono = $_SESSION['usuario_telefono'] ?? '';
        
        // Recoger fecha del evento y notas del formulario
        $fechaEvento = trim($_POST['fechaEvento'] ?? '');
        $notas = trim($_POST['notas'] ?? '');

        $errors = [];
        
        // Validar que haya usuario en sesión
        if (!$usuario_id) {
            $errors[] = 'Debes iniciar sesión para realizar un pedido.';
        }
        
        // Validar que la fecha del evento sea obligatoria y futura
        if ($fechaEvento === '') {
            $errors[] = 'La fecha del evento es obligatoria.';
        } else {
            $fechaEventoObj = DateTime::createFromFormat('Y-m-d', $fechaEvento);
            $hoy = new DateTime();
            $hoy->setTime(0, 0, 0);
            
            if (!$fechaEventoObj) {
                $errors[] = 'El formato de la fecha es inválido.';
            } elseif ($fechaEventoObj <= $hoy) {
                $errors[] = 'La fecha del evento debe ser al menos 1 día en el futuro.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['citas_errors'] = $errors;
            $_SESSION['citas_old'] = $_POST;
            header('Location: ' . BASE_URL . 'citas/formulario-paquete');
            exit;
        }

        try {
            $db->beginTransaction();

            // Insertar pedido directamente con el usuario logueado
            $pstmt = $db->prepare('INSERT INTO pedidos (id_usuario, tipoEvento, fechaEvento, notas, id_paquete) VALUES (:id_usuario, :tipoEvento, :fechaEvento, :notas, :id_paquete)');
            $pstmt->bindValue(':id_usuario', $usuario_id, PDO::PARAM_INT);
            $pstmt->bindValue(':tipoEvento', $paquete['tipo_evento']);
            $pstmt->bindValue(':fechaEvento', $fechaEvento);
            $pstmt->bindValue(':notas', $notas);
            $pstmt->bindValue(':id_paquete', $paquete['id_paquete'], PDO::PARAM_INT);
            $pstmt->execute();
            $id_pedido = $db->lastInsertId();

            // 3) Insertar detalles del paquete
            $insDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');
            
            // Obtener precio promedio por postre
            $precioPorPostre = $paquete['precio'] / $paquete['cantidad_postres'];
            
            foreach ($paquete['productos'] as $prod) {
                $insDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $insDetalle->bindValue(':id_producto', $prod['id_producto'] ?? 0, PDO::PARAM_INT);
                $insDetalle->bindValue(':cantidad', $prod['cantidad'], PDO::PARAM_INT);
                $insDetalle->bindValue(':precio_unitario', $precioPorPostre);
                $insDetalle->execute();
            }

            $db->commit();

            // Limpiar sesión
            $_SESSION['cliente_nombre'] = $nombre; // Guardar nombre para mostrar en confirmaciones futuras
            unset($_SESSION['paquete_seleccionado'], $_SESSION['citas_errors'], $_SESSION['citas_old']);
            $_SESSION['citas_success'] = 'Pedido de paquete guardado correctamente. Nos pondremos en contacto contigo.';
            
            header('Location: ' . BASE_URL . 'citas?success=1');
            exit;

        } catch (PDOException $e) {
            $db->rollBack();
            $_SESSION['citas_errors'] = ['Error al guardar el pedido: ' . $e->getMessage()];
            $_SESSION['citas_old'] = $_POST;
            header('Location: ' . BASE_URL . 'citas/formulario-paquete');
            exit;
        }
    }

    /**
     * Confirma y registra el pedido del paquete directamente
     * URL: /citas/confirmar-paquete (POST)
     */
    public function confirmarPaquete() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            require_once 'Config/Database.php';

            // Verificar que el usuario esté logueado
            if (!isset($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para realizar un pedido']);
                exit;
            }

            if (!isset($_SESSION['paquete_seleccionado'])) {
                echo json_encode(['success' => false, 'error' => 'No hay paquete seleccionado']);
                exit;
            }

            $paquete = $_SESSION['paquete_seleccionado'];
            $database = new Database();
            $db = $database->getConnection();

            // Obtener datos del usuario
            $usuario_id = $_SESSION['usuario_id'];
            $stmtUsuario = $db->prepare('SELECT nombre, email FROM usuarios WHERE id = :id LIMIT 1');
            $stmtUsuario->bindValue(':id', $usuario_id, PDO::PARAM_INT);
            $stmtUsuario->execute();
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
                exit;
            }

            $db->beginTransaction();

            // Usar directamente el usuario logueado
            $id_usuario = $usuario_id;

            // Insertar pedido
            $pstmt = $db->prepare('INSERT INTO pedidos (id_usuario, tipoEvento, id_paquete) VALUES (:id_usuario, :tipoEvento, :id_paquete)');
            $pstmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $pstmt->bindValue(':tipoEvento', $paquete['tipo_evento']);
            $pstmt->bindValue(':id_paquete', $paquete['id_paquete'], PDO::PARAM_INT);
            $pstmt->execute();
            $id_pedido = $db->lastInsertId();

            // 3) Insertar detalles del paquete
            $insDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');
            
            // Calcular precio promedio por postre
            $precioPorPostre = $paquete['precio'] / $paquete['cantidad_postres'];
            
            foreach ($paquete['productos'] as $prod) {
                $insDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $insDetalle->bindValue(':id_producto', $prod['id_producto'], PDO::PARAM_INT);
                $insDetalle->bindValue(':cantidad', $prod['cantidad'], PDO::PARAM_INT);
                $insDetalle->bindValue(':precio_unitario', $precioPorPostre);
                $insDetalle->execute();
            }

            $db->commit();

            // Limpiar sesión
            unset($_SESSION['paquete_seleccionado']);
            
            echo json_encode(['success' => true]);
            exit;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
?>