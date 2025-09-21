<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

// === Recuperar mensaje de sesión (flash message) ===
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';

// === Limpiar el mensaje para que no aparezca al recargar ===
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

$conn = getConnection();

// === Configuración de paginación y búsqueda ===
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
$busqueda = trim($_GET['busqueda'] ?? '');

if (!in_array($porPagina, [10, 50, 100])) $porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// === Contar total facturas (con filtro de búsqueda) ===
$where = "";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $busqueda_like = '%' . $busqueda . '%';
    $where = " WHERE (c.nombre LIKE ? OR u.usuario LIKE ? OR f.idfactura LIKE ? OR f.fecha LIKE ?) ";
    $params = [$busqueda_like, $busqueda_like, $busqueda_like, $busqueda_like];
    $types = "ssss";
}

try {
    $sqlCount = "SELECT COUNT(*) as total FROM facturas f 
                 INNER JOIN clientes c ON f.idcliente = c.idcliente 
                 INNER JOIN usuarios u ON f.idusuario = u.idusuario $where";
    
    $stmtCount = $conn->prepare($sqlCount);
    if ($stmtCount) {
        if (!empty($params)) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $totalRegistros = $resultCount->fetch_assoc()['total'] ?? 0;
    } else {
        $totalRegistros = 0;
    }
} catch (Exception $e) {
    $totalRegistros = 0;
}

$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

