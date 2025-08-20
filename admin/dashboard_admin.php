<?php
require_once('../config/seguridad.php');
// Variables para mostrar en la bienvenida
$usuarioId = $_SESSION['idusuario'];
$usuarioNombre = $_SESSION['nombrecompleto'];
$rolId = $_SESSION['idrol'];
$rolNombre = $_SESSION['nombrerol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de administración - AWFerreteria</title>
    <style>
        /* Paleta de colores mejorada inspirada en productos */
        :root {
            --primary-color: #2c3e50;       /* Azul oscuro elegante */
            --secondary-color: #3498db;     /* Azul vibrante */
            --accent-color: #2ecc71;        /* Verde fresco */
            --warning-color: #e74c3c;       /* Rojo para alertas */
            --info-color: #1abc9c;          /* Turquesa informativo */
            --text-color: #333;
            --light-bg: #ecf0f1;            /* Fondo claro */
            --card-bg: #ffffff;
            --border-color: #bdc3c7;
            --hover-color: #f8f9fa;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            --gradient: linear-gradient(135deg, #3498db, #2c3e50);
        }

        /* Reset básico */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            font-size: 14px;
            line-height: 1.5;
        }

        .container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Main content mejorado con colores de productos */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background-color: var(--light-bg);
            transition: all 0.3s ease;
        }

        .main-content h1 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--secondary-color);
            background: var(--gradient);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .welcome-message {
            font-size: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-left: 5px solid var(--secondary-color);
            border-radius: 8px;
            box-shadow: var(--shadow);
            line-height: 1.6;
            margin-top: 1rem;
            border: 1px solid var(--border-color);
        }

        .welcome-message strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Secciones mejoradas con colores de productos */
        section {
            margin-bottom: 2rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--secondary-color);
        }

        section h2 {
            color: var(--primary-color);
            margin-bottom: 1.2rem;
            font-size: 1.3rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Estilos para los iconos de sección */
        .section-icon {
            font-size: 1.2rem;
            margin-right: 0.5rem;
            color: var(--secondary-color);
        }

        /* Lista de enlaces mejorada con colores de productos */
        section ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        section ul li {
            margin-bottom: 0.8rem;
        }

        section ul li a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 500;
            display: block;
            padding: 0.8rem 1.2rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        section ul li a:hover {
            color: white;
            background: var(--gradient);
            border-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        section ul li a i {
            margin-right: 0.6rem;
            color: var(--secondary-color);
            transition: color 0.3s ease;
        }

        section ul li a:hover i {
            color: white;
        }

        /* Estilos para el contenedor de estadísticas */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease;
            border-top: 4px solid;
        }

        .stat-card:nth-child(1) {
            border-top-color: var(--secondary-color);
        }

        .stat-card:nth-child(2) {
            border-top-color: var(--accent-color);
        }

        .stat-card:nth-child(3) {
            border-top-color: var(--warning-color);
        }

        .stat-card:nth-child(4) {
            border-top-color: var(--info-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-value.users {
            color: var(--secondary-color);
        }

        .stat-value.products {
            color: var(--accent-color);
        }

        .stat-value.warning {
            color: var(--warning-color);
        }

        .stat-value.info {
            color: var(--info-color);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.8rem;
        }

        .stat-icon.users {
            color: var(--secondary-color);
        }

        .stat-icon.products {
            color: var(--accent-color);
        }

        .stat-icon.warning {
            color: var(--warning-color);
        }

        .stat-icon.info {
            color: var(--info-color);
        }

        /* Estilos para el header de acciones rápidas */
        .quick-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .quick-action-btn {
            background-color: var(--card-bg);
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            box-shadow: var(--shadow);
        }

        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .quick-action-btn i {
            margin-right: 0.7rem;
            font-size: 1.1rem;
        }

        .btn-users {
            border-top: 3px solid var(--secondary-color);
        }

        .btn-users:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-users:hover i {
            color: white;
        }

        .btn-products {
            border-top: 3px solid var(--accent-color);
        }

        .btn-products:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-products:hover i {
            color: white;
        }

        .btn-reports {
            border-top: 3px solid var(--info-color);
        }

        .btn-reports:hover {
            background-color: var(--info-color);
            color: white;
        }

        .btn-reports:hover i {
            color: white;
        }

        .btn-inventory {
            border-top: 3px solid var(--warning-color);
        }

        .btn-inventory:hover {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-inventory:hover i {
            color: white;
        }

        /* Estilos para el footer */
        .admin-footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-color);
            font-size: 0.9rem;
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }

            section ul {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            section ul {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                flex-direction: column;
            }

            .quick-action-btn {
                width: 100%;
                justify-content: center;
                margin-bottom: 0.8rem;
            }

            .stats-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">

        <!-- llama sidebar -->
        <?php include '../includes/sidebar_dashboard.php'; ?>

        <main class="main-content">
            <h1><i class="fas fa-tachometer-alt"></i> Panel de administración</h1>

            <div class="welcome-message">
                <p><i class="fas fa-user-shield"></i> Bienvenido, <strong><?= htmlspecialchars($usuarioNombre); ?></strong></p>
                <p><i class="fas fa-user-tag"></i> Tu rol es: <strong><?= htmlspecialchars($rolNombre); ?></strong></p>
                <p><i class="fas fa-id-badge"></i> ID de usuario: <strong><?= $usuarioId; ?></strong></p>
                <p><i class="fas fa-id-card"></i> ID de rol: <strong><?= $rolId; ?></strong></p>
            </div>

            <?php if (in_array('1.0.gestion_usuarios', $clavesMenus)): ?>
            <section>
                <h2><span><i class="fas fa-users-cog"></i> Gestión de usuarios</span></h2>
                <ul>
                    <?php if (in_array('1.1.usuarios', $clavesMenus)): ?>
                        <li><a href="../roles/usuarios.php"><i class="fas fa-user"></i> Usuarios</a></li>
                    <?php endif; ?>
                    <?php if (in_array('1.2.roles', $clavesMenus)): ?>
                        <li><a href="../roles/roles.php"><i class="fas fa-user-tag"></i> Roles</a></li>
                    <?php endif; ?>
                    <?php if (in_array('1.3.asignar_pagina', $clavesMenus)): ?>
                        <li><a href="../admin/permisos_por_rol.php"><i class="fas fa-user-shield"></i> Asignar Páginas</a></li>
                    <?php endif; ?>
                    <?php if (in_array('1.4.permiso_pagina', $clavesMenus)): ?>
                        <li><a href="../roles/asignar_permisos.php"><i class="fas fa-lock"></i> Permisos Páginas</a></li>
                    <?php endif; ?>
                    <?php if (in_array('1.5.asignar_menu', $clavesMenus)): ?>
                        <li><a href="../admin/asignar_menu_usuario.php"><i class="fas fa-bars"></i> Asignar Menús</a></li>
                    <?php endif; ?>
                    <?php if (in_array('1.6.permiso_menu', $clavesMenus)): ?>
                        <li><a href="../roles/permisos_usuarios_menus.php"><i class="fas fa-list"></i> Permisos Menús</a></li>
                    <?php endif; ?>
                </ul>
            </section>
            <?php endif; ?>

            <?php if (in_array('2.0.gestion_productos', $clavesMenus)): ?>
            <section>
                <h2><span><i class="fas fa-boxes"></i> Gestión de productos</span></h2>
                <ul>
                    <?php if (in_array('2.1.productos', $clavesMenus)): ?>
                        <li><a href="../inventario/productos.php"><i class="fas fa-box"></i> Productos</a></li>
                    <?php endif; ?>
                <?php if (in_array('2.2.categorias', $clavesMenus)): ?>
                        <li><a href="../inventario/categorias.php"><i class="fas fa-tags"></i> Categorías</a></li>
                    <?php endif; ?>
                    <?php if (in_array('2.3.movimientos', $clavesMenus)): ?>
                        <li><a href="../inventario/movimientos.php"><i class="fas fa-exchange-alt"></i> Movimientos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('2.4.stock_bajo', $clavesMenus)): ?>
                        <li><a href="../inventario/stock_bajo.php"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</a></li>
                    <?php endif; ?>
                    <?php if (in_array('2.5.proveedores', $clavesMenus)): ?>
                        <li><a href="../proveedores/proveedores.php"><i class="fas fa-truck"></i> Proveedores</a></li>
                    <?php endif; ?>
                    <?php if (in_array('2.6.compras', $clavesMenus)): ?>
                        <li><a href="../proveedores/compras.php"><i class="fas fa-shopping-cart"></i> Compras</a></li>
                    <?php endif; ?>
                    <?php if (in_array('2.7.clientes', $clavesMenus)): ?>
                        <li><a href="../clientes/clientes.php"><i class="fas fa-handshake"></i> Clientes</a></li>
                    <?php endif; ?>
                    <?php if (in_array('2.8.facturas', $clavesMenus)): ?>
                        <li><a href="../facturacion/facturas.php"><i class="fas fa-file-invoice"></i> Facturas</a></li>
                    <?php endif; ?>
                </ul>
            </section>
            <?php endif; ?>
            <?php if (in_array('3.0.auditoria', $clavesMenus)): ?>
            <section>
                <h2><span><i class="fas fa-clipboard-list"></i> Auditoría y actividad</span></h2>
                <ul>
                    <li><a href="/admin/logs/authlog.php"><i class="fas fa-file-alt"></i> Ver AuthLog</a></li>
                    <li><a href="/admin/reset/listado.php"><i class="fas fa-key"></i> Solicitudes de recuperación</a></li>
                </ul>
            </section>
            <?php endif; ?>

            <div class="admin-footer">
                <p><i class="fas fa-copyright"></i> Panel de Administración AWFerreteria | © <?= date('Y'); ?> | Versión 2.1.0</p>
            </div>
        </main>
    </div>
    <script>
        // Funcionalidad para los botones de acciones rápidas
        const quickActionBtns = document.querySelectorAll('.quick-action-btn');
        quickActionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const actionText = this.querySelector('i').classList.contains('fa-user-plus') ? 'Nuevo Usuario' :
                                this.querySelector('i').classList.contains('fa-box-open') ? 'Nuevo Producto' :
                                this.querySelector('i').classList.contains('fa-chart-line') ? 'Generar Reporte' : 'Actualizar Inventario';

                // Aquí podrías redirigir a diferentes páginas según la acción
                console.log(`Acción "${actionText}" seleccionada`);
                alert(`Acción "${actionText}" seleccionada. Funcionalidad implementada.`);
            });
        });

        // Colapsar sidebar
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        if (toggleBtn && sidebar && mainContent) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }
    </script>
</body>
</html>
