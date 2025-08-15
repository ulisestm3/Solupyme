<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();
$movimientos = [];
$totalRegistros = 0;

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
if (!in_array($porPagina, [10, 50, 100])) $porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Contar total
try {
    $sqlCount = "SELECT COUNT(*) as total FROM movimientos m
                 INNER JOIN productos p ON m.idproducto = p.idproducto
                 INNER JOIN usuarios u ON m.idusuario = u.idusuario";
    $stmtCount = $conn->query($sqlCount);
    $totalRegistros = $stmtCount->fetch_assoc()['total'];
    $totalPaginas = ceil($totalRegistros / $porPagina);
} catch (Exception $e) {
    $totalRegistros = 0;
    $totalPaginas = 1;
}

// Obtener movimientos con paginación
try {
    $sql = "SELECT m.idmovimiento, p.nombre AS producto, m.tipo, m.cantidad, m.comentario, m.fecha, u.usuario
            FROM movimientos m
            INNER JOIN productos p ON m.idproducto = p.idproducto
            INNER JOIN usuarios u ON m.idusuario = u.idusuario
            ORDER BY m.fecha DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $porPagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $movimientos = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    echo "Error al cargar movimientos: " . $e->getMessage();
}

$mensaje = '';
$tipoMensaje = '';
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