// === Obtener facturas paginadas (con búsqueda) ===
$facturas = [];
try {
    $sql = "SELECT f.idfactura, 
               CONCAT(c.nombre, ' ', c.apellido) AS cliente, 
               u.usuario AS vendedor, 
               f.fecha, 
               f.total, 
               f.iva
        FROM facturas f
        INNER JOIN clientes c ON f.idcliente = c.idcliente
        INNER JOIN usuarios u ON f.idusuario = u.idusuario
        $where
        ORDER BY f.fecha DESC 
        LIMIT ? OFFSET ?";


    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types . "ii", ...array_merge($params, [$porPagina, $offset]));
        } else {
            $stmt->bind_param("ii", $porPagina, $offset);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $facturas = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $facturas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facturas </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos existentes (igual que tu código original) */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f85c; color: #333; }
        .container { display: flex; height: 100vh; }
        .main-content { flex: 1; padding: 1rem 1.5rem; margin: 0.5rem; overflow-y: auto; font-size: 14px; }
        .main-content h2 { color: #004080; margin-bottom: 1rem; font-size: 1.5rem; display: flex; align-items: center; }
        .main-content h2 i { margin-right: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; background-color: #004080; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: none; cursor: pointer; transition: background-color 0.3s; }
        .btn:hover { background-color: #2563eb; }
        .btn i { margin-right: 8px; }
        .btn-primary { background-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-info { background-color: #17a2b8; color: white; padding: 6px 10px; font-size: 13px; }
        .btn-info:hover { background-color: #138496; }
        .action-btn { border: none; background: none; cursor: pointer; padding: 5px 8px; border-radius: 5px; transition: 0.3s; }
        .action-btn i { font-size: 14px; }
        .edit-btn { color: #007bff; }
        .edit-btn:hover { background-color: #e6f0ff; }
        .print-btn { color: #28a745; }
        .print-btn:hover { background-color: #eafaf1; }
        .delete-btn { color: #dc3545; }
        .delete-btn:hover { background-color: #fdecea; }
        table { width: 100%; border-collapse: collapse; background-color: #fff; font-size: 13px; margin-top: 10px; }
        th, td { padding: 10px 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007acc; color: white; font-weight: 600; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .search-container { max-width: 350px; margin: 10px 0 20px 0; }
        .search-container input { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; font-size: 13px; color: #555; }
        .pagination { display: flex; list-style: none; margin: 0; padding: 0; gap: 5px; }
        .pagination a, .pagination span { padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #007bff; font-size: 13px; }
        .pagination a:hover { background-color: #007bff; color: white; }
        .pagination .current { background-color: #007bff; color: white; font-weight: bold; }
        .pagination .disabled { color: #ccc; cursor: not-allowed; }
        .per-page-select { padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; }
        .no-results { text-align: center; padding: 20px; color: #666; font-style: italic; }

        /* Estilos para modales (igual que tu código original) */
        .success-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 9999; }
        .success-modal-content { background: white; padding: 25px; border-radius: 8px; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); animation: fadeIn 0.3s ease; }
        .success-modal-content i { font-size: 50px; margin-bottom: 10px; }
        .success-modal-content h3 { margin: 0 0 10px 0; color: #004080; }
        .success-modal-content p { margin: 0 0 15px 0; color: #555; font-size: 14px; }
        .success-modal-content button { padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .success-modal-content button:hover { background-color: #0056b3; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/sidebar_inventario.php'); ?>

        <main class="main-content">
            <h2><i class="fas fa-file-invoice"></i> Gestión de Facturas</h2>

            <!-- Contenedor flex para alinear botón y búsqueda -->
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                <a href="nueva_factura.php" class="btn">
                    <i class="fas fa-plus"></i> Nueva Factura
                </a>
                <div class="search-container" style="flex: 1; max-width: 350px;">
                    <input type="text" id="busquedaFactura" 
                        placeholder="Buscar factura, cliente, vendedor, fecha..." 
                        value="<?= htmlspecialchars($busqueda) ?>">
                </div>
            </div>

            <!-- Tabla -->
            <table id="tablaFacturas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>IVA</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyFacturas">
                    <?php if (!empty($facturas)): ?>
                        <?php foreach ($facturas as $factura): ?>
                            <tr>
                                <td><?= $factura['idfactura'] ?></td>
                                <td><?= htmlspecialchars($factura['cliente']) ?></td>
                                <td><?= htmlspecialchars($factura['vendedor']) ?></td>
                                <td><?= $factura['fecha'] ?></td>
                                <td><?= number_format($factura['total'], 2) ?></td>
                                <td><?= number_format($factura['iva'], 2) ?></td>
                                <td>
                                    <button class="action-btn edit-btn"
                                            onclick="window.location.href='detalle_factura.php?id=<?= $factura['idfactura'] ?>'"
                                            title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="action-btn print-btn"
                                            onclick="window.location.href='imprimir_factura.php?id=<?= $factura['idfactura'] ?>'"
                                            title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="no-results">No se encontraron facturas.</td></tr>
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
                        <select id="por_pagina" class="per-page-select">
                            <option value="10" <?= $porPagina == 10 ? 'selected' : '' ?>>10</option>
                            <option value="50" <?= $porPagina == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $porPagina == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                </div>

                <ul class="pagination" style="margin-top:10px;">
                    <li>
                        <a href="?pagina=<?= max(1, $pagina - 1) ?>&por_pagina=<?= $porPagina ?>&busqueda=<?= urlencode($busqueda) ?>" 
                           class="<?= $pagina <= 1 ? 'disabled' : '' ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li>
                            <a href="?pagina=<?= $i ?>&por_pagina=<?= $porPagina ?>&busqueda=<?= urlencode($busqueda) ?>" 
                               class="<?= $i == $pagina ? 'current' : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li>
                        <a href="?pagina=<?= min($totalPaginas, $pagina + 1) ?>&por_pagina=<?= $porPagina ?>&busqueda=<?= urlencode($busqueda) ?>" 
                           class="<?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">Siguiente</a>
                    </li>
                </ul>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal de Éxito -->
    <div id="successModal" class="success-modal" style="<?= ($mensaje && $tipoMensaje === 'success') ? 'display:flex;' : 'display:none;' ?>">
        <div class="success-modal-content">
            <i class="fas fa-check-circle" style="color: #28a745;"></i>
            <h3>¡Éxito!</h3>
            <p><?= htmlspecialchars($mensaje) ?></p>
            <button onclick="cerrarSuccessModal()">Aceptar</button>
        </div>
    </div>

    <!-- Modal de Error -->
    <?php if ($mensaje && $tipoMensaje === 'error'): ?>
    <div id="errorModal" class="success-modal" style="display:flex; background-color: rgba(0,0,0,0.7);">
        <div class="success-modal-content">
            <i class="fas fa-times-circle" style="color: #dc3545;"></i>
            <h3>Error</h3>
            <p><?= htmlspecialchars($mensaje) ?></p>
            <button onclick="cerrarErrorModal()" style="background-color: #dc3545;">Aceptar</button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Funciones para cerrar modales
        function cerrarSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) modal.style.display = 'none';
        }

        function cerrarErrorModal() {
            const modal = document.getElementById('errorModal');
            if (modal) modal.style.display = 'none';
        }

        // Búsqueda en tiempo real y paginación segura
        document.addEventListener('DOMContentLoaded', function () {
            const inputBusqueda = document.getElementById('busquedaFactura');
            const filas = document.querySelectorAll('#tbodyFacturas tr');

            if (inputBusqueda) {
                inputBusqueda.addEventListener('input', function () {
                    const filtro = inputBusqueda.value.toLowerCase().trim();

                    // Actualizar URL sin recargar
                    const url = new URL(window.location);
                    if (filtro) {
                        url.searchParams.set('busqueda', filtro);
                    } else {
                        url.searchParams.delete('busqueda');
                    }
                    window.history.replaceState({}, '', url);

                    filas.forEach(fila => {
                        if (fila.querySelector('td[colspan]')) {
                            fila.style.display = filtro ? 'none' : '';
                            return;
                        }
                        const texto = fila.textContent.toLowerCase();
                        fila.style.display = texto.includes(filtro) ? '' : 'none';
                    });
                });
            }

            const selectPorPagina = document.getElementById('por_pagina');
            if (selectPorPagina) {
                selectPorPagina.addEventListener('change', function () {
                    const url = new URL(window.location);
                    url.searchParams.set('por_pagina', this.value);
                    url.searchParams.set('pagina', '1');
                    window.location.href = url.toString();
                });
            }
        });
    </script>
</body>
</html>
