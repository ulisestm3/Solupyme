<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
$conn = getConnection();
$result = $conn->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.idcategoria = c.idcategoria WHERE p.stock <= p.stock_minimo AND p.activo = 1");
$alertas = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Productos con Stock Bajo - AWFerreteria</title>
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

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert-info i {
    margin-right: 10px;
    font-size: 20px;
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

/* Responsive ajustes fuera del sidebar */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
    }

    .main-content {
        padding: 1rem;
        margin: 0;
        font-size: 13px;
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
            <h3><i class="fas fa-exclamation-triangle"></i> Productos con Stock Bajo</h3>
            <?php if (empty($alertas)): ?>
                <div class="alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>No hay alertas de stock bajo en este momento.</span>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Faltante</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($alertas as $a):
                        $faltante = $a['stock_minimo'] - $a['stock'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($a['nombre']) ?></td>
                            <td><?= htmlspecialchars($a['categoria']) ?></td>
                            <td><?= $a['stock'] ?></td>
                            <td><?= $a['stock_minimo'] ?></td>
                            <td style="color: <?= $faltante > $a['stock_minimo'] ? '#dc3545' : '#040404ff'; ?>; font-weight: bold;">
                                <?= $faltante ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
