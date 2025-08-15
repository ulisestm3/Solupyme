<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
$idusuario = $_SESSION['idusuario'];
$menus = obtenerMenusUsuario($idusuario);
$clavesMenus = array_column($menus, 'clave');
?>

<style>
    .sidebar {
    width: 200px;
    background-color: #352b56ff;
    color: white;
    display: flex;
    flex-direction: column;
    padding: 1rem 1rem;
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
    background-color: #5596ebff;
    padding: 0.6rem 1rem;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 0.9rem;
}

.sidebar .logout-btn:hover {
    background-color: #a3ad4cff;
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
    }

    .sidebar nav a {
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
        <a href="../admin/dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

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
    </nav>
    <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
</aside>
