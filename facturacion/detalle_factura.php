<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
// verificarPermisoPagina(); // Comentado según requerimiento

// No es necesario: session_start() ya está en seguridad.php

$conn = getConnection();

// === Obtener ID de factura ===
$idfactura = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idfactura <= 0) {
    $_SESSION['mensaje'] = "ID de factura no válido.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: facturas.php");
    exit();
}

// === Obtener datos de la factura ===
$sqlFactura = "
    SELECT f.*, c.nombre AS cliente_nombre, c.apellido, u.usuario AS vendedor
    FROM facturas f
    INNER JOIN clientes c ON f.idcliente = c.idcliente
    INNER JOIN usuarios u ON f.idusuario = u.idusuario
    WHERE f.idfactura = ?
    LIMIT 1";

$stmt = $conn->prepare($sqlFactura);
$stmt->bind_param("i", $idfactura);
$stmt->execute();
$result = $stmt->get_result();
$factura = $result->fetch_assoc();

if (!$factura) {
    $_SESSION['mensaje'] = "Factura no encontrada.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: facturas.php");
    exit();
}

// Verificar si está anulada
$isAnulada = ($factura['activo'] == 0);

// === Obtener productos actuales de la factura ===
$sqlDetalle = "
    SELECT df.idproducto, df.cantidad, df.precio, p.nombre, p.stock
    FROM detalle_factura df
    INNER JOIN productos p ON df.idproducto = p.idproducto
    WHERE df.idfactura = ?";

$stmtDetalle = $conn->prepare($sqlDetalle);
$stmtDetalle->bind_param("i", $idfactura);
$stmtDetalle->execute();
$resultDetalle = $stmtDetalle->get_result();
$productosFactura = $resultDetalle->fetch_all(MYSQLI_ASSOC);

// === Obtener todos los productos disponibles (activos) ===
$productosDisponibles = [];
$resultProd = $conn->query("SELECT idproducto, nombre, precio, stock FROM productos WHERE activo = 1");
while ($p = $resultProd->fetch_assoc()) {
    $productosDisponibles[] = $p;
}

