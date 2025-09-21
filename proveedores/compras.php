<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();
$conn = getConnection();

// Función para obtener proveedores
function obtenerProveedores($conn) {
    $sql = "SELECT idproveedor, nombre FROM proveedores WHERE activo = b'1'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener productos
function obtenerProductos($conn) {
    $sql = "SELECT idproducto, nombre FROM productos WHERE activo = b'1'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener detalles de una compra (para incrustar en el botón)
function obtenerDetallesCompra($conn, $idcompra) {
    $sql = "
        SELECT cd.idproducto, cd.cantidad, cd.precio_unitario, p.nombre AS producto
        FROM compras_detalle cd
        INNER JOIN productos p ON cd.idproducto = p.idproducto
        WHERE cd.idcompra = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idcompra);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener compra completa (para edición)
function obtenerCompraCompleta($conn, $idcompra) {
    $sql = "
        SELECT c.idcompra, c.idproveedor, c.numero_factura, c.fecha_factura, c.iva
        FROM compras c
        WHERE c.idcompra = ? AND c.activo = b'1'
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idcompra);
    $stmt->execute();
    $result = $stmt->get_result();
    $compra = $result->fetch_assoc();
    if (!$compra) return null;

    // Obtener detalles
    $detalles = obtenerDetallesCompra($conn, $idcompra);
    $compra['detalles'] = $detalles;
    return $compra;
}

// Mensajes desde sesión (registro/edición/eliminar)
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
if (!in_array($porPagina, [10, 50, 100])) $porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Contar total compras activas
try {
    $stmtCount = $conn->query("SELECT COUNT(*) as total FROM compras WHERE activo = b'1'");
    $totalRegistros = $stmtCount->fetch_assoc()['total'] ?? 0;
    $totalPaginas = max(1, ceil($totalRegistros / $porPagina));
} catch (Exception $e) {
    $totalRegistros = 0;
    $totalPaginas = 1;
}

// Obtener compras paginadas
$compras = [];
try {
    $sql = "
        SELECT c.idcompra, c.numero_factura, c.fecha_factura, c.subtotal, c.iva, c.total,
               p.nombre AS proveedor, c.fecharegistro
        FROM compras c
        INNER JOIN proveedores p ON c.idproveedor = p.idproveedor
        WHERE c.activo = b'1'
        ORDER BY c.fecharegistro DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $porPagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $compras = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $compras = [];
}

// Eliminar lógico (GET)
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $usuarioactualiza = $_SESSION['idusuario'] ?? 1;

    if ($idEliminar > 0) {
        $conn->begin_transaction();

        try {
            // 1. Verificar que la compra está activa (no anulada aún)
            $sqlCheck = "SELECT activo FROM compras WHERE idcompra = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $idEliminar);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            if ($resultCheck->num_rows === 0) {
                throw new Exception("La compra no existe.");
            }
            $row = $resultCheck->fetch_assoc();
            if ($row['activo'] == 0) {
                throw new Exception("La compra ya está anulada.");
            }

            // 2. Anular la compra
            $sqlAnular = "UPDATE compras SET activo = b'0', usuarioactualiza = ?, fechaactualizacion = NOW() WHERE idcompra = ?";
            $stmtAnular = $conn->prepare($sqlAnular);
            $stmtAnular->bind_param("ii", $usuarioactualiza, $idEliminar);
            $stmtAnular->execute();

            // 3. Obtener detalles de la compra para ajustar movimientos
            $sqlDetalles = "SELECT idproducto, cantidad FROM compras_detalle WHERE idcompra = ?";
            $stmtDetalles = $conn->prepare($sqlDetalles);
            $stmtDetalles->bind_param("i", $idEliminar);
            $stmtDetalles->execute();
            $resultDetalles = $stmtDetalles->get_result();

            // 4. Insertar movimientos de salida para ajustar stock
            $sqlMovimiento = "INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, idusuario) VALUES (?, 'salida', ?, 'Ajuste de compra anulada', ?)";
            $stmtMovimiento = $conn->prepare($sqlMovimiento);

            while ($detalle = $resultDetalles->fetch_assoc()) {
                $idproducto = $detalle['idproducto'];
                $cantidad = $detalle['cantidad'];
                $stmtMovimiento->bind_param("iii", $idproducto, $cantidad, $usuarioactualiza);
                $stmtMovimiento->execute();
            }

            $conn->commit();

            $_SESSION['mensaje'] = "Compra anulada correctamente y stock ajustado.";
            $_SESSION['tipoMensaje'] = 'success';
            header('Location: compras.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['mensaje'] = "Error al anular la compra: " . $e->getMessage();
            $_SESSION['tipoMensaje'] = 'error';
            header('Location: compras.php');
            exit;
        }
    }
}

