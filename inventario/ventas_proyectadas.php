<?php
require_once '../config/database.php';
require_once '../config/seguridad.php';
verificarPermisoPagina();

$conn = getConnection();

$sql = "SELECT * FROM vista_ventas_proyectadas ORDER BY idproducto ASC";
$resultado = $conn->query($sql);

$registros = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $registros[] = $fila;
    }
}
$totalRegistros = count($registros);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ventas Proyectadas</title>
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
        }

        tr:nth-child(even) {
            background-color: #fafbfd;
        }

        .venta-proyectada {
            font-weight: bold;
            color: #28a745;
        }

        input[type="text"] {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 13px;
            flex: 1;
        }

        .top-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            margin-top: 12px;
            gap: 4px;
        }

        .pagination a,
        .pagination span {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #007bff;
            text-decoration: none;
            background: white;
            font-size: 0.875rem;
        }

        .pagination .active a {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .no-registros {
            text-align: center;
            padding: 10px;
            font-size: 0.9rem;
        }

        /* Responsive: scroll horizontal si es necesario */
        @media (max-width: 900px) {
            .main-content {
                padding: 1rem;
            }

            #tabla {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar_inventario.php'; ?>

        <main class="main-content">
            <h3><i class="fas fa-chart-line"></i> Ventas Proyectadas (+10 unidades)</h3>

            <div class="top-bar">
                <a href="productos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <input type="text" id="busqueda" placeholder="Buscar producto o mes...">
            </div>

            <table id="tabla">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Mes Actual</th>
                        <th>Cant. Vendida</th>
                        <th>Total Venta</th>
                        <th>Precio Venta</th>
                        <th>Mes Proyectado</th>
                        <th>Proy. Unidades</th>
                        <th>Venta Proyectada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $fila): ?>
                            <tr class="fila">
                                <td><?= htmlspecialchars($fila['idproducto']) ?></td>
                                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                                <td><?= htmlspecialchars($fila['mes_actual']) ?></td>
                                <td><?= number_format($fila['total_cantidad_vendida'], 0) ?></td>
                                <td><?= number_format($fila['total_venta'], 2) ?></td>
                                <td><?= number_format($fila['precio_venta'], 2) ?></td>
                                <td><?= htmlspecialchars($fila['mes_proyectado']) ?></td>
                                <td><?= number_format($fila['proyeccion_unidades'], 0) ?></td>
                                <td class="venta-proyectada"><?= number_format($fila['total_venta_proyectada'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-registros">No hay registros</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <ul class="pagination" id="paginacion"></ul>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filas = document.querySelectorAll('.fila');
            const inputBusqueda = document.getElementById('busqueda');
            const paginacion = document.getElementById('paginacion');
            const porPagina = 10;
            let paginaActual = 1;
            let filtradas = Array.from(filas);

            function mostrarPagina(pagina) {
                const inicio = (pagina - 1) * porPagina;
                const fin = inicio + porPagina;

                filtradas.forEach(fila => fila.style.display = 'none');
                filtradas.slice(inicio, fin).forEach(fila => fila.style.display = '');

                renderPaginas();
            }

            function renderPaginas() {
                const totalPaginas = Math.ceil(filtradas.length / porPagina);
                paginacion.innerHTML = '';

                if (totalPaginas <= 1) return;

                for (let i = 1; i <= totalPaginas; i++) {
                    const li = document.createElement('li');
                    li.innerHTML = `<a href="#">${i}</a>`;
                    if (i === paginaActual) li.classList.add('active');

                    li.addEventListener('click', e => {
                        e.preventDefault();
                        paginaActual = i;
                        mostrarPagina(paginaActual);
                    });

                    paginacion.appendChild(li);
                }
            }

            inputBusqueda.addEventListener('input', e => {
                const termino = e.target.value.toLowerCase();
                filtradas = Array.from(filas).filter(fila =>
                    fila.textContent.toLowerCase().includes(termino)
                );
                paginaActual = 1;
                mostrarPagina(paginaActual);
            });

            mostrarPagina(paginaActual);
        });
    </script>
</body>
</html>