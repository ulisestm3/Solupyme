<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();
$mensaje = "";
$showSuccessModal = false;
$successMessage = "";
// Redirección con mensaje de éxito
if (isset($_GET['success'])) {
    $showSuccessModal = true;
    $successMessage = htmlspecialchars($_GET['success']);
}
function cargarCategorias($conn) {
    return $conn->query("SELECT * FROM categorias WHERE activo = b'1'")->fetch_all(MYSQLI_ASSOC);
}
$categorias = cargarCategorias($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $usuarioregistra = $_SESSION['idusuario'] ?? 1;
    if ($nombre === "") {
        $mensaje = "El nombre de la categoría es obligatorio.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, activo, usuarioregistra, fecharegistro) VALUES (?, b'1', ?, NOW())");
        $stmt->bind_param("si", $nombre, $usuarioregistra);
        if ($stmt->execute()) {
            header("Location: categorias.php?success=" . urlencode("Categoría guardada correctamente."));
            exit();
        } else {
            $mensaje = "Error al guardar la categoría.";
        }
    }
}
if (isset($_POST['actualizar'])) {
    $id = intval($_POST['editar_id']);
    $nombre = isset($_POST['editar_nombre']) ? trim($_POST['editar_nombre']) : '';
    if ($id && $nombre !== "") {
        $stmt = $conn->prepare("UPDATE categorias SET nombre = ? WHERE idcategoria = ?");
        $stmt->bind_param("si", $nombre, $id);
        if ($stmt->execute()) {
            header("Location: categorias.php?success=" . urlencode("Categoría actualizada correctamente."));
            exit();
        } else {
            $mensaje = "Error al actualizar la categoría.";
        }
    } else {
        $mensaje = "Faltan datos para actualizar.";
    }
}
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $usuarioactualiza = $_SESSION['idusuario'] ?? 1;
    $stmt = $conn->prepare("UPDATE categorias SET activo = b'0', usuarioactualiza = ?, fechaactualizacion = NOW() WHERE idcategoria = ?");
    $stmt->bind_param("ii", $usuarioactualiza, $idEliminar);
    if ($stmt->execute()) {
        header("Location: categorias.php?success=" . urlencode("Categoría eliminada correctamente."));
        exit();
    } else {
        $mensaje = "Error al eliminar la categoría.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Categorías</title>
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
        /* ESTILOS MODALES ARRASTRABLES Y MENOS VERTICALES */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.0);
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
        .modal-body input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .modal-body input:focus {
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
            background-color: rgba(0, 0, 0, 0.0);
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
            <h3><i class="fas fa-tags"></i> Categorías de Productos</h3> <br>
            <?php if (!empty($mensaje)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $mensaje ?></span>
                </div>
            <?php endif; ?>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 15px; max-width: 420px;">
                <button class="btn" onclick="mostrarModal()" style="margin-bottom: 0;"><i class="fas fa-plus"></i> Agregar Categoría</button>
                <input type="text" id="busquedaCategoria" placeholder="Buscar..." style="flex: 1 1 0px; min-width: 0; padding: 7px 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; margin-bottom: 0;">
            </div>

            <table id="tablaCategorias">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // --- PAGINACIÓN ---
                $porPagina = 10;
                $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
                $offset = ($pagina - 1) * $porPagina;
                $totalRegistros = count($categorias);
                $totalPaginas = ceil($totalRegistros / $porPagina);
                $categoriasPagina = array_slice($categorias, $offset, $porPagina);
                foreach ($categoriasPagina as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="abrirModalEdicion(<?= $c['idcategoria'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')" title="Editar">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="abrirModalEliminar(<?= $c['idcategoria'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <div style="margin-top: 15px; display: flex; justify-content: flex-end; align-items: center; gap: 5px;">
                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación">
                        <ul style="list-style: none; display: flex; gap: 3px; padding: 0; margin: 0;">
                            <?php if ($pagina > 1): ?>
                                <li><a href="?pagina=<?= $pagina - 1 ?>" class="btn" style="padding: 4px 10px; font-size: 13px;">&laquo;</a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li><a href="?pagina=<?= $i ?>" class="btn" style="padding: 4px 10px; font-size: 13px;<?= $i == $pagina ? ' background: #2563eb;' : '' ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php if ($pagina < $totalPaginas): ?>
                                <li><a href="?pagina=<?= $pagina + 1 ?>" class="btn" style="padding: 4px 10px; font-size: 13px;">&raquo;</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        <script>
        // Búsqueda inline para categorías
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('busquedaCategoria');
            const tabla = document.getElementById('tablaCategorias').getElementsByTagName('tbody')[0];
            input.addEventListener('keyup', function() {
                const filtro = input.value.toLowerCase();
                for (let fila of tabla.rows) {
                    let texto = fila.textContent.toLowerCase();
                    fila.style.display = texto.includes(filtro) ? '' : 'none';
                }
            });
        });
        </script>
            <div id="modalCategoria" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-plus-circle"></i> Nueva Categoría</h3>
                        <span class="close" onclick="cerrarModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <label for="nombre">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" required placeholder="Ingrese el nombre de la categoría">
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModal()"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="modalEditar" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-pencil-alt"></i> Editar Categoría</h3>
                        <span class="close" onclick="cerrarModalEditar()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="editar_id" id="editar_id">
                            <label for="editar_nombre">Nombre *</label>
                            <input type="text" name="editar_nombre" id="editar_nombre" required placeholder="Ingrese el nombre de la categoría">
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModalEditar()"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" name="actualizar" class="btn-primary"><i class="fas fa-sync-alt"></i> Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="modalEliminar" class="modal">
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
                            ¿Estás seguro que deseas eliminar la categoría <strong id="nombreCategoriaEliminar"></strong>?
                        </p>
                        <p style="text-align: center; font-size: 14px; color: #666;">
                            Esta acción no se puede deshacer y la categoría dejará de estar disponible en el sistema.
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
        function mostrarModal() {
            document.getElementById('modalCategoria').style.display = 'block';
        }
        function cerrarModal() {
            document.getElementById('modalCategoria').style.display = 'none';
        }
        function abrirModalEdicion(id, nombre) {
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('modalEditar').style.display = 'block';
        }
        function cerrarModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        let idCategoriaEliminar = null;
        function abrirModalEliminar(id, nombre) {
            idCategoriaEliminar = id;
            document.getElementById('nombreCategoriaEliminar').textContent = nombre;
            document.getElementById('modalEliminar').style.display = 'block';
        }
        function cerrarModalEliminar() {
            document.getElementById('modalEliminar').style.display = 'none';
            idCategoriaEliminar = null;
        }
        function confirmarEliminar() {
            if (idCategoriaEliminar !== null) {
                window.location.href = '?eliminar=' + idCategoriaEliminar;
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
            const modalCategoria = document.getElementById('modalCategoria');
            const modalEditar = document.getElementById('modalEditar');
            const modalEliminar = document.getElementById('modalEliminar');
            const successModal = document.getElementById('successModal');
            if (event.target === modalCategoria) cerrarModal();
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
                { id: 'modalCategoria' },
                { id: 'modalEditar' },
                { id: 'modalEliminar' },
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
