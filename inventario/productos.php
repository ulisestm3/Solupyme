<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
$conn = getConnection();
$mensaje = "";
$showSuccessModal = false;
$successMessage = "";

// Redirección con mensaje de éxito
if (isset($_GET['success'])) {
    $showSuccessModal = true;
    $successMessage = htmlspecialchars($_GET['success']);
}

$productos = $conn->query("
    SELECT p.*, c.nombre AS categoria
    FROM productos p
    LEFT JOIN categorias c ON p.idcategoria = c.idcategoria
    WHERE p.activo = b'1'
")->fetch_all(MYSQLI_ASSOC);

$categorias = $conn->query("SELECT * FROM categorias WHERE activo = b'1'")
    ->fetch_all(MYSQLI_ASSOC);

// Guardar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_producto'])) {
    $nombre = $_POST['nombre'];
    $idcategoria = $_POST['idcategoria'];
    $descripcion = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $idusuario = $_SESSION['idusuario'] ?? 1;

    if ($nombre && $idcategoria && $precio >= 0) {
        $stmt = $conn->prepare("
            INSERT INTO productos (nombre, idcategoria, descripcion, precio, stock_minimo, activo, usuarioregistra, fecharegistro)
            VALUES (?, ?, ?, ?, ?, b'1', ?, NOW())
        ");
        $stmt->bind_param("sisddi", $nombre, $idcategoria, $descripcion, $precio, $stock_minimo, $idusuario);
        if ($stmt->execute()) {
            header("Location: productos.php?success=" . urlencode("Producto guardado correctamente."));
            exit();
        } else {
            $mensaje = "Error al guardar el producto.";
        }
    } else {
        $mensaje = "Faltan datos obligatorios.";
    }
}

// Editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_producto'])) {
    $idproducto = intval($_POST['idproducto']);
    $nombre = $_POST['nombre'];
    $idcategoria = $_POST['idcategoria'];
    $descripcion = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $idusuario = $_SESSION['idusuario'] ?? 1;

    if ($idproducto && $nombre && $idcategoria && $precio >= 0) {
        $stmt = $conn->prepare("
            UPDATE productos
            SET nombre = ?, idcategoria = ?, descripcion = ?, precio = ?, stock_minimo = ?, usuarioactualiza = ?, fechaactualizacion = NOW()
            WHERE idproducto = ?
        ");
        $stmt->bind_param("sisddii", $nombre, $idcategoria, $descripcion, $precio, $stock_minimo, $idusuario, $idproducto);
        if ($stmt->execute()) {
            header("Location: productos.php?success=" . urlencode("Producto actualizado correctamente."));
            exit();
        } else {
            $mensaje = "Error al actualizar el producto.";
        }
    } else {
        $mensaje = "Datos incompletos para edición.";
    }
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $usuarioactualiza = $_SESSION['idusuario'] ?? 1;

    $stmt = $conn->prepare("
        UPDATE productos
        SET activo = b'0', usuarioactualiza = ?, fechaactualizacion = NOW()
        WHERE idproducto = ?
    ");
    $stmt->bind_param("ii", $usuarioactualiza, $idEliminar);

    if ($stmt->execute()) {
        header("Location: productos.php?success=" . urlencode("Producto eliminado correctamente."));
        exit();
    } else {
        $mensaje = "Error al eliminar el producto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Productos - AWFerreteria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 8px;
            border-radius: 4px;
            margin-right: 5px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            background: none;
            color: inherit;
        }
        .action-btn i {
            margin-right: 0;
            font-size: 14px;
        }
        .action-btn:hover {
            transform: scale(1.1);
        }
        .edit-btn {
            color: #0d6efd;
        }
        .delete-btn {
            color: #dc3545;
        }
        /* ESTILOS MODALES ARRASTRABLES Y MENOS VERTICALES */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.0); /* Fondo transparente */
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
        .modal-body select,
        .modal-body textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .modal-body input:focus,
        .modal-body select:focus,
        .modal-body textarea:focus {
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
            background-color: rgba(0, 0, 0, 0.0); /* Fondo transparente */
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
        .success-modal-content > i.fas.fa-check-circle {
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
                width: 90%;
            }
            .success-modal-content {
                width: 90%;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!--sidebar_inventario-->
        <?php include('../includes/sidebar_inventario.php'); ?>

        <main class="main-content">
            <h3><i class="fas fa-boxes"></i> Gestión de Productos</h3> <br>

            <?php if (!empty($mensaje)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $mensaje ?></span>
                </div>
            <?php endif; ?>

            <button class="btn" onclick="mostrarModalNuevo()"><i class="fas fa-plus"></i> Agregar Producto</button>

            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td><?= htmlspecialchars($p['descripcion']) ?></td>
                        <td><?= number_format($p['precio'], 2) ?></td>
                        <td><?= $p['stock'] ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p)) ?>)" title="Editar">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="abrirModalEliminar(<?= $p['idproducto'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>')" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div id="modalNuevoProducto" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-plus-circle"></i> Nuevo Producto</h3>
                        <span class="close" onclick="cerrarModalNuevo()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <label for="nombre">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" required placeholder="Ingrese el nombre del producto">

                            <label for="idcategoria">Categoría *</label>
                            <select name="idcategoria" id="idcategoria" required>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idcategoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label for="descripcion">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Ingrese la descripción del producto"></textarea>

                            <label for="precio">Precio *</label>
                            <input type="number" name="precio" id="precio" step="0.01" min="0" required placeholder="Ingrese el precio del producto">

                            <label for="stock_minimo">Stock mínimo</label>
                            <input type="number" name="stock_minimo" id="stock_minimo" min="0" value="5" placeholder="Ingrese el stock mínimo">

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModalNuevo()"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" class="btn-primary" name="guardar_producto"><i class="fas fa-save"></i> Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="modalEditarProducto" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-pencil-alt"></i> Editar Producto</h3>
                        <span class="close" onclick="cerrarModalEditar()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="idproducto" id="editar_idproducto">

                            <label for="editar_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="editar_nombre" required placeholder="Ingrese el nombre del producto">

                            <label for="editar_idcategoria">Categoría *</label>
                            <select name="idcategoria" id="editar_idcategoria" required>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idcategoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label for="editar_descripcion">Descripción</label>
                            <textarea name="descripcion" id="editar_descripcion" rows="3" placeholder="Ingrese la descripción del producto"></textarea>

                            <label for="editar_precio">Precio *</label>
                            <input type="number" name="precio" id="editar_precio" step="0.01" min="0" required placeholder="Ingrese el precio del producto">

                            <label for="editar_stock_minimo">Stock mínimo</label>
                            <input type="number" name="stock_minimo" id="editar_stock_minimo" min="0" placeholder="Ingrese el stock mínimo">

                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModalEditar()"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" class="btn-primary" name="editar_producto"><i class="fas fa-sync-alt"></i> Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="modalEliminarProducto" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-trash-alt"></i> Confirmar Eliminación</h3>
                        <span class="close" onclick="cerrarModalEliminar()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p style="text-align: center;">
                            <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 50px; margin-bottom: 15px;"></i>
                        </p>
                        <p style="text-align: center; font-size: 16px; margin-bottom: 10px;">
                            ¿Estás seguro que deseas eliminar el producto <strong id="nombreProductoEliminar"></strong>?
                        </p>
                        <p style="text-align: center; font-size: 14px; color: #666;">
                            Esta acción no se puede deshacer y el producto dejará de estar disponible en el sistema.
                        </p>
                    </div>
                    <div class="modal-footer" style="justify-content: center;">
                        <button type="button" class="btn-secondary" onclick="cerrarModalEliminar()"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="button" class="btn-danger" onclick="confirmarEliminar()"><i class="fas fa-trash-alt"></i> Eliminar</button>
                    </div>
                </div>
            </div>

            <div id="successModal" class="success-modal" style="<?php echo $showSuccessModal ? 'display: flex;' : ''; ?>">
                <div class="success-modal-content">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background-color: #28a745; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-check" style="color: white; font-size: 40px;"></i>
                    </div>
                    <h3 style="color: #28a745; font-size: 24px; margin-bottom: 10px;">¡Éxito!</h3>
                    <p style="color: #6c757d; margin-bottom: 25px;"><?php echo isset($successMessage) ? $successMessage : ''; ?></p>
                    <button onclick="cerrarSuccessModal()" style="padding: 10px 25px; border-radius: 5px; background-color: #28a745; color: white; border: none; cursor: pointer; font-weight: 500; font-size: 16px;">
                        Aceptar
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function mostrarModalNuevo() {
            document.getElementById('modalNuevoProducto').style.display = 'block';
        }

        function cerrarModalNuevo() {
            document.getElementById('modalNuevoProducto').style.display = 'none';
        }

        function abrirModalEditar(producto) {
            document.getElementById('editar_idproducto').value = producto.idproducto;
            document.getElementById('editar_nombre').value = producto.nombre;
            document.getElementById('editar_idcategoria').value = producto.idcategoria;
            document.getElementById('editar_descripcion').value = producto.descripcion || '';
            document.getElementById('editar_precio').value = producto.precio;
            document.getElementById('editar_stock_minimo').value = producto.stock_minimo;
            document.getElementById('modalEditarProducto').style.display = 'block';
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditarProducto').style.display = 'none';
        }

        let idProductoEliminar = null;

        function abrirModalEliminar(id, nombre) {
            idProductoEliminar = id;
            document.getElementById('nombreProductoEliminar').textContent = nombre;
            document.getElementById('modalEliminarProducto').style.display = 'block';
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminarProducto').style.display = 'none';
            idProductoEliminar = null;
        }

        function confirmarEliminar() {
            if (idProductoEliminar !== null) {
                window.location.href = '?eliminar=' + idProductoEliminar;
            }
        }
        
        function cerrarSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'none';
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                window.history.replaceState({path: url.href}, '', url.href);
            }
        }

        window.onclick = function(event) {
            const modalNuevo = document.getElementById('modalNuevoProducto');
            const modalEditar = document.getElementById('modalEditarProducto');
            const modalEliminar = document.getElementById('modalEliminarProducto');
            const successModal = document.getElementById('successModal');

            if (event.target === modalNuevo) cerrarModalNuevo();
            if (event.target === modalEditar) cerrarModalEditar();
            if (event.target === modalEliminar) cerrarModalEliminar();
            if (event.target === successModal) cerrarSuccessModal();
        }

        // Script para hacer los modales arrastrables
        function makeDraggable(element, dragHandle) {
            let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

            dragHandle.onmousedown = dragMouseDown;

            function dragMouseDown(e) {
                e = e || window.event;
                e.preventDefault();
                // obtener la posición inicial del cursor
                pos3 = e.clientX;
                pos4 = e.clientY;
                document.onmouseup = closeDragElement;
                // llamar a una función cuando el cursor se mueva
                document.onmousemove = elementDrag;
            }

            function elementDrag(e) {
                e = e || window.event;
                e.preventDefault();
                // calcular la nueva posición del cursor
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                // establecer la nueva posición del elemento
                element.style.top = (element.offsetTop - pos2) + "px";
                element.style.left = (element.offsetLeft - pos1) + "px";
            }

            function closeDragElement() {
                // detener el movimiento al soltar el botón del ratón
                document.onmouseup = null;
                document.onmousemove = null;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const modales = [
                { id: 'modalNuevoProducto' },
                { id: 'modalEditarProducto' },
                { id: 'modalEliminarProducto' },
            ];

            modales.forEach(modal => {
                const modalElement = document.getElementById(modal.id);
                if (modalElement) {
                    const modalContent = modalElement.querySelector('.modal-content');
                    const dragHandle = modalElement.querySelector('.modal-header');
                    if(modalContent && dragHandle) {
                        makeDraggable(modalContent, dragHandle);
                    }
                }
            });
        });
    </script>
</body>
</html>