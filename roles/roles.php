<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();
//verificarRol([1]); // Solo administradores pueden acceder al CRUD de roles


// Mensajes de éxito y error
$mensajes = [
    'exito' => [
        1 => "Rol agregado correctamente.",
        2 => "Rol editado correctamente.",
        3 => "Rol desactivado correctamente."
    ],
    'error' => [
        'duplicado' => "El nombre del rol ya existe. Por favor, elija otro.",
        'edicion' => "Error al editar el rol. Por favor, intente nuevamente.",
        'db' => "Error en la base de datos: ",
        'desactivacion' => "Error al borrar el rol: ",
        'rol_protegido' => "No se puede modificar o borrar el rol protegido."
    ]
];

$mensajeError = '';
if (isset($_GET['exito']) && isset($mensajes['exito'][$_GET['exito']])) {
    $mensajeError = $mensajes['exito'][$_GET['exito']];
} elseif (isset($_GET['error']) && isset($mensajes['error'][$_GET['error']])) {
    $mensajeError = $mensajes['error'][$_GET['error']] . (isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '');
}

// Verificación de sesión
if (!isset($_SESSION['idusuario'])) {
    header('Location: ../index.php');
    exit();
}

// Conexión
$conn = getConnection();

// Consulta de roles activos
$sql = "SELECT idrol, nombrerol, descripcion FROM roles WHERE activo = 1 ORDER BY idrol DESC";
$result = $conn->query($sql);

// Lógica para agregar rol
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_rol'])) {
    $nombrerol = $_POST['nombrerol'];
    $descripcion = $_POST['descripcion'];
    $usuarioregistra = $_SESSION['idusuario'];

    // Verificar si el rol ya existe
    $sqlCheck = "SELECT idrol FROM roles WHERE nombrerol = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $nombrerol);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        header("Location: roles.php?error=duplicado");
        exit();
    } else {
        // Insertar rol nuevo
        $sql = "INSERT INTO roles (nombrerol, descripcion, usuarioregistra) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombrerol, $descripcion, $usuarioregistra);

        if ($stmt->execute()) {
            header("Location: roles.php?exito=1");
            exit();
        } else {
            header("Location: roles.php?error=db&message=" . urlencode($conn->error));
            exit();
        }
    }
}

// Lógica para editar rol
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_rol'])) {
    $idrol = $_POST['idrol'];
    $nombrerol = $_POST['nombrerol'];
    $descripcion = $_POST['descripcion'];

    // Validación para el rol con ID 1 (Administrador)
    if ($idrol == 1) {
        header("Location: roles.php?error=rol_protegido");
        exit();
    }

    $sql = "UPDATE roles SET nombrerol = ?, descripcion = ? WHERE idrol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nombrerol, $descripcion, $idrol);

    if ($stmt->execute()) {
        header("Location: roles.php?exito=2");
        exit();
    } else {
        header("Location: roles.php?error=edicion&message=" . urlencode($conn->error));
        exit();
    }
}

// Lógica para desactivar rol
if (isset($_GET['desactivar'])) {
    $idrol = $_GET['desactivar'];

    // Validación para el rol con ID 1 (Administrador)
    if ($idrol == 1) {
        header("Location: roles.php?error=rol_protegido");
        exit();
    }

    $sql = "UPDATE roles SET activo = 0 WHERE idrol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idrol);

    if ($stmt->execute()) {
        header("Location: roles.php?exito=3");
        exit();
    } else {
        header("Location: roles.php?error=desactivacion&message=" . urlencode($conn->error));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Roles - AWFerreteria</title>
    <style>
        /* Reset básico */
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
        /* Main content */
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
        }
        a.btn {
            display: inline-block;
            background-color: #004080;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        a.btn:hover {
            background-color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            font-size: 13px;
        }
        th, td {
            padding: 6px 8px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007acc;
            color: white;
            font-weight: 600;
        }
        /* Acción editar */
        .action-link {
            display: inline-block;
            margin-right: 8px;
            cursor: pointer;
            vertical-align: middle;
        }
        .action-link svg {
            vertical-align: middle;
            transition: transform 0.2s ease;
        }
        .action-link:hover svg {
            transform: scale(1.2);
        }
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding: 0.5rem;
            }
            .main-content {
                padding: 1rem 1rem;
                margin: 0;
                font-size: 13px;
            }
        }
        /* Estilos para modales */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 15px;
        }
        .close, .close-message {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus, .close-message:hover, .close-message:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-content label {
            display: block;
            margin-top: 10px;
        }
        .modal-content input[type="text"],
        .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .modal-content button {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        .modal-content button:hover {
            background-color: #2563eb;
        }
        /* Estilos para mensajes */
        .mensaje-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .mensaje-contenido {
            background-color: white;
            border-radius: 10px;
            padding: 20px 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            text-align: center;
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }
        .mensaje-contenido.success {
            border-top: 6px solid #28a745;
        }
        .mensaje-contenido.error {
            border-top: 6px solid #dc3545;
        }
        .mensaje-icono {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .mensaje-texto {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        .mensaje-cerrar {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .mensaje-cerrar:hover {
            background-color: #0056b3;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!--sidebar_seguridad-->
        <?php include('../includes/sidebar_seguridad.php'); ?>

        <main class="main-content">
            <h1>Roles registrados</h1>
            <a href="#" class="btn btn-primary mb-3" id="nuevoRolBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 5px; vertical-align: middle;">
                    <path d="M12 12c2.7 0 4.88-2.18 4.88-4.88S14.7 2.25 12 2.25 7.13 4.43 7.13 7.13 9.3 12 12 12zm0 2.25c-3.38 0-10.13 1.69-10.13 5.06v2.44h20.25v-2.44c0-3.37-6.75-5.06-10.13-5.06z"/>
                </svg>
                Nuevo Rol
            </a>
            <table>
                <thead>
                    <tr>
                        <th style="display: none;">ID</th>
                        <th>Nombre del Rol</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="display: none;"><?= htmlspecialchars($row['idrol']) ?></td>
                        <td><?= htmlspecialchars($row['nombrerol']) ?></td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                        <td align="center">
                            <a href="#" title="Editar" class="action-link" onclick="abrirModalEdicion(
                                <?= $row['idrol'] ?>,
                                '<?= htmlspecialchars($row['nombrerol'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($row['descripcion'], ENT_QUOTES) ?>'
                            )">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007acc" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/>
                                </svg>
                            </a>
                            <a href="#" title="Desactivar" class="action-link" onclick="abrirModalConfirmacionDesactivar(<?= $row['idrol'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#d9534f" viewBox="0 0 24 24">
                                    <path d="M3 6h18v2H3V6zm2 3h14v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9zm3 3v6h2v-6H8zm4 0v6h2v-6h-2zM9 4h6v2H9V4z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal para nuevo rol -->
    <div id="nuevoRolModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('nuevoRolModal')">&times;</span>
            <h2 style="margin-bottom: 20px; color: #004080;">Agregar Nuevo Rol</h2>
            <form action="roles.php" method="post">
                <label for="nombrerol">Nombre del Rol: *</label>
                <input type="text" id="nombrerol" name="nombrerol" required>
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                <button type="submit" name="agregar_rol">Agregar Rol</button>
            </form>
        </div>
    </div>

    <!-- Modal para editar rol -->
    <div id="editarRolModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('editarRolModal')">&times;</span>
            <h2 style="margin-bottom: 20px; color: #004080;">Editar Rol</h2>
            <form action="roles.php" method="post">
                <input type="hidden" id="editar_idrol" name="idrol">
                <label for="editar_nombrerol">Nombre del Rol: *</label>
                <input type="text" id="editar_nombrerol" name="nombrerol" required>
                <label for="editar_descripcion">Descripción:</label>
                <textarea id="editar_descripcion" name="descripcion" rows="3"></textarea>
                <button type="submit" name="editar_rol">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación de desactivación -->
    <div id="confirmarDesactivarModal" class="modal">
        <div class="modal-content" style="text-align: center; width: auto; max-width: 400px;">
            <h3 style="margin-bottom: 20px; color: #004080;">Confirmar Borrado</h3>
            <p style="margin-bottom: 30px;">¿Estás seguro de que quieres borrar este rol?</p>
            <div style="display: inline-flex; justify-content: center; gap: 20px; margin: 0 auto;">
                <button id="btnConfirmarDesactivar"
                        style="background-color: #d9534f; color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; width: 120px;">
                    Borrar
                </button>
                <button id="btnCancelarDesactivar"
                        style="background-color: #ccc; color: #333; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; width: 120px;">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar mensajes
        function mostrarMensaje(mensaje, tipo = 'success') {
            const overlay = document.createElement('div');
            overlay.className = 'mensaje-modal';
            const contenido = document.createElement('div');
            contenido.className = `mensaje-contenido ${tipo}`;
            const icono = document.createElement('div');
            icono.className = 'mensaje-icono';
            icono.innerHTML = tipo === 'success' ? '✅' : '❌';
            const texto = document.createElement('div');
            texto.className = 'mensaje-texto';
            texto.innerText = mensaje;
            const botonCerrar = document.createElement('button');
            botonCerrar.className = 'mensaje-cerrar';
            botonCerrar.innerText = 'Cerrar';
            botonCerrar.onclick = () => {
                document.body.removeChild(overlay);
            };
            contenido.appendChild(icono);
            contenido.appendChild(texto);
            contenido.appendChild(botonCerrar);
            overlay.appendChild(contenido);
            document.body.appendChild(overlay);
        }

        // Función para cerrar modales y limpiar campos
        function cerrarModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';

            // Limpiar campos del formulario
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
        }

        // Función para abrir el modal de edición
        function abrirModalEdicion(idrol, nombrerol, descripcion) {
            document.getElementById('editar_idrol').value = idrol;
            document.getElementById('editar_nombrerol').value = nombrerol;
            document.getElementById('editar_descripcion').value = descripcion;
            document.getElementById('editarRolModal').style.display = 'block';
        }

        // Variable para almacenar el ID del rol a desactivar
        let rolADesactivar = null;

        // Función para abrir el modal de confirmación de desactivación
        function abrirModalConfirmacionDesactivar(idrol) {
            rolADesactivar = idrol;
            document.getElementById('confirmarDesactivarModal').style.display = 'block';
        }

        // Manejar el botón de confirmar desactivación
        document.getElementById('btnConfirmarDesactivar').addEventListener('click', function() {
            if (rolADesactivar) {
                window.location.href = `roles.php?desactivar=${rolADesactivar}`;
            }
        });

        // Manejar el botón de cancelar desactivación
        document.getElementById('btnCancelarDesactivar').addEventListener('click', function() {
            document.getElementById('confirmarDesactivarModal').style.display = 'none';
            rolADesactivar = null;
        });

        // Manejar el botón de nuevo rol
        document.getElementById("nuevoRolBtn").addEventListener("click", function(event) {
            event.preventDefault();
            document.getElementById("nuevoRolModal").style.display = "block";
        });

        // Cerrar modales si se hace clic fuera de ellos
        window.addEventListener("click", function(event) {
            const modales = ['nuevoRolModal', 'editarRolModal', 'confirmarDesactivarModal'];
            modales.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    cerrarModal(modalId);
                }
            });
        });

        <?php if (!empty($mensajeError)): ?>
            window.onload = function () {
                <?php $tipoMensaje = (strpos($mensajeError, 'correctamente') !== false) ? 'success' : 'error'; ?>
                mostrarMensaje("<?= htmlspecialchars($mensajeError) ?>", "<?= $tipoMensaje ?>");
                <?php if ($tipoMensaje === 'error'): ?>
                    document.getElementById('nuevoRolModal').style.display = 'block';
                <?php endif; ?>
            };
        <?php endif; ?>
    </script>
</body>
</html>