// Manejar la creación de una nueva compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_compra'])) {
    $idproveedor = $_POST['proveedor'];
    $numero_factura = $_POST['numero_factura'];
    $fecha_factura = $_POST['fecha_factura'];
    $detalles = json_decode($_POST['detalles'], true);

    $conn->begin_transaction();

    try {
        // Insertar la compra
        $sqlCompra = "INSERT INTO compras (idproveedor, numero_factura, fecha_factura, idusuario) VALUES (?, ?, ?, ?)";
        $stmtCompra = $conn->prepare($sqlCompra);
        $idusuario = $_SESSION['idusuario'] ?? 1;
        $stmtCompra->bind_param("issi", $idproveedor, $numero_factura, $fecha_factura, $idusuario);
        $stmtCompra->execute();
        $idcompra = $stmtCompra->insert_id;

        // Insertar los detalles de la compra
        $sqlDetalle = "INSERT INTO compras_detalle (idcompra, idproducto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmtDetalle = $conn->prepare($sqlDetalle);

        $subtotalTotal = 0;
        foreach ($detalles as $detalle) {
            $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
            $subtotalTotal += $subtotal;
            $stmtDetalle->bind_param("iiidd", $idcompra, $detalle['idproducto'], $detalle['cantidad'], $detalle['precio_unitario'], $subtotal);
            $stmtDetalle->execute();

            // Insertar el movimiento de inventario
            $sqlMovimiento = "INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, idusuario) VALUES (?, 'entrada', ?, 'Compra de producto', ?)";
            $stmtMovimiento = $conn->prepare($sqlMovimiento);
            $stmtMovimiento->bind_param("iii", $detalle['idproducto'], $detalle['cantidad'], $idusuario);
            $stmtMovimiento->execute();
        }

        // Actualizar el subtotal, IVA y total de la compra
        $iva = $subtotalTotal * 0.15; // IVA del 15%
        $total = $subtotalTotal + $iva;

        $sqlActualizarCompra = "UPDATE compras SET subtotal = ?, iva = ?, total = ? WHERE idcompra = ?";
        $stmtActualizarCompra = $conn->prepare($sqlActualizarCompra);
        $stmtActualizarCompra->bind_param("dddi", $subtotalTotal, $iva, $total, $idcompra);
        $stmtActualizarCompra->execute();

        $conn->commit();

        $_SESSION['mensaje'] = "Compra registrada correctamente.";
        $_SESSION['tipoMensaje'] = 'success';
        header('Location: compras.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['mensaje'] = "Error al registrar la compra: " . $e->getMessage();
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: compras.php');
        exit;
    }
}

