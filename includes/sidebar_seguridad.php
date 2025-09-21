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
        --sidebar-active: #2980b9;
        --sidebar-text: #ecf0f1;
        --logout-color: #e74c3c;
        --logout-hover: #c0392b;
        --transition-speed: 0.3s;
        --sidebar-shadow: 2px 0 15px rgba(0, 0, 0, 0.25);
        --overlay-bg: rgba(0, 0, 0, 0.5);
    }

    /* Sidebar base */
    .sidebar {
        width: 240px;
        background: linear-gradient(135deg, var(--sidebar-bg), #1a252f);
        color: var(--sidebar-text);
        display: flex;
        flex-direction: column;
        padding: 1.2rem 0.8rem;
        font-size: 0.9rem;
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
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
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

    /* Enlaces del menú */
    .sidebar nav a {
        display: flex;
        align-items: center;
        color: var(--sidebar-text);
        text-decoration: none;
        padding: 0.7rem 1rem;
        border-radius: 6px;
        margin-bottom: 0.4rem;
        transition: all var(--transition-speed) ease;
        font-size: 0.95rem;
        position: relative;
    }

    .sidebar nav a i {
        margin-right: 0.8rem;
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    .sidebar nav a:hover {
        background-color: var(--sidebar-hover);
        color: white;
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
        border-radius: 6px 0 0 6px;
        transition: background-color var(--transition-speed) ease;
    }

    .sidebar nav a:hover::before {
        background: var(--sidebar-active);
    }

    /* Botón de logout */
    .logout-btn {
        margin-top: auto;
        background-color: var(--logout-color);
        padding: 0.7rem 1rem;
        border-radius: 6px;
        text-align: center;
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-speed) ease;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .logout-btn:hover {
        background-color: var(--logout-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    .logout-btn i {
        margin-right: 0.6rem;
        font-size: 1rem;
    }

    /* Sidebar colapsada — OPCIONAL, PERO NO SE USA EN ESTE CASO */
    .sidebar.collapsed {
        width: 60px;
    }

    .sidebar.collapsed h2,
    .sidebar.collapsed nav a span,
    .sidebar.collapsed .logout-btn span {
        display: none;
    }

    .sidebar.collapsed nav a {
        justify-content: center;
        padding: 0.7rem;
    }

    .sidebar.collapsed nav a i {
        margin-right: 0;
    }

    .sidebar.collapsed .logout-btn {
        justify-content: center;
        padding: 0.7rem;
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

    /* Sidebar móvil: oculto por defecto */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            box-shadow: none;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
            width: 240px;
        }

        /* Botón flotante para abrir sidebar en móvil */
        .mobile-menu-toggle {
            display: block;
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: var(--sidebar-active);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            z-index: 1001;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            transform: rotate(90deg);
        }

        .main-content {
            margin-left: 0 !important;
            padding: 1.5rem;
            transition: all var(--transition-speed) ease;
        }
    }

    /* Contenido principal (para desktop) */
    .main-content {
        margin-left: 240px;
        transition: margin-left var(--transition-speed) ease;
        padding: 1.5rem;
    }

    .sidebar.collapsed + .main-content {
        margin-left: 60px;
    }
</style>

<aside class="sidebar" id="sidebar">
    <!-- ❌ BOTÓN HAMBURGUESA INTERNO ELIMINADO -->
    <h2><?php echo htmlspecialchars(getDatosEmpresa()['nombrecormercial'] ?? 'Sistema'); ?></h2>
    <nav>
        <a href="../admin/dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        
        <?php if (in_array('1.1.usuarios', $clavesMenus)): ?>
            <a href="../roles/usuarios.php"><i class="fas fa-users"></i> <span>Usuarios</span></a>
        <?php endif; ?>
        <?php if (in_array('1.2.roles', $clavesMenus)): ?>
            <a href="../roles/roles.php"><i class="fas fa-user-tag"></i> <span>Roles</span></a>
        <?php endif; ?>
        <?php if (in_array('1.3.asignar_pagina', $clavesMenus)): ?>
            <a href="../admin/permisos_por_rol.php"><i class="fas fa-file-signature"></i> <span>Asignar Páginas</span></a>
        <?php endif; ?>
        <?php if (in_array('1.4.permiso_pagina', $clavesMenus)): ?>
            <a href="../roles/asignar_permisos.php"><i class="fas fa-key"></i> <span>Permisos Páginas</span></a>
        <?php endif; ?>
        <?php if (in_array('1.5.asignar_menu', $clavesMenus)): ?>
            <a href="../admin/asignar_menu_usuario.php"><i class="fas fa-list-alt"></i> <span>Asignar Menús</span></a>
        <?php endif; ?>
        <?php if (in_array('1.6.permiso_menu', $clavesMenus)): ?>
            <a href="../roles/permisos_usuarios_menus.php"><i class="fas fa-key"></i> <span>Permisos Menús</span></a>
        <?php endif; ?>
        <?php if (in_array('1.7.parametros', $clavesMenus)): ?>
            <a href="../parametros/parametros.php"><i class="fas fa-cogs"></i> <span>Parámetros</span></a>
        <?php endif; ?>
    </nav>
    <a href="../auth/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
    </a>
</aside>

<!-- Overlay para cerrar sidebar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ✅ ÚNICO BOTÓN HAMBURGUESA — SOLO EN MÓVIL -->
<button class="mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
    <i class="fas fa-bars"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const mobileToggle = document.getElementById('mobileMenuToggle');

        // Función para actualizar UI según tamaño de pantalla
        function updateMobileUI() {
            if (window.innerWidth <= 768) {
                mobileToggle.style.display = 'block';
                sidebar.classList.remove('collapsed'); // Ignorar colapsado en móvil
            } else {
                mobileToggle.style.display = 'none';
                overlay.classList.remove('active');
                sidebar.classList.remove('active');
            }
        }

        // Inicializar
        updateMobileUI();

        // Toggle sidebar solo en móvil
        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        }

        // Eventos
        mobileToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Cerrar sidebar al hacer clic en un enlace (solo móvil)
        const navLinks = sidebar.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });

        // Revisar al redimensionar
        window.addEventListener('resize', updateMobileUI);
    });
</script>