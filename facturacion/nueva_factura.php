<?php
require_once('../config/database.php');
require_once('../config/seguridad.php');
//verificarPermisoPagina();

$conn = getConnection();
$clientes = $conn->query("SELECT idcliente, nombre, apellido FROM clientes WHERE activo = 1");
$productos = $conn->query("SELECT idproducto, nombre, precio, stock FROM productos WHERE activo = 1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Factura - AWFerreteria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f85c; color: #333; }
        .container { display: flex; height: 100vh; }
        .main-content { flex: 1; padding: 1.5rem; margin: 0.5rem; overflow-y: auto; font-size: 14px; }
        .main-content h2 { color: #004080; margin-bottom: 1.5rem; font-size: 1.6rem; display: flex; align-items: center; gap: 10px; }
        .main-content h3 { color: #004080; margin: 1.5rem 0 1rem; font-size: 1.3rem; border-bottom: 2px solid #007acc; padding-bottom: 5px; }
        
        /* Formulario */
        form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #444; }
        select, input[type="text"], input[type="number"] {
            width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; transition: border-color 0.3s; outline: none;
        }
        select:focus, input:focus {
            border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
        }

        /* Tabla de productos */
        table { width: 100%; border-collapse: collapse; margin: 1rem 0 1.5rem; font-size: 14px; background: #fff; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007acc; color: white; font-weight: 600; }
        tr:hover { background-color: #f8f9fa; }
        .btn-row-add { 
            background-color: #28a745; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 16px; 
            display: flex; align-items: center; justify-content: center; width: 30px; height: 30px;
        }
        .btn-row-add:hover { background-color: #218838; }
        .btn-row-remove { 
            background-color: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 14px; 
            display: flex; align-items: center; justify-content: center;
        }
        .btn-row-remove:hover { background-color: #c82333; }

        /* Inputs readonly */
        input[readonly] { background-color: #f8f9fa; font-weight: 500; color: #212529; }

        /* Botón principal */
        .btn-submit { 
            background-color: #004080; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; margin-top: 1rem; 
            display: inline-flex; align-items: center; gap: 6px; 
        }
        .btn-submit:hover { background-color: #2563eb; }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content { padding: 1rem; margin: 0.3rem; }
            .main-content h2 { font-size: 1.4rem; }
            .main-content h3 { font-size: 1.2rem; }
            form { padding: 1rem; }
            table, th, td { font-size: 13px; padding: 8px; }
            .btn-submit { padding: 9px 16px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../includes/sidebar_inventario.php'); ?>

        <main class="main-content">
            <h2><i class="fas fa-file-invoice"></i> Nueva Factura</h2>

            <form action="guardar_factura.php" method="POST">
                <!-- Cliente -->
                <div class="form-group">
                    <label for="idcliente">Cliente:</label>
                    <select name="idcliente" id="idcliente" required>
                        <option value="">Seleccione un cliente...</option>
                        <?php while ($c = $clientes->fetch_assoc()): ?>
                            <option value="<?= $c['idcliente'] ?>">
                                <?= htmlspecialchars(trim($c['nombre'] . ' ' . ($c['apellido'] ?? ''))) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Productos -->
                <h3><i class="fas fa-boxes"></i> Productos</h3>
                <table id="productosTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                            <th>
                                <button type="button" onclick="addRow()" class="btn-row-add" title="Agregar producto">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <!-- Totales -->
                <div class="form-group">
                    <label for="subtotal">Subtotal:</label>
                    <input type="text" id="subtotal" name="subtotal" value="0.00" readonly>
                </div>
                <div class="form-group">
                    <label for="iva">IVA (15%):</label>
                    <input type="text" id="iva" name="iva" value="0.00" readonly>
                </div>
                <div class="form-group">
                    <label for="total_final">Total con IVA:</label>
                    <input type="text" id="total_final" name="total_final" value="0.00" readonly>
                </div>

                <!-- Botón Guardar -->
                 <a href="facturas.php" class="btn-submit" style="background-color: #6c757d;">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                <button type="submit" name="guardar_factura" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Factura
                </button>
            </form>
        </main>
    </div>

    <script>
        // Lista de productos disponibles (desde PHP)
        const productosDisponibles = [
            <?php 
            $productos->data_seek(0);
            while ($p = $productos->fetch_assoc()): ?>
                {
                    id: <?= $p['idproducto'] ?>,
                    nombre: "<?= addslashes(htmlspecialchars($p['nombre'])) ?>",
                    precio: <?= $p['precio'] ?>,
                    stock: <?= $p['stock'] ?>
                },
            <?php endwhile; ?>
        ];

        // Agregar nueva fila de producto
        function addRow() {
            const tbody = document.querySelector("#productosTable tbody");
            const row = document.createElement('tr');
            
            // Generar opciones de productos (solo los no seleccionados)
            let selectHtml = `<select name="productos[]" required data-precio-field="precios[]" onchange="productoSeleccionado(this)" data-idproducto="">`;
            selectHtml += `<option value="">Seleccione producto...</option>`;
            
            // Filtrar productos que ya están seleccionados
            const productosSeleccionados = Array.from(tbody.querySelectorAll("select[name='productos[]']"))
                .map(sel => sel.value.split('|')[0])
                .filter(v => v !== "");

            productosDisponibles.forEach(p => {
                if (!productosSeleccionados.includes(String(p.id))) {
                    selectHtml += `<option value="${p.id}|${p.precio}" data-precio="${p.precio}">
                        ${p.nombre} (Stock: ${p.stock})
                    </option>`;
                }
            });
            
            selectHtml += `</select>`;

            row.innerHTML = `
                <td>${selectHtml}</td>
                <td><input type="number" name="cantidades[]" min="1" value="1" style="width:70px;" oninput="calcular()"></td>
                <td><input type="text" name="precios[]" readonly value="0.00"></td>
                <td><input type="text" name="subtotales[]" readonly value="0.00"></td>
                <td>
                    <button type="button" class="btn-row-remove" title="Eliminar" onclick="eliminarFila(this)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);

            // Prevenir Enter en inputs
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        calcular();
                    }
                });
            });

            calcular(); // Recalcular
        }

        // Cuando se selecciona un producto, actualizar precio y refrescar opciones
        function productoSeleccionado(select) {
            const option = select.options[select.selectedIndex];
            const precio = option.getAttribute('data-precio');
            const tr = select.closest('tr');
            tr.querySelector("input[name='precios[]']").value = precio ? parseFloat(precio).toFixed(2) : '0.00';
            calcular();
            actualizarSelects(); // Refrescar todos los selects
        }

        // Eliminar fila y liberar producto
        function eliminarFila(button) {
            const tr = button.closest('tr');
            tr.remove();
            actualizarSelects(); // Volver a mostrar el producto eliminado
            calcular();
        }

        // Actualizar todos los <select> para ocultar productos ya seleccionados
        function actualizarSelects() {
            const tbody = document.querySelector("#productosTable tbody");
            const selects = tbody.querySelectorAll("select[name='productos[]']");
            
            // Obtener IDs de productos ya seleccionados
            const seleccionados = Array.from(selects)
                .map(s => s.value.split('|')[0])
                .filter(v => v !== "");

            // Actualizar cada select
            selects.forEach(select => {
                const currentId = select.value.split('|')[0];
                const fragment = document.createDocumentFragment();
                
                // Opción por defecto
                const defaultOption = document.createElement('option');
                defaultOption.value = "";
                defaultOption.textContent = "Seleccione producto...";
                fragment.appendChild(defaultOption);

                // Agregar solo productos no seleccionados (o el que ya está seleccionado en este select)
                productosDisponibles.forEach(p => {
                    if (p.id == currentId || !seleccionados.includes(String(p.id))) {
                        const option = document.createElement('option');
                        option.value = `${p.id}|${p.precio}`;
                        option.setAttribute('data-precio', p.precio);
                        option.textContent = `${p.nombre} (Stock: ${p.stock})`;
                        if (p.id == currentId) option.selected = true;
                        fragment.appendChild(option);
                    }
                });

                // Reemplazar opciones
                select.innerHTML = '';
                select.appendChild(fragment);
            });
        }

        // Calcular totales
        function calcular() {
            let subtotal = 0;
            document.querySelectorAll("#productosTable tbody tr").forEach(tr => {
                const precio = parseFloat(tr.querySelector("input[name='precios[]']").value) || 0;
                const cantidad = parseInt(tr.querySelector("input[name='cantidades[]']").value) || 0;
                const subtotalFila = precio * cantidad;
                tr.querySelector("input[name='subtotales[]']").value = subtotalFila.toFixed(2);
                subtotal += subtotalFila;
            });

            const iva = subtotal * 0.15;
            const totalFinal = subtotal + iva;

            document.getElementById("subtotal").value = subtotal.toFixed(2);
            document.getElementById("iva").value = iva.toFixed(2);
            document.getElementById("total_final").value = totalFinal.toFixed(2);
        }

        // Prevenir Enter en formularios
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'textarea') {
                e.preventDefault();
                calcular();
            }
        });
    </script>
</body>
</html>