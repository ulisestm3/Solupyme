<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
// verificarPermisoPagina();

$conn = getConnection();
$movimientos = [];

// Obtener lista de movimientos
try {
    $sql = "SELECT m.idmovimiento, p.nombre AS producto, m.tipo, m.cantidad, m.comentario, m.fecha, u.usuario 
            FROM movimientos m
            INNER JOIN productos p ON m.idproducto = p.idproducto
            INNER JOIN usuarios u ON m.idusuario = u.idusuario
            ORDER BY m.fecha DESC";

    $stmt = $conn->query($sql);
    $movimientos = $stmt->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    echo "Error al cargar movimientos: " . $e->getMessage();
}

$mensaje = '';
$tipoMensaje = '';

// Lista de productos activos
$productos = $conn->query("SELECT idproducto, nombre FROM productos WHERE activo = b'1'")
                  ->fetch_all(MYSQLI_ASSOC);

// --- Función separada para actualizar stock ---
function actualizarStock(mysqli $conn, int $idproducto, string $tipo, int $cantidad): void {
    if (strtolower($tipo) === 'entrada') {
        $stmt = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE idproducto = ?");
    } else {
        $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE idproducto = ?");
    }
    $stmt->bind_param("ii", $cantidad, $idproducto);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idproducto = intval($_POST["idproducto"]);
    $tipo = $_POST["tipo"];
    $cantidad = intval($_POST["cantidad"]);
    $comentario = trim($_POST["comentario"]);
    $idusuario = $_SESSION['idusuario'] ?? 1;

    // Validación básica
    if ($cantidad <= 0 || $comentario === '') {
        $mensaje = "La cantidad debe ser mayor a cero y el comentario es obligatorio.";
        $tipoMensaje = "error";
    }

    // Validación específica para salida
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

    // Si pasa todas las validaciones
    if ($mensaje === '') {
        try {
            $conn->begin_transaction();

            // Insertar el movimiento
            $insert = $conn->prepare("INSERT INTO movimientos (idproducto, tipo, cantidad, comentario, fecha, idusuario) VALUES (?, ?, ?, ?, NOW(), ?)");
            $insert->bind_param("isisi", $idproducto, $tipo, $cantidad, $comentario, $idusuario);
            $insert->execute();
            $insert->close();

            // ✅ Aquí se usa la función correctamente
            actualizarStock($conn, $idproducto, $tipo, $cantidad);

            $conn->commit();
            $_SESSION['mensaje'] = "✅ Movimiento registrado correctamente.";
            $_SESSION['tipoMensaje'] = "success";
            header("Location: movimientos.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "❌ Error al registrar el movimiento: " . $e->getMessage();
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
    <style>
        /* --- Sidebar --- */
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
        /* --- Main content --- */
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
        /* Modal mensajes */
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
        .mensaje-contenido.warning {
            border-top: 6px solid #ffc107;
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
                <a href="../inventario/productos.php">Productos</a>
                <a href="../inventario/categorias.php">Categorías</a>
                <a href="../inventario/movimientos.php">Movimientos</a>
                <a href="../inventario/stock_bajo.php">Stock Bajo</a>
            </nav>
            <a href="../auth/logout.php" class="logout-btn">Cerrar sesión</a>
        </aside>
        <main class="main-content">
            <h3>Movimientos de Inventario (Kardex)</h3> <br>

            <form method="POST" style="margin-bottom: 20px;">
                <label for="idproducto">Producto:</label><br>
                <select name="idproducto" id="idproducto" required style="width: 100%; padding: 6px; margin-bottom: 10px;">
                    <option value="">Seleccione un producto</option>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['idproducto'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="tipo">Tipo:</label><br>
                <select name="tipo" id="tipo" required style="width: 100%; padding: 6px; margin-bottom: 10px;">
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                </select>

                <div id="stockDisponibleContainer" style="display:none; margin-top: 5px;">
                    <strong>Stock disponible:</strong> <span id="stockDisponibleTexto">0</span>
                </div>

                <label for="cantidad">Cantidad:</label><br>
                <input type="number" name="cantidad" id="cantidad" min="1" required style="width: 100%; padding: 6px; margin-bottom: 10px;">

                <label for="comentario">Comentario:</label><br>
                <input type="text" name="comentario" required style="width: 100%; padding: 6px; margin-bottom: 10px;">

                <button type="submit" class="btn">Guardar Movimiento</button>
            </form>

            <?php if ($mensaje): ?>
            <div class="mensaje-modal" id="modalMensaje">
                <div class="mensaje-contenido <?= $tipoMensaje ?>">
                    <div class="mensaje-icono"><?= $tipoMensaje === 'success' ? '✅' : '❌' ?></div>
                    <div class="mensaje-texto"><?= $mensaje ?></div>
                    <button class="mensaje-cerrar" onclick="document.getElementById('modalMensaje').style.display='none'">Cerrar</button>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const modal = document.getElementById('modalMensaje');
                    if(modal) modal.style.display = 'none';
                }, 4000);
            </script>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($movimientos)) : ?>
                    <?php foreach ($movimientos as $mov) : ?>
                    <tr>
                        <td><?= $mov['idmovimiento'] ?></td>
                        <td><?= htmlspecialchars($mov['producto']) ?></td>
                        <td><?= ucfirst($mov['tipo']) ?></td>
                        <td><?= $mov['cantidad'] ?></td>
                        <td><?= htmlspecialchars($mov['comentario']) ?></td>
                        <td><?= $mov['fecha'] ?></td>
                        <td><?= $mov['usuario'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7">No hay movimientos registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Advertencia cantidad -->
    <div class="mensaje-modal" id="modalAdvertenciaCantidad" style="display:none;">
        <div class="mensaje-contenido warning">
            <div class="mensaje-icono">⚠️</div>
            <div class="mensaje-texto" id="textoAdvertenciaCantidad">No puedes ingresar una cantidad mayor que el stock disponible.</div>
            <button class="mensaje-cerrar" onclick="cerrarModalAdvertencia()">Cerrar</button>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo');
    const productoSelect = document.getElementById('idproducto');
    const stockContainer = document.getElementById('stockDisponibleContainer');
    const stockTexto = document.getElementById('stockDisponibleTexto');
    const cantidadInput = document.getElementById('cantidad');
    const modalAdvertencia = document.getElementById('modalAdvertenciaCantidad');
    const textoAdvertencia = document.getElementById('textoAdvertenciaCantidad');
    let stockActual = 0;
    let timeoutModal;

    function mostrarModalAdvertencia(mensaje) {
        textoAdvertencia.textContent = mensaje;
        modalAdvertencia.style.display = 'flex';
        if(timeoutModal) clearTimeout(timeoutModal);
        timeoutModal = setTimeout(() => {
            modalAdvertencia.style.display = 'none';
        }, 20000);
    }

    window.cerrarModalAdvertencia = function () {
        modalAdvertencia.style.display = 'none';
        if(timeoutModal) clearTimeout(timeoutModal);
    }

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
                            mostrarModalAdvertencia(`La cantidad máxima disponible para salida es ${stockActual}. Se ha ajustado el valor.`);
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
                mostrarModalAdvertencia(`No puedes ingresar una cantidad mayor que el stock disponible *** ${stockActual} ***.`);
            }
        }
    });
});
</script>
</body>
</html>
