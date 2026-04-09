# Pasteles UPBC - Sistema de Ventas y Pedidos

## Descripción
Sistema web responsivo para gestión de ventas de pasteles, promociones y pedidos personalizados. Incluye soporte para el inicio de sesión de usuarios, un carrito de compras interactivo, generación dinámica de catálogos y un panel administrativo completo con gráficas en tiempo real. 

Adicionalmente, el sistema se integra con una pasarela de pagos vía **Stripe Checkout** y utiliza un rastreador de **Geolocalización** para certificar que el cliente se encuentre dentro del perímetro de reparto a domicilio.

## Tecnologías Utilizadas
- **Backend:** PHP 7.4+ con arquitectura MVC.
- **Base de Datos:** MySQL 5.7+ (Consultas preparadas con PDO).
- **Frontend:** HTML5, CSS3 Nativo, JavaScript Vanilla (sin jQuery).
- **Pagos:** API REST de Stripe Checkout.
- **Entorno Local:** XAMPP (Apache + MySQL).

---

## 📂 Estructura del Proyecto

```text
PastelesUPBC/
├── Config/            # Archivos de configuración (Database, App, Constantes)
├── Controller/        # Controladores que enlazan Modelos con Vistas
├── Model/             # Modelos de datos y reglas de negocio
├── View/              # Vistas al usuario
│   ├── paginas/       # Páginas principales del flujo web
│   └── plantillas/    # Header, footer, modales y sidebar global
├── Public/            # Recursos y elementos visuales estáticos
│   ├── css/           # Estilos (Hojas en cascada)
│   ├── js/            # Interacciones y peticiones asíncronas
│   └── img/           # Banco de imágenes SVG, PNG, WebP
└── index.php          # Enrutador principal de la aplicación (Front Controller)
```

---

## ⚙️ Configuración e Instalación

### 1. Preparar el Entorno
1. Clona, descarga o mueve esta carpeta hasta que quede alojada justo en `C:\xampp\htdocs\PastelesUPBC`.
2. Renombra la carpeta a `PastelesUPBC` (si no se llama así) para evitar inconsistencias con las rutas base por defecto.

### 2. Base de Datos
1. Abre el panel de control de **XAMPP** e inicia de manera simultánea `Apache` y `MySQL`.
2. Ve a [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Crea una base de datos nueva llamada `pastelesupbc`.
4. Importa en esa base de datos recién creada el script SQL de respaldo provisto con el proyecto o (`pastelesupbc.sql`).

### 3. Conexiones a la BD (Database.php)
Debes verificar que el entorno coincida dentro del archivo `Config/Database.php`. La clase `Database` maneja la conexión. Tienes dos plantillas en caso de subir este desarrollo a producción:

**Para tu Máquina Local (XAMPP):**
```php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "pastelesupbc";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() { /* ... PDO connection */ }
}
?>
```

**Para Despliegue en Producción (ByetHost):**
```php
<?php
class Database {
    private $host = "pastelesupbc.byethost24.com";
    private $db_name = "sql202.byethost24.com";
    private $username = "b24_40405106";
    private $password = "bf86ph1n";
    /* ... */
}
?>
```
*Si tienes constantes de credenciales para Stripe u otras integraciones, debes definirlas oportunamente en el `Config/App.php`.*

---

## 👥 Credenciales de Prueba

**👤 Perfil Cliente:**
- **Email:** `usuario@ejemplo.com`
- **Password:** No asignado (el administrador restringe acceso o crea uno nuevo de prueba).

**🛡️ Perfil Administrador:**
- **Email:** `admin@gmail.com`
- **Password:** `admin1`
- **Capacidades:** Permisos para la gestión transversal de productos, revisiones de compras y restock.

---

## 💡 Funcionalidades Destacadas

### Panel del Cliente:
- **Catálogo Dinámico:** Permite explorar y filtrar postres y buscar por promociones activas (los precios con descuento aplicados se muestran de color acentuado y tachados en su versión base).
- **Carrito Persistente:** Se guarda en el `localStorage` del navegador para evitar su desaparición durante las transiciones de página. Previene lógicamente superar el límite de stock local existente por cada artículo.
- **Cotizador y Paquetes Personalizados:** Permite crear pedidos hechos a la medida con API local para cambiar texturas de relleno, pan y base limitando al usuario según opciones y fechas. Las acciones de pago redirigen de forma segura hacia Stripe.
- **Mis Pedidos:** Historial unificado donde el cliente visualiza sus transacciones concretadas (vía carrito general y de personalizaciones hechas vía agenda).

### Panel de Administración:
- **Operaciones de Productos:** Tablero para activar descuentos de temporada, subir o sustituir nuevas imágenes a la galería y modificar especificaciones.
- **Restock Masivo:** Opción que le permite al administrador proveer stock general (ej. resurtir uniformemente 15 ítems extra a todos los elementos del catálogo) a través de una acción rápida de backend.
- **Dashboard Estadístico:** Gráficas visuales (Chart.js) que se alimentan de la actividad reciente dentro de la BD para graficar métricas.

---

## 🛡️ Características de Seguridad y Control MVC
- **Protección de Rutas:** Se valida el booleano `$_SESSION['usuario_admin']` tanto en los enrutadores iniciales del `index.php` como en los constructures del controlador protegido para limitar cualquier intrusión a paneles confidenciales.
- **Escapes Visuales:** Toda vista escapa entidades web haciendo uso exhaustivo de la función `htmlspecialchars`.
- **Integridad del Stock (Transacciones ACID):** Todo descuento de producto dentro de la BD está bajo el control de inicio (`beginTransaction()`) y un cierre exitoso (`commit()`). Si algún query falla, nada se cobra ni se decrementa.

---
**Última actualización:** 08 de Abril de 2026.
**Versión:** 1.9
