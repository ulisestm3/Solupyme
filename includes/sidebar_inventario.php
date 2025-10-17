<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
require_once('../config/datosempresa.php');

$idusuario = $_SESSION['idusuario'];
$menus = obtenerMenusUsuario($idusuario);
$clavesMenus = array_column($menus, 'clave');
?>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    :root {
        --sidebar-bg: #2c3e50;
        --sidebar-hover: #34495e;
        --sidebar-active: #3498db;
        --sidebar-text: #ecf0f1;
        --logout-color: #e74c3c;
        --logout-hover: #c0392b;
        --transition-speed: 0.3s;
        --sidebar-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
        --overlay-bg: rgba(0, 0, 0, 0.6);
    }

    /* Sidebar base */
    .sidebar {
        width: 250px;
        background: linear-gradient(160deg, var(--sidebar-bg), #1c2b3a);
        color: var(--sidebar-text);
        display: flex;
        flex-direction: column;
        padding: 1.5rem 1rem;
        font-size: 0.95rem;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        box-shadow: var(--sidebar-shadow);
        z-index: 1000;
        overflow-y: auto;
        transition: transform var(--transition-speed) ease, width var(--transition-speed) ease;
    }

    .sidebar h2 {
        font-size: 1.4rem;
        margin-bottom: 1.8rem;
        text-align: center;
        font-weight: 700;
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        padding: 0.6rem 0;
        letter-spacing: 0.5px;
        position: relative;
    }

    .sidebar h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: var(--sidebar-active);
        border-radius: 2px;
    }

    /* Botón hamburguesa (solo visible en móvil por defecto) */
    .hamburger-btn {
        display: none;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        font-size: 1.4rem;
        cursor: pointer;
        margin-bottom: 1.5rem;
        padding: 0.6rem;
        border-radius: 8px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .hamburger-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.05);
    }

    /* Enlaces del menú */
    .sidebar nav a {
        display: flex;
        align-items: center;
        color: var(--sidebar-text);
        text-decoration: none;
        padding: 0.8rem 1.2rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        transition: all var(--transition-speed) ease;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .sidebar nav a i {
        margin-right: 0.8rem;
        font-size: 1.1rem;
        width: 24px;
        text-align: center;
        transition: transform 0.3s ease;
    }

    .sidebar nav a:hover {
        background: var(--sidebar-hover);
        color: white;
        transform: translateX(6px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .sidebar nav a:hover i {
        transform: translateX(4px);
    }

    .sidebar nav a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: background-color var(--transition-speed) ease;
        border-radius: 8px 0 0 8px;
    }

    .sidebar nav a:hover::before {
        background: var(--sidebar-active);
    }

    /* Botón de logout */
    .logout-btn {
        margin-top: auto;
        background: var(--logout-color);
        padding: 0.8rem 1.2rem;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border: none;
        text-decoration: none;
    }

    .logout-btn:hover {
        background: var(--logout-hover);
        transform: translateY(-3px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
    }

    .logout-btn i {
        margin-right: 0.7rem;
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .logout-btn:hover i {
        transform: translateX(3px);
    }

    /* Sidebar colapsada (solo desktop) */
    .sidebar.collapsed {
        width: 70px;
        padding: 1.5rem 0.5rem;
    }

    .sidebar.collapsed h2,
    .sidebar.collapsed nav a span,
    .sidebar.collapsed .logout-btn span {
        display: none;
    }

    .sidebar.collapsed nav a {
        justify-content: center;
        padding: 0.8rem;
    }

    .sidebar.collapsed nav a i {
        margin-right: 0;
    }

    .sidebar.collapsed .logout-btn {
        justify-content: center;
        padding: 0.8rem;
    }

    .sidebar.collapsed .hamburger-btn {
        justify-content: center;
    }

    /* Overlay para móvil */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--overlay-bg);
        z-index: 999;
        opacity: 0;
        transition: opacity var(--transition-speed) ease;
    }

    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Botón flotante hamburguesa en móvil */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 1.2rem;
        left: 1.2rem;
        background: var(--sidebar-active);
        color: white;
        border: none;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        font-size: 1.3rem;
        z-index: 1001;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mobile-menu-toggle:hover {
        transform: rotate(180deg) scale(1.1);
        background: #2980b9;
    }

    /* Responsive: Sidebar móvil como drawer */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 250px;
            box-shadow: none;
            padding-top: 2rem;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        /* Ignorar modo colapsado en móvil */
        .sidebar.collapsed {
            transform: translateX(-100%);
            width: 250px;
        }

        .mobile-menu-toggle {
            display: flex;
        }

        /* Ocultar hamburguesa interna en móvil */
        .hamburger-btn {
            display: block;
        }

        /* Ajustar contenido principal */
        .main-content {
            margin-left: 0 !important;
            padding: 1.5rem;
        }
    }

    /* Contenido principal (para desktop) */
    .main-content {
        margin-left: 250px;
        transition: margin-left var(--transition-speed) ease;
        padding: 1.5rem;
    }

    .sidebar.collapsed + .main-content {
        margin-left: 70px;
    }

    /* Scroll suave en sidebar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>

<aside class="sidebar" id="sidebar">
    <button id="toggleSidebar" class="hamburger-btn" title="Mostrar/Ocultar menú">
        <i class="fas fa-bars"></i>
    </button>
    <h2><?php echo htmlspecialchars(getDatosEmpresa()['nombrecormercial'] ?? 'Sistema'); ?></h2>
    <nav>
        <a href="../admin/dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>

        <?php if (in_array('2.1.productos', $clavesMenus)): ?>
            <a href="../inventario/productos.php"><i class="fas fa-box"></i> <span>Productos</span></a>
        <?php endif; ?>
        <?php if (in_array('2.2.categorias', $clavesMenus)): ?>
            <a href="../inventario/categorias.php"><i class="fas fa-tags"></i> <span>Categorías</span></a>
        <?php endif; ?>
        <?php if (in_array('2.3.movimientos', $clavesMenus)): ?>
            <a href="../inventario/movimientos.php"><i class="fas fa-exchange-alt"></i> <span>Movimientos</span></a>
        <?php endif; ?>
        <?php if (in_array('2.4.stock_bajo', $clavesMenus)): ?>
            <a href="../inventario/stock_bajo.php"><i class="fas fa-exclamation-triangle"></i> <span>Stock Bajo</span></a>
        <?php endif; ?>
        <?php if (in_array('2.5.proveedores', $clavesMenus)): ?>
            <a href="../proveedores/proveedores.php"><i class="fas fa-truck"></i> <span>Proveedores</span></a>
        <?php endif; ?>
        <?php if (in_array('2.6.compras', $clavesMenus)): ?>
            <a href="../proveedores/compras.php"><i class="fas fa-shopping-cart"></i> <span>Compras</span></a>
        <?php endif; ?>
        <?php if (in_array('2.7.clientes', $clavesMenus)): ?>
            <a href="../clientes/clientes.php"><i class="fas fa-handshake"></i> <span>Clientes</span></a>
        <?php endif; ?>
        <?php if (in_array('2.8.facturas', $clavesMenus)): ?>
            <a href="../facturacion/facturas.php"><i class="fas fa-file-invoice"></i> <span>Facturas</span></a>
        <?php endif; ?>
        <?php if (in_array('2.9.precio_venta', $clavesMenus)): ?>
            <a href="../inventario/precio_venta.php"><i class="fas fa-tags"></i> <span>Precio de Venta</span></a>
        <?php endif; ?>
        <?php if (in_array('2.10.costo_producto', $clavesMenus)): ?>
            <a href="../inventario/costo_producto.php"><i class="fas fa-dollar-sign"></i> <span>Costo de Productos</span></a>
        <?php endif; ?>
    </nav>
    <a href="../auth/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
    </a>
</aside>

<!-- Overlay para cerrar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Botón flotante para móvil -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const mobileToggle = document.getElementById('mobileMenuToggle');
        let isMobile = window.innerWidth <= 768;

        // Mostrar/ocultar botón móvil según tamaño
        function updateMobileUI() {
            isMobile = window.innerWidth <= 768;
            if (isMobile) {
                mobileToggle.style.display = 'flex';
                sidebar.classList.remove('collapsed'); // ignorar collapsed en móvil
            } else {
                mobileToggle.style.display = 'none';
            }
        }

        updateMobileUI();

        // Función para alternar sidebar
        function toggleSidebar() {
            if (isMobile) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        }

        // Eventos
        toggleBtn.addEventListener('click', toggleSidebar);
        mobileToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', () => {
            if (isMobile) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });

        // Cerrar sidebar al hacer clic en un enlace (solo móvil)
        const navLinks = sidebar.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });

        // Actualizar UI al redimensionar
        window.addEventListener('resize', updateMobileUI);
    });
</script>