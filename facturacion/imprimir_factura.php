<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
//verificarPermisoPagina();

$conn = getConnection();

$idfactura = intval($_GET['id'] ?? 0);

// Consultar datos de la factura (incluyendo activo)
$sql = "SELECT f.idfactura, f.fecha, f.total, f.iva, f.activo,
               c.nombre, c.apellido, c.telefono, c.direccion,
               u.usuario AS vendedor
        FROM facturas f
        INNER JOIN clientes c ON f.idcliente = c.idcliente
        INNER JOIN usuarios u ON f.idusuario = u.idusuario
        WHERE f.idfactura = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idfactura);
$stmt->execute();
$factura = $stmt->get_result()->fetch_assoc();

// Consultar detalle de productos
$sqlDetalle = "SELECT d.cantidad, d.precio, p.nombre
               FROM detalle_factura d
               INNER JOIN productos p ON d.idproducto = p.idproducto
               WHERE d.idfactura = ?";
$stmtDetalle = $conn->prepare($sqlDetalle);
$stmtDetalle->bind_param("i", $idfactura);
$stmtDetalle->execute();
$detalle = $stmtDetalle->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?= $factura['idfactura'] ?> - AWFerretería</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; color: #333; }
        .factura-container { 
            max-width: 800px; margin: auto; border: 1px solid #ddd; 
            padding: 20px; border-radius: 6px; background: #fff; 
            position: relative;
        }
        h1 { text-align: center; color: #004080; margin-bottom: 10px; }
        .datos-empresa { text-align: center; margin-bottom: 20px; }
        .datos-factura, .datos-cliente { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 14px; }
        th { background: #004080; color: #fff; }
        .totales { margin-top: 20px; float: right; width: 300px; }
        .totales table { width: 100%; }
        .no-print { margin-top: 20px; text-align: center; }
        .btn-print { background: #004080; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin: 5px; }
        .btn-print:hover { background: #2563eb; }

        /* Etiqueta de factura anulada */
        .sello-anulada {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.25); /* rojo semitransparente */
            border: 5px solid rgba(220, 53, 69, 0.25);
            padding: 20px 50px;
            border-radius: 10px;
            text-transform: uppercase;
            pointer-events: none; /* que no interfiera con selección de texto */
            z-index: 1000;
        }

        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            .factura-container { border: none; }
        }
    </style>
</head>
<body>
<div class="factura-container">
    <?php if (isset($factura['activo']) && $factura['activo'] == 0): ?>
        <div class="sello-anulada">ANULADA</div>
    <?php endif; ?>

    <h1>AWFerretería</h1>
    <div class="datos-empresa">
        <p>Dirección: Managua, Nicaragua</p>
        <p>Tel: (505) 2222-3333</p>
    </div>

    <div class="datos-factura">
        <strong>Factura N°:</strong> <?= $factura['idfactura'] ?><br>
        <strong>Fecha:</strong> <?= $factura['fecha'] ?><br>
        <strong>Vendedor:</strong> <?= htmlspecialchars($factura['vendedor']) ?>
    </div>

    <div class="datos-cliente">
        <strong>Cliente:</strong> <?= htmlspecialchars($factura['nombre'] . " " . $factura['apellido']) ?><br>
        <strong>Teléfono:</strong> <?= htmlspecialchars($factura['telefono']) ?><br>
        <strong>Dirección:</strong> <?= htmlspecialchars($factura['direccion']) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Descripción</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalle as $item): ?>
            <tr>
                <td><?= $item['cantidad'] ?></td>
                <td><?= htmlspecialchars($item['nombre']) ?></td>
                <td><?= number_format($item['precio'], 2) ?></td>
                <td><?= number_format($item['cantidad'] * $item['precio'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totales">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td><?= number_format($factura['total'] - $factura['iva'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>IVA:</strong></td>
                <td><?= number_format($factura['iva'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>Total:</strong></td>
                <td><strong><?= number_format($factura['total'], 2) ?></strong></td>
            </tr>
        </table>
    </div>

    <div style="clear:both;"></div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
        <button class="btn-print" style="background:#6c757d;" onclick="window.location.href='facturas.php'">↩️ Volver a Facturas</button>
    </div>
</div>
</body>
</html>