// === Recuperar mensaje de sesión ===
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Factura #<?= $factura['idfactura'] ?> </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f85c; color: #333; }
        .container { display: flex; height: 100vh; }
        .main-content { flex: 1; padding: 1.5rem; margin: 0.5rem; overflow-y: auto; font-size: 14px; }

        .main-content h2 { color: #004080; margin-bottom: 1.5rem; font-size: 1.6rem; display: flex; align-items: center; gap: 10px; }
        .main-content h3 { color: #004080; margin: 1.5rem 0 1rem; font-size: 1.3rem; border-bottom: 2px solid #007acc; padding-bottom: 5px; }

        /* Formulario */
        form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }

        .form-group { margin-bottom: 1rem; }
        .form-group label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 600; 
            color: #444; 
        }

        /* Estilo mejorado de inputs y selects */
        select, 
        input[type="text"], 
        input[type="number"] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
            background-color: white;
        }

        select:focus, 
        input[type="text"]:focus, 
        input[type="number"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
        }

        /* Input con error */
        input.error {
            border-color: #dc3545 !important;
            background-color: #fdf2f2;
        }

        /* Estilo para readonly */
        input[readonly], 
        select[disabled] {
            background-color: #f8f9fa;
            font-weight: 500;
            color: #212529;
            cursor: not-allowed;
        }

        /* Tabla de productos */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 1rem 0 1.5rem; 
            font-size: 14px; 
            background: #fff; 
        }
        th, td { 
            padding: 10px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
        }
        th { 
            background-color: #007acc; 
            color: white; 
            font-weight: 600; 
        }
        tr:hover { 
            background-color: #f8f9fa; 
        }

        /* Botones en filas */
        .btn-row-add { 
            background-color: #28a745; 
            color: white; 
            border: none; 
            padding: 6px 10px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            width: 30px; 
            height: 30px;
        }
        .btn-row-add:hover { 
            background-color: #218838; 
        }

        .btn-row-remove { 
            background-color: #dc3545; 
            color: white; 
            border: none; 
            padding: 4px 8px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 14px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .btn-row-remove:hover { 
            background-color: #c82333; 
        }

        /* Botón principal */
        .btn-submit { 
            background-color: #004080; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 6px; 
            font-size: 15px; 
            font-weight: 500; 
            cursor: pointer; 
            margin-top: 1rem; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
        }
        .btn-submit:hover { 
            background-color: #2563eb; 
        }
        .btn-submit:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .btn-danger { 
            background-color: #dc3545; 
        }
        .btn-danger:hover { 
            background-color: #c82333; 
        }

        /* Mensaje de anulada - Centrado */
        .alert-anulada {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        .alert-anulada div {
            background: #dc3545;
            color: white;
            text-align: center;
            padding: 14px 20px;
            font-size: 1.25rem;
            font-weight: bold;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            max-width: 700px;
            width: 100%;
            line-height: 1.4;
        }

        /* Modal universal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s ease;
        }
        .modal-content h3 {
            margin: 10px 0;
            color: #333;
        }
        .modal-content p {
            color: #555;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .modal-btn-primary {
            background-color: #004080;
            color: white;
        }
        .modal-btn-primary:hover {
            background-color: #2563eb;
        }
        .modal-btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .modal-btn-danger:hover {
            background-color: #c82333;
        }
        .modal-btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .modal-btn-secondary:hover {
            background-color: #5a6268;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #modalIcono {
            font-size: 50px;
            margin-bottom: 10px;
        }
        #modalIcono.success { color: #28a745; }
        #modalIcono.error { color: #dc3545; }
        #modalIcono.warning { color: #ffc107; }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content { padding: 1rem; margin: 0.3rem; }
            .main-content h2 { font-size: 1.4rem; }
            .main-content h3 { font-size: 1.2rem; }
            form { padding: 1rem; }
            .form-group label { font-size: 14px; }
            select, input { font-size: 14px; }
            table, th, td { font-size: 13px; padding: 8px; }
            .btn-submit { padding: 9px 16px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/sidebar_inventario.php'); ?>

        <main class="main-content">
            <!-- Mensaje de anulada -->
            <?php if ($isAnulada): ?>
                <div class="alert-anulada">
                    <div>⚠️ FACTURA ANULADA</div>
                </div>
            <?php endif; ?>

            <h2><i class="fas fa-file-invoice"></i> Editar Factura #<?= $factura['idfactura'] ?></h2>

            <form id="formFactura" method="POST" action="actualizar_detalle_factura.php">
                <input type="hidden" name="idfactura" value="<?= $idfactura ?>">

                <div class="invoice-container">
                    <!-- Información básica (solo lectura) -->
                    <div class="form-group">
                        <label>Cliente:</label>
                        <input type="text" value="<?= htmlspecialchars(trim($factura['cliente_nombre'] . ' ' . ($factura['apellido'] ?? ''))) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Vendedor:</label>
                        <input type="text" value="<?= htmlspecialchars($factura['vendedor']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Fecha:</label>
                        <input type="text" value="<?= $factura['fecha'] ?>" readonly>
                    </div>

                    <!-- Productos -->
                    <h3><i class="fas fa-boxes"></i> Productos</h3>
                    <table id="productosTable">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th>
                                    <?php if (!$isAnulada): ?>
                                        <button type="button" onclick="addRow()" class="btn-row-add" title="Agregar producto">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosFactura as $pf): ?>
                                <tr>
                                    <td>
                                        <select name="productos[]" required onchange="productoSeleccionado(this)" 
                                            <?= $isAnulada ? 'disabled' : '' ?>>
                                            <option value="">Seleccione producto...</option>
                                            <?php foreach ($productosDisponibles as $p): ?>
                                                <option value="<?= $p['idproducto'] ?>"
                                                    data-precio="<?= $p['precio'] ?>"
                                                    data-stock="<?= $p['stock'] ?>"
                                                    <?= ($p['idproducto'] == $pf['idproducto']) ? 'selected' : '' ?>
                                                    <?= ($p['idproducto'] != $pf['idproducto'] && in_array($p['idproducto'], array_column($productosFactura, 'idproducto'))) ? 'disabled' : '' ?>>
                                                    <?= htmlspecialchars($p['nombre']) ?> (Stock: <?= $p['stock'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="cantidades[]" min="1" value="<?= $pf['cantidad'] ?>" 
                                            style="width:70px;" oninput="calcular()" <?= $isAnulada ? 'disabled' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="precios[]" value="<?= number_format($pf['precio'], 2) ?>" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="subtotales[]" value="<?= number_format($pf['cantidad'] * $pf['precio'], 2) ?>" readonly>
                                    </td>
                                    <td>
                                        <?php if (!$isAnulada): ?>
                                            <button type="button" class="btn-row-remove" title="Eliminar" onclick="eliminarFila(this)">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totales -->
                    <div class="form-group">
                        <label>Subtotal:</label>
                        <input type="text" id="subtotal" name="subtotal" value="<?= number_format(array_sum(array_map(function($p) { return $p['cantidad'] * $p['precio']; }, $productosFactura)), 2) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>IVA (15%):</label>
                        <input type="text" id="iva" name="iva" value="<?= number_format(array_sum(array_map(function($p) { return $p['cantidad'] * $p['precio']; }, $productosFactura)) * 0.15, 2) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Total con IVA:</label>
                        <input type="text" id="total_final" name="total_final" value="<?= number_format(array_sum(array_map(function($p) { return $p['cantidad'] * $p['precio']; }, $productosFactura)) * 1.15, 2) ?>" readonly>
                    </div>

                    <!-- Botones de acción -->
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <a href="facturas.php" class="btn-submit" style="background-color: #6c757d;">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>

                        <?php if (!$isAnulada): ?>
                            <button type="submit" class="btn-submit" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <button type="button" onclick="confirmarAnulacion()" class="btn-submit btn-danger">
                                <i class="fas fa-times-circle"></i> Anular Factura
                            </button>
                        <?php else: ?>
                            <span style="color: #dc3545; font-weight: bold; margin-top: 10px;">Esta factura no puede editarse porque está anulada.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Modal de Mensaje (éxito/error) -->
    <div id="modalMensaje" class="modal">
        <div class="modal-content">
            <div id="modalIcono"></div>
            <h3 id="modalTitulo"></h3>
            <p id="modalCuerpo"></p>
            <button class="modal-btn modal-btn-primary" onclick="cerrarModal()">Aceptar</button>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div id="modalConfirm" class="modal">
        <div class="modal-content">
            <div id="modalIconoConfirm" class="warning"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>¿Confirmar acción?</h3>
            <p>¿Está seguro de anular esta factura? Esta acción no se puede deshacer.</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-secondary" onclick="cerrarModalConfirm()">Cancelar</button>
                <button class="modal-btn modal-btn-danger" onclick="ejecutarAnulacion()">Anular</button>
            </div>
        </div>
    </div>

    <script>
        // Mostrar modal si hay mensaje de sesión
        <?php if ($mensaje): ?>
            mostrarModal(
                '<?= $tipoMensaje === 'success' ? 'Éxito' : 'Error' ?>',
                '<?= htmlspecialchars($mensaje) ?>',
                '<?= $tipoMensaje ?>'
            );
        <?php endif; ?>

        // === Modal de Mensaje ===
        function mostrarModal(titulo, mensaje, tipo) {
            const modal = document.getElementById('modalMensaje');
            const icono = document.getElementById('modalIcono');
            const tituloElem = document.getElementById('modalTitulo');
            const cuerpoElem = document.getElementById('modalCuerpo');

            tituloElem.textContent = titulo;
            cuerpoElem.textContent = mensaje;

            icono.className = '';
            icono.innerHTML = 
                tipo === 'success' ? '<i class="fas fa-check-circle"></i>' :
                tipo === 'error'   ? '<i class="fas fa-times-circle"></i>' :
                                   '<i class="fas fa-info-circle"></i>';
            icono.classList.add(tipo);

            modal.style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalMensaje').style.display = 'none';
        }

        // === Modal de Confirmación ===
        function confirmarAnulacion() {
            document.getElementById('modalConfirm').style.display = 'flex';
        }

        function cerrarModalConfirm() {
            document.getElementById('modalConfirm').style.display = 'none';
        }

        function ejecutarAnulacion() {
            cerrarModalConfirm();
            fetch('anular_factura.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'idfactura=<?= $idfactura ?>'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarModal('Éxito', 'Factura anulada correctamente.', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarModal('Error', data.message, 'error');
                }
            })
            .catch(err => {
                mostrarModal('Error', 'Error de conexión. Intente nuevamente.', 'error');
                console.error(err);
            });
        }

        // Pasar productos disponibles a JavaScript
        const productosDisponibles = [
            <?php foreach ($productosDisponibles as $p): ?>,
                {
                    id: <?= $p['idproducto'] ?>,
                    nombre: "<?= addslashes(htmlspecialchars($p['nombre'])) ?>",
                    precio: <?= $p['precio'] ?>,
                    stock: <?= $p['stock'] ?>
                },
            <?php endforeach; ?>
        ];

        // Agregar nueva fila
        function addRow() {
            const tbody = document.querySelector("#productosTable tbody");
            const row = document.createElement('tr');

            let selectHtml = `<select name="productos[]" required onchange="productoSeleccionado(this)">`;
            selectHtml += `<option value="">Seleccione producto...</option>`;

            // Productos ya seleccionados
            const seleccionados = Array.from(tbody.querySelectorAll("select[name='productos[]']"))
                .map(s => s.value)
                .filter(v => v);

            productosDisponibles.forEach(p => {
                if (!seleccionados.includes(String(p.id))) {
                    selectHtml += `<option value="${p.id}" data-precio="${p.precio}" data-stock="${p.stock}">
                        ${p.nombre} (Stock: ${p.stock})
                    </option>`;
                }
            });
            selectHtml += `</select>`;

            row.innerHTML = `
                <td>${selectHtml}</td>
                <td><input type="number" name="cantidades[]" min="1" value="1" style="width:70px;" oninput="calcular()"></td>
                <td><input type="text" name="precios[]" readonly value="0.00"></td>
                <td><input type="text" name="subtotales[]" readonly value="0.00"></td>
                <td>
                    <button type="button" class="btn-row-remove" title="Eliminar" onclick="eliminarFila(this)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            calcular();
        }

        // Actualizar precio cuando se selecciona un producto
        function productoSeleccionado(select) {
            const option = select.options[select.selectedIndex];
            const precio = option.getAttribute('data-precio');
            const tr = select.closest('tr');
            tr.querySelector("input[name='precios[]']").value = parseFloat(precio).toFixed(2);
            calcular();
            actualizarSelects();
        }

        // Eliminar fila
        function eliminarFila(button) {
            const tr = button.closest('tr');
            tr.remove();
            actualizarSelects();
            calcular();
        }

        // Actualizar selects para no repetir productos
        function actualizarSelects() {
            const tbody = document.querySelector("#productosTable tbody");
            const selects = tbody.querySelectorAll("select[name='productos[]']");
            const seleccionados = Array.from(selects)
                .map(s => s.value)
                .filter(v => v);

            selects.forEach(select => {
                const current = select.value;
                const fragment = document.createDocumentFragment();
                const defaultOption = document.createElement('option');
                defaultOption.value = ""; defaultOption.textContent = "Seleccione producto...";
                fragment.appendChild(defaultOption);

                productosDisponibles.forEach(p => {
                    if (String(p.id) === current || !seleccionados.includes(String(p.id))) {
                        const option = document.createElement('option');
                        option.value = p.id;
                        option.setAttribute('data-precio', p.precio);
                        option.setAttribute('data-stock', p.stock);
                        option.textContent = `${p.nombre} (Stock: ${p.stock})`;
                        if (String(p.id) === current) option.selected = true;
                        fragment.appendChild(option);
                    }
                });
                select.innerHTML = '';
                select.appendChild(fragment);
            });
        }

        // Calcular totales y validar stock
        function calcular() {
            let subtotal = 0;
            let hayError = false;

            document.querySelectorAll("#productosTable tbody tr").forEach(tr => {
                const select = tr.querySelector("select[name='productos[]']");
                const inputCantidad = tr.querySelector("input[name='cantidades[]']");
                const precio = parseFloat(tr.querySelector("input[name='precios[]']").value) || 0;
                const cantidad = parseInt(inputCantidad.value) || 0;

                let stockMax = 0;
                if (select.value) {
                    const option = select.options[select.selectedIndex];
                    stockMax = parseInt(option.getAttribute('data-stock')) || 0;
                }

                // Validar stock
                if (cantidad > stockMax) {
                    inputCantidad.classList.add('error');
                    hayError = true;
                } else {
                    inputCantidad.classList.remove('error');
                }

                const subtotalFila = precio * cantidad;
                tr.querySelector("input[name='subtotales[]']").value = subtotalFila.toFixed(2);
                subtotal += subtotalFila;
            });

            const iva = subtotal * 0.15;
            const totalFinal = subtotal + iva;

            document.getElementById("subtotal").value = subtotal.toFixed(2);
            document.getElementById("iva").value = iva.toFixed(2);
            document.getElementById("total_final").value = totalFinal.toFixed(2);

            // Desactivar botón si hay error
            document.getElementById("btnGuardar").disabled = hayError;
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            actualizarSelects();
            calcular();
        });
    </script>
</body>
</html>