<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();
// Obtener roles
$roles = $conn->query("SELECT idrol, nombrerol FROM roles WHERE activo = b'1'")->fetch_all(MYSQLI_ASSOC);
// Lista de páginas permitidas
$paginasDisponibles = [
    'usuarios.php',
    'roles.php',
    'permisos_por_rol.php',
    'asignar_permisos.php',
    'asignar_menu_usuario.php',
    'permisos_usuarios_menus.php',
    'parametros.php',
    'productos.php',
    'categorias.php',
    'movimientos.php',
    'stock_bajo.php',
    'proveedores.php',
    'clientes.php',
    'compras.php',
    'facturas.php'
];
$idrolSeleccionado = $_GET['idrol'] ?? $roles[0]['idrol'];
// Obtener permisos actuales del rol
$stmt = $conn->prepare("SELECT pagina FROM roles_paginas WHERE idrol = ?");
$stmt->bind_param("i", $idrolSeleccionado);
$stmt->execute();
$result = $stmt->get_result();
$paginasPermitidas = array_column($result->fetch_all(MYSQLI_ASSOC), 'pagina');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Páginas</title>
     <style>
        /* Reset básico */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar .logout-btn:hover {
            background-color: #a3ad4c;
        }
        /* Main content */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            margin: 0.5rem;
            overflow-y: auto;
            font-size: 14px;
        }
        .main-content h2 {
            text-align: center;
            color: #004080;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            padding-bottom: 0.5rem;
        }
        /* Card styles */
        .card-permisos {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        /* Form styles */
        .select-rol {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .select-rol label {
            font-weight: bold;
            color: #004080;
            white-space: nowrap;
        }
        select {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ccc;
            flex: 1;
        }
        /* Checkbox styles */
        .checkbox-group {
            margin: 1rem 0;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .checkbox-item input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .checkbox-item label {
            margin-left: 0.2rem;
        }
        /* Button styles */
        button[type="submit"] {
            background-color: #007acc;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: block;
            margin: 1.5rem auto 0;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #005f9e;
        }
        /* Message styles */
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: bold;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .main-content {
                padding: 1rem;
                margin: 0;
            }
            .card-permisos {
                padding: 1.5rem;
                margin: 0 0.5rem;
            }
            .select-rol {
                flex-direction: column;
                align-items: flex-start;
            }
            select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!--sidebar_seguridad-->
        <?php include('../includes/sidebar_seguridad.php'); ?>

        <main class="main-content">
            <h2>Asignar Páginas a Rol</h2>
            <div class="card-permisos">
                <form method="get">
                    <div class="select-rol">
                        <label for="idrol">Selecciona un rol:</label>
                        <select name="idrol" id="idrol" onchange="this.form.submit()">
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['idrol'] ?>" <?= $rol['idrol'] == $idrolSeleccionado ? 'selected' : '' ?>>
                                    <?= $rol['nombrerol'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php if ($idrolSeleccionado > 0): ?>
                <form method="post" action="guardar_permisos_rol.php">
                    <input type="hidden" name="idrol" value="<?= $idrolSeleccionado ?>">
                    <h3 style="margin-bottom: 1rem;">Páginas disponibles</h3>
                    <div class="checkbox-group">
                        <?php foreach ($paginasDisponibles as $pagina): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="paginas[]" id="pagina-<?= $pagina ?>" value="<?= $pagina ?>"
                                    <?= in_array($pagina, $paginasPermitidas) ? 'checked' : '' ?>>
                                <label for="pagina-<?= $pagina ?>"><?= $pagina ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit">Guardar permisos</button>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
