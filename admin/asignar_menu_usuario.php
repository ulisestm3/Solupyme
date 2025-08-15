<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();

// Obtener roles
$usuarios = $conn->query("SELECT idusuario, usuario FROM usuarios WHERE activo = b'1'")->fetch_all(MYSQLI_ASSOC);

// Lista de páginas permitidas
$clavesDisponibles = [
    '1.0.gestion_usuarios',
    '1.1.usuarios',
    '1.2.roles',
    '1.3.asignar_pagina',
    '1.4.permiso_pagina',
    '1.5.asignar_menu',
    '1.6.permiso_menu',
    '2.0.gestion_productos',
    '2.1.productos',
    '2.2.categorias',
    '2.3.movimientos',
    '2.4.stock_bajo',
    '2.5.proveedores',
    '2.6.compras',
    '2.7.clientes',
    '3.0.auditoria'
];

$idusuarioSeleccionado = $_GET['idusuario'] ?? $usuarios[0]['idusuario'];

// Obtener permisos actuales del rol
$stmt = $conn->prepare("SELECT clave FROM usuarios_menus WHERE idusuario = ?");
$stmt->bind_param("i", $idusuarioSeleccionado);
$stmt->execute();
$result = $stmt->get_result();
$clavesPermitidas = array_column($result->fetch_all(MYSQLI_ASSOC), 'clave');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Menus - AWFerreteria</title>
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
        .select-usuario {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .select-usuario label {
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
            .select-usuario {
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
            <h2>Asignar Menús a Usuarios</h2>
            <div class="card-permisos">
                <form method="get">
                    <div class="select-usuario">
                        <label for="idusuario">Selecciona un Menú:</label>
                        <select name="idusuario" id="idusuario" onchange="this.form.submit()">
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $usuario['idusuario'] ?>" <?= $usuario['idusuario'] == $idusuarioSeleccionado ? 'selected' : '' ?>>
                                    <?= $usuario['usuario'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php if ($idusuarioSeleccionado > 0): ?>
                <form method="post" action="guardar_usuarios_menu.php">
                    <input type="hidden" name="idusuario" value="<?= $idusuarioSeleccionado ?>">
                    <h3 style="margin-bottom: 1rem;">Menús disponibles</h3>
                    <div class="checkbox-group">
                        <?php foreach ($clavesDisponibles as $clave): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="claves[]" id="clave-<?= $clave ?>" value="<?= $clave ?>"
                                    <?= in_array($clave, $clavesPermitidas) ? 'checked' : '' ?>>
                                <label for="clave-<?= $clave ?>"><?= $clave ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit">Guardar Menús</button>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