// Manejar la edición de una compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_compra'])) {
    $idcompra = $_POST['idcompra'];
    $idproveedor = $_POST['proveedor'];
    $numero_factura = $_POST['numero_factura'];
    $fecha_factura = $_POST['fecha_factura'];
    $detalles = json_decode($_POST['detalles'], true);
    $calcular_iva = isset($_POST['calcular_iva']) ? true : false;

    $conn->begin_transaction();

    try {
        // 1. Actualizar datos básicos de la compra
        $sqlCompra = "UPDATE compras SET idproveedor = ?, numero_factura = ?, fecha_factura = ? WHERE idcompra = ?";
        $stmtCompra = $conn->prepare($sqlCompra);
        $stmtCompra->bind_param("issi", $idproveedor, $numero_factura, $fecha_factura, $idcompra);
        $stmtCompra->execute();

        // 2. Obtener detalles antiguos para ajustar stock (salida)
        $sqlDetallesAnt = "SELECT idproducto, cantidad FROM compras_detalle WHERE idcompra = ?";
        $stmtDetallesAnt = $conn->prepare($sqlDetallesAnt);
        $stmtDetallesAnt->bind_param("i", $idcompra);
        $stmtDetallesAnt->execute();
        $resultDetallesAnt = $stmtDetallesAnt->get_result();

        $usuarioactualiza = $_SESSION['idusuario'] ?? 1;

        $sqlInsertMovimiento = "INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, idusuario) VALUES (?, ?, ?, ?, ?)";
        $stmtMovimiento = $conn->prepare($sqlInsertMovimiento);

        // Insertar movimientos tipo 'salida' para revertir stock de la compra anterior
        while ($detalleAnt = $resultDetallesAnt->fetch_assoc()) {
            $idproducto = $detalleAnt['idproducto'];
            $cantidad = $detalleAnt['cantidad'];
            $tipo = 'salida';
            $comentario = 'Anulación por actualización';
            $stmtMovimiento->bind_param("isisi", $idproducto, $tipo, $cantidad, $comentario, $usuarioactualiza);
            $stmtMovimiento->execute();
        }

        // 3. Eliminar detalles antiguos
        $sqlEliminarDetalles = "DELETE FROM compras_detalle WHERE idcompra = ?";
        $stmtEliminarDetalles = $conn->prepare($sqlEliminarDetalles);
        $stmtEliminarDetalles->bind_param("i", $idcompra);
        $stmtEliminarDetalles->execute();

        // 4. Insertar nuevos detalles y movimientos tipo 'entrada'
        $sqlDetalle = "INSERT INTO compras_detalle (idcompra, idproducto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmtDetalle = $conn->prepare($sqlDetalle);

        $subtotalTotal = 0;

        foreach ($detalles as $detalle) {
            $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
            $subtotalTotal += $subtotal;

            $stmtDetalle->bind_param("iiidd", $idcompra, $detalle['idproducto'], $detalle['cantidad'], $detalle['precio_unitario'], $subtotal);
            $stmtDetalle->execute();

            $idproducto = $detalle['idproducto'];
            $cantidad = $detalle['cantidad'];
            $tipo = 'entrada';
            $comentario = 'Actualización de compra';
            $stmtMovimiento->bind_param("isisi", $idproducto, $tipo, $cantidad, $comentario, $usuarioactualiza);
            $stmtMovimiento->execute();
        }

        // 5. Calcular IVA y total
        $iva = $calcular_iva ? $subtotalTotal * 0.15 : 0;
        $total = $subtotalTotal + $iva;

        $sqlActualizarCompra = "UPDATE compras SET subtotal = ?, iva = ?, total = ? WHERE idcompra = ?";
        $stmtActualizarCompra = $conn->prepare($sqlActualizarCompra);
        $stmtActualizarCompra->bind_param("dddi", $subtotalTotal, $iva, $total, $idcompra);
        $stmtActualizarCompra->execute();

        $conn->commit();

        $_SESSION['mensaje'] = "Compra actualizada correctamente.";
        $_SESSION['tipoMensaje'] = 'success';
        header('Location: compras.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['mensaje'] = "Error al actualizar la compra: " . $e->getMessage();
        $_SESSION['tipoMensaje'] = 'error';
        header('Location: compras.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Tus estilos existentes */
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
        /* Estilos para el modal */
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
        .action-btn i { margin-right:0; font-size:14px; }
        .action-btn:hover { transform:scale(1.05); }
        .edit-btn { color:#0d6efd; }
        .delete-btn { color:#dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/sidebar_inventario.php'); ?>
        <main class="main-content">
            <h3><i class="fas fa-shopping-cart"></i> Gestión de Compras</h3>
            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo $tipoMensaje === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            <div style="display: flex; gap: 10px; align-items: center; margin: 10px 0 20px 0; flex-wrap: wrap;">
                <button class="btn" id="openModal" style="margin-bottom:0;"><i class="fas fa-plus"></i> Nueva Compra</button>
                <input type="text" id="busquedaCompra" class="form-control" placeholder="Buscar compra..." style="max-width:350px;width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:4px;font-size:14px;">
            </div>
            <table id="tablaCompras">
                <thead>
                    <tr>
                        <th hidden="true">ID</th>
                        <th>Proveedor</th>
                        <th>No. Factura</th>
                        <th>Fecha Factura</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyCompras">
                <?php if (!empty($compras)): ?>
                    <?php foreach ($compras as $compra): 
                        // Cargar datos completos para edición
                        $datosCompra = obtenerCompraCompleta($conn, $compra['idcompra']);
                        $datosJson = json_encode($datosCompra);
                    ?>
                        <tr>
                            <td hidden="true"><?= htmlspecialchars($compra['idcompra']) ?></td>
                            <td><?= htmlspecialchars($compra['proveedor']) ?></td>
                            <td><?= htmlspecialchars($compra['numero_factura']) ?></td>
                            <td><?= htmlspecialchars($compra['fecha_factura']) ?></td>
                            <td><?= number_format($compra['subtotal'], 2) ?></td>
                            <td><?= number_format($compra['iva'], 2) ?></td>
                            <td><?= number_format($compra['total'], 2) ?></td>
                            <td><?= htmlspecialchars($compra['fecharegistro']) ?></td>
                            <td>
                                <button class="action-btn edit-btn" 
                                        onclick="abrirModalEditarCompra(<?= $compra['idcompra'] ?>, <?= htmlspecialchars($datosJson, ENT_QUOTES) ?>)" 
                                        title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="action-btn delete-btn" 
                                        onclick="abrirModalEliminar(<?= $compra['idcompra'] ?>, '<?= htmlspecialchars($compra['numero_factura'], ENT_QUOTES) ?>')" 
                                        title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9">No hay compras registradas.</td></tr>
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

            <!-- Modal Nueva Compra -->
<div id="modalNuevaCompra" class="modal">
    <div class="modal-content" style="max-width: 700px; border-radius: 12px;">
        <form method="post" action="compras.php">
            <!-- Encabezado -->
            <div class="modal-header" style="padding: 15px 25px; border-bottom: 1px solid #ddd;">
                <h3 style="margin: 0; color: #004080; font-size: 1.4rem;">
                    <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i> Nueva Compra
                </h3>
                <span class="close" style="font-size: 28px;" onclick="cerrarModalNuevaCompra()">&times;</span>
            </div>

            <div class="modal-body" style="padding: 20px;">
                <!-- Datos Generales (2 columnas) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                    <div>
                        <label for="proveedor" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Proveedor</label>
                        <select id="proveedor" name="proveedor" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            <option value="">Seleccionar proveedor</option>
                            <?php
                            $proveedores = obtenerProveedores($conn);
                            foreach ($proveedores as $proveedor) {
                                echo "<option value='{$proveedor['idproveedor']}'>{$proveedor['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="numero_factura" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">No. Factura</label>
                        <input type="text" id="numero_factura" name="numero_factura" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>

                    <div>
                        <label for="fecha_factura" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Fecha Factura</label>
                        <input type="date" id="fecha_factura" name="fecha_factura" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>

                    <div style="display: flex; align-items: center; margin-top: 24px;">
                        <label for="calcular_iva" style="display: flex; align-items: center; font-size: 14px;">
                            <input type="checkbox" id="calcular_iva" name="calcular_iva" checked 
                                   style="margin-right: 6px; transform: scale(1.1);">
                            Incluir IVA (15%)
                        </label>
                    </div>
                </div>

                <!-- Sección: Agregar Producto (compacta y horizontal) -->
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 18px;">
                    <div style="display: grid; grid-template-columns: 2.5fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <!-- Producto -->
                        <div>
                            <label for="producto" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Producto</label>
                            <select id="producto" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">Seleccionar producto</option>
                                <?php
                                $productos = obtenerProductos($conn);
                                foreach ($productos as $producto) {
                                    echo "<option value='{$producto['idproducto']}'>{$producto['nombre']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label for="cantidad" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Cant.</label>
                            <input type="number" id="cantidad" min="1" placeholder="1" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>

                        <!-- Precio -->
                        <div>
                            <label for="precio_unitario" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Precio</label>
                            <input type="number" step="0.01" id="precio_unitario" placeholder="0.00" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>

                        <!-- Botón Agregar -->
                        <div>
                            <button type="button" id="agregarProducto"
                                    style="
                                        width: 100%;
                                        padding: 10px;
                                        background-color: #28a745;
                                        color: white;
                                        border: none;
                                        border-radius: 6px;
                                        font-size: 14px;
                                        font-weight: 600;
                                        cursor: pointer;
                                        transition: background-color 0.3s ease;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        gap: 6px;
                                    "
                                    onmouseover="this.style.backgroundColor='#218838'"
                                    onmouseout="this.style.backgroundColor='#28a745'">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de productos agregados -->
                <h4 style="margin: 0 0 12px 0; color: #004080; font-size: 1.1rem; font-weight: 600;">Productos Agregados</h4>
                <div style="overflow-y: auto; max-height: 200px; border: 1px solid #eee; border-radius: 6px;">
                    <table id="detalleCompra" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background-color: #007acc; color: white;">
                                <th style="padding: 8px 10px; text-align: left;">Producto</th>
                                <th style="padding: 8px 10px; text-align: left;">Cant.</th>
                                <th style="padding: 8px 10px; text-align: left;">Precio</th>
                                <th style="padding: 8px 10px; text-align: left;">Subtotal</th>
                                <th style="padding: 8px 10px; text-align: left;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Campos ocultos -->
                <input type="hidden" name="detalles" id="detalles">
                <input type="hidden" name="guardar_compra" value="1">
            </div>

            <!-- Botones -->
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #eee; text-align: right;">
                <button type="button" class="btn-secondary" onclick="cerrarModalNuevaCompra()"
                        style="padding: 8px 16px; font-size: 14px;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary"
                        style="padding: 8px 16px; font-size: 14px; background-color: #007bff;">
                    <i class="fas fa-save"></i> Guardar Compra
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Compra -->
<div id="modalEditarCompra" class="modal">
    <div class="modal-content" style="max-width: 700px; border-radius: 12px;">
        <form method="post" action="compras.php">
            <!-- Encabezado -->
            <div class="modal-header" style="padding: 15px 25px; border-bottom: 1px solid #ddd;">
                <h3 style="margin: 0; color: #004080; font-size: 1.4rem;">
                    <i class="fas fa-edit" style="margin-right: 8px;"></i> Editar Compra
                </h3>
                <span class="close" style="font-size: 28px;" onclick="cerrarModalEditarCompra()">&times;</span>
            </div>

            <div class="modal-body" style="padding: 20px;">
                <input type="hidden" name="idcompra" id="editar_idcompra">

                <!-- Datos Generales (2 columnas) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                    <div>
                        <label for="editar_proveedor" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Proveedor</label>
                        <select id="editar_proveedor" name="proveedor" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            <option value="">Seleccionar proveedor</option>
                            <?php
                            $proveedores = obtenerProveedores($conn);
                            foreach ($proveedores as $proveedor) {
                                echo "<option value='{$proveedor['idproveedor']}'>{$proveedor['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="editar_numero_factura" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">No. Factura</label>
                        <input type="text" id="editar_numero_factura" name="numero_factura" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>

                    <div>
                        <label for="editar_fecha_factura" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Fecha Factura</label>
                        <input type="date" id="editar_fecha_factura" name="fecha_factura" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>

                    <div style="display: flex; align-items: center; margin-top: 24px;">
                        <label for="editar_calcular_iva" style="display: flex; align-items: center; font-size: 14px;">
                            <input type="checkbox" id="editar_calcular_iva" name="calcular_iva" checked 
                                   style="margin-right: 6px; transform: scale(1.1);">
                            Incluir IVA (15%)
                        </label>
                    </div>
                </div>

                <!-- Sección: Agregar Producto (compacta y horizontal) -->
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 18px;">
                    <div style="display: grid; grid-template-columns: 2.5fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <!-- Producto -->
                        <div>
                            <label for="editar_producto" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Producto</label>
                            <select id="editar_producto" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">Seleccionar producto</option>
                                <?php
                                $productos = obtenerProductos($conn);
                                foreach ($productos as $producto) {
                                    echo "<option value='{$producto['idproducto']}'>{$producto['nombre']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label for="editar_cantidad" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Cant.</label>
                            <input type="number" id="editar_cantidad" min="1" placeholder="1" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>

                        <!-- Precio -->
                        <div>
                            <label for="editar_precio_unitario" style="font-weight: 600; color: #444; margin-bottom: 5px; display: block;">Precio</label>
                            <input type="number" step="0.01" id="editar_precio_unitario" placeholder="0.00" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>

                        <!-- Botón Agregar -->
                        <div>
                            <button type="button" id="agregarProductoEditar"
                                    style="
                                        width: 100%;
                                        padding: 10px;
                                        background-color: #28a745;
                                        color: white;
                                        border: none;
                                        border-radius: 6px;
                                        font-size: 14px;
                                        font-weight: 600;
                                        cursor: pointer;
                                        transition: background-color 0.3s ease;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        gap: 6px;
                                    "
                                    onmouseover="this.style.backgroundColor='#218838'"
                                    onmouseout="this.style.backgroundColor='#28a745'">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de productos agregados -->
                <h4 style="margin: 0 0 12px 0; color: #004080; font-size: 1.1rem; font-weight: 600;">Productos Agregados</h4>
                <div style="overflow-y: auto; max-height: 200px; border: 1px solid #eee; border-radius: 6px;">
                    <table id="editar_detalleCompra" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background-color: #007acc; color: white;">
                                <th style="padding: 8px 10px; text-align: left;">Producto</th>
                                <th style="padding: 8px 10px; text-align: left;">Cant.</th>
                                <th style="padding: 8px 10px; text-align: left;">Precio</th>
                                <th style="padding: 8px 10px; text-align: left;">Subtotal</th>
                                <th style="padding: 8px 10px; text-align: left;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Campos ocultos -->
                <input type="hidden" name="detalles" id="editar_detalles">
                <input type="hidden" name="editar_compra" value="1">
            </div>

            <!-- Botones -->
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #eee; text-align: right;">
                <button type="button" class="btn-secondary" onclick="cerrarModalEditarCompra()"
                        style="padding: 8px 16px; font-size: 14px;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary"
                        style="padding: 8px 16px; font-size: 14px; background-color: #007bff;">
                    <i class="fas fa-save"></i> Actualizar Compra
                </button>
            </div>
        </form>
    </div>
</div>

            <!-- Modal Eliminar Compra -->
            <div id="modalEliminarCompra" class="modal">
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
                            ¿Estás seguro que deseas eliminar la compra con factura <strong id="numeroFacturaEliminar"></strong>?
                        </p>
                        <p style="text-align:center; font-size:14px; color:#666;">Esta acción no se puede deshacer y la compra dejará de estar disponible en el sistema.</p>
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
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let idCompraEliminar = null;
        let detallesCompra = [];
        let detallesCompraEditar = [];

        function abrirModalNuevaCompra() {
            document.getElementById('modalNuevaCompra').style.display = 'block';
        }

        function cerrarModalNuevaCompra() {
            document.getElementById('modalNuevaCompra').style.display = 'none';
        }

        document.getElementById('agregarProducto').addEventListener('click', function() {
            const productoSelect = document.getElementById('producto');
            const cantidadInput = document.getElementById('cantidad');
            const precioUnitarioInput = document.getElementById('precio_unitario');

            const productoId = productoSelect.value;
            const productoNombre = productoSelect.options[productoSelect.selectedIndex]?.text;
            const cantidad = cantidadInput.value;
            const precioUnitario = precioUnitarioInput.value;

            if (productoId && cantidad && precioUnitario && productoNombre) {
                const subtotal = cantidad * precioUnitario;
                const tbody = document.querySelector('#detalleCompra tbody');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${productoNombre}</td>
                    <td>${cantidad}</td>
                    <td>${precioUnitario}</td>
                    <td>${subtotal.toFixed(2)}</td>
                    <td><button class="btn-delete" data-id="${productoId}"><i class="fas fa-trash"></i></button></td>
                `;
                tbody.appendChild(tr);

                detallesCompra.push({
                    idproducto: productoId,
                    cantidad: cantidad,
                    precio_unitario: precioUnitario
                });
                document.getElementById('detalles').value = JSON.stringify(detallesCompra);

                productoSelect.value = '';
                cantidadInput.value = '';
                precioUnitarioInput.value = '';
            } else {
                Swal.fire('Error', 'Completa todos los campos', 'error');
            }
        });

        document.querySelector('#detalleCompra tbody').addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                const button = e.target.closest('.btn-delete');
                const productoId = button.getAttribute('data-id');
                detallesCompra = detallesCompra.filter(d => d.idproducto != productoId);
                document.getElementById('detalles').value = JSON.stringify(detallesCompra);
                button.closest('tr').remove();
            }
        });

        function abrirModalEditarCompra(idcompra, datos) {
            const modal = document.getElementById('modalEditarCompra');
            if (!modal) return;

            // Limpiar
            document.getElementById('editar_idcompra').value = idcompra;
            document.getElementById('editar_numero_factura').value = datos.numero_factura;
            document.getElementById('editar_fecha_factura').value = datos.fecha_factura;
            document.getElementById('editar_proveedor').value = datos.idproveedor;
            document.getElementById('editar_calcular_iva').checked = datos.iva > 0;

            const tbody = document.querySelector('#editar_detalleCompra tbody');
            tbody.innerHTML = '';
            detallesCompraEditar = [];

            datos.detalles.forEach(detalle => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${detalle.producto}</td>
                    <td>${detalle.cantidad}</td>
                    <td>${detalle.precio_unitario}</td>
                    <td>${(detalle.cantidad * detalle.precio_unitario).toFixed(2)}</td>
                    <td><button class="btn-delete" data-id="${detalle.idproducto}"><i class="fas fa-trash"></i></button></td>
                `;
                tbody.appendChild(tr);
                detallesCompraEditar.push({
                    idproducto: detalle.idproducto,
                    cantidad: detalle.cantidad,
                    precio_unitario: detalle.precio_unitario
                });
            });

            document.getElementById('editar_detalles').value = JSON.stringify(detallesCompraEditar);
            modal.style.display = 'block';
        }

        function cerrarModalEditarCompra() {
            document.getElementById('modalEditarCompra').style.display = 'none';
        }

        function abrirModalEliminar(id, numeroFactura) {
            idCompraEliminar = id;
            document.getElementById('numeroFacturaEliminar').textContent = numeroFactura;
            document.getElementById('modalEliminarCompra').style.display = 'block';
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminarCompra').style.display = 'none';
            idCompraEliminar = null;
        }

        function confirmarEliminar() {
            if (idCompraEliminar !== null) {
                window.location.href = '?eliminar=' + idCompraEliminar;
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
            const modals = ['modalNuevaCompra', 'modalEliminarCompra', 'modalEditarCompra', 'successModal'];
            modals.forEach(id => {
                const el = document.getElementById(id);
                if (event.target === el) {
                    el.style.display = 'none';
                }
            });
        }

        // Búsqueda inline de compras
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('openModal').addEventListener('click', abrirModalNuevaCompra);

            // Hacer drag los modales principales
            hacerDraggable('modalNuevaCompra', '.modal-header');
            hacerDraggable('modalEditarCompra', '.modal-header');
            hacerDraggable('modalEliminarCompra', '.modal-header');

            // Búsqueda inline
            const inputBusqueda = document.getElementById('busquedaCompra');
            if (inputBusqueda) {
                inputBusqueda.addEventListener('input', function() {
                    const filtro = inputBusqueda.value.toLowerCase();
                    const filas = document.querySelectorAll('#tablaCompras tbody tr');
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

            // Eventos para edición
            document.getElementById('agregarProductoEditar').addEventListener('click', function() {
                const productoSelect = document.getElementById('editar_producto');
                const cantidadInput = document.getElementById('editar_cantidad');
                const precioUnitarioInput = document.getElementById('editar_precio_unitario');

                const productoId = productoSelect.value;
                const productoNombre = productoSelect.options[productoSelect.selectedIndex]?.text;
                const cantidad = cantidadInput.value;
                const precioUnitario = precioUnitarioInput.value;

                if (productoId && cantidad && precioUnitario && productoNombre) {
                    const subtotal = cantidad * precioUnitario;
                    const tbody = document.querySelector('#editar_detalleCompra tbody');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${productoNombre}</td>
                        <td>${cantidad}</td>
                        <td>${precioUnitario}</td>
                        <td>${subtotal.toFixed(2)}</td>
                        <td><button class="btn-delete" data-id="${productoId}"><i class="fas fa-trash"></i></button></td>
                    `;
                    tbody.appendChild(tr);

                    detallesCompraEditar.push({
                        idproducto: productoId,
                        cantidad: cantidad,
                        precio_unitario: precioUnitario
                    });
                    document.getElementById('editar_detalles').value = JSON.stringify(detallesCompraEditar);

                    productoSelect.value = '';
                    cantidadInput.value = '';
                    precioUnitarioInput.value = '';
                } else {
                    Swal.fire('Error', 'Completa todos los campos', 'error');
                }
            });

            document.querySelector('#editar_detalleCompra tbody').addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete')) {
                    const button = e.target.closest('.btn-delete');
                    const productoId = button.getAttribute('data-id');
                    detallesCompraEditar = detallesCompraEditar.filter(d => d.idproducto != productoId);
                    document.getElementById('editar_detalles').value = JSON.stringify(detallesCompraEditar);
                    button.closest('tr').remove();
                }
            });

            <?php if ($mensaje && $tipoMensaje === 'success'): ?>
                document.getElementById('successModal').style.display = 'flex';
            <?php endif; ?>
        });

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
    </script>
</body>
</html>