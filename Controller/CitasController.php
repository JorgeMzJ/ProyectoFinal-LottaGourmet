<?php
class CitasController
{

    /**
     * Muestra el formulario de citas/pedidos personalizados
     * Este método es llamado cuando la URL es /citas
     */
    public function formulario()
    {
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
    public function guardar()
    {
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
        if ($nombre === '')
            $errors[] = 'El nombre es requerido.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Email inválido.';
        if (empty($product_ids) || !is_array($product_ids))
            $errors[] = 'Debes seleccionar al menos un producto.';

        // Normalizar productos seleccionados: pares id => cantidad
        $items = [];
        $totalItems = 0;
        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int) $product_ids[$i];
            $qty = isset($cantidades[$i]) ? (int) $cantidades[$i] : 0;

            if ($pid > 0 && $qty > 0) {
                if ($qty > 25) {
                    $errors[] = 'Alcanzaste el límite de 25 unidades de un mismo producto.';
                    break;
                }
                $items[] = ['id' => $pid, 'cantidad' => $qty];
                $totalItems += $qty;
            }
        }

        if ($totalItems > 100) {
            $errors[] = 'El pedido excede el límite de 100 productos en total.';
        }

        if (empty($items))
            $errors[] = 'Debes indicar cantidad (>0) para al menos un producto.';

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

            // Calcular multiplicador de descuento basado en el volumen total
            $descuentoMultiplier = 1.0;
            $notaDescuento = '';

            if ($totalItems >= 24) {
                $descuentoMultiplier = 0.90; // 10% de descuento
                $notaDescuento = 'Aplicado 10% de descuento automático por volumen (>= 24 unidades).';
            } elseif ($totalItems >= 12) {
                $descuentoMultiplier = 0.95; // 5% de descuento
                $notaDescuento = 'Aplicado 5% de descuento automático por volumen (>= 12 unidades).';
            }

            // Insertar pedido directamente con el usuario logueado
            $pstmt = $db->prepare('INSERT INTO pedidos (id_usuario, tipoEvento, fechaEvento, notas) VALUES (:id_usuario, :tipoEvento, :fechaEvento, :notas)');
            $pstmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $pstmt->bindValue(':tipoEvento', $tipoEvento);
            if ($fechaEvento === '')
                $fechaEvento = null;
            $pstmt->bindValue(':fechaEvento', $fechaEvento);
            $pstmt->bindValue(':notas', $notaDescuento);
            $pstmt->execute();
            $id_pedido = $db->lastInsertId();

            // 3) Insertar detalle_pedidos consultando precio de productos
            $getPrice = $db->prepare('SELECT precio FROM productos WHERE id_producto = :id LIMIT 1');
            $insDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');

            foreach ($items as $it) {
                $getPrice->bindValue(':id', $it['id'], PDO::PARAM_INT);
                $getPrice->execute();
                $prod = $getPrice->fetch(PDO::FETCH_ASSOC);

                $precioOriginal = $prod ? (float) $prod['precio'] : 0;
                $precioConDescuento = $precioOriginal * $descuentoMultiplier;

                $insDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $insDetalle->bindValue(':id_producto', $it['id'], PDO::PARAM_INT);
                $insDetalle->bindValue(':cantidad', $it['cantidad'], PDO::PARAM_INT);
                $insDetalle->bindValue(':precio_unitario', $precioConDescuento);
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

    private function calcularTotalCotizacion(array $data): array
    {
        $precios_pan = ["Vainilla" => 100, "Chocolate" => 120, "Zanahoria" => 150];
        $precios_relleno = ["Fresa" => 30, "Nutella" => 50, "Cajeta" => 40];
        $precios_cobertura = ["Crema" => 40, "Fondant" => 150, "Chocolate" => 60];
        $precio_base_persona = 25;
        $descuento_por_ingrediente = 15;

        $personas = isset($data['personas']) ? (int) $data['personas'] : 0;
        $costo_tamanio = max(0, $personas) * $precio_base_persona;
        $costo_pan = isset($precios_pan[$data['pan'] ?? '']) ? $precios_pan[$data['pan']] : 0;
        $costo_relleno = isset($precios_relleno[$data['relleno'] ?? '']) ? $precios_relleno[$data['relleno']] : 0;
        $costo_cobertura = isset($precios_cobertura[$data['cobertura'] ?? '']) ? $precios_cobertura[$data['cobertura']] : 0;

        $total = $costo_tamanio + $costo_pan + $costo_relleno + $costo_cobertura;
        $ingredientes_eliminados = isset($data['eliminar_ingrediente']) && is_array($data['eliminar_ingrediente']) ? $data['eliminar_ingrediente'] : [];
        $descuento_total = count($ingredientes_eliminados) * $descuento_por_ingrediente;
        $total = max(0, $total - $descuento_total);

        return [
            'total' => $total,
            'detalle' => [
                'personas' => $personas,
                'pan' => $data['pan'] ?? 'N/A',
                'relleno' => $data['relleno'] ?? 'N/A',
                'cobertura' => $data['cobertura'] ?? 'N/A',
                'descuento_aplicado' => $descuento_total,
                'moneda' => 'MXN'
            ]
        ];
    }

    private function buildAbsoluteUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        return $scheme . '://' . $host . ($base ? $base . '/' : '') . $path;
    }

