<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
$idusuario = $_SESSION['idusuario'];
$menus = obtenerMenusUsuario($idusuario);
$clavesMenus = array_column($menus, 'clave');
?>
<style>
    /* Variables de color para el sidebar */
    :root {
        --sidebar-bg: #2c3e50;
        --sidebar-hover: #34495e;
        --sidebar-active: #2980b9;
        --sidebar-text: #ecf0f1;
        --sidebar-border: #2c3e50;
        --accordion-active: #1abc9c;
        --logout-color: #3ca0e7ff;
        --logout-hover: #ff8274ff;
        --transition-speed: 0.3s;
    }

    /* Estilos mejorados para el sidebar */
    .sidebar {
        width: 240px;
        background: linear-gradient(135deg, var(--sidebar-bg), #1a252f);
        color: var(--sidebar-text);
        display: flex;
        flex-direction: column;
        padding: 1.2rem 0.8rem;
        font-size: 0.9rem;
        height: 100vh;
        position: relative;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        overflow: hidden;
        transition: width var(--transition-speed) ease;
    }

    .sidebar h2 {
        font-size: 1.3rem;
        margin-bottom: 1.8rem;
        text-align: center;
        font-weight: 600;
        color: white;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        padding: 0.5rem;
        position: relative;
    }

    .sidebar h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50%;
        height: 2px;
        background: var(--sidebar-active);
        border-radius: 1px;
    }

    /* Botón hamburguesa mejorado */
    .hamburger-btn {
        background: none;
        border: none;
        color: var(--sidebar-text);
        font-size: 1.5rem;
        cursor: pointer;
        margin-bottom: 1.2rem;
        align-self: flex-start;
        padding: 0.5rem;
        width: 100%;
        text-align: left;
        transition: all var(--transition-speed) ease;
    }

    .hamburger-btn:hover {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
    }

    /* Estilos para los botones del acordeón */
    .accordion-btn {
        background: none;
        border: none;
        color: var(--sidebar-text);
        padding: 0.7rem 1rem;
        width: 100%;
        text-align: left;
        font-size: 0.95rem;
        cursor: pointer;
        margin-bottom: 0.2rem;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all var(--transition-speed) ease;
        position: relative;
        overflow: hidden;
    }

    .accordion-btn:hover {
        background-color: var(--sidebar-hover);
        color: white;
    }

    .accordion-btn::after {
        content: '▼';
        font-size: 0.7rem;
        transition: transform var(--transition-speed) ease;
    }

    .accordion-btn.active::after {
        transform: rotate(180deg);
    }

    .accordion-btn i {
        margin-right: 0.8rem;
        font-size: 1rem;
    }

    /* Paneles del acordeón */
    .accordion-panel {
        display: none;
        padding-left: 0.5rem;
        margin-top: 0.3rem;
        font-size: 0.9rem;
        overflow: hidden;
        transition: all var(--transition-speed) ease;
    }

    .accordion-panel a {
        display: block;
        color: var(--sidebar-text);
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        margin-bottom: 0.3rem;
        transition: all var(--transition-speed) ease;
        position: relative;
        overflow: hidden;
    }

    .accordion-panel a:hover {
        background-color: var(--sidebar-hover);
        color: white;
        padding-left: 1.2rem;
    }

    .accordion-panel a::before {
        margin-right: 0.8rem;
        font-size: 0.7rem;
        position: absolute;
        left: 0.8rem;
    }

    /* Botón de logout mejorado */
    .logout-btn {
        margin-top: auto;
        background-color: var(--logout-color);
        padding: 0.7rem 1rem;
        border-radius: 4px;
        text-align: center;
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .logout-btn:hover {
        background-color: var(--logout-hover);
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .logout-btn i {
        margin-right: 0.5rem;
        font-size: 1rem;
    }

    /* Sidebar colapsada */
    .sidebar.collapsed {
        width: 60px;
        padding: 1rem 0.5rem;
        overflow: hidden;
    }

    .sidebar.collapsed h2,
    .sidebar.collapsed .accordion-panel,
    .sidebar.collapsed .accordion-btn span {
        display: none;
    }

    .sidebar.collapsed .accordion-btn {
        justify-content: center;
        padding: 0.7rem;
    }

    .sidebar.collapsed .accordion-btn::after {
        display: none;
    }

    .sidebar.collapsed .logout-btn span {
        display: none;
    }

    .sidebar.collapsed .logout-btn {
        justify-content: center;
        padding: 0.7rem;
    }

    .sidebar.collapsed .hamburger-btn {
        justify-content: center;
    }

    /* Main content */
    .main-content {
        flex-grow: 1;
        padding: 1.5rem;
        transition: margin var(--transition-speed) ease;
    }

    .main-content.expanded {
        margin-left: 60px;
    }

    /* Animación para el sidebar */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .accordion-panel {
        animation: fadeIn var(--transition-speed) ease;
    }
</style>

<aside class="sidebar">
    <button id="toggleSidebar" class="hamburger-btn" title="Mostrar/Ocultar menú">☰</button>
    <h2><i class="fas fa-tools"></i> AWFerreteria</h2>
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

<!-- Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script>
    // Funcionalidad del acordeón
    const accordionButtons = document.querySelectorAll('.accordion-btn');

    accordionButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');

            // Cierra otros paneles si están abiertos
            accordionButtons.forEach(otherBtn => {
                if (otherBtn !== btn && otherBtn.classList.contains('active')) {
                    otherBtn.classList.remove('active');
                    otherBtn.nextElementSibling.style.display = 'none';
                }
            });

            // Muestra/oculta el panel actual
            const panel = btn.nextElementSibling;
            panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
        });
    });

    // Funcionalidad para colapsar el sidebar
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');

        if (mainContent) {
            mainContent.classList.toggle('expanded');
        }
    });
</script>
