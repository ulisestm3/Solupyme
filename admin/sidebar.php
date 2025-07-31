<?php
require_once('../config/database.php');
require_once('../config/seguridad.php'); // aquí ya haces session_start()

$idusuario = $_SESSION['idusuario'];
$menus = obtenerMenusUsuario($idusuario);
$clavesMenus = array_column($menus, 'clave');
?>

<aside class="sidebar">
    <button id="toggleSidebar" class="hamburger-btn" title="Mostrar/Ocultar menú">☰</button>
    <h2>AWFerreteria</h2>
    <nav>
        <?php if (in_array('gestion_usuarios', $clavesMenus)): ?>
        <button class="accordion-btn">Gestión de usuarios</button>
        <div class="accordion-panel">
            <?php if (in_array('usuarios', $clavesMenus)): ?>
                <a href="../roles/usuarios.php">Usuarios</a>
            <?php endif; ?>
            <?php if (in_array('roles', $clavesMenus)): ?>
                <a href="../roles/roles.php">Roles</a>
            <?php endif; ?>
            <?php if (in_array('asignar_pagina', $clavesMenus)): ?>
                <a href="../admin/permisos_por_rol.php">Asignar Páginas</a>
            <?php endif; ?>
            <?php if (in_array('permiso_pagina', $clavesMenus)): ?>
                <a href="../roles/asignar_permisos.php">Permisos Páginas</a>
            <?php endif; ?>
            <?php if (in_array('asignar_menu', $clavesMenus)): ?>
                <a href="../admin/asignar_menu_usuario.php">Asignar Menús</a>
            <?php endif; ?>
            <?php if (in_array('permiso_menu', $clavesMenus)): ?>
                <a href="../roles/permisos_usuarios_menus.php">Permisos Menús</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (in_array('gestion_productos', $clavesMenus)): ?>
        <button class="accordion-btn">Gestión de productos</button>
        <div class="accordion-panel">
            <a href="/admin/productos/nuevo.php">Agregar producto</a>
            <a href="/admin/productos/listado.php">Listar productos</a>
        </div>
        <?php endif; ?>

        <?php if (in_array('auditoria', $clavesMenus)): ?>
        <button class="accordion-btn">Auditoría y actividad</button>
        <div class="accordion-panel">
            <a href="/admin/logs/authlog.php">Ver AuthLog</a>
            <a href="/admin/reset/listado.php">Solicitudes recuperación</a>
        </div>
        <?php endif; ?>
    </nav>

    <a href="../auth/logout.php" class="logout-btn">Cerrar sesión</a>
</aside>