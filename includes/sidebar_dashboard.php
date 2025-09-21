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
        --accordion-bg: #3a506b;
        --accordion-active: #1abc9c;
        --logout-color: #e74c3c;
        --logout-hover: #c0392b;
        --transition-speed: 0.3s;
        --sidebar-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
        --overlay-bg: rgba(0, 0, 0, 0.6);
    }

    /* Sidebar base */
    .sidebar {
        width: 250px;
        background: linear-gradient(160deg, var(--sidebar-bg), #1a252f);
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

    /* Botones del acordeón */
    .accordion-btn {
        background: none;
        border: none;
        color: var(--sidebar-text);
        padding: 0.8rem 1.2rem;
        width: 100%;
        text-align: left;
        font-size: 0.95rem;
        cursor: pointer;
        margin-bottom: 0.4rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all var(--transition-speed) ease;
        position: relative;
        font-weight: 500;
    }

    .accordion-btn i {
        margin-right: 0.8rem;
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .accordion-btn:hover {
        background: var(--sidebar-hover);
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .accordion-btn:hover i {
        transform: translateX(3px);
    }

    .accordion-btn::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        border-radius: 8px 0 0 8px;
        transition: background-color var(--transition-speed) ease;
    }

    .accordion-btn:hover::before,
    .accordion-btn.active::before {
        background: var(--sidebar-active);
    }

    .accordion-btn::after {
        content: '▼';
        font-size: 0.7rem;
        transition: transform var(--transition-speed) ease;
        color: rgba(255, 255, 255, 0.7);
    }

    .accordion-btn.active::after {
        transform: rotate(180deg);
        color: white;
    }

    /* Paneles del acordeón */
    .accordion-panel {
        display: none;
        padding-left: 1rem;
        margin-top: 0.5rem;
        margin-bottom: 0.8rem;
        font-size: 0.9rem;
        overflow: hidden;
        transition: all var(--transition-speed) ease;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .accordion-panel a {
        display: flex;
        align-items: center;
        color: var(--sidebar-text);
        text-decoration: none;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        margin-bottom: 0.3rem;
        transition: all var(--transition-speed) ease;
        font-size: 0.92rem;
        position: relative;
        opacity: 0.9;
    }

    .accordion-panel a i {
        margin-right: 0.8rem;
        font-size: 0.9rem;
        width: 18px;
        text-align: center;
    }

    .accordion-panel a:hover {
        background: var(--accordion-bg);
        color: white;
        transform: translateX(6px);
        opacity: 1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        text-decoration: none;
        border: none;
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

    /* Sidebar colapsada (solo desktop - OPCIONAL) */
    /* Si quieres que en escritorio se pueda colapsar, descomenta esta sección */
    /*
    .sidebar.collapsed {
        width: 70px;
        padding: 1.5rem 0.5rem;
    }

    .sidebar.collapsed h2,
    .sidebar.collapsed .accordion-panel,
    .sidebar.collapsed .accordion-btn span {
        display: none;
    }

    .sidebar.collapsed .accordion-btn {
        justify-content: center;
        padding: 0.8rem;
    }

    .sidebar.collapsed .accordion-btn i {
        margin-right: 0;
    }

    .sidebar.collapsed .accordion-btn::after {
        display: none;
    }

    .sidebar.collapsed .logout-btn span {
        display: none;
    }

    .sidebar.collapsed .logout-btn {
        justify-content: center;
        padding: 0.8rem;
    }
    */

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

    /* Botón flotante hamburguesa en móvil — ÚNICO BOTÓN */
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

        /* Si usas collapsed en escritorio, ignóralo en móvil */
        .sidebar.collapsed {
            transform: translateX(-100%);
            width: 250px;
        }

        .mobile-menu-toggle {
            display: flex;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 1.5rem;
            transition: all var(--transition-speed) ease;
        }
    }

    /* Contenido principal (para desktop) */
    .main-content {
        margin-left: 250px;
        transition: margin-left var(--transition-speed) ease;
        padding: 1.5rem;
    }

    /*
    .sidebar.collapsed + .main-content {
        margin-left: 70px;
    }
    */

    /* Scroll personalizado */
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
    <!-- ❌ BOTÓN HAMBURGUESA INTERNO ELIMINADO -->
    <h2><?php echo htmlspecialchars(getDatosEmpresa()['nombrecormercial'] ?? 'Sistema'); ?></h2>
    <nav>
        <?php if (in_array('1.0.gestion_usuarios', $clavesMenus)): ?>
        <button class="accordion-btn">
            <span><i class="fas fa-users-cog"></i> Gestión de usuarios</span>
        </button>
        <div class="accordion-panel">
            <?php if (in_array('1.1.usuarios', $clavesMenus)): ?>
                <a href="../roles/usuarios.php"><i class="fas fa-user"></i> Usuarios</a>
            <?php endif; ?>
            <?php if (in_array('1.2.roles', $clavesMenus)): ?>
                <a href="../roles/roles.php"><i class="fas fa-user-tag"></i> Roles</a>
            <?php endif; ?>
            <?php if (in_array('1.3.asignar_pagina', $clavesMenus)): ?>
                <a href="../admin/permisos_por_rol.php"><i class="fas fa-user-shield"></i> Asignar Páginas</a>
            <?php endif; ?>
            <?php if (in_array('1.4.permiso_pagina', $clavesMenus)): ?>
                <a href="../roles/asignar_permisos.php"><i class="fas fa-lock"></i> Permisos Páginas</a>
            <?php endif; ?>
            <?php if (in_array('1.5.asignar_menu', $clavesMenus)): ?>
                <a href="../admin/asignar_menu_usuario.php"><i class="fas fa-bars"></i> Asignar Menús</a>
            <?php endif; ?>
            <?php if (in_array('1.6.permiso_menu', $clavesMenus)): ?>
                <a href="../roles/permisos_usuarios_menus.php"><i class="fas fa-list"></i> Permisos Menús</a>
            <?php endif; ?>
            <?php if (in_array('1.7.parametros', $clavesMenus)): ?>
                <a href="../parametros/parametros.php"><i class="fas fa-cogs"></i> Parámetros</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('2.0.gestion_productos', $clavesMenus)): ?>
        <button class="accordion-btn">
            <span><i class="fas fa-boxes"></i> Gestión de productos</span>
        </button>
        <div class="accordion-panel">
            <?php if (in_array('2.1.productos', $clavesMenus)): ?>
                <a href="../inventario/productos.php"><i class="fas fa-box"></i> Productos</a>
            <?php endif; ?>
            <?php if (in_array('2.2.categorias', $clavesMenus)): ?>
                <a href="../inventario/categorias.php"><i class="fas fa-tags"></i> Categorías</a>
            <?php endif; ?>
            <?php if (in_array('2.3.movimientos', $clavesMenus)): ?>
                <a href="../inventario/movimientos.php"><i class="fas fa-exchange-alt"></i> Movimientos</a>
            <?php endif; ?>
            <?php if (in_array('2.4.stock_bajo', $clavesMenus)): ?>
                <a href="../inventario/stock_bajo.php"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</a>
            <?php endif; ?>
            <?php if (in_array('2.5.proveedores', $clavesMenus)): ?>
                <a href="../proveedores/proveedores.php"><i class="fas fa-truck"></i> Proveedores</a>
            <?php endif; ?>
            <?php if (in_array('2.6.compras', $clavesMenus)): ?>
                <a href="../proveedores/compras.php"><i class="fas fa-shopping-cart"></i> Compras</a>
            <?php endif; ?>
            <?php if (in_array('2.7.clientes', $clavesMenus)): ?>
                <a href="../clientes/clientes.php"><i class="fas fa-handshake"></i> Clientes</a>
            <?php endif; ?>
            <?php if (in_array('2.8.facturas', $clavesMenus)): ?>
                <a href="../facturacion/facturas.php"><i class="fas fa-file-invoice"></i> Facturas</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('3.0.auditoria', $clavesMenus)): ?>
        <button class="accordion-btn">
            <span><i class="fas fa-clipboard-list"></i> Auditoría y actividad</span>
        </button>
        <div class="accordion-panel">
            <a href="/admin/logs/authlog.php"><i class="fas fa-file-alt"></i> Ver AuthLog</a>
            <a href="/admin/reset/listado.php"><i class="fas fa-key"></i> Solicitudes recuperación</a>
        </div>
        <?php endif; ?>
    </nav>
    <a href="../auth/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
    </a>
</aside>

<!-- Overlay para cerrar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ✅ ÚNICO BOTÓN HAMBURGUESA — SOLO VISIBLE EN MÓVIL -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accordionButtons = document.querySelectorAll('.accordion-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const mobileToggle = document.getElementById('mobileMenuToggle');
        let isMobile = window.innerWidth <= 768;

        // Inicializar UI móvil
        function updateMobileUI() {
            isMobile = window.innerWidth <= 768;
            if (isMobile) {
                mobileToggle.style.display = 'flex';
            } else {
                mobileToggle.style.display = 'none';
                // Opcional: si quieres que en desktop se colapse con otro método, aquí puedes agregarlo
                // Ej: hacer clic en logo, atajo de teclado, etc.
            }
        }

        updateMobileUI();

        // Toggle acordeón
        accordionButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const isActive = btn.classList.contains('active');
                
                // Cerrar todos los demás
                accordionButtons.forEach(otherBtn => {
                    if (otherBtn !== btn) {
                        otherBtn.classList.remove('active');
                        otherBtn.nextElementSibling.style.display = 'none';
                    }
                });

                // Abrir/cerrar actual
                btn.classList.toggle('active', !isActive);
                const panel = btn.nextElementSibling;
                if (!isActive) {
                    panel.style.display = 'block';
                } else {
                    panel.style.display = 'none';
                }
            });
        });

        // Función para toggle del sidebar (solo en móvil)
        function toggleSidebar() {
            if (isMobile) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
            // En escritorio, no hacemos nada (sidebar siempre visible)
            // Opcional: aquí podrías agregar funcionalidad de colapsar en escritorio si lo deseas
        }

        // Eventos
        mobileToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', () => {
            if (isMobile) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });

        // Cerrar sidebar al hacer clic en un enlace (solo móvil)
        const navLinks = sidebar.querySelectorAll('.accordion-panel a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });

        // Redimensionar ventana
        window.addEventListener('resize', updateMobileUI);
    });
</script>