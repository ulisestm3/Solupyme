<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();

$mensaje = '';
$tipoMensaje = '';
// Mensajes desde sesión (registro/edición/eliminar)
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
if (!in_array($porPagina, [10, 50, 100])) $porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Contar total proveedores activos
try {
    $stmtCount = $conn->query("SELECT COUNT(*) as total FROM proveedores WHERE activo = b'1'");
    $totalRegistros = $stmtCount->fetch_assoc()['total'] ?? 0;
    $totalPaginas = max(1, ceil($totalRegistros / $porPagina));
} catch (Exception $e) {
    $totalRegistros = 0;
    $totalPaginas = 1;
}

// Obtener proveedores paginados
$proveedores = [];
try {
    $sql = "SELECT * FROM proveedores WHERE activo = b'1' ORDER BY idproveedor DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $porPagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $proveedores = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $proveedores = [];
}

// Guardar nuevo proveedor (desde modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_proveedor'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $idusuario = $_SESSION['idusuario'] ?? 1;

    if ($nombre === '') {
        $_SESSION['mensaje'] = "El nombre del proveedor es obligatorio.";
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: proveedores.php');
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO proveedores (nombre, contacto, telefono, correo, direccion, activo, usuarioregistra, fecharegistro) VALUES (?, ?, ?, ?, ?, b'1', ?, NOW())");
        $stmt->bind_param("sssssi", $nombre, $contacto, $telefono, $correo, $direccion, $idusuario);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Proveedor guardado correctamente.";
            $_SESSION['tipoMensaje'] = 'success';
            header('Location: proveedores.php');
            exit;
        } else {
            $_SESSION['mensaje'] = "Error al guardar el proveedor.";
            $_SESSION['tipoMensaje'] = 'error';
            header('Location: proveedores.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al guardar: " . $e->getMessage();
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: proveedores.php');
        exit;
    }
}

