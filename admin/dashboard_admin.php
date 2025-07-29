<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['idusuario'])) {
    header("Location: ../index.php?error=Acceso no autorizado.");
    exit();
}

// Variables para mostrar en la bienvenida
$usuarioId = $_SESSION['idusuario'];
$usuarioNombre = $_SESSION['nombrecompleto'];
$rolNombre = ''; // Aquí podrías traer el nombre del rol desde BD o definir según idrol

// Ejemplo básico para rolNombre (puedes mejorar esto con consulta real)
switch ($_SESSION['idrol']) {
    case 1: $rolNombre = 'Administrador'; break;
    case 2: $rolNombre = 'Usuario'; break;
    default: $rolNombre = 'Invitado';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel de administración - AWFerreteria</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #322757ff;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
        }
        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: bold;
        }
        .sidebar nav a {
            color: white;
            text-decoration: none;
            padding: 0.8rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            display: block;
            transition: background-color 0.3s ease;
        }
        .sidebar nav a:hover {
            background-color: #0066cc;
        }
        .sidebar .logout-btn {
            margin-top: auto;
            background-color: #5596ebff;
            padding: 0.8rem 1rem;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .sidebar .logout-btn:hover {
            background-color: #a3ad4cff;
        }

        /* Main content */
        .main-content {
            flex: 1;
            padding: 2rem 3rem;
            overflow-y: auto;
        }
        .main-content h1 {
            color: #004080;
            margin-bottom: 1rem;
        }
        .welcome-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            padding: 1rem 1.5rem;
            background-color: #dbeafe;
            border-left: 5px solid #3b82f6;
            border-radius: 4px;
        }

        section {
            margin-bottom: 2.5rem;
        }
        section h2 {
            color: #004080;
            margin-bottom: 0.75rem;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 0.25rem;
        }
        section ul {
            list-style: none;
        }
        section ul li a {
            text-decoration: none;
            color: #1e40af;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }
        section ul li a:hover {
            color: #2563eb;
        }

        .hamburger-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            margin-bottom: 1rem;
            align-self: flex-start;
        }

        .accordion-btn {
            background: none;
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            width: 100%;
            text-align: left;
            font-size: 1rem;
            cursor: pointer;
        }

        .accordion-btn:hover {
            background-color: #34495e;
        }

        .accordion-panel {
            display: none;
            padding-left: 1rem;
            margin-top: 0.5rem;
        }

        .accordion-panel a {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 0.25rem 0;
        }

        .accordion-panel a:hover {
            text-decoration: underline;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            transition: margin 0.3s ease;
        }

        /* Sidebar colapsada */
        .sidebar.collapsed {
            width: 60px;
            padding: 1rem 0.5rem;
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
        <aside class="sidebar">
        <button id="toggleSidebar" class="hamburger-btn">☰</button>
            <h2>AWFerreteria</h2>
            <nav>
            
    <button class="accordion-btn">Gestión de usuarios</button>
    <div class="accordion-panel">
        <a href="/admin/usuarios/listado.php">Ver usuarios</a>
        <a href="/admin/roles/asignar.php">Asignar roles</a>
    </div>

    <button class="accordion-btn">Gestión de productos</button>
    <div class="accordion-panel">
        <a href="/admin/productos/nuevo.php">Agregar producto</a>
        <a href="/admin/productos/listado.php">Listar productos</a>
    </div>

    <button class="accordion-btn">Auditoría y actividad</button>
    <div class="accordion-panel">
        <a href="/admin/logs/authlog.php">Ver AuthLog</a>
        <a href="/admin/reset/listado.php">Solicitudes recuperación</a>
    </div>
</nav>

            <a href="../auth/logout.php" class="logout-btn">Cerrar sesión</a>
        </aside>

        <main class="main-content">

            <h1>Panel de administración</h1>
            <div class="welcome-message">
                Bienvenido, <strong><?php echo htmlspecialchars($usuarioNombre); ?></strong>.<br />
                Tu rol es: <strong><?php echo htmlspecialchars($rolNombre); ?></strong>.<br />
                ID de usuario: <strong><?php echo $usuarioId; ?></strong>
            </div>

            <section>
                <h2>Gestión de usuarios</h2>
                <ul>
                    <li><a href="/admin/usuarios/listado.php">Ver usuarios</a></li>
                    <li><a href="/admin/roles/asignar.php">Asignar roles</a></li>
                </ul>
            </section>

            <section>
                <h2>Gestión de productos</h2>
                <ul>
                    <li><a href="/admin/productos/nuevo.php">Agregar producto</a></li>
                    <li><a href="/admin/productos/listado.php">Listar productos</a></li>
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

                    // Luego, alterna el botón clicado
                    btn.classList.toggle('active');
                    const panel = btn.nextElementSibling;
                    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
                });
            });
            
            // Colapsar sidebar
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.querySelector('.sidebar'); // asegúrate que tu sidebar tenga esta clase
            const mainContent = document.querySelector('.main-content'); // si usas uno

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                if (mainContent) {
                    mainContent.classList.toggle('expanded');
                }
            });
        </script>

</body>
</html>
