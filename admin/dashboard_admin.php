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
        /* Reset básico */
        * {
            box-sizing: border-box;
            margin: 0; padding: 0;
        }
        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f85c;
            color: #333;
            font-size: 14px; /* Tamaño base más pequeño */
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 180px; /* Más pequeño */
            background-color: #322757ff;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1rem 0.8rem; /* Menos padding */
            font-size: 13px;
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
            padding: 0.5rem 0.8rem; /* Más compacto */
            border-radius: 6px;
            margin-bottom: 0.35rem;
            display: block;
            transition: background-color 0.3s ease;
        }
        .sidebar nav a:hover {
            background-color: #0066cc;
        }
        .sidebar .logout-btn {
            margin-top: auto;
            background-color: #5596ebff;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 13px;
        }
        .sidebar .logout-btn:hover {
            background-color: #a3ad4cff;
        }

        /* Main content */
        .main-content {
            flex: 1;
            padding: 1rem 1.5rem; /* Menos padding */
            overflow-y: auto;
            font-size: 14px;
        }
        .main-content h1 {
            color: #004080;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }
        .welcome-message {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            padding: 0.7rem 1rem;
            background-color: #dbeafe;
            border-left: 5px solid #3b82f6;
            border-radius: 4px;
            line-height: 1.3;
        }

        section {
            margin-bottom: 1.5rem;
        }
        section h2 {
            color: #004080;
            margin-bottom: 0.5rem;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 0.25rem;
            font-size: 1.1rem;
        }
        section ul {
            list-style: none;
        }
        section ul li a {
            text-decoration: none;
            color: #1e40af;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.35rem;
            font-size: 13px;
            transition: color 0.3s ease;
        }
        section ul li a:hover {
            color: #2563eb;
        }

        .hamburger-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem; /* Más pequeño */
            cursor: pointer;
            margin-bottom: 1rem;
            align-self: flex-start;
            padding-left: 0.3rem;
        }

        .accordion-btn {
            background: none;
            border: none;
            color: white;
            padding: 0.4rem 0.8rem;
            width: 100%;
            text-align: left;
            font-size: 0.9rem;
            cursor: pointer;
            margin-bottom: 0.1rem;
        }

        .accordion-btn:hover {
            background-color: #34495e;
        }

        .accordion-panel {
            display: none;
            padding-left: 1rem;
            margin-top: 0.3rem;
            font-size: 13px;
        }

        .accordion-panel a {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 0.2rem 0;
        }

        .accordion-panel a:hover {
            text-decoration: underline;
        }

        .main-content {
            flex-grow: 1;
            padding: 1rem 1.5rem;
            transition: margin 0.3s ease;
        }

        /* Sidebar colapsada */
        .sidebar.collapsed {
            width: 50px;
            padding: 1rem 0.3rem;
            overflow: hidden;
        }

        .sidebar.collapsed h2,
        .sidebar.collapsed nav,
        .sidebar.collapsed .logout-btn,
        .sidebar.collapsed .accordion-btn,
        .sidebar.collapsed .accordion-panel {
            display: none;
        }

        .main-content.expanded {
            margin-left: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>


        <main class="main-content">

            <h1>Panel de administración</h1>
            <div class="welcome-message">
                Bienvenido, <strong><?= htmlspecialchars($usuarioNombre); ?></strong>.<br />
                Tu rol es: <strong><?= htmlspecialchars($rolNombre); ?></strong>.<br />
                ID de usuario: <strong><?= $usuarioId; ?></strong>.<br/>
                ID de rol: <strong><?= $rolId; ?></strong>
            </div>

            <section>
                <h2>Gestión de usuarios</h2>
                <ul>
                    <li><a href="../roles/usuarios.php">Usuarios</a></li>
                    <li><a href="../roles/roles.php">Roles</a></li>
                    <li><a href="../admin/permisos_por_rol.php">Asignar Páginas</a></li>
                    <li><a href="../roles/asignar_permisos.php">Permisos Páginas</a></li>
                    <li><a href="../admin/asignar_menu_usuario.php">Asignar Menús</a></li>
                    <li><a href="../roles/permisos_usuarios_menus.php">Permisos Menús</a></li>
                    
                </ul>
            </section>

            <section>
                <h2>Gestión de productos</h2>
                <ul>
                    <li><a href="../inventario/productos.php">Productos</a></li>
                    <li><a href="../inventario/categorias.php">Caterorías</a></li>
                    <li><a href="../inventario/movimientos.php">Movimientos</a></li>
                    <li><a href="../inventario/stock_bajo.php">Stock Bajo</a></li>
                </ul>
            </section>

            <section>
                <h2>Auditoría y actividad</h2>
                <ul>
                    <li><a href="/admin/logs/authlog.php">Ver AuthLog</a></li>
                    <li><a href="/admin/reset/listado.php">Solicitudes de recuperación</a></li>
                </ul>
            </section>
        </main>
    </div>

    <script>
        const accordionButtons = document.querySelectorAll('.accordion-btn');

        accordionButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Cierra todos los paneles primero
                accordionButtons.forEach(otherBtn => {
                    if (otherBtn !== btn) {
                        otherBtn.classList.remove('active');
                        otherBtn.nextElementSibling.style.display = 'none';
                    }
                });

                // Alterna el panel clicado
                btn.classList.toggle('active');
                const panel = btn.nextElementSibling;
                panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Colapsar sidebar
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

</body>
</html>