// Editar proveedor (desde modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_proveedor'])) {
    $idproveedor = intval($_POST['idproveedor'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $idusuario = $_SESSION['idusuario'] ?? 1;

    if ($idproveedor <= 0 || $nombre === '') {
        $_SESSION['mensaje'] = "Datos incompletos para edición.";
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: proveedores.php');
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE proveedores SET nombre = ?, contacto = ?, telefono = ?, correo = ?, direccion = ?, usuarioactualiza = ?, fechaactualizacion = NOW() WHERE idproveedor = ?");
        $stmt->bind_param("sssssii", $nombre, $contacto, $telefono, $correo, $direccion, $idusuario, $idproveedor);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Proveedor actualizado correctamente.";
            $_SESSION['tipoMensaje'] = 'success';
            header('Location: proveedores.php');
            exit;
        } else {
            $_SESSION['mensaje'] = "Error al actualizar el proveedor.";
            $_SESSION['tipoMensaje'] = 'error';
            header('Location: proveedores.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: proveedores.php');
        exit;
    }
}

// Eliminar lógico (GET)
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $usuarioactualiza = $_SESSION['idusuario'] ?? 1;

    if ($idEliminar > 0) {
        try {
            $stmt = $conn->prepare("UPDATE proveedores SET activo = b'0', usuarioactualiza = ?, fechaactualizacion = NOW() WHERE idproveedor = ?");
            $stmt->bind_param("ii", $usuarioactualiza, $idEliminar);
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Proveedor eliminado correctamente.";
                $_SESSION['tipoMensaje'] = 'success';
                header('Location: proveedores.php');
                exit;
            } else {
                $_SESSION['mensaje'] = "Error al eliminar el proveedor.";
                $_SESSION['tipoMensaje'] = 'error';
                header('Location: proveedores.php');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
            $_SESSION['tipoMensaje'] = 'error';
            header('Location: proveedores.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proveedores - AWFerreteria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Copiado exactamente los estilos del ejemplo (movimientos/productos) */
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
        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); overflow: auto; animation: fadeIn 0.3s; }
        .modal-content { background-color: #ffffff; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); padding: 20px 25px; border-radius: 10px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border: none; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #eee; cursor:move; }
        .modal-header h3 { color:#333; font-size:1.5rem; display:flex; align-items:center; }
        .modal-header h3 i { margin-right:10px; }
        .close { color:#aaa; font-size:24px; font-weight:bold; cursor:pointer; transition:color 0.3s; }
        .close:hover { color:#333; }
        .modal-body { margin-bottom:20px; }
        .modal-body label { display:block; margin-bottom:8px; font-weight:600; color:#444; }
        .modal-body input[type="text"], .modal-body select, .modal-body textarea { width:100%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px; font-size:14px; transition:border-color 0.3s; }
        .modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus { border-color:#007bff; outline:none; box-shadow:0 0 0 3px rgba(0,123,255,0.25); }
        .modal-footer { display:flex; justify-content:flex-end; gap:10px; }
        .modal-footer button { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px; border-radius:6px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.3s; border:none; }
        .modal-footer button i { margin-right:8px; }
        .btn-primary { background-color:#007bff; color:white; } .btn-primary:hover { background-color:#0056b3; }
        .btn-secondary { background-color:#f8f9fa; color:#333; border:1px solid #ddd; } .btn-secondary:hover { background-color:#e2e6ea; }
        .btn-danger { background-color:#dc3545; color:white; } .btn-danger:hover { background-color:#c82333; }
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
        /* Paginación */
        .pagination-container { display:flex; justify-content:space-between; align-items:center; margin-top:15px; font-size:13px; color:#555; }
        .pagination { display:flex; list-style:none; margin:0; padding:0; gap:5px; }
        .pagination a, .pagination span { padding:5px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#007bff; font-size:13px; }
        .pagination a:hover { background-color:#007bff; color:white; }
        .pagination .current { background-color:#007bff; color:white; font-weight:bold; }
        .pagination .disabled { color:#ccc; cursor:not-allowed; }
        .per-page-select { padding:5px; border:1px solid #ddd; border-radius:4px; font-size:13px; }
        /* Action buttons */
        .action-btn { display:inline-flex; align-items:center; justify-content:center; padding:5px 8px; border-radius:4px; margin-right:5px; text-decoration:none; font-size:13px; transition:all .3s; border:none; cursor:pointer; background:none; color:inherit; }
        .action-btn i { margin-right:0; font-size:14px; } .action-btn:hover { transform:scale(1.05); }
        .edit-btn { color:#0d6efd; } .delete-btn { color:#dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/sidebar_inventario.php'); ?>

        <main class="main-content">
            <h3><i class="fas fa-truck"></i> Gestión de Proveedores</h3>

            <?php if (!empty($mensaje) && $tipoMensaje === 'error'): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($mensaje) ?></span>
                </div>
            <?php endif; ?>

            <button class="btn" id="openModal"><i class="fas fa-plus"></i> Agregar Proveedor</button>

            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($proveedores)): ?>
                    <?php foreach ($proveedores as $prov): ?>
                        <tr>
                            <td><?= htmlspecialchars($prov['nombre']) ?></td>
                            <td><?= htmlspecialchars($prov['contacto']) ?></td>
                            <td><?= htmlspecialchars($prov['telefono']) ?></td>
                            <td><?= htmlspecialchars($prov['correo']) ?></td>
                            <td><?= htmlspecialchars($prov['direccion']) ?></td>
                            <td>
                                <button class="action-btn edit-btn" onclick='abrirModalEditar(<?= htmlspecialchars(json_encode($prov), ENT_QUOTES) ?>)' title="Editar"><i class="fas fa-pencil-alt"></i></button>
                                <button class="action-btn delete-btn" onclick="abrirModalEliminar(<?= $prov['idproveedor'] ?>, '<?= htmlspecialchars($prov['nombre'], ENT_QUOTES) ?>')" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No hay proveedores registrados.</td></tr>
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

            <!-- Modal Nuevo Proveedor -->
            <div id="modalNuevoProveedor" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-plus-circle"></i> Nuevo Proveedor</h3>
                        <span class="close" onclick="cerrarModalNuevo()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="formNuevoProveedor">
                            <label for="nombre">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" required placeholder="Nombre del proveedor">

                            <label for="contacto">Contacto</label>
                            <input type="text" name="contacto" id="contacto" placeholder="Nombre de la persona de contacto">

                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" placeholder="Teléfono">

                            <label for="correo">Correo</label>
                            <input type="text" name="correo" id="correo" placeholder="Correo electrónico">

                            <label for="direccion">Dirección</label>
                            <textarea name="direccion" id="direccion" rows="3" placeholder="Dirección del proveedor"></textarea>

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModalNuevo()"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" class="btn-primary" name="guardar_proveedor"><i class="fas fa-save"></i> Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Proveedor -->
            <div id="modalEditarProveedor" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-pencil-alt"></i> Editar Proveedor</h3>
                        <span class="close" onclick="cerrarModalEditar()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="formEditarProveedor">
                            <input type="hidden" name="idproveedor" id="editar_idproveedor">
                            <label for="editar_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="editar_nombre" required>

                            <label for="editar_contacto">Contacto</label>
                            <input type="text" name="contacto" id="editar_contacto">

                            <label for="editar_telefono">Teléfono</label>
                            <input type="text" name="telefono" id="editar_telefono">

                            <label for="editar_correo">Correo</label>
                            <input type="text" name="correo" id="editar_correo">

                            <label for="editar_direccion">Dirección</label>
                            <textarea name="direccion" id="editar_direccion" rows="3"></textarea>

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModalEditar()"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" class="btn-primary" name="editar_proveedor"><i class="fas fa-sync-alt"></i> Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Eliminar Proveedor -->
            <div id="modalEliminarProveedor" class="modal">
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
                            ¿Estás seguro que deseas eliminar el proveedor <strong id="nombreProveedorEliminar"></strong>?
                        </p>
                        <p style="text-align:center; font-size:14px; color:#666;">Esta acción no se puede deshacer y el proveedor dejará de estar disponible en el sistema.</p>
                    </div>
                    <div class="modal-footer" style="justify-content:center;">
                        <button type="button" class="btn-secondary" onclick="cerrarModalEliminar()"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="button" class="btn-danger" onclick="confirmarEliminar()"><i class="fas fa-trash-alt"></i> Eliminar</button>
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

            <div id="warningModal" class="warning-modal">
                <div class="warning-modal-content">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Advertencia</h3>
                    <p id="warningMessage">Mensaje de advertencia</p>
                    <button onclick="cerrarWarningModal()">Aceptar</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        let idProveedorEliminar = null;

        function mostrarModalNuevo() { document.getElementById('modalNuevoProveedor').style.display = 'block'; }
        function cerrarModalNuevo() { document.getElementById('modalNuevoProveedor').style.display = 'none'; }

        function abrirModalEditar(proveedor) {
            document.getElementById('editar_idproveedor').value = proveedor.idproveedor;
            document.getElementById('editar_nombre').value = proveedor.nombre || '';
            document.getElementById('editar_contacto').value = proveedor.contacto || '';
            document.getElementById('editar_telefono').value = proveedor.telefono || '';
            document.getElementById('editar_correo').value = proveedor.correo || '';
            document.getElementById('editar_direccion').value = proveedor.direccion || '';
            document.getElementById('modalEditarProveedor').style.display = 'block';
        }
        function cerrarModalEditar() { document.getElementById('modalEditarProveedor').style.display = 'none'; }

        function abrirModalEliminar(id, nombre) {
            idProveedorEliminar = id;
            document.getElementById('nombreProveedorEliminar').textContent = nombre;
            document.getElementById('modalEliminarProveedor').style.display = 'block';
        }
        function cerrarModalEliminar() { document.getElementById('modalEliminarProveedor').style.display = 'none'; idProveedorEliminar = null; }
        function confirmarEliminar() {
            if (idProveedorEliminar !== null) {
                window.location.href = '?eliminar=' + idProveedorEliminar;
            }
        }

        function cerrarSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'none';
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                // limpiar parámetros si existen
                url.searchParams.delete('success');
                window.history.replaceState({path: url.href}, '', url.href);
            }
            // recargar para refrescar lista después de cerrar
            window.location.reload();
        }

        function mostrarWarningModal(mensaje) {
            const warningModal = document.getElementById('warningModal');
            const warningMessage = document.getElementById('warningMessage');
            warningMessage.textContent = mensaje;
            warningModal.style.display = 'flex';
        }
        function cerrarWarningModal() { document.getElementById('warningModal').style.display = 'none'; }

        function cambiarPorPagina(valor) {
            const url = new URL(window.location);
            url.searchParams.set('por_pagina', valor);
            url.searchParams.set('pagina', '1');
            window.location.href = url.toString();
        }

        // Cerrar modales haciendo clic fuera
        window.onclick = function(event) {
            const modalNuevo = document.getElementById('modalNuevoProveedor');
            const modalEditar = document.getElementById('modalEditarProveedor');
            const modalEliminar = document.getElementById('modalEliminarProveedor');
            const successModal = document.getElementById('successModal');
            const warningModal = document.getElementById('warningModal');
            if (event.target === modalNuevo) cerrarModalNuevo();
            if (event.target === modalEditar) cerrarModalEditar();
            if (event.target === modalEliminar) cerrarModalEliminar();
            if (event.target === successModal) cerrarSuccessModal();
            if (event.target === warningModal) cerrarWarningModal();
        }

        // Hacer modales arrastrables (igual que en tu ejemplo)
        function makeDraggable(element, dragHandle) {
            let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
            dragHandle.onmousedown = dragMouseDown;
            function dragMouseDown(e) {
                e = e || window.event;
                e.preventDefault();
                pos3 = e.clientX;
                pos4 = e.clientY;
                document.onmouseup = closeDragElement;
                document.onmousemove = elementDrag;
            }
            function elementDrag(e) {
                e = e || window.event;
                e.preventDefault();
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                element.style.top = (element.offsetTop - pos2) + "px";
                element.style.left = (element.offsetLeft - pos1) + "px";
            }
            function closeDragElement() { document.onmouseup = null; document.onmousemove = null; }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('openModal').addEventListener('click', mostrarModalNuevo);

            const modales = [
                { id: 'modalNuevoProveedor' },
                { id: 'modalEditarProveedor' },
                { id: 'modalEliminarProveedor' }
            ];
            modales.forEach(m => {
                const modalElement = document.getElementById(m.id);
                if (modalElement) {
                    const modalContent = modalElement.querySelector('.modal-content');
                    const dragHandle = modalElement.querySelector('.modal-header');
                    if (modalContent && dragHandle) {
                        makeDraggable(modalContent, dragHandle);
                    }
                }
            });

            <?php if ($mensaje && $tipoMensaje === 'success'): ?>
                document.getElementById('successModal').style.display = 'flex';
            <?php endif; ?>
        });
    </script>
</body>
</html>
