<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
$idusuario = $_SESSION['idusuario'];
$menus = obtenerMenusUsuario($idusuario);
$clavesMenus = array_column($menus, 'clave');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    /* Sidebar */
    .sidebar {
        width: 200px;
        background-color: #352b56;
        color: white;
        display: flex;
        flex-direction: column;
        padding: 1rem;
    }
    .sidebar h2 {
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: bold;
    }
    .sidebar nav a {
        color: white;
        text-decoration: none;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        margin-bottom: 0.4rem;
        display: block;
        transition: background-color 0.3s ease;
        font-size: 0.9rem;
    }
    .sidebar nav a:hover {
        background-color: #0066cc;
    }
    .sidebar .logout-btn {
        margin-top: auto;
        background-color: #5596eb;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        text-align: center;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 0.9rem;
    }
    .sidebar .logout-btn:hover {
        background-color: #a3ad4c;
    }
    /* Responsive Sidebar */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            flex-direction: row;
            overflow-x: auto;
            padding: 0.5rem;
        }
        .sidebar h2 {
            flex: 1 0 auto;
            margin-bottom: 0;
            padding-right: 1rem;
            text-align: left;
            font-size: 1rem;
        }
        .sidebar nav {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        .sidebar nav a {
            white-space: nowrap;
            margin-bottom: 0;
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }
        .sidebar .logout-btn {
            margin-top: 0;
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }
    }
</style>

<aside class="sidebar">
<h2><i class="fas fa-store"></i> AWFerreteria</h2>
<nav>
    <?php
        $idusuario = $_SESSION['idusuario'];
        $menus = obtenerMenusUsuario($idusuario);
        $clavesMenus = array_column($menus, 'clave');
    ?>
    <a href="../admin/dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    
    <?php if (in_array('1.1.usuarios', $clavesMenus)): ?>
        <a href="../roles/usuarios.php"><i class="fas fa-users"></i> Usuarios</a>
    <?php endif; ?>
    <?php if (in_array('1.2.roles', $clavesMenus)): ?>
        <a href="../roles/roles.php"><i class="fas fa-user-tag"></i> Roles</a>
    <?php endif; ?>
    <?php if (in_array('1.3.asignar_pagina', $clavesMenus)): ?>
        <a href="../admin/permisos_por_rol.php"><i class="fas fa-file-signature"></i> Asignar Páginas</a>
    <?php endif; ?>
    <?php if (in_array('1.4.permiso_pagina', $clavesMenus)): ?>
        <a href="../roles/asignar_permisos.php"><i class="fas fa-key"></i> Permisos Páginas</a>
    <?php endif; ?>
    <?php if (in_array('1.5.asignar_menu', $clavesMenus)): ?>
        <a href="../admin/asignar_menu_usuario.php"><i class="fas fa-list-alt"></i> Asignar Menús</a>
    <?php endif; ?>
    <?php if (in_array('1.6.permiso_menu', $clavesMenus)): ?>
        <a href="../roles/permisos_usuarios_menus.php"><i class="fas fa-key"></i> Permisos Menús</a>
    <?php endif; ?>
</nav>
<a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
</aside>