    private function ensureStripeQuoteSessionStore(): void
    {
        if (!isset($_SESSION['stripe_quote_sessions']) || !is_array($_SESSION['stripe_quote_sessions'])) {
            $_SESSION['stripe_quote_sessions'] = [];
        }
        if (!isset($_SESSION['stripe_quote_saved']) || !is_array($_SESSION['stripe_quote_saved'])) {
            $_SESSION['stripe_quote_saved'] = [];
        }
    }

    private function storeStripeQuoteSessionData(string $stripeSessionId, array $payload, array $cotizacion, string $description): void
    {
        $this->ensureStripeQuoteSessionStore();
        $_SESSION['stripe_quote_sessions'][$stripeSessionId] = [
            'user_id' => $_SESSION['usuario_id'],
            'user_name' => $_SESSION['usuario_nombre'] ?? '',
            'user_email' => $_SESSION['usuario_email'] ?? '',
            'payload' => $payload,
            'total' => $cotizacion['total'],
            'description' => $description,
            'type' => $payload['type'] ?? 'cotizacion',
            'metadata' => [
                'personas' => $cotizacion['detalle']['personas'] ?? 0,
                'pan' => $cotizacion['detalle']['pan'] ?? '',
                'relleno' => $cotizacion['detalle']['relleno'] ?? '',
                'cobertura' => $cotizacion['detalle']['cobertura'] ?? '',
                'descuento_aplicado' => $cotizacion['detalle']['descuento_aplicado'] ?? 0,
                'correo' => $_SESSION['usuario_email'] ?? '',
                'usuario_nombre' => $_SESSION['usuario_nombre'] ?? '',
            ],
        ];
    }

    private function getStripeQuoteSessionData(string $stripeSessionId): ?array
    {
        $this->ensureStripeQuoteSessionStore();
        return $_SESSION['stripe_quote_sessions'][$stripeSessionId] ?? null;
    }

    private function markStripeQuoteSaved(string $stripeSessionId, int $compraId): void
    {
        $this->ensureStripeQuoteSessionStore();
        $_SESSION['stripe_quote_saved'][$stripeSessionId] = $compraId;
    }

    private function getSavedStripeQuotePurchaseId(string $stripeSessionId): ?int
    {
        $this->ensureStripeQuoteSessionStore();
        return isset($_SESSION['stripe_quote_saved'][$stripeSessionId]) ? (int) $_SESSION['stripe_quote_saved'][$stripeSessionId] : null;
    }

