<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$mensajes = [
    'exito' => [
        1 => "Parámetros de empresa actualizados correctamente."
    ],
    'error' => [
        'db' => "Error en la base de datos: ",
        'edicion' => "Error al actualizar los parámetros. Intente nuevamente."
    ]
];

$mensajeError = '';
$mostrarModalMensaje = false;
$tipoMensaje = '';
$successMessage = "";

// Verificar mensajes de éxito/error
if (isset($_GET['exito']) && isset($mensajes['exito'][$_GET['exito']])) {
    $successMessage = $mensajes['exito'][$_GET['exito']];
    $mostrarModalMensaje = true;
    $tipoMensaje = 'exito';
} elseif (isset($_GET['error']) && isset($mensajes['error'][$_GET['error']])) {
    $mensajeError = $mensajes['error'][$_GET['error']] . (isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '');
    $mostrarModalMensaje = true;
    $tipoMensaje = 'error';
}

// Conexión a la base de datos
$conn = getConnection();
$sql = "SELECT idempresa, nombrecormercial, razonsocial, ruc, direccion, contacto FROM empresa LIMIT 1";
$result = $conn->query($sql);
$empresa = $result->fetch_assoc();

// Lógica para actualizar empresa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_empresa'])) {
    $idempresa = $_POST['idempresa'];
    $nombrecormercial = $_POST['nombrecormercial'];
    $razonsocial = $_POST['razonsocial'];
    $ruc = $_POST['ruc'];
    $direccion = $_POST['direccion'];
    $contacto = $_POST['contacto'];

    $sql = "UPDATE empresa
            SET nombrecormercial = ?, razonsocial = ?, ruc = ?, direccion = ?, contacto = ?
            WHERE idempresa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nombrecormercial, $razonsocial, $ruc, $direccion, $contacto, $idempresa);

    if ($stmt->execute()) {
        header("Location: parametros.php?exito=1");
        exit();
    } else {
        header("Location: parametros.php?error=db&message=" . urlencode($conn->error));
        exit();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parámetros de Empresa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .main-content h1 {
            color: #004080;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .main-content h1 i {
            margin-right: 10px;
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

        .btn-editar {
            background-color: #007acc;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .btn-editar:hover {
            background-color: #2563eb;
        }

        .btn-editar i {
            margin-right: 8px;
        }

        /* ESTILOS MODALES ARRASTRABLES (ESTILO CATEGORÍAS) */
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
                width: 95%;
                padding: 15px;
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
        <?php include('../includes/sidebar_seguridad.php'); ?>

        <main class="main-content">
            <h1><i class="fas fa-cog"></i> Parámetros de Empresa</h1>

            <table>
                <thead>
                    <tr>
                        <th>Nombre Comercial</th>
                        <th>Razón Social</th>
                        <th>RUC</th>
                        <th>Dirección</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($empresa): ?>
                    <tr>
                        <td><?= htmlspecialchars($empresa['nombrecormercial']) ?></td>
                        <td><?= htmlspecialchars($empresa['razonsocial']) ?></td>
                        <td><?= htmlspecialchars($empresa['ruc']) ?></td>
                        <td><?= htmlspecialchars($empresa['direccion']) ?></td>
                        <td><?= htmlspecialchars($empresa['contacto']) ?></td>
                        <td>
                            <button class="btn-editar" onclick="document.getElementById('editarEmpresaModal').style.display='block'">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal para mensajes de éxito -->
    <div id="successModal" class="success-modal" style="<?= $mostrarModalMensaje && $tipoMensaje == 'exito' ? 'display: flex;' : '' ?>">
        <div class="success-modal-content">
            <div style="width: 80px; height: 80px; border-radius: 50%; background-color: #28a745; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-check" style="color: white; font-size: 40px;"></i>
            </div>
            <h3 style="color: #28a745; font-size: 24px; margin-bottom: 10px;">¡Éxito!</h3>
            <p style="color: #6c757d; margin-bottom: 25px;"><?= $successMessage ?></p>
            <button onclick="cerrarSuccessModal()" style="padding: 10px 25px; border-radius: 5px; background-color: #28a745; color: white; border: none; cursor: pointer; font-weight: 500; font-size: 16px;">
                Aceptar
            </button>
        </div>
    </div>

    <!-- Modal para mensajes de error -->
    <div id="errorModal" class="success-modal" style="<?= $mostrarModalMensaje && $tipoMensaje == 'error' ? 'display: flex;' : '' ?>">
        <div class="success-modal-content" style="border-left: 5px solid #dc3545;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background-color: #dc3545; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-times" style="color: white; font-size: 40px;"></i>
            </div>
            <h3 style="color: #dc3545; font-size: 24px; margin-bottom: 10px;">Error</h3>
            <p style="color: #6c757d; margin-bottom: 25px;"><?= $mensajeError ?></p>
            <button onclick="cerrarErrorModal()" style="padding: 10px 25px; border-radius: 5px; background-color: #dc3545; color: white; border: none; cursor: pointer; font-weight: 500; font-size: 16px;">
                Aceptar
            </button>
        </div>
    </div>

    <!-- Modal editar empresa -->
    <div id="editarEmpresaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Parámetros de Empresa</h3>
                <span class="close" onclick="document.getElementById('editarEmpresaModal').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <form action="parametros.php" method="post">
                    <input type="hidden" name="idempresa" value="<?= htmlspecialchars($empresa['idempresa']) ?>">

                    <!-- Nombre Comercial (máx. 100 caracteres) -->
                    <label for="nombrecormercial">Nombre Comercial *</label>
                    <input type="text" name="nombrecormercial" id="nombrecormercial"
                        value="<?= htmlspecialchars($empresa['nombrecormercial']) ?>"
                        maxlength="100" required>

                    <!-- Razón Social (máx. 100 caracteres) -->
                    <label for="razonsocial">Razón Social *</label>
                    <input type="text" name="razonsocial" id="razonsocial"
                        value="<?= htmlspecialchars($empresa['razonsocial']) ?>"
                        maxlength="100" required>

                    <!-- RUC (máx. 50 caracteres) -->
                    <label for="ruc">RUC *</label>
                    <input type="text" name="ruc" id="ruc"
                        value="<?= htmlspecialchars($empresa['ruc']) ?>"
                        maxlength="50" required>

                    <!-- Dirección (máx. 255 caracteres) -->
                    <label for="direccion">Dirección *</label>
                    <input type="text" name="direccion" id="direccion"
                        value="<?= htmlspecialchars($empresa['direccion']) ?>"
                        maxlength="255" required>

                    <!-- Contacto (máx. 11 dígitos, solo números) -->
                    <label for="contacto">Contacto *</label>
                    <input type="tel" name="contacto" id="contacto"
                        value="<?= htmlspecialchars($empresa['contacto']) ?>"
                        maxlength="11"
                        pattern="[0-9]{1,11}"
                        title="Solo se permiten números (máximo 11 dígitos)"
                        required
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="document.getElementById('editarEmpresaModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" name="editar_empresa" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
                </form>
        </div>
    </div>


    <script>
        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const modalEditar = document.getElementById('editarEmpresaModal');
            const successModal = document.getElementById('successModal');
            const errorModal = document.getElementById('errorModal');

            if (event.target == modalEditar) {
                modalEditar.style.display = "none";
            }
            if (event.target == successModal) {
                cerrarSuccessModal();
            }
            if (event.target == errorModal) {
                cerrarErrorModal();
            }
        };

        function cerrarSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'none';
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('exito');
                window.history.replaceState({path: url.href}, '', url.href);
            }
        }

        function cerrarErrorModal() {
            const errorModal = document.getElementById('errorModal');
            errorModal.style.display = 'none';
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({path: url.href}, '', url.href);
            }
        }

        // Script para hacer los modales arrastrables
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

        document.addEventListener('DOMContentLoaded', () => {
            const modalEditar = document.getElementById('editarEmpresaModal');
            if (modalEditar) {
                const modalContent = modalEditar.querySelector('.modal-content');
                const dragHandle = modalEditar.querySelector('.modal-header');
                if(modalContent && dragHandle) {
                    makeDraggable(modalContent, dragHandle);
                }
            }
        });
    </script>
</body>
</html>
