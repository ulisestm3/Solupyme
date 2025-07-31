<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
//verificarPermisoPagina();

$conn = getConnection();
$alertas = $conn->query("
    SELECT * FROM productos 
    WHERE stock <= stock_minimo
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Roles - AWFerreteria</title>
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
        /* Main content */
        .main-content {
            flex: 1;
            padding: 1rem 1.5rem;
            margin: 0.5rem;
            overflow-y: auto;
            font-size: 14px;
        }
        .main-content h1 {
            color: #004080;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        a.btn {
            display: inline-block;
            background-color: #004080;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        a.btn:hover {
            background-color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            font-size: 13px;
        }
        th, td {
            padding: 6px 8px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007acc;
            color: white;
            font-weight: 600;
        }
        /* Acción editar */
        .action-link {
            display: inline-block;
            margin-right: 8px;
            cursor: pointer;
            vertical-align: middle;
        }
        .action-link svg {
            vertical-align: middle;
            transition: transform 0.2s ease;
        }
        .action-link:hover svg {
            transform: scale(1.2);
        }
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
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
            .main-content {
                padding: 1rem 1rem;
                margin: 0;
                font-size: 13px;
            }
        }
        /* Estilos para modales */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 15px;
        }
        .close, .close-message {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus, .close-message:hover, .close-message:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-content label {
            display: block;
            margin-top: 10px;
        }
        .modal-content input[type="text"],
        .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .modal-content button {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        .modal-content button:hover {
            background-color: #2563eb;
        }
        /* Estilos para mensajes */
        .mensaje-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .mensaje-contenido {
            background-color: white;
            border-radius: 10px;
            padding: 20px 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            text-align: center;
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }
        .mensaje-contenido.success {
            border-top: 6px solid #28a745;
        }
        .mensaje-contenido.error {
            border-top: 6px solid #dc3545;
        }
        .mensaje-icono {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .mensaje-texto {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        .mensaje-cerrar {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .mensaje-cerrar:hover {
            background-color: #0056b3;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>AWFerreteria</h2>
            
            <nav>
                <a href="../admin/dashboard_admin.php">Dashboard</a>
                <a href="../inventario/productos.php">Productos</a>
                <a href="../inventario/categorias.php">Caterorías</a>
                <a href="../inventario/movimientos.php">Movimientos</a>
                <a href="../inventario/stock_bajo.php">Stock Bajo</a>
            </nav>
            <a href="../auth/logout.php" class="logout-btn">Cerrar sesión</a>
        </aside>
        <main class="main-content">

<h3>Productos con Bajo Stock</h3>

<?php if (empty($alertas)): ?>
    <p>No hay alertas de stock bajo.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Stock Actual</th>
            <th>Mínimo Requerido</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($alertas as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['nombre']) ?></td>
            <td><?= $a['stock'] ?></td>
            <td><?= $a['stock_minimo'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

        </main>
    </body>
</html>