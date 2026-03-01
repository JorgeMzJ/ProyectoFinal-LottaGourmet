<?php
// Controller/PromosController.php

require_once 'Config/Database.php';
require_once 'Model/Producto.php';

class PromosController {
	/**
	 * Muestra la página de promociones
	 */
	public function mostrar() {
		try {
			// Cargar productos en oferta desde BD
			$database = new Database();
			$db = $database->getConnection();
			if ($db === null) {
				throw new Exception("Error: No se pudo establecer conexión con la base de datos.");
			}
			$db->exec("SET NAMES 'utf8mb4'");
			$productoModel = new Producto($db);
			$ofertas = $productoModel->obtenerPromociones();			// Las imágenes se toman desde la columna 'imagen' en la BD y se cargan desde Public/img/
			
			// Cargar las vistas
			require_once 'View/plantillas/header.php';
			require_once 'View/plantillas/sidebar.php';
			require_once 'View/paginas/promociones.php';
			require_once 'View/plantillas/footer.php';
		} catch (Exception $e) {
			error_log("Error en PromosController::mostrar - " . $e->getMessage());
			die("<h1>Error del sistema</h1><p>No se pudieron cargar las promociones. Por favor, intenta más tarde.</p>");
		}
	}
}