$productos = $conn->query("SELECT idproducto, nombre FROM productos WHERE activo = b'1'")
                  ->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idproducto = intval($_POST["idproducto"]);
    $tipo = $_POST["tipo"];
    $cantidad = intval($_POST["cantidad"]);
    $comentario = trim($_POST["comentario"]);
    $idusuario = $_SESSION['idusuario'] ?? 1;

    if ($cantidad <= 0 || $comentario === '') {
        $mensaje = "La cantidad debe ser mayor a cero y el comentario es obligatorio.";
        $tipoMensaje = "error";
    }

    if ($mensaje === '' && strtolower($tipo) === 'salida') {
        $stmt = $conn->prepare("SELECT stock FROM productos WHERE idproducto = ?");
        $stmt->bind_param("i", $idproducto);
        $stmt->execute();
        $stmt->bind_result($stockActual);
        if ($stmt->fetch()) {
            if ($stockActual <= 0) {
                $mensaje = "No hay stock disponible para este producto.";
                $tipoMensaje = "error";
            } elseif ($cantidad > $stockActual) {
                $mensaje = "La cantidad de salida ($cantidad) excede el stock disponible ($stockActual).";
                $tipoMensaje = "error";
            }
        } else {
            $mensaje = "Producto no encontrado.";
            $tipoMensaje = "error";
        }
        $stmt->close();
    }

    if ($mensaje === '') {
        try {
            $conn->begin_transaction();
            $insert = $conn->prepare("INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, fecha, idusuario) VALUES (?, ?, ?, ?, NOW(), ?)");
            $insert->bind_param("isisi", $idproducto, $tipo, $cantidad, $comentario, $idusuario);
            $insert->execute();
            $insert->close();
            $conn->commit();
            $_SESSION['mensaje'] = "Movimiento registrado correctamente.";
            $_SESSION['tipoMensaje'] = "success";
            header('Location: movimientos.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "Error al registrar el movimiento: " . $e->getMessage();
            $tipoMensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movimientos de Inventario - AWFerreteria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos existentes sin cambios */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
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
        .main-content {
            flex: 1;
            padding: 1rem 1.5rem;
            margin: 0.5rem;
            overflow-y: auto;
            font-size: 14px;
        }
        .main-content h3 {
            color: #004080;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .main-content h3 i {
            margin-right: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #004080;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 15px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2563eb;
        }
        .btn i {
            margin-right: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            font-size: 13px;
        }
        th, td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007acc;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .error-message i {
            margin-right: 10px;
            color: #721c24;
        }
        /* Estilos para el textarea de comentario */
        .comentario-container {
            position: relative;
            margin-bottom: 15px;
        }
        #comentario {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
            font-family: inherit;
            margin-bottom: 5px;
        }
        #contador-caracteres {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        /* Tooltip para comentarios largos */
        .comentario-tooltip {
            position: relative;
            display: inline-block;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        .comentario-tooltip:hover::after {
            content: attr(data-comentario-completo);
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 100%;
            background-color: #333;
            color: white;
            padding: 10px;
            border-radius: 4px;
            width: 300px;
            white-space: normal;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 100;
            font-size: 13px;
        }
        /* ESTILOS MODALES ARRASTRABLES */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
            animation: fadeIn 0.3s;
        }
        .modal-content {
            background-color: #ffffff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: none;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            cursor: move;
        }
        .modal-header h3 {
            color: #333;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .modal-header h3 i {
            margin-right: 10px;
        }
        .close {
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        .close:hover {
            color: #333;
        }
        .modal-body {
            margin-bottom: 20px;
        }
        .modal-body label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        .modal-body input[type="text"],
        .modal-body input[type="number"],
        .modal-body select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .modal-body input:focus,
        .modal-body select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .modal-footer button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        .modal-footer button i {
            margin-right: 8px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background-color: #e2e6ea;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .success-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s;
        }
        .success-modal-content {
            background-color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .success-modal-content i.fas.fa-check-circle {
            color: #28a745;
            font-size: 50px;
            margin-bottom: 15px;
        }
        .success-modal-content h3 {
            color: #28a745;
            margin-bottom: 15px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        .success-modal-content p {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
        }
        .success-modal-content button {
            display: block;
            width: 100%;
            max-width: 150px;
            margin: 0 auto;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .success-modal-content button:hover {
            background-color: #218838;
        }
        .warning-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s;
        }
        .warning-modal-content {
            background-color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .warning-modal-content i.fas.fa-exclamation-triangle {
            color: #ffa52fff;
            font-size: 50px;
            margin-bottom: 15px;
        }
        .warning-modal-content h3 {
            color: #ffa52fff;
            margin-bottom: 15px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        .warning-modal-content p {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
        }
        .warning-modal-content button {
            display: block;
            width: 100%;
            max-width: 150px;
            margin: 0 auto;
            background-color: #ffc107;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .warning-modal-content button:hover {
            background-color: #e0a800;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .main-content {
                padding: 1rem;
                margin: 0;
                font-size: 13px;
            }
            .modal-content {
                width: 95%;
            }
            .success-modal-content {
                width: 95%;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 8px 10px;
            }
        }
        /* Paginación */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 13px;
            color: #555;
        }
        .pagination {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 5px;
        }
        .pagination a, .pagination span {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
            font-size: 13px;
        }
        .pagination a:hover {
            background-color: #007bff;
            color: white;
        }
        .pagination .current {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        .per-page-select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!--sidebar_inventario-->
        <?php include('../includes/sidebar_inventario.php'); ?>

        <main class="main-content">
            <h3><i class="fas fa-exchange-alt"></i> Movimientos de Inventario (Kardex)</h3>
            <div style="display: flex; gap: 10px; align-items: center; margin: 10px 0 20px 0; flex-wrap: wrap;">
                <button class="btn" id="openModal" style="margin-bottom:0;"><i class="fas fa-plus"></i> Agregar Registro</button>
                <input type="text" id="busquedaMovimiento" class="form-control" placeholder="Buscar movimiento..." style="max-width:350px;width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:4px;font-size:14px;">
            </div>
            <!-- Tabla -->
            <table id="tablaMovimientos">
                <thead>
                    <tr>
                        <th hidden="true">ID</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody id="tbodyMovimientos">
    <script>
    // Búsqueda inline de movimientos
    document.addEventListener('DOMContentLoaded', function() {
        const inputBusqueda = document.getElementById('busquedaMovimiento');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', function() {
                const filtro = inputBusqueda.value.toLowerCase();
                const filas = document.querySelectorAll('#tablaMovimientos tbody tr');
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
    </script>
                <?php if (!empty($movimientos)) : ?>
                    <?php foreach ($movimientos as $mov) : ?>
                    <tr>
                        <td hidden="true"><?= $mov['idmovimiento'] ?></td>
                        <td><?= htmlspecialchars($mov['producto']) ?></td>
                        <td><?= ucfirst($mov['tipo']) ?></td>
                        <td><?= $mov['cantidad'] ?></td>
                        <td>
                            <button type="button" class="btn" style="padding: 5px 10px; font-size: 12px;"
                                    onclick="verComentario('<?= addslashes(htmlspecialchars($mov['comentario'])) ?>')">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        </td>
                        <td><?= date('d/m/Y', strtotime($mov['fecha'])) ?></td>
                        <td><?= $mov['usuario'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7">No hay movimientos registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <?php if ($totalRegistros > 0): ?>
            <div class="pagination-container">
                <div>
                    Mostrando <strong><?= min($offset + 1, $totalRegistros) ?> - <?= min($offset + $porPagina, $totalRegistros) ?></strong>
                    de <strong><?= $totalRegistros ?></strong> registros
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

            <ul class="pagination">
                <li><a href="?pagina=<?= max(1, $pagina - 1) ?>&por_pagina=<?= $porPagina ?>" class="<?= $pagina <= 1 ? 'disabled' : '' ?>">Anterior</a></li>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li><a href="?pagina=<?= $i ?>&por_pagina=<?= $porPagina ?>" class="<?= $i == $pagina ? 'current' : '' ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li><a href="?pagina=<?= min($totalPaginas, $pagina + 1) ?>&por_pagina=<?= $porPagina ?>" class="<?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">Siguiente</a></li>
            </ul>
            <?php endif; ?>

            <!-- Modal para ver comentario -->
            <div id="comentarioModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-comment"></i> Comentario</h3>
                        <span class="close" onclick="cerrarComentarioModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <textarea id="textoComentario" rows="4" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background-color: #f8f9fa; font-size: 14px; resize: none;"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="cerrarComentarioModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modales existentes -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Nuevo Movimiento</h3>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="modalForm">
                    <label for="idproducto">Producto *</label>
                    <select name="idproducto" id="idproducto" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['idproducto'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="tipo">Tipo *</label>
                    <select name="tipo" id="tipo" required>
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                    <div id="stockDisponibleContainer" style="display:none; margin-top: 5px;">
                        <strong>Stock disponible:</strong> <span id="stockDisponibleTexto">0</span>
                    </div>
                    <label for="cantidad">Cantidad *</label>
                    <input type="number" name="cantidad" id="cantidad" min="1" required>
                    <div class="comentario-container">
                        <label for="comentario">Comentario * (máx. 255 caracteres)</label>
                        <textarea name="comentario" id="comentario" required placeholder="Ingrese un comentario"></textarea>
                        <div id="contador-caracteres">255 caracteres restantes</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="cerrarModal()"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="successModal" class="success-modal" style="<?php echo ($mensaje && $tipoMensaje === 'success') ? 'display: flex;' : ''; ?>">
        <div class="success-modal-content">
            <i class="fas fa-check-circle"></i>
            <h3>¡Éxito!</h3>
            <p><?= isset($mensaje) ? $mensaje : 'Movimiento registrado correctamente.' ?></p>
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

    <script>
        function mostrarModal() {
            document.getElementById('myModal').style.display = 'block';
        }
        function cerrarModal() {
            document.getElementById('myModal').style.display = 'none';
        }
        function cerrarSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'none';
            window.location.reload();
        }
        function mostrarWarningModal(mensaje) {
            const warningModal = document.getElementById('warningModal');
            const warningMessage = document.getElementById('warningMessage');
            warningMessage.textContent = mensaje;
            warningModal.style.display = 'flex';
        }
        function cerrarWarningModal() {
            const warningModal = document.getElementById('warningModal');
            warningModal.style.display = 'none';
        }
        function verComentario(comentario) {
            document.getElementById('textoComentario').value = comentario;
            document.getElementById('comentarioModal').style.display = 'block';
        }
        function cerrarComentarioModal() {
            document.getElementById('comentarioModal').style.display = 'none';
        }
        function cambiarPorPagina(valor) {
            const url = new URL(window.location);
            url.searchParams.set('por_pagina', valor);
            url.searchParams.set('pagina', '1');
            window.location.href = url.toString();
        }
        window.onclick = function(event) {
            const modal = document.getElementById('myModal');
            const successModal = document.getElementById('successModal');
            const warningModal = document.getElementById('warningModal');
            const comentarioModal = document.getElementById('comentarioModal');
            if (event.target === modal) cerrarModal();
            if (event.target === successModal) cerrarSuccessModal();
            if (event.target === warningModal) cerrarWarningModal();
            if (event.target === comentarioModal) cerrarComentarioModal();
        }

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
            function closeDragElement() {
                document.onmouseup = null;
                document.onmousemove = null;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('openModal').addEventListener('click', mostrarModal);

            const comentarioModal = document.getElementById('comentarioModal');
            if (comentarioModal) {
                const content = comentarioModal.querySelector('.modal-content');
                const header = comentarioModal.querySelector('.modal-header');
                if (content && header) {
                    makeDraggable(content, header);
                }
            }

            const modal = document.getElementById('myModal');
            if (modal) {
                const modalContent = modal.querySelector('.modal-content');
                const dragHandle = modal.querySelector('.modal-header');
                if (modalContent && dragHandle) {
                    makeDraggable(modalContent, dragHandle);
                }
            }

            const comentarioTextarea = document.getElementById('comentario');
            const contador = document.getElementById('contador-caracteres');
            comentarioTextarea.addEventListener('input', function() {
                const restantes = 255 - this.value.length;
                contador.textContent = `${restantes} caracteres restantes`;
                if (restantes < 0) {
                    contador.style.color = '#dc3545';
                    this.value = this.value.substring(0, 255);
                } else if (restantes < 30) {
                    contador.style.color = '#ffc107';
                } else {
                    contador.style.color = '#666';
                }
            });

            const tipoSelect = document.getElementById('tipo');
            const productoSelect = document.getElementById('idproducto');
            const stockContainer = document.getElementById('stockDisponibleContainer');
            const stockTexto = document.getElementById('stockDisponibleTexto');
            const cantidadInput = document.getElementById('cantidad');
            let stockActual = 0;

            function actualizarStockVisible() {
                const tipo = tipoSelect.value.toLowerCase();
                const idProducto = productoSelect.value;
                if (tipo === 'salida' && idProducto) {
                    fetch(`../inventario/obtener_stock.php?idproducto=${idProducto}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                stockActual = parseInt(data.stock, 10);
                                stockTexto.textContent = stockActual;
                                stockContainer.style.display = 'block';
                                cantidadInput.max = stockActual;
                                if (parseInt(cantidadInput.value, 10) > stockActual) {
                                    cantidadInput.value = stockActual;
                                    mostrarWarningModal(`La cantidad máxima disponible para salida es ${stockActual}. Se ha ajustado el valor.`);
                                }
                            } else {
                                stockActual = 0;
                                stockTexto.textContent = '0';
                                stockContainer.style.display = 'block';
                                cantidadInput.max = 0;
                                cantidadInput.value = 0;
                            }
                        });
                } else {
                    stockActual = 0;
                    stockContainer.style.display = 'none';
                    cantidadInput.removeAttribute('max');
                }
            }
            tipoSelect.addEventListener('change', actualizarStockVisible);
            productoSelect.addEventListener('change', actualizarStockVisible);
            cantidadInput.addEventListener('input', () => {
                if (tipoSelect.value.toLowerCase() === 'salida') {
                    const val = parseInt(cantidadInput.value, 10);
                    if (val > stockActual) {
                        cantidadInput.value = stockActual;
                        mostrarWarningModal(`No puedes ingresar una cantidad mayor que el stock disponible (${stockActual}).`);
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