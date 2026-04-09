<?php
class ApiController {

    public function cotizar() {
        // 1. Configurar las cabeceras para que responda estrictamente en JSON
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");

        // 2. Solo aceptar peticiones POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // "Method Not Allowed"
            echo json_encode(["error" => "Método no permitido. Usa POST."]);
            return;
        }

        // 3. Capturar el JSON que Postman envia en el 'Body'
        $data = json_decode(file_get_contents("php://input"));

        // 4. Validación de datos mínimos requeridos (Casos de Error)
        if (!isset($data->personas) || !isset($data->pan) || !isset($data->relleno) || !isset($data->cobertura)) {
            http_response_code(400); // "Bad Request"
            echo json_encode([
                "exito" => false,
                "mensaje" => "Faltan datos requeridos (personas, pan, relleno, cobertura)."
            ]);
            return;
        }

        // 5. Catálogo de precios base definidos
        $precios_pan = ["Vainilla" => 100, "Chocolate" => 120, "Zanahoria" => 150];
        $precios_relleno = ["Fresa" => 30, "Nutella" => 50, "Cajeta" => 40];
        $precios_cobertura = ["Crema" => 40, "Fondant" => 150, "Chocolate" => 60];
        
        $precio_base_persona = 25; // Costo por tamaño
        $descuento_ingrediente = 15; // Descuento si omiten un ingrediente (Nuez, Almendra, Lactosa)

        // 6. Cálculos
        $costo_tamanio = $data->personas * $precio_base_persona;
        
        // Verificar que las opciones elegidas existan en el catálogo, si no, cobrar "0" de extra
        $costo_pan = isset($precios_pan[$data->pan]) ? $precios_pan[$data->pan] : 0;
        $costo_relleno = isset($precios_relleno[$data->relleno]) ? $precios_relleno[$data->relleno] : 0;
        $costo_cobertura = isset($precios_cobertura[$data->cobertura]) ? $precios_cobertura[$data->cobertura] : 0;

        $total = $costo_tamanio + $costo_pan + $costo_relleno + $costo_cobertura;

        // 7. Aplicar descuentos si se eliminan ingredientes extra
        $descuento_total = 0;
        if (isset($data->eliminar_ingrediente) && is_array($data->eliminar_ingrediente)) {
            $cantidad_eliminados = count($data->eliminar_ingrediente);
            $descuento_total = $cantidad_eliminados * $descuento_ingrediente;
            $total -= $descuento_total;
        }

        // 8. Responder con Éxito "Status 200" por defecto
        echo json_encode([
            "exito" => true,
            "mensaje" => "Cotización generada correctamente.",
            "desglose" => [
                "personas" => $data->personas,
                "pan" => $data->pan,
                "relleno" => $data->relleno,
                "cobertura" => $data->cobertura,
                "costo_calculado" => $total,
                "descuento_aplicado" => $descuento_total,
                "moneda" => "MXN"
            ]
        ]);
    }

    public function opciones() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido. Usa GET."]);
            return;
        }

        echo json_encode([
            "panes" => ["Vainilla", "Chocolate", "Zanahoria"],
            "rellenos" => ["Fresa", "Nutella", "Cajeta"],
            "coberturas" => ["Crema", "Fondant", "Chocolate"]
        ]);
    }
}
?>