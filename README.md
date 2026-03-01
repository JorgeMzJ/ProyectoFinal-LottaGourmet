# Pasteles UPBC - Sistema de Ventas y Pedidos

## Descripción
Sistema web para gestión de ventas de pasteles con carrito de compras, promociones, pedidos personalizados, y panel administrativo.

## Tecnologías
- PHP 7.4+ con arquitectura MVC
- MySQL 5.7+
- HTML5, CSS3, JavaScript Vanilla
- XAMPP (Apache + MySQL)

Estructura del Proyecto
```
PastelesUPBC/
├── Config/            # Configuración de base de datos
├── Controller/        # Controladores MVC
├── Model/             # Modelos de datos
├── View/              # Vistas (plantillas y páginas)
│   ├── paginas/       # Páginas principales
│   └── plantillas/    # Header, footer, sidebar
├── Public/            # Recursos públicos
│   ├── css/           # Estilos
│   ├── js/            # Scripts del cliente
│   └── img/           # Imágenes de productos
└── index.php          # Punto de entrada (router)
```

## Configuración de Base de Datos

### Local (XAMPP)
Editar `Config/Database.php`:
    php
private $host = "localhost";
private $db_name = "pastelesupbc";
private $username = "root";
private $password = "";


### Producción (ByetHost)
```php
private $host = "pastelesupbc.byethost24.com";
private $db_name = "sql202.byethost24.com";
private $username = "b24_40405106";
private $password = "bf86ph1n";
```

## Instalación

### Cambiar el nombre de la carpeta a `PastelesUPBC` o `Lottagourmet`

1. Clonar/copiar el proyecto en `C:\xampp\htdocs\PastelesUPBC`

2. Crear base de datos:
   - Abrir phpMyAdmin (http://localhost/phpmyadmin)
   - Crear base de datos `pastelesupbc`
   - Importar el archivo `pastelesupbc.sql`

3. Configurar credenciales:
   - Verificar `Config/Database.php` tenga credenciales locales

4. Iniciar servidor:
   - Abrir XAMPP Control Panel
   - Iniciar Apache y MySQL
   - Entrar a: http://localhost/PastelesUPBC

## Credenciales de Prueba

### Usuario Normal
- Email: usuario@ejemplo.com
- Password: (registrar nuevo usuario)

### Administrador
- Email: admin@gmail.com
- Password: admin1
- Permisos: Gestión de productos, compras, pedidos, restock

## Funcionalidades Principales

### Cliente
- Catálogo de productos: Menú con búsqueda y filtros
- Promociones: Productos con descuento y precio tachado
- Carrito de compras persistente
  - Límites de stock por producto
  - Validación en tiempo real
  - Cálculo de totales con descuentos
- Pedidos personalizados: Eventos con paquetes predefinidos
- Historial: Ver pedidos realizados

### Administrador
- Gestión de productos:
  - CRUD completo (Crear, Leer, Actualizar, Eliminar)
  - Activar/desactivar promociones
  - Subir imágenes (JPG, PNG)
  - Restock masivo: Agregar stock a todos los productos simultáneamente
- Compras y pedidos:
  - Ver todas las transacciones
  - Priorización por fecha de evento
  - Detalles de productos por pedido
- Alertas de stock bajo: Productos con stock ≤ 3 unidades
- Gráficas de ventas: Estadísticas semanales

## Base de Datos

### Tablas Principales
- usuarios: Clientes y administradores
- productos: Catálogo con stock, precio, promociones
- compras: Compras directas del carrito
- detalle_compras: Items de cada compra
- pedidos: Pedidos personalizados para eventos
- detalle_pedidos: Productos de cada pedido
- paquetes: Paquetes predefinidos por tipo de evento
- citas: Solicitudes de eventos especiales

## Características Técnicas

### Sistema de Stock
- Validación cliente: JavaScript previene agregar productos agotados
- Validación servidor: PHP verifica stock antes de confirmar compra
- Transacciones: UPDATE con WHERE para evitar stock negativo
- Decrementos automáticos: Al procesar compras

### Carrito de Compras
- Persistencia: localStorage (clave: `pastelesupbc_carrito`)
- Sincronización: Entre páginas del sitio
- Validaciones:
  - Stock disponible por producto
  - Límite máximo = stock actual
  - Precios de promoción aplicados

### Manejo de Sesiones
- `$_SESSION['usuario_id']`: ID del usuario logueado
- `$_SESSION['usuario_nombre']`: Nombre completo
- `$_SESSION['usuario_email']`: Email
- `$_SESSION['usuario_admin']`: 1 = admin, 0 = cliente
- `$_SESSION['usuario_logueado']`: Flag de autenticación

### Rutas (index.php)
```
/                      → Inicio (carrusel, ofertas)
/menu                  → Catálogo de productos
/promociones           → Solo productos en oferta
/nosotros              → Información de la empresa
/citas                 → Pedidos personalizados
/login                 → Iniciar sesión
/registro              → Crear cuenta
/compras/confirmar     → Confirmar compra del carrito
/admin/panel           → Dashboard administrativo
/admin/productos       → Gestión de productos
/admin/compras         → Ver todas las compras
/admin/bulkRestock     → Restock masivo
```

## Estilos y UI
- Paleta de colores:
  - Primario (header): `#FFC0CB` (rosa pastel)
  - Acento (botones): `#E91E63` (rosa fuerte)
  - Éxito: `#7bd389` (verde)
  - Alerta: `#fbbf24` (amarillo)
- Fuentes: Comfortaa (títulos), IBM Plex Sans (texto)
- Responsive: Layout adaptable a móviles
- Animaciones: Transiciones suaves, sin keyframes en modales

## Scripts Importantes

### `/Public/js/carrito.js`
- Manejo del carrito
- Validaciones de stock
- Persistencia en localStorage
- Modal del carrito

### `/Public/js/custom-modal.js`
- Sistema de alertas/confirmaciones personalizadas
- Reemplaza alert() y confirm() nativos
- Estilos consistentes con el tema

### `/Public/js/citas-pedido.js`
- Lógica de paquetes por evento
- Modal de fecha para pedidos
- Validación de fechas futuras

---

Última actualización: 26 de Noviembre de 2025
Versión: 1.8

//ESTE DOCUMENTO SE PUEDE REEMPLAZAR CON EL Database.php
//ESTE DOCUMENTO ES SOLO PARA EL HOST WEB

<?php
class Database {
    private $host = "pastelesupbc.byethost24.com";
    private $db_name = "sql202.byethost24.com";
    private $username = "b24_40405106";
    private $password = "bf86ph1n";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",  
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>

//LOCAL

<?php
class Database {
    private $host = "localhost";
    private $db_name = "pastelesupbc";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",  
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
