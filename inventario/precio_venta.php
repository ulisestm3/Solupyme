<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
verificarPermisoPagina();

$conn = getConnection();

// Consulta a la vista: obtenemos TODOS los registros para permitir búsqueda inline eficaz
$sql = "SELECT *
        FROM vista_precios_productos
        ORDER BY nombre ASC";

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Precios de Venta</title>
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
        /* Paginación estilo productos.php */
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 15px 0 0;
            justify-content: center;
            gap: 4px;
        }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            text-decoration: none;
            color: #007bff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: white;
            font-size: 13px;
        }
        .pagination a:hover {
            background-color: #e9ecef;
        }
        .pagination .active a {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination .disabled span {
            color: #6c757d;
            pointer-events: none;
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
            <h3><i class="fas fa-dollar-sign"></i> Precios de Venta (15% margen de ganancia)</h3>

            <!-- Botón de regreso y búsqueda -->
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 15px; flex-wrap: wrap;">
                <a href="productos.php" class="btn" style="background-color: #6c757d;"><i class="fas fa-arrow-left"></i> Volver a Productos</a>
                <input type="text" id="busquedaPrecios" placeholder="Buscar por nombre, ID o factura..." 
                       style="flex: 1; min-width: 200px; padding: 7px 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px;">
            </div>

            <table id="tablaPrecios">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>N° Factura</th>
                        <th>Fecha Factura</th>
                        <th>Precio Unitario</th>
                        <th>Precio Venta</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $fila): ?>
                            <tr class="fila-registro">
                                <td><?= htmlspecialchars($fila['idproducto']) ?></td>
                                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                                <td><?= htmlspecialchars($fila['numero_factura']) ?></td>
                                <td><?= date('d/m/Y', strtotime($fila['fecha_factura'])) ?></td>
                                <td><?= number_format($fila['ultimo_precio'], 2) ?></td>
                                <td style="color: #28a745; font-weight: bold;"><?= number_format($fila['precio_venta'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #666; padding: 15px;">No se encontraron registros</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación (generada por JS) -->
            <ul class="pagination" id="paginacion"></ul>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filas = document.querySelectorAll('.fila-registro');
            const inputBusqueda = document.getElementById('busquedaPrecios');
            const paginacionContainer = document.getElementById('paginacion');

            const registrosPorPagina = 10;
            let paginaActual = 1;
            let filasFiltradas = Array.from(filas);

            function mostrarPagina(pagina) {
                const inicio = (pagina - 1) * registrosPorPagina;
                const fin = inicio + registrosPorPagina;

                filasFiltradas.forEach(fila => fila.style.display = 'none');
                filasFiltradas.slice(inicio, fin).forEach(fila => fila.style.display = '');

                renderizarPaginacion();
            }

            function renderizarPaginacion() {
                const totalPaginas = Math.ceil(filasFiltradas.length / registrosPorPagina);
                paginacionContainer.innerHTML = '';

                if (totalPaginas <= 1) return;

                // Anterior
                if (paginaActual > 1) {
                    const li = document.createElement('li');
                    li.innerHTML = `<a href="#">&laquo;</a>`;
                    li.addEventListener('click', e => {
                        e.preventDefault();
                        paginaActual--;
                        mostrarPagina(paginaActual);
                    });
                    paginacionContainer.appendChild(li);
                }

                // Rango de páginas
                let inicio = Math.max(1, paginaActual - 2);
                let fin = Math.min(totalPaginas, paginaActual + 2);
                if (fin - inicio < 4) {
                    if (inicio === 1) fin = Math.min(totalPaginas, 5);
                    else inicio = Math.max(1, fin - 4);
                }

                if (inicio > 1) {
                    const li1 = document.createElement('li');
                    li1.innerHTML = '<a href="#">1</a>';
                    li1.addEventListener('click', () => { paginaActual = 1; mostrarPagina(paginaActual); });
                    paginacionContainer.appendChild(li1);
                    if (inicio > 2) {
                        const li2 = document.createElement('li');
                        li2.innerHTML = '<span>...</span>';
                        li2.classList.add('disabled');
                        paginacionContainer.appendChild(li2);
                    }
                }

                for (let i = inicio; i <= fin; i++) {
                    const li = document.createElement('li');
                    if (i === paginaActual) {
                        li.classList.add('active');
                        li.innerHTML = `<a href="#">${i}</a>`;
                    } else {
                        li.innerHTML = `<a href="#">${i}</a>`;
                    }
                    li.addEventListener('click', (function(p) {
                        return function(e) {
                            e.preventDefault();
                            paginaActual = p;
                            mostrarPagina(paginaActual);
                        };
                    })(i));
                    paginacionContainer.appendChild(li);
                }

                if (fin < totalPaginas) {
                    if (fin < totalPaginas - 1) {
                        const li1 = document.createElement('li');
                        li1.innerHTML = '<span>...</span>';
                        li1.classList.add('disabled');
                        paginacionContainer.appendChild(li1);
                    }
                    const li2 = document.createElement('li');
                    li2.innerHTML = `<a href="#">${totalPaginas}</a>`;
                    li2.addEventListener('click', () => { paginaActual = totalPaginas; mostrarPagina(paginaActual); });
                    paginacionContainer.appendChild(li2);
                }

                // Siguiente
                if (paginaActual < totalPaginas) {
                    const li = document.createElement('li');
                    li.innerHTML = `<a href="#">&raquo;</a>`;
                    li.addEventListener('click', e => {
                        e.preventDefault();
                        paginaActual++;
                        mostrarPagina(paginaActual);
                    });
                    paginacionContainer.appendChild(li);
                }
            }

            inputBusqueda.addEventListener('input', function () {
                const termino = this.value.toLowerCase().trim();
                filasFiltradas = Array.from(filas).filter(fila => {
                    return termino === '' || fila.textContent.toLowerCase().includes(termino);
                });
                paginaActual = 1;
                mostrarPagina(paginaActual);
            });

            // Inicializar
            mostrarPagina(paginaActual);
        });
    </script>
</body>
</html>