    private function findOrCreateCustomProductId(PDO $db): int
    {
        $productName = 'Pedido personalizado';
        $stmt = $db->prepare('SELECT id_producto FROM productos WHERE nombre = :nombre LIMIT 1');
        $stmt->bindValue(':nombre', $productName);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['id_producto'])) {
            return (int) $row['id_producto'];
        }

        $insert = $db->prepare('INSERT INTO productos (nombre, descripcion, precio, stock, imagen, en_promocion, precio_oferta) VALUES (:nombre, :descripcion, :precio, :stock, :imagen, :en_promocion, :precio_oferta)');
        $insert->bindValue(':nombre', $productName);
        $insert->bindValue(':descripcion', 'Registro de pedido personalizado generado desde Stripe Checkout.');
        $insert->bindValue(':precio', 0);
        $insert->bindValue(':stock', 0, PDO::PARAM_INT);
        $insert->bindValue(':imagen', null);
        $insert->bindValue(':en_promocion', 0, PDO::PARAM_INT);
        $insert->bindValue(':precio_oferta', null);
        $insert->execute();
        return (int) $db->lastInsertId();
    }

    private function saveQuotePurchaseToDatabase(PDO $db, array $quoteSessionData): int
    {
        $db->beginTransaction();

        $id_usuario = $quoteSessionData['user_id'] ?? ($_SESSION['usuario_id'] ?? 3);
        $payload = $quoteSessionData['payload'] ?? [];
        $total = (float) $quoteSessionData['total'];

        $pan = $payload['pan'] ?? 'N/A';
        $relleno = $payload['relleno'] ?? 'N/A';
        $cobertura = $payload['cobertura'] ?? 'N/A';
        $ingredientes = (isset($payload['eliminar_ingrediente']) && !empty($payload['eliminar_ingrediente']))
            ? implode(", ", $payload['eliminar_ingrediente'])
            : "Ninguno";
        $notas = $payload['notas'] ?? "";
        $fecha_entrega = $payload['fecha'] ?? null;

        $stmt = $db->prepare('INSERT INTO pedidos (id_usuario, tipoEvento, fechaEvento, notas, es_personalizado, pan_personalizado, relleno_personalizado, cobertura_personalizada, ingredientes_omitidos, total_cotizado) VALUES (:id_usuario, \'Personalizado\', :fechaEvento, :notas, 1, :pan, :relleno, :cobertura, :ingredientes, :total)');
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindValue(':fechaEvento', $fecha_entrega);
        $stmt->bindValue(':notas', $notas);
        $stmt->bindValue(':pan', $pan);
        $stmt->bindValue(':relleno', $relleno);
        $stmt->bindValue(':cobertura', $cobertura);
        $stmt->bindValue(':ingredientes', $ingredientes);
        $stmt->bindValue(':total', $total);
        $stmt->execute();
        $id_pedido = (int) $db->lastInsertId();

        $productoId = $this->findOrCreateCustomProductId($db);
        $stmtDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');
        $stmtDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmtDetalle->bindValue(':id_producto', $productoId, PDO::PARAM_INT);
        $stmtDetalle->bindValue(':cantidad', 1, PDO::PARAM_INT);
        $stmtDetalle->bindValue(':precio_unitario', $total);
        $stmtDetalle->execute();

        $db->commit();

        return $id_pedido;
    }

    private function savePackagePurchaseToDatabase(PDO $db, array $packageSessionData): int
    {
        $db->beginTransaction();

        $nombre = trim($packageSessionData['user_name'] ?? '') ?: 'Cliente paquete';
        $email = trim($packageSessionData['user_email'] ?? '');
        $total = (float) $packageSessionData['total'];
        $payload = $packageSessionData['payload'] ?? [];

        $tipoEvento = trim($payload['paquete_nombre'] ?? $payload['paquete_id'] ?? 'Paquete predefinido');
        $fechaEvento = trim($payload['fecha'] ?? null);
        $notas = 'Paquete predefinido: ' . ($payload['paquete_nombre'] ?? '');
        if (!empty($payload['paquete_desc'])) {
            $notas .= ' - ' . $payload['paquete_desc'];
        }
        if (!empty($payload['personas'])) {
            $notas .= ' | Personas: ' . $payload['personas'];
        }
        if (!empty($payload['fecha'])) {
            $notas .= ' | Fecha: ' . $payload['fecha'];
        }

        $stmt = $db->prepare('INSERT INTO pedidos (id_usuario, tipoEvento, fechaEvento, notas, id_paquete) VALUES (:id_usuario, :tipoEvento, :fechaEvento, :notas, :id_paquete)');
        $stmt->bindValue(':id_usuario', $_SESSION['usuario_id'], PDO::PARAM_INT);
        $stmt->bindValue(':tipoEvento', $tipoEvento);
        $stmt->bindValue(':fechaEvento', $fechaEvento ?: null);
        $stmt->bindValue(':notas', $notas);
        if (isset($payload['paquete_id']) && is_numeric($payload['paquete_id'])) {
            $stmt->bindValue(':id_paquete', (int) $payload['paquete_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':id_paquete', null, PDO::PARAM_NULL);
        }
        $stmt->execute();
        $id_pedido = (int) $db->lastInsertId();

        $productoId = $this->findOrCreateCustomProductId($db);
        $stmtDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');
        $stmtDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmtDetalle->bindValue(':id_producto', $productoId, PDO::PARAM_INT);
        $stmtDetalle->bindValue(':cantidad', 1, PDO::PARAM_INT);
        $stmtDetalle->bindValue(':precio_unitario', $total);
        $stmtDetalle->execute();

        $db->commit();

        return $id_pedido;
    }

    /**
     * Guarda el pedido PERSONALIZADO proveniente de la API (Cotizador)
     */
    public function guardarPersonalizado()
    {
        // 1. Cabeceras para respuesta JSON
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
            return;
        }

        // 2. Leer los datos JSON enviados por el fetch
        $data = json_decode(file_get_contents("php://input"));

        // Validación básica
        if (!isset($data->fecha_entrega) || !isset($data->pan)) {
            echo json_encode(["exito" => false, "mensaje" => "Faltan datos requeridos."]);
            return;
        }

        // 3. Iniciar sesión y preparar variables
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id_usuario = $_SESSION['usuario_id'] ?? 3;

        $pan = $data->pan;
        $relleno = $data->relleno;
        $cobertura = $data->cobertura;
        $ingredientes = (isset($data->eliminar_ingrediente) && !empty($data->eliminar_ingrediente))
            ? implode(", ", $data->eliminar_ingrediente)
            : "Ninguno";
        $notas = $data->notas ?? "";
        $fecha_entrega = $data->fecha_entrega;
        $total = $data->total_cotizado;

        try {
            // 4. Conexión a BD 
            require_once 'Config/Database.php';
            $database = new Database();
            $db = $database->getConnection();
            $db->exec("SET NAMES 'utf8mb4'");

            // 5. Query de Inserción
            $query = "INSERT INTO pedidos 
                      (id_usuario, tipoEvento, fechaEvento, notas, es_personalizado, pan_personalizado, relleno_personalizado, cobertura_personalizada, ingredientes_omitidos, total_cotizado) 
                      VALUES (:id_usuario, 'Personalizado', :fechaEvento, :notas, 1, :pan, :relleno, :cobertura, :ingredientes, :total)";

            $stmt = $db->prepare($query);

            // Bind de valores PDO
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':fechaEvento', $fecha_entrega);
            $stmt->bindValue(':notas', $notas);
            $stmt->bindValue(':pan', $pan);
            $stmt->bindValue(':relleno', $relleno);
            $stmt->bindValue(':cobertura', $cobertura);
            $stmt->bindValue(':ingredientes', $ingredientes);
            $stmt->bindValue(':total', $total);

            // 6. Ejecutar y responder
            if ($stmt->execute()) {
                $id_pedido = $db->lastInsertId();
                echo json_encode([
                    "exito" => true,
                    "mensaje" => "Pedido guardado con éxito.",
                    "id_pedido" => $id_pedido
                ]);
            } else {
                echo json_encode(["exito" => false, "mensaje" => "Error al guardar en la base de datos."]);
            }

        } catch (Exception $e) {
            echo json_encode(["exito" => false, "mensaje" => "Excepción: " . $e->getMessage()]);
        }
    }

    public function crearStripeSession()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido. Usa POST.']);
            return;
        }

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para continuar con el pago.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['personas'], $data['pan'], $data['relleno'], $data['cobertura'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos de cotización incompletos.']);
            return;
        }

        $cotizacion = $this->calcularTotalCotizacion($data);
        if ($cotizacion['total'] <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'El total de la cotización debe ser mayor a cero.']);
            return;
        }

        $description = sprintf('Cotización pastel personalizado para %d personas', $cotizacion['detalle']['personas']);
        $body = [
            'payment_method_types[]' => 'card',
            'mode' => 'payment',
            'line_items[0][price_data][currency]' => 'mxn',
            'line_items[0][price_data][product_data][name]' => 'Cotización pastel personalizado',
            'line_items[0][price_data][product_data][description]' => $description,
            'line_items[0][price_data][unit_amount]' => (int) round($cotizacion['total'] * 100),
            'line_items[0][quantity]' => 1,
            'success_url' => $this->buildAbsoluteUrl('citas/stripe-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => $this->buildAbsoluteUrl('citas?stripe=cancel'),
            'metadata[usuario_id]' => $_SESSION['usuario_id'],
            'metadata[tipo_compra]' => 'cotizacion',
            'metadata[usuario_nombre]' => $_SESSION['usuario_nombre'] ?? '',
            'metadata[personas]' => $cotizacion['detalle']['personas'],
            'metadata[pan]' => $cotizacion['detalle']['pan'],
            'metadata[relleno]' => $cotizacion['detalle']['relleno'],
            'metadata[cobertura]' => $cotizacion['detalle']['cobertura'],
            'metadata[descuento_aplicado]' => $cotizacion['detalle']['descuento_aplicado'],
            'metadata[correo]' => $_SESSION['usuario_email'] ?? ''
        ];

        if (!empty($data['notas'])) {
            $body['metadata[notas]'] = $data['notas'];
        }
        if (!empty($data['fecha'])) {
            $body['metadata[fecha]'] = $data['fecha'];
        }

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . trim(STRIPE_SECRET_KEY)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            http_response_code(502);
            echo json_encode(['success' => false, 'error' => 'Error de conexión con Stripe: ' . $error]);
            return;
        }

        $stripeResponse = json_decode($response, true);
        if (isset($stripeResponse['id'])) {
            $this->storeStripeQuoteSessionData($stripeResponse['id'], $data, $cotizacion, $description);
            echo json_encode(['success' => true, 'id' => $stripeResponse['id']]);
            return;
        }

        $mensaje = $stripeResponse['error']['message'] ?? 'Error al crear la sesión de pago en Stripe.';
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $mensaje]);
    }

    public function crearStripeSessionPaquete()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido. Usa POST.']);
            return;
        }

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para continuar con el pago.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['paquete_id'], $data['paquete_nombre'], $data['paquete_precio'], $data['personas'], $data['fecha'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos del paquete incompletos.']);
            return;
        }

        $precioBase = (float) $data['paquete_precio'];
        $personas = max(0, (int) $data['personas']);
        $total = $precioBase;
        if ($personas > 20) {
            $total += ($personas - 20) * 20;
        }

        if ($total <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'El total del paquete debe ser mayor a cero.']);
            return;
        }

        $description = sprintf('Paquete predefinido %s para %d personas', $data['paquete_nombre'], $personas);
        $body = [
            'payment_method_types[]' => 'card',
            'mode' => 'payment',
            'line_items[0][price_data][currency]' => 'mxn',
            'line_items[0][price_data][product_data][name]' => $data['paquete_nombre'],
            'line_items[0][price_data][product_data][description]' => $data['paquete_desc'] ?? $description,
            'line_items[0][price_data][unit_amount]' => (int) round($total * 100),
            'line_items[0][quantity]' => 1,
            'success_url' => $this->buildAbsoluteUrl('citas/stripe-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => $this->buildAbsoluteUrl('citas?stripe=cancel'),
            'metadata[usuario_id]' => $_SESSION['usuario_id'],
            'metadata[tipo_compra]' => 'paquete',
            'metadata[usuario_nombre]' => $_SESSION['usuario_nombre'] ?? '',
            'metadata[correo]' => $_SESSION['usuario_email'] ?? '',
            'metadata[paquete_id]' => $data['paquete_id'],
            'metadata[paquete_nombre]' => $data['paquete_nombre'],
            'metadata[paquete_desc]' => $data['paquete_desc'] ?? '',
            'metadata[paquete_precio]' => $precioBase,
            'metadata[personas]' => $personas,
            'metadata[fecha]' => $data['fecha'] ?? '',
        ];

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . trim(STRIPE_SECRET_KEY)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            http_response_code(502);
            echo json_encode(['success' => false, 'error' => 'Error de conexión con Stripe: ' . $error]);
            return;
        }

        $stripeResponse = json_decode($response, true);
        if (isset($stripeResponse['id'])) {
            $this->storeStripeQuoteSessionData($stripeResponse['id'], $data, ['total' => $total, 'detalle' => ['personas' => $personas]], $description);
            echo json_encode(['success' => true, 'id' => $stripeResponse['id']]);
            return;
        }

        $mensaje = $stripeResponse['error']['message'] ?? 'Error al crear la sesión de pago en Stripe.';
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $mensaje]);
    }

    public function stripeSuccess($session_id = null)
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        if (!$session_id) {
            $session_id = $_GET['session_id'] ?? null;
        }

        if (!$session_id) {
            $_SESSION['stripe_error'] = 'No se recibió el identificador de sesión de pago.';
            header('Location: ' . BASE_URL . 'citas');
            exit;
        }

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($session_id));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . trim(STRIPE_SECRET_KEY)
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $savedPurchaseId = null;
        if ($error) {
            $sessionDetails = null;
            $errorMessage = 'No se pudo verificar el pago en Stripe: ' . $error;
        } else {
            $sessionDetails = json_decode($response, true);
            $errorMessage = null;
            if (!isset($sessionDetails['payment_status']) || $sessionDetails['payment_status'] !== 'paid') {
                $errorMessage = 'El pago no se completó correctamente. Si ya realizaste el pago, espera unos minutos y revisa tu correo.';
            } else {
                // Guardar el pedido en la base de datos solo si el pago se completó.
                $quoteData = $this->getStripeQuoteSessionData($session_id);
                $savedPurchaseId = $this->getSavedStripeQuotePurchaseId($session_id);

                if (!$savedPurchaseId) {
                    try {
                        require_once 'Config/Database.php';
                        $database = new Database();
                        $db = $database->getConnection();
                        $db->exec("SET NAMES 'utf8mb4'");

                        if (!$quoteData) {
                            // Si no hay datos de sesión, intentar reconstruirlos desde los metadatos de Stripe.
                            $quoteData = [
                                'user_name' => $sessionDetails['metadata']['usuario_nombre'] ?? 'Cliente personalizado',
                                'user_email' => $sessionDetails['metadata']['correo'] ?? '',
                                'total' => isset($sessionDetails['amount_total']) ? ($sessionDetails['amount_total'] / 100) : 0,
                                'payload' => [
                                    'type' => $sessionDetails['metadata']['tipo_compra'] ?? 'cotizacion',
                                    'personas' => $sessionDetails['metadata']['personas'] ?? 0,
                                    'pan' => $sessionDetails['metadata']['pan'] ?? 'N/A',
                                    'relleno' => $sessionDetails['metadata']['relleno'] ?? 'N/A',
                                    'cobertura' => $sessionDetails['metadata']['cobertura'] ?? 'N/A',
                                    'notas' => $sessionDetails['metadata']['notas'] ?? '',
                                    'fecha' => $sessionDetails['metadata']['fecha'] ?? null,
                                    'paquete_id' => $sessionDetails['metadata']['paquete_id'] ?? null,
                                    'paquete_nombre' => $sessionDetails['metadata']['paquete_nombre'] ?? null,
                                    'paquete_desc' => $sessionDetails['metadata']['paquete_desc'] ?? null,
                                ],
                                'metadata' => $sessionDetails['metadata'] ?? [],
                            ];
                        }

                        if (($quoteData['payload']['type'] ?? 'cotizacion') === 'paquete') {
                            $savedPurchaseId = $this->savePackagePurchaseToDatabase($db, $quoteData);
                        } else {
                            $savedPurchaseId = $this->saveQuotePurchaseToDatabase($db, $quoteData);
                        }

                        $this->markStripeQuoteSaved($session_id, $savedPurchaseId);
                    } catch (Exception $e) {
                        $errorMessage = 'Pago confirmado, pero no se pudo guardar el pedido: ' . $e->getMessage();
                    }
                }
            }
        }

        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/citas_pago_exito.php';
        require_once 'View/plantillas/footer.php';
        exit;
    }

    /**
     * Procesa la selección de un paquete de evento
     * URL: /citas/paquete/{id_paquete}
     */
    public function paquete($id_paquete)
    {
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
    public function formularioPaquete()
    {
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
    public function guardarPaquete()
    {
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
    public function confirmarPaquete()
    {
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

    /**
     * Guarda el pedido de PAQUETE PREDEFINIDO proveniente del Frontend interactivo
     */
    public function guardarPaqueteAPI()
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->id_paquete) || !isset($data->fechaEvento)) {
            echo json_encode(["exito" => false, "mensaje" => "Faltan datos requeridos."]);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id_usuario = $_SESSION['usuario_id'] ?? 3;
        $id_paquete = $data->id_paquete;
        $fechaEvento = $data->fechaEvento;
        $total = $data->total_estimado;
        $personas = $data->personas ?? 20;

        try {
            require_once 'Config/Database.php';
            $database = new Database();
            $db = $database->getConnection();
            $db->exec("SET NAMES 'utf8mb4'");

            $db->beginTransaction();

            // 1. Insertar la cabecera del pedido
            $query = "INSERT INTO pedidos 
                      (id_usuario, tipoEvento, fechaEvento, notas, id_paquete) 
                      VALUES (:id_usuario, 'Paquete', :fechaEvento, :notas, :id_paquete)";

            $stmt = $db->prepare($query);
            $notas = "Reserva rápida de paquete para " . $personas . " personas.";
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':fechaEvento', $fechaEvento);
            $stmt->bindValue(':notas', $notas);
            $stmt->bindValue(':id_paquete', $id_paquete, PDO::PARAM_INT);
            $stmt->execute();

            $id_pedido = $db->lastInsertId();

            // 2. Obtener la receta (los productos) que componen este paquete
            $stmtPaq = $db->prepare("SELECT id_producto, cantidad FROM paquete_productos WHERE id_paquete = :id_paquete");
            $stmtPaq->bindValue(':id_paquete', $id_paquete, PDO::PARAM_INT);
            $stmtPaq->execute();
            $productos = $stmtPaq->fetchAll(PDO::FETCH_ASSOC);

            // 3. Insertar esos productos en detalle_pedidos para que salgan en "Mis Pedidos"
            $cantidad_total_items = array_sum(array_column($productos, 'cantidad'));
            $precioPorItem = $cantidad_total_items > 0 ? ($total / $cantidad_total_items) : 0;

            $insDetalle = $db->prepare('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');

            foreach ($productos as $prod) {
                $insDetalle->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $insDetalle->bindValue(':id_producto', $prod['id_producto'], PDO::PARAM_INT);
                $insDetalle->bindValue(':cantidad', $prod['cantidad'], PDO::PARAM_INT);
                $insDetalle->bindValue(':precio_unitario', $precioPorItem);
                $insDetalle->execute();
            }

            $db->commit();

            echo json_encode([
                "exito" => true,
                "mensaje" => "Paquete guardado con éxito.",
                "id_pedido" => $id_pedido
            ]);

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            echo json_encode(["exito" => false, "mensaje" => "Excepción: " . $e->getMessage()]);
        }
    }
}
?>