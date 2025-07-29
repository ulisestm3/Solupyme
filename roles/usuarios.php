<?php
require_once('../config/database.php');
session_start();

$mensajeError = '';

if (isset($_GET['exito']) && $_GET['exito'] == 1) {
    $mensajeError = "Usuario agregado correctamente.";
} elseif (isset($_GET['error']) && $_GET['error'] === 'duplicado') {
    $mensajeError = "El nombre de usuario ya existe. Por favor, elija otro.";
}


// Verificación de sesión
if (!isset($_SESSION['idusuario'])) {
    header('Location: ../index.php');
    exit();
}

// Conexión
$conn = getConnection();

// Consulta de roles
$sqlRoles = "SELECT idrol, nombrerol FROM roles WHERE activo = 1";
$resultRoles = $conn->query($sqlRoles);

// Consulta de usuarios con su rol
$sql = "SELECT u.idusuario, u.nombrecompleto, u.usuario, u.correo, u.telefono, u.activo, r.nombrerol
        FROM usuarios u
        LEFT JOIN roles r ON u.idrol = r.idrol
        ORDER BY u.idusuario DESC";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_usuario'])) {
    $nombrecompleto = $_POST['nombrecompleto'];
    $usuario = $_POST['usuario'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $idrol = $_POST['idrol'];
    $usuarioregistra = $_SESSION['idusuario'];

    // Verificar si el usuario ya existe
    $sqlCheck = "SELECT idusuario FROM usuarios WHERE usuario = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $usuario);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $mensajeError = "El nombre de usuario ya existe. Por favor, elija otro.";
    } else {
        // Insertar usuario nuevo
        $sql = "INSERT INTO usuarios (nombrecompleto, usuario, contrasena, correo, telefono, idrol, usuarioregistra) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $nombrecompleto, $usuario, $contrasena, $correo, $telefono, $idrol, $usuarioregistra);

        if ($stmt->execute()) {
            // Redirige para evitar reenvío del formulario
            header("Location: usuarios.php?exito=1");
            exit();
        } else {
            header("Location: usuarios.php?error=duplicado");
            exit();
        }
    
        $stmt->close();
        
    }

    $stmtCheck->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Usuarios</title>
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
        /* Sidebar */
        .sidebar {
            width: 200px;
            background-color: #352b56ff;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1rem 1rem;
        }
        .sidebar h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        .sidebar nav a {
            color: white;
            text-decoration: none;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.4rem;
            display: block;
            transition: background-color 0.3s ease;
            font-size: 0.9rem;
        }
        .sidebar nav a:hover {
            background-color: #0066cc;
        }
        .sidebar .logout-btn {
            margin-top: auto;
            background-color: #5596ebff;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.9rem;
        }
        .sidebar .logout-btn:hover {
            background-color: #a3ad4cff;
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
            .sidebar h2 {
                flex: 1 0 auto;
                margin-bottom: 0;
                padding-right: 1rem;
                text-align: left;
                font-size: 1rem;
            }
            .sidebar nav {
                display: flex;
                gap: 1rem;
            }
            .sidebar nav a {
                margin-bottom: 0;
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }
            .sidebar .logout-btn {
                margin-top: 0;
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }
            .main-content {
                padding: 1rem 1rem;
                margin: 0;
                font-size: 13px;
            }
        }
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
        .modal-content input[type="password"],
        .modal-content input[type="email"],
        .modal-content select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal-content button {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .modal-content button:hover {
            background-color: #2563eb;
        }
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
        <aside class="sidebar">
            <h2>AWFerreteria</h2>
            <nav>
                <a href="../admin/dashboard_admin.php">Dashboard</a>
                <a href="../roles/usuarios.php">Usuarios</a>
                <a href="/admin/productos/listado.php">Roles</a>
            </nav>
            <a href="../auth/logout.php" class="logout-btn">Cerrar sesión</a>
        </aside>
        <main class="main-content">
            <h1>Usuarios registrados</h1>
            <a href="?nuevo=1" class="btn btn-primary mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 5px; vertical-align: middle;">
                    <path d="M12 12c2.7 0 4.88-2.18 4.88-4.88S14.7 2.25 12 2.25 7.13 4.43 7.13 7.13 9.3 12 12 12zm0 2.25c-3.38 0-10.13 1.69-10.13 5.06v2.44h20.25v-2.44c0-3.37-6.75-5.06-10.13-5.06z"/>
                </svg>
                Nuevo
            </a>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre completo</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['idusuario']) ?></td>
                        <td><?= htmlspecialchars($row['nombrecompleto']) ?></td>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= htmlspecialchars($row['correo']) ?></td>
                        <td><?= htmlspecialchars($row['telefono']) ?></td>
                        <td><?= htmlspecialchars($row['nombrerol']) ?></td>
                        <td><?= $row['activo'] ? 'Sí' : 'No' ?></td>
                        <td align="center">
                            <a href="?editar=<?= $row['idusuario'] ?>" title="Editar" class="action-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007acc" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/>
                                </svg>
                            </a>
                            <a href="?eliminar=<?= $row['idusuario'] ?>" title="Eliminar" class="action-link" onclick="return confirm('¿Eliminar este usuario?')">
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

    <!-- Modal para nuevo usuario -->
    <div id="nuevoUsuarioModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('nuevoUsuarioModal').style.display='none'">&times;</span>
            <h2>Agregar Nuevo Usuario</h2>
            <form action="usuarios.php" method="post">
                <label for="nombrecompleto">Nombre Completo:</label>
                <input type="text" id="nombrecompleto" name="nombrecompleto" required>
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono">
                <label for="idrol">Rol:</label>
                <select id="idrol" name="idrol" required>
                    <option value="">Seleccione un rol</option>
                    <?php
                    // Reiniciar el puntero del resultado de roles
                    $resultRoles->data_seek(0);
                    while ($rowRol = $resultRoles->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($rowRol['idrol']) ?>">
                            <?= htmlspecialchars($rowRol['nombrerol']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="agregar_usuario">Agregar Usuario</button>
            </form>
        </div>
    </div>

    <!-- Modal para mensajes -->
    <div id="mensajeModal" class="modal">
        <div class="modal-content">
            <span class="close-message" onclick="document.getElementById('mensajeModal').style.display='none'">&times;</span>
            <p id="mensajeTexto"></p>
        </div>
    </div>

        <script>
            // Obtener el modal de nuevo usuario
            var modal = document.getElementById("nuevoUsuarioModal");
            // Obtener el botón que abre el modal de nuevo usuario
            var btn = document.querySelector("a.btn");

            // Cuando el usuario hace clic en el botón, abre el modal de nuevo usuario
            btn.onclick = function(event) {
                event.preventDefault();
                modal.style.display = "block";
            }

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
    </script>

<?php if (!empty($mensajeError)): ?>
<script>
    window.onload = function () {
        <?php $tipoMensaje = (strpos($mensajeError, 'correctamente') !== false) ? 'success' : 'error'; ?>
        mostrarMensaje("<?= htmlspecialchars($mensajeError) ?>", "<?= $tipoMensaje ?>");

        <?php if ($tipoMensaje === 'error'): ?>
            // Si hubo error (como usuario existente), reabre el modal
            document.getElementById('nuevoUsuarioModal').style.display = 'block';
        <?php endif; ?>
    };
</script>
<?php endif; ?>


</body>
</html>
