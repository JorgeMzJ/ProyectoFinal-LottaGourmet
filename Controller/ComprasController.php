<?php
// Controller/ComprasController.php
class ComprasController {
    
    public function confirmar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificar que haya items en el carrito desde POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['carrito'])) {
            $_SESSION['carrito_compra'] = json_decode($_POST['carrito'], true);
        }
        
        if (empty($_SESSION['carrito_compra'])) {
            header('Location: ' . BASE_URL . 'menu');
            exit;
        }
        
        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/confirmacion_compra.php';
        require_once 'View/plantillas/footer.php';
    }

    private function buildAbsoluteUrl(string $path): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        return $scheme . '://' . $host . ($base ? $base . '/' : '') . $path;
    }

    private function ensureStripeCartSessionStore(): void {
        if (!isset($_SESSION['stripe_cart_sessions']) || !is_array($_SESSION['stripe_cart_sessions'])) {
            $_SESSION['stripe_cart_sessions'] = [];
        }
        if (!isset($_SESSION['stripe_cart_saved']) || !is_array($_SESSION['stripe_cart_saved'])) {
            $_SESSION['stripe_cart_saved'] = [];
        }
    }

    private function storeStripeCartSessionData(string $stripeSessionId, array $cart, float $total): void {
        $this->ensureStripeCartSessionStore();
        $_SESSION['stripe_cart_sessions'][$stripeSessionId] = [
            'user_id' => $_SESSION['usuario_id'],
            'user_name' => $_SESSION['usuario_nombre'] ?? '',
            'user_email' => $_SESSION['usuario_email'] ?? '',
            'cart' => $cart,
            'total' => $total,
        ];
    }

    private function getStripeCartSessionData(string $stripeSessionId): ?array {
        $this->ensureStripeCartSessionStore();
        return $_SESSION['stripe_cart_sessions'][$stripeSessionId] ?? null;
    }

    private function markStripeCartSaved(string $stripeSessionId, int $compraId): void {
        $this->ensureStripeCartSessionStore();
        $_SESSION['stripe_cart_saved'][$stripeSessionId] = $compraId;
    }

    private function getSavedStripeCartPurchaseId(string $stripeSessionId): ?int {
        $this->ensureStripeCartSessionStore();
        return isset($_SESSION['stripe_cart_saved'][$stripeSessionId]) ? (int)$_SESSION['stripe_cart_saved'][$stripeSessionId] : null;
    }

    private function saveCartPurchaseToDatabase(PDO $db, array $cartData): int {
        $db->beginTransaction();

        $nombre = trim($cartData['user_name'] ?? '') ?: 'Cliente de carrito';
        $email = trim($cartData['user_email'] ?? '');
        $total = (float)($cartData['total'] ?? 0);
        $cart = $cartData['cart'] ?? [];

        $stmt = $db->prepare('INSERT INTO compras (fecha, total, nombre_cliente, email_cliente) VALUES (NOW(), :total, :nombre, :email)');
        $stmt->bindValue(':total', $total);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $id_compra = (int)$db->lastInsertId();

        $stmtDetalle = $db->prepare('INSERT INTO compra_detalles (id_compra, id_producto, cantidad, precio_unitario) VALUES (:id_compra, :id_producto, :cantidad, :precio_unitario)');

        foreach ($cart as $item) {
            $stmtDetalle->bindValue(':id_compra', $id_compra, PDO::PARAM_INT);
            $stmtDetalle->bindValue(':id_producto', $item['id'], PDO::PARAM_INT);
            $stmtDetalle->bindValue(':cantidad', $item['cantidad'], PDO::PARAM_INT);
            $stmtDetalle->bindValue(':precio_unitario', $item['precio']);
            $stmtDetalle->execute();

            $stmtUpdate = $db->prepare('UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id AND stock >= :cantidad');
            $stmtUpdate->bindValue(':cantidad', (int)$item['cantidad'], PDO::PARAM_INT);
            $stmtUpdate->bindValue(':id', (int)$item['id'], PDO::PARAM_INT);
            $stmtUpdate->execute();
            if ($stmtUpdate->rowCount() === 0) {
                throw new Exception('No se pudo actualizar stock para el producto ID ' . (int)$item['id']);
            }
        }

        $db->commit();
        return $id_compra;
    }

    public function procesar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido. Usa POST.']);
            return;
        }

        try {
            require_once 'Config/Database.php';

            if (!isset($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para continuar con el pago.']);
                return;
            }

            if (empty($_SESSION['carrito_compra']) || !is_array($_SESSION['carrito_compra'])) {
                echo json_encode(['success' => false, 'error' => 'El carrito está vacío.']);
                return;
            }

            $carrito = $_SESSION['carrito_compra'];
            $database = new Database();
            $db = $database->getConnection();
            $db->exec("SET NAMES 'utf8mb4'");

            $usuario_id = $_SESSION['usuario_id'];
            $stmtUsuario = $db->prepare('SELECT nombre, email FROM usuarios WHERE id = :id LIMIT 1');
            $stmtUsuario->bindValue(':id', $usuario_id, PDO::PARAM_INT);
            $stmtUsuario->execute();
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
                return;
            }

            $total = 0;
            $insuficientes = [];
            foreach ($carrito as $item) {
                $stmtStock = $db->prepare('SELECT stock, nombre FROM productos WHERE id_producto = :id LIMIT 1');
                $stmtStock->bindValue(':id', $item['id'], PDO::PARAM_INT);
                $stmtStock->execute();
                $row = $stmtStock->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $insuficientes[] = 'Producto no encontrado (ID ' . (int)$item['id'] . ')';
                    continue;
                }
                $stockActual = (int)$row['stock'];
                $nombreProd = $row['nombre'];
                if ($stockActual < (int)$item['cantidad']) {
                    $insuficientes[] = $nombreProd . ' (stock disponible: ' . $stockActual . ')';
                }
                $total += ($item['precio'] * $item['cantidad']);
            }

            if (!empty($insuficientes)) {
                echo json_encode(['success' => false, 'error' => 'Stock insuficiente para: ' . implode(', ', $insuficientes)]);
                return;
            }

            $body = [
                'payment_method_types[]' => 'card',
                'mode' => 'payment',
                'success_url' => $this->buildAbsoluteUrl('compras/stripe-success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => $this->buildAbsoluteUrl('compras/confirmar'),
                'metadata[usuario_id]' => $_SESSION['usuario_id'],
                'metadata[tipo_compra]' => 'carrito',
                'metadata[usuario_nombre]' => $_SESSION['usuario_nombre'] ?? '',
                'metadata[correo]' => $_SESSION['usuario_email'] ?? '',
            ];

            foreach ($carrito as $index => $item) {
                $body['line_items[' . $index . '][price_data][currency]'] = 'mxn';
                $body['line_items[' . $index . '][price_data][product_data][name]'] = $item['nombre'];
                $body['line_items[' . $index . '][price_data][product_data][description]'] = 'Cantidad: ' . $item['cantidad'];
                $body['line_items[' . $index . '][price_data][unit_amount]'] = (int)round($item['precio'] * 100);
                $body['line_items[' . $index . '][quantity]'] = (int)$item['cantidad'];
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
                $this->storeStripeCartSessionData($stripeResponse['id'], $carrito, $total);
                echo json_encode(['success' => true, 'id' => $stripeResponse['id']]);
                return;
            }

            $mensaje = $stripeResponse['error']['message'] ?? 'Error al crear la sesión de pago en Stripe.';
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $mensaje]);
            return;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            return;
        }
    }

    public function stripeSuccess($session_id = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!$session_id) {
            $session_id = $_GET['session_id'] ?? null;
        }

        $sessionDetails = null;
        $errorMessage = null;
        $savedPurchaseId = null;

        if (!$session_id) {
            $errorMessage = 'No se recibió el identificador de sesión de pago.';
        } else {
            $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($session_id));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . trim(STRIPE_SECRET_KEY)
            ]);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $errorMessage = 'No se pudo verificar el pago en Stripe: ' . $error;
            } else {
                $sessionDetails = json_decode($response, true);
                if (!isset($sessionDetails['payment_status']) || $sessionDetails['payment_status'] !== 'paid') {
                    $errorMessage = 'El pago no se completó correctamente. Si ya realizaste el pago, espera unos minutos y revisa tu correo.';
                } else {
                    $savedPurchaseId = $this->getSavedStripeCartPurchaseId($session_id);
                    if (!$savedPurchaseId) {
                        $cartData = $this->getStripeCartSessionData($session_id);
                        if (!$cartData) {
                            $errorMessage = 'Pago confirmado, pero no se encontró el carrito asociado. Por favor contacta soporte.';
                        } else {
                            try {
                                require_once 'Config/Database.php';
                                $database = new Database();
                                $db = $database->getConnection();
                                $db->exec("SET NAMES 'utf8mb4'");

                                $savedPurchaseId = $this->saveCartPurchaseToDatabase($db, $cartData);
                                $this->markStripeCartSaved($session_id, $savedPurchaseId);
                                unset($_SESSION['carrito_compra']);
                            } catch (Exception $e) {
                                $errorMessage = 'Pago confirmado, pero no se pudo guardar la compra: ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        require_once 'View/plantillas/header.php';
        require_once 'View/plantillas/sidebar.php';
        require_once 'View/paginas/compras_pago_exito.php';
        require_once 'View/plantillas/footer.php';
        exit;
    }
}
?>
