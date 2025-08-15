<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();
$conn = getConnection();

// Función para obtener clientes paginados
function obtenerClientesPaginados($conn, $porPagina, $offset) {
    $sql = "
        SELECT idcliente, nombre, apellido, identificacion, tipo_identificacion, telefono, email, activo, fecharegistro
        FROM clientes
        WHERE activo = b'1'
        ORDER BY fecharegistro DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $porPagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para contar clientes activos
function contarClientes($conn) {
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE activo = b'1'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'] ?? 0;
}

// Obtener cliente por ID
function obtenerClientePorId($conn, $idcliente) {
    $sql = "
        SELECT idcliente, nombre, apellido, identificacion, tipo_identificacion, telefono, email, direccion, ciudad, activo
        FROM clientes
        WHERE idcliente = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idcliente);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Mensajes desde sesión
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
if (!in_array($porPagina, [10, 50, 100])) $porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Contar clientes activos
$totalRegistros = contarClientes($conn);
$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

// Obtener clientes paginados
$clientes = obtenerClientesPaginados($conn, $porPagina, $offset);

// Eliminar lógico (GET)
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $usuarioactualiza = $_SESSION['idusuario'] ?? 1;
    if ($idEliminar > 0) {
        try {
            $sql = "UPDATE clientes SET activo = b'0', usuarioactualiza = ?, fechaactualizacion = NOW() WHERE idcliente = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $usuarioactualiza, $idEliminar);
            $stmt->execute();
            $_SESSION['mensaje'] = "Cliente desactivado correctamente.";
            $_SESSION['tipoMensaje'] = 'success';
            header('Location: clientes.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al desactivar el cliente: " . $e->getMessage();
            $_SESSION['tipoMensaje'] = 'error';
            header('Location: clientes.php');
            exit;
        }
    }
}

// Manejar la creación de un nuevo cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cliente'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $identificacion = $_POST['identificacion'];
    $tipo_identificacion = $_POST['tipo_identificacion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $usuarioregistra = $_SESSION['idusuario'] ?? 1;

    try {
        $sql = "INSERT INTO clientes (nombre, apellido, identificacion, tipo_identificacion, telefono, email, direccion, ciudad, usuarioregistra)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $nombre, $apellido, $identificacion, $tipo_identificacion, $telefono, $email, $direccion, $ciudad, $usuarioregistra);
        $stmt->execute();
        $_SESSION['mensaje'] = "Cliente registrado correctamente.";
        $_SESSION['tipoMensaje'] = 'success';
        header('Location: clientes.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar el cliente: " . $e->getMessage();
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: clientes.php');
        exit;
    }
}

// Manejar la edición de un cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_cliente'])) {
    $idcliente = intval($_POST['idcliente']);
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $identificacion = $_POST['identificacion'];
    $tipo_identificacion = $_POST['tipo_identificacion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $usuarioactualiza = intval($_SESSION['idusuario'] ?? 1);

    try {
        $sql = "UPDATE clientes SET
                nombre = ?,
                apellido = ?,
                identificacion = ?,
                tipo_identificacion = ?,
                telefono = ?,
                email = ?,
                direccion = ?,
                ciudad = ?,
                usuarioactualiza = ?,
                fechaactualizacion = NOW()
                WHERE idcliente = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssii",
            $nombre,
            $apellido,
            $identificacion,
            $tipo_identificacion,
            $telefono,
            $email,
            $direccion,
            $ciudad,
            $usuarioactualiza,
            $idcliente
        );

        $stmt->execute();
        $_SESSION['mensaje'] = "Cliente actualizado correctamente.";
        $_SESSION['tipoMensaje'] = 'success';
        header('Location: clientes.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar el cliente: " . $e->getMessage();
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: clientes.php');
        exit;
    }
}

// Endpoint para obtener datos del cliente en formato JSON (para edición)
if (isset($_GET['idcliente'])) {
    header('Content-Type: application/json');
    $idcliente = intval($_GET['idcliente']);
    $cliente = obtenerClientePorId($conn, $idcliente);
    if ($cliente) {
        echo json_encode($cliente);
    } else {
        echo json_encode(['error' => 'Cliente no encontrado']);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes - AWFerreteria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f85c; color: #333; }
        .container { display: flex; height: 100vh; }
        .main-content { flex: 1; padding: 1rem 1.5rem; margin: 0.5rem; overflow-y: auto; font-size: 14px; }
        .main-content h3 { color: #004080; margin-bottom: 1rem; font-size: 1.5rem; display: flex; align-items: center; }
        .main-content h3 i { margin-right: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; background-color: #004080; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: none; cursor: pointer; transition: background-color 0.3s; }
        .btn:hover { background-color: #2563eb; }
        .btn i { margin-right: 8px; }
        table { width: 100%; border-collapse: collapse; background-color: #fff; font-size: 13px; }
        th, td { padding: 10px 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007acc; color: white; font-weight: 600; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px 15px; margin-bottom: 15px; display: flex; align-items: center; }
        .error-message i { margin-right: 10px; color: #721c24; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); overflow: auto; animation: fadeIn 0.3s; }
        .modal-content { background-color: #ffffff; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); padding: 20px 25px; border-radius: 10px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border: none; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #eee; cursor:move; }
        .modal-header h3 { color:#333; font-size:1.5rem; display:flex; align-items:center; }
        .modal-header h3 i { margin-right:10px; }
        .close { color:#aaa; font-size:24px; font-weight:bold; cursor:pointer; transition:color 0.3s; }
        .close:hover { color:#333; }
        .modal-body { margin-bottom:20px; }
        .modal-body label { display:block; margin-bottom:8px; font-weight:600; color:#444; }
        .modal-body input[type="text"], .modal-body select, .modal-body textarea, .modal-body input[type="email"] {
            width:100%; padding:12px; margin-bottom:5px; border:1px solid #ddd; border-radius:6px; font-size:14px; transition:border-color 0.3s;
        }
        .modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus, .modal-body input[type="email"]:focus {
            border-color:#007bff; outline:none; box-shadow:0 0 0 3px rgba(0,123,255,0.25);
        }
        .modal-footer { display:flex; justify-content:flex-end; gap:10px; }
        .modal-footer button { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px; border-radius:6px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.3s; border:none; }
        .modal-footer button i { margin-right:8px; }
        .btn-primary { background-color:#007bff; color:white; }
        .btn-primary:hover { background-color:#0056b3; }
        .btn-secondary { background-color:#f8f9fa; color:#333; border:1px solid #ddd; }
        .btn-secondary:hover { background-color:#e2e6ea; }
        .btn-danger { background-color:#dc3545; color:white; }
        .btn-danger:hover { background-color:#c82333; }
        .success-modal { display:none; position:fixed; z-index:1001; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4); animation:fadeIn 0.3s; }
        .success-modal-content { background-color:white; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); padding:30px; border-radius:10px; width:90%; max-width:400px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.3); display:flex; flex-direction:column; align-items:center; justify-content:center; }
        .success-modal-content i.fas.fa-check-circle { color:#28a745; font-size:50px; margin-bottom:15px; }
        .success-modal-content h3 { color:#28a745; margin-bottom:15px; font-size:1.5rem; text-align:center; font-weight:bold; }
        .success-modal-content p { color:#666; margin-bottom:25px; font-size:14px; text-align:center; }
        .success-modal-content button { display:block; width:100%; max-width:150px; margin:0 auto; background-color:#28a745; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:500; transition:background-color 0.3s; }
        .success-modal-content button:hover { background-color:#218838; }
        .warning-modal { display:none; position:fixed; z-index:1001; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4); animation:fadeIn 0.3s; }
        .warning-modal-content { background-color:white; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); padding:30px; border-radius:10px; width:90%; max-width:400px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.3); display:flex; flex-direction:column; align-items:center; justify-content:center; }
        .warning-modal-content i.fas.fa-exclamation-triangle { color:#ffa52fff; font-size:50px; margin-bottom:15px; }
        .warning-modal-content h3 { color:#ffa52fff; margin-bottom:15px; font-size:1.5rem; text-align:center; font-weight:bold; }
        .warning-modal-content p { color:#666; margin-bottom:25px; font-size:14px; text-align:center; }
        .warning-modal-content button { display:block; width:100%; max-width:150px; margin:0 auto; background-color:#ffc107; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:500; transition:background-color 0.3s; }
        .warning-modal-content button:hover { background-color:#e0a800; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .main-content { padding: 1rem; margin: 0; font-size: 13px; }
            .modal-content { width: 95%; }
            .success-modal-content { width: 95%; }
            table { font-size: 12px; }
            th, td { padding: 8px 10px; }
        }
        .pagination-container { display:flex; justify-content:space-between; align-items:center; margin-top:15px; font-size:13px; color:#555; }
        .pagination { display:flex; list-style:none; margin:0; padding:0; gap:5px; }
        .pagination a, .pagination span { padding:5px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#007bff; font-size:13px; }
        .pagination a:hover { background-color:#007bff; color:white; }
        .pagination .current { background-color:#007bff; color:white; font-weight:bold; }
        .pagination .disabled { color:#ccc; cursor:not-allowed; }
        .per-page-select { padding:5px; border:1px solid #ddd; border-radius:4px; font-size:13px; }
        .action-btn { display:inline-flex; align-items:center; justify-content:center; padding:5px 8px; border-radius:4px; margin-right:5px; text-decoration:none; font-size:13px; transition:all .3s; border:none; cursor:pointer; background:none; color:inherit; }
        .action-btn i { margin-right:0; font-size:14px; }
        .action-btn:hover { transform:scale(1.05); }
        .edit-btn { color:#0d6efd; }
        .delete-btn { color:#dc3545; }
        .error-text { color: #dc3545; font-size: 12px; display: none; }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/sidebar_inventario.php'); ?>
        <main class="main-content">
            <h3><i class="fas fa-users"></i> Gestión de Clientes</h3>
            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo $tipoMensaje === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            <div style="display: flex; gap: 10px; align-items: center; margin: 10px 0 20px 0; flex-wrap: wrap;">
                <button class="btn" id="openModal" style="margin-bottom:0;"><i class="fas fa-plus"></i> Nuevo Cliente</button>
                <input type="text" id="busquedaCliente" class="form-control" placeholder="Buscar cliente..." style="max-width:350px;width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:4px;font-size:14px;">
            </div>
            <table id="tablaClientes">
                <thead>
                    <tr>
                        <th hidden="true">ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Identificación</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyClientes">
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td hidden="true"><?= htmlspecialchars($cliente['idcliente']) ?></td>
                            <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                            <td><?= htmlspecialchars($cliente['apellido']) ?></td>
                            <td><?= htmlspecialchars($cliente['identificacion']) ?></td>
                            <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                            <td><?= htmlspecialchars($cliente['email']) ?></td>
                            <td><?= htmlspecialchars($cliente['fecharegistro']) ?></td>
                            <td>
                                <button class="action-btn edit-btn"
                                        onclick="abrirModalEditarCliente(<?= $cliente['idcliente'] ?>)"
                                        title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="action-btn delete-btn"
                                        onclick="abrirModalEliminar(<?= $cliente['idcliente'] ?>, '<?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido'], ENT_QUOTES) ?>')"
                                        title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No hay clientes registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <!-- Paginación -->
            <?php if ($totalRegistros > 0): ?>
                <div class="pagination-container">
                    <div>
                        Mostrando <strong><?= min($offset + 1, $totalRegistros) ?> - <?= min($offset + $porPagina, $totalRegistros) ?></strong> de <strong><?= $totalRegistros ?></strong> registros
                    </div>
                    <div>
                        <label for="por_pagina">Mostrar: </label>
                        <select id="por_pagina" class="per-page-select" onchange="cambiarPorPagina(this.value)">
                            <option value="10" <?= $porPagina == 10 ? 'selected' : '' ?>>10</option>
                            <option value="50" <?= $porPagina == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $porPagina == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                </div>
                <ul class="pagination" style="margin-top:10px;">
                    <li><a href="?pagina=<?= max(1, $pagina - 1) ?>&por_pagina=<?= $porPagina ?>" class="<?= $pagina <= 1 ? 'disabled' : '' ?>">Anterior</a></li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li><a href="?pagina=<?= $i ?>&por_pagina=<?= $porPagina ?>" class="<?= $i == $pagina ? 'current' : '' ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li><a href="?pagina=<?= min($totalPaginas, $pagina + 1) ?>&por_pagina=<?= $porPagina ?>" class="<?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">Siguiente</a></li>
                </ul>
            <?php endif; ?>
            <!-- Modal Nuevo Cliente -->
            <div id="modalNuevoCliente" class="modal">
                <div class="modal-content">
                    <form method="post" action="clientes.php">
                        <div class="modal-header">
                            <h3><i class="fas fa-user-plus"></i> Nuevo Cliente</h3>
                            <span class="close" onclick="cerrarModalNuevoCliente()">&times;</span>
                        </div>
                        <div class="modal-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div>
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" name="nombre" required>
                                </div>
                                <div>
                                    <label for="apellido">Apellido</label>
                                    <input type="text" id="apellido" name="apellido" required>
                                </div>
                                <div>
                                    <label for="identificacion">Identificación</label>
                                    <input type="text" id="identificacion" name="identificacion" required
                                           oninput="formatearIdentificacion(this)">
                                </div>
                                <div>
                                    <label for="tipo_identificacion">Tipo Identificación</label>
                                    <select id="tipo_identificacion" name="tipo_identificacion" required>
                                        <option value="CEDULA">Cédula</option>
                                        <option value="RUC">RUC</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" id="telefono" name="telefono"
                                           oninput="formatearTelefono(this)">
                                </div>
                                <div>
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email"
                                           onblur="validarEmail(this)">
                                    <span id="email-error" class="error-text">Por favor, ingresa un correo electrónico válido (ej: usuario@dominio.com).</span>
                                </div>
                                <div>
                                    <label for="direccion">Dirección</label>
                                    <textarea id="direccion" name="direccion" rows="2"></textarea>
                                </div>
                                <div>
                                    <label for="ciudad">Ciudad</label>
                                    <input type="text" id="ciudad" name="ciudad">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="guardar_cliente" value="1">
                            <button type="button" class="btn-secondary" onclick="cerrarModalNuevoCliente()"><i class="fas fa-times"></i> Cancelar</button>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal Editar Cliente -->
            <div id="modalEditarCliente" class="modal">
                <div class="modal-content">
                    <form method="post" action="clientes.php">
                        <div class="modal-header">
                            <h3><i class="fas fa-user-edit"></i> Editar Cliente</h3>
                            <span class="close" onclick="cerrarModalEditarCliente()">&times;</span>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="idcliente" id="editar_idcliente">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div>
                                    <label for="editar_nombre">Nombre</label>
                                    <input type="text" id="editar_nombre" name="nombre" required>
                                </div>
                                <div>
                                    <label for="editar_apellido">Apellido</label>
                                    <input type="text" id="editar_apellido" name="apellido" required>
                                </div>
                                <div>
                                    <label for="editar_identificacion">Identificación</label>
                                    <input type="text" id="editar_identificacion" name="identificacion" required
                                           oninput="formatearIdentificacion(this)">
                                </div>
                                <div>
                                    <label for="editar_tipo_identificacion">Tipo Identificación</label>
                                    <select id="editar_tipo_identificacion" name="tipo_identificacion" required>
                                        <option value="CEDULA">Cédula</option>
                                        <option value="RUC">RUC</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="editar_telefono">Teléfono</label>
                                    <input type="text" id="editar_telefono" name="telefono"
                                           oninput="formatearTelefono(this)">
                                </div>
                                <div>
                                    <label for="editar_email">Email</label>
                                    <input type="email" id="editar_email" name="email"
                                           onblur="validarEmail(this)">
                                    <span id="editar_email-error" class="error-text">Por favor, ingresa un correo electrónico válido (ej: usuario@dominio.com).</span>
                                </div>
                                <div>
                                    <label for="editar_direccion">Dirección</label>
                                    <textarea id="editar_direccion" name="direccion" rows="2"></textarea>
                                </div>
                                <div>
                                    <label for="editar_ciudad">Ciudad</label>
                                    <input type="text" id="editar_ciudad" name="ciudad">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="editar_cliente" value="1">
                            <button type="button" class="btn-secondary" onclick="cerrarModalEditarCliente()"><i class="fas fa-times"></i> Cancelar</button>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal Eliminar Cliente -->
            <div id="modalEliminarCliente" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-trash-alt"></i> Confirmar Eliminación</h3>
                        <span class="close" onclick="cerrarModalEliminar()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p style="text-align:center;">
                            <i class="fas fa-exclamation-triangle" style="color:#dc3545; font-size:50px; margin-bottom:15px;"></i>
                        </p>
                        <p style="text-align:center; font-size:16px; margin-bottom:10px;">
                            ¿Estás seguro que deseas borrar al cliente <strong id="nombreClienteEliminar"></strong>?
                        </p>
                        <p style="text-align:center; font-size:14px; color:#666;">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer" style="justify-content:center;">
                        <button type="button" class="btn-secondary" onclick="cerrarModalEliminar()"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="button" class="btn-danger" onclick="confirmarEliminar()"><i class="fas fa-trash-alt"></i> Borrar</button>
                    </div>
                </div>
            </div>
            <div id="successModal" class="success-modal" style="<?= ($mensaje && $tipoMensaje === 'success') ? 'display:flex;' : '' ?>">
                <div class="success-modal-content">
                    <i class="fas fa-check-circle"></i>
                    <h3>¡Éxito!</h3>
                    <p><?= isset($mensaje) ? htmlspecialchars($mensaje) : 'Operación realizada correctamente.' ?></p>
                    <button onclick="cerrarSuccessModal()">Aceptar</button>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let idClienteEliminar = null;

        // Búsqueda inline de clientes
        document.addEventListener('DOMContentLoaded', function() {
            const inputBusqueda = document.getElementById('busquedaCliente');
            if (inputBusqueda) {
                inputBusqueda.addEventListener('input', function() {
                    const filtro = inputBusqueda.value.toLowerCase();
                    const filas = document.querySelectorAll('#tablaClientes tbody tr');
                    filas.forEach(fila => {
                        let texto = fila.textContent.toLowerCase();
                        if (texto.includes(filtro)) {
                            fila.style.display = '';
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                });
            }
        });

        function formatearIdentificacion(input) {
            input.value = input.value.toUpperCase();
            input.value = input.value.replace(/[^A-Z0-9-]/g, '');
        }

        function formatearTelefono(input) {
            input.value = input.value.replace(/[^0-9-]/g, '');
        }

        function validarEmail(input) {
            const email = input.value.trim();
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const errorSpanId = input.id + "-error";
            const errorSpan = document.getElementById(errorSpanId);

            if (email === "") {
                errorSpan.style.display = "none";
                input.style.borderColor = "#ddd";
            } else if (!regex.test(email)) {
                errorSpan.style.display = "block";
                input.style.borderColor = "#dc3545";
            } else {
                errorSpan.style.display = "none";
                input.style.borderColor = "#ddd";
            }
        }


        // Función para hacer drag de los modales
        function hacerDraggable(modalId, headerSelector) {
            const modal = document.getElementById(modalId);
            const header = modal.querySelector(headerSelector);
            let offsetX = 0, offsetY = 0, mouseX = 0, mouseY = 0;
            if (!header) return;
            header.style.cursor = 'move';
            header.onmousedown = function(e) {
                e.preventDefault();
                mouseX = e.clientX;
                mouseY = e.clientY;
                document.onmousemove = function(e2) {
                    e2.preventDefault();
                    offsetX = e2.clientX - mouseX;
                    offsetY = e2.clientY - mouseY;
                    const rect = modal.querySelector('.modal-content');
                    let style = window.getComputedStyle(rect);
                    let left = parseInt(style.left);
                    let top = parseInt(style.top);
                    rect.style.left = (left + offsetX) + 'px';
                    rect.style.top = (top + offsetY) + 'px';
                    mouseX = e2.clientX;
                    mouseY = e2.clientY;
                };
                document.onmouseup = function() {
                    document.onmousemove = null;
                    document.onmouseup = null;
                };
            };
        }

        function abrirModalNuevoCliente() {
            document.getElementById('modalNuevoCliente').style.display = 'block';
        }

        function cerrarModalNuevoCliente() {
            document.getElementById('modalNuevoCliente').style.display = 'none';
        }

        function abrirModalEditarCliente(idcliente) {
            fetch(`clientes.php?idcliente=${idcliente}`)
                .then(response => response.json())
                .then(cliente => {
                    document.getElementById('editar_idcliente').value = cliente.idcliente;
                    document.getElementById('editar_nombre').value = cliente.nombre;
                    document.getElementById('editar_apellido').value = cliente.apellido;
                    document.getElementById('editar_identificacion').value = cliente.identificacion;
                    document.getElementById('editar_tipo_identificacion').value = cliente.tipo_identificacion;
                    document.getElementById('editar_telefono').value = cliente.telefono;
                    document.getElementById('editar_email').value = cliente.email;
                    document.getElementById('editar_direccion').value = cliente.direccion;
                    document.getElementById('editar_ciudad').value = cliente.ciudad;
                    document.getElementById('modalEditarCliente').style.display = 'block';
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudo cargar la información del cliente.', 'error');
                });
        }

        function cerrarModalEditarCliente() {
            document.getElementById('modalEditarCliente').style.display = 'none';
        }

        function abrirModalEliminar(id, nombre) {
            idClienteEliminar = id;
            document.getElementById('nombreClienteEliminar').textContent = nombre;
            document.getElementById('modalEliminarCliente').style.display = 'block';
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminarCliente').style.display = 'none';
            idClienteEliminar = null;
        }

        function confirmarEliminar() {
            if (idClienteEliminar !== null) {
                window.location.href = `?eliminar=${idClienteEliminar}`;
            }
        }

        function cerrarSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.reload();
        }

        function cambiarPorPagina(valor) {
            const url = new URL(window.location);
            url.searchParams.set('por_pagina', valor);
            url.searchParams.set('pagina', '1');
            window.location.href = url.toString();
        }

        window.onclick = function(event) {
            const modals = ['modalNuevoCliente', 'modalEditarCliente', 'modalEliminarCliente', 'successModal'];
            modals.forEach(id => {
                const el = document.getElementById(id);
                if (event.target === el) {
                    el.style.display = 'none';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('openModal').addEventListener('click', abrirModalNuevoCliente);
            // Hacer drag los modales principales
            hacerDraggable('modalNuevoCliente', '.modal-header');
            hacerDraggable('modalEditarCliente', '.modal-header');
            hacerDraggable('modalEliminarCliente', '.modal-header');
            <?php if ($mensaje && $tipoMensaje === 'success'): ?>
                document.getElementById('successModal').style.display = 'flex';
            <?php endif; ?>
        });
    </script>
</body>
</html>
