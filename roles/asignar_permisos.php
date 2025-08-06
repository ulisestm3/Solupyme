<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();
// Obtener roles
$roles = $conn->query("SELECT idrol, nombrerol FROM roles WHERE activo = b'1'")->fetch_all(MYSQLI_ASSOC);
// Determinar el rol seleccionado: el enviado por GET o el primero por defecto
$idrolSeleccionado = isset($_GET['idrol']) && $_GET['idrol'] != ''
    ? (int) $_GET['idrol']
    : ($roles[0]['idrol'] ?? 0);  // Si no hay roles, 0
// Obtener páginas asignadas al rol seleccionado
$paginas = [];
if ($idrolSeleccionado > 0) {
    $res = $conn->query("SELECT pagina FROM roles_paginas WHERE idrol = $idrolSeleccionado AND activo = b'1'");
    while ($row = $res->fetch_assoc()) {
        $paginas[] = $row['pagina'];
    }
}
// Guardar permisos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idrol = isset($_POST['idrol']) ? (int)$_POST['idrol'] : 0;
    if ($idrol === 0) {
        header("Location: asignar_permisos.php?error=seleccionar_rol");
        exit();
    }
    $paginasSeleccionadas = $_POST['paginas'] ?? [];
    $usuarioRegistra = $_SESSION['idusuario'];
    $conn->query("DELETE FROM permisos WHERE idrol = $idrol");
    $stmt = $conn->prepare("INSERT INTO permisos (idrol, pagina, activo, usuarioregistra, fecharegistro) VALUES (?, ?, b'1', ?, NOW())");
    foreach ($paginasSeleccionadas as $pagina) {
        $stmt->bind_param("isi", $idrol, $pagina, $usuarioRegistra);
        $stmt->execute();
    }
    header("Location: asignar_permisos.php?exito=1&idrol=$idrol");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Permisos - AWFerreteria</title>
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
            <h2>Asignar Permisos a Página</h2>
            <div class="card-permisos">
                <?php if (isset($_GET['exito'])): ?>
                    <p class="success-message">Permisos actualizados correctamente</p>
                <?php endif; ?>
                <form method="GET">
                    <div class="select-rol">
                        <label for="idrol">Selecciona un rol:</label>
                        <select name="idrol" id="idrol" required onchange="this.form.submit()">
                           <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['idrol'] ?>" <?= $rol['idrol'] == $idrolSeleccionado ? 'selected' : '' ?>>
                                    <?= $rol['nombrerol'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php if ($idrolSeleccionado > 0):
                    $permisosActuales = [];
                    $res = $conn->query("SELECT pagina FROM vista_roles_permisos WHERE idrol = $idrolSeleccionado AND activo = b'1'");
                    while ($row = $res->fetch_assoc()) {
                        $permisosActuales[] = $row['pagina'];
                    }
                ?>
                <form method="POST">
                    <input type="hidden" name="idrol" value="<?= $idrolSeleccionado ?>">
                    <h3>Páginas disponibles:</h3>
                    <div class="checkbox-group">
                        <?php foreach ($paginas as $pagina): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="paginas[]" id="pagina-<?= $pagina ?>" value="<?= $pagina ?>"
                                    <?= in_array($pagina, $permisosActuales) ? 'checked' : '' ?>>
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
