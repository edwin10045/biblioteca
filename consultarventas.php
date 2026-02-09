<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include "conexion.php";
include "encabezado_admin.php";

$filtro_tipo = strtolower($_GET['filtro_tipo'] ?? 'todos');
$filtro_fecha_raw = match($filtro_tipo) {
    'dia' => $_GET['fecha_dia'] ?? '',
    'mes' => $_GET['fecha_mes'] ?? '',
    'año' => $_GET['fecha_anio'] ?? '',
    default => ''
};

$filtro_fecha = $filtro_fecha_raw;
$where = '';
$consulta_valida = true;

if ($filtro_tipo !== 'todos' && $filtro_fecha_raw !== '') {
    if ($filtro_tipo === 'dia' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtro_fecha_raw)) {
        $where = "WHERE DATE(v.fecha_compra) = '$filtro_fecha_raw'";
    } elseif ($filtro_tipo === 'mes' && preg_match('/^\d{4}-\d{2}$/', $filtro_fecha_raw)) {
        $where = "WHERE DATE_FORMAT(v.fecha_compra, '%Y-%m') = '$filtro_fecha_raw'";
    } elseif ($filtro_tipo === 'año' && preg_match('/^\d{4}$/', $filtro_fecha_raw)) {
        $where = "WHERE YEAR(v.fecha_compra) = '$filtro_fecha_raw'";
    } else {
        $consulta_valida = false;
    }
}

$result = false;
if ($consulta_valida) {
    $sql = "SELECT v.id AS id, v.fecha_compra, v.cliente_nombre, v.cliente_telefono, v.empleado_id,
                   v.total_pagado, v.metodo_pago, v.cantidad, l.titulo AS libro_titulo,
                   e.nombre_completo AS empleado_nombre
            FROM ventas v
            LEFT JOIN empleados e ON v.empleado_id = e.id
            LEFT JOIN libros l ON v.libro_id = l.id
            $where
            ORDER BY v.fecha_compra DESC";
    $result = mysqli_query($enlace, $sql);
}
?>


<style>
    h2 {
        text-align: center;
        color: #2c3e50;
        margin: 20px 0;
    }
    form.filtro-form {
        width: 90%;
        max-width: 700px;
        margin: 0 auto 30px auto;
        background: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: center;
    }
    form.filtro-form label {
        font-weight: 600;
        color: #2980b9;
        margin-right: 6px;
    }
    form.filtro-form select,
    form.filtro-form input[type="date"],
    form.filtro-form input[type="month"],
    form.filtro-form input[type="number"],
    form.filtro-form button {
        padding: 8px 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 15px;
    }
    form.filtro-form button {
        background-color: #2980b9;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    form.filtro-form button:hover {
        background-color: #1f6390;
    }
    table {
        width: 60%;
        margin: 0 auto 30px auto;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
    }
    th {
        background-color: #2980b9;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    tr:hover {
        background-color: #f1faff;
    }
    td a {
        color: #2980b9;
        text-decoration: none;
        font-weight: 600;
        margin-right: 8px;
        transition: color 0.3s ease;
    }
    td a:hover {
        color: #1c5980;
    }
    .registrar-btn {
        display: inline-block;
        margin: 10px auto 20px auto;
        padding: 10px 20px;
        background-color: #27ae60;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }
    .registrar-btn:hover {
        background-color: #1e8449;
    }
</style>

<h2>Historial de Ventas</h2>

<form method="GET" class="filtro-form" oninput="
    diaInput.style.display = filtro_tipo.value === 'dia' ? 'inline-block' : 'none';
    mesInput.style.display = filtro_tipo.value === 'mes' ? 'inline-block' : 'none';
    anioInput.style.display = filtro_tipo.value === 'año' ? 'inline-block' : 'none';
    if(filtro_tipo.value === 'todos'){
        diaInput.style.display = 'none';
        mesInput.style.display = 'none';
        anioInput.style.display = 'none';
    }
">
    <label for="filtro_tipo">Ver por:</label>
    <select name="filtro_tipo" id="filtro_tipo">
        <option value="todos" <?= $filtro_tipo === 'todos' ? 'selected' : '' ?>>Todos</option>
        <option value="dia" <?= $filtro_tipo === 'dia' ? 'selected' : '' ?>>Día</option>
        <option value="mes" <?= $filtro_tipo === 'mes' ? 'selected' : '' ?>>Mes</option>
        <option value="año" <?= $filtro_tipo === 'año' ? 'selected' : '' ?>>Año</option>
    </select>

    <input
        type="date"
        name="fecha_dia"
        id="diaInput"
        value="<?= $filtro_tipo === 'dia' ? htmlspecialchars($filtro_fecha) : '' ?>"
        style="display: <?= $filtro_tipo === 'dia' ? 'inline-block' : 'none' ?>"
    >

    <input
        type="month"
        name="fecha_mes"
        id="mesInput"
        value="<?= $filtro_tipo === 'mes' ? htmlspecialchars($filtro_fecha) : '' ?>"
        style="display: <?= $filtro_tipo === 'mes' ? 'inline-block' : 'none' ?>"
    >

    <input
        type="number"
        name="fecha_anio"
        id="anioInput"
        min="2000"
        max="<?= date('Y') ?>"
        step="1"
        value="<?= $filtro_tipo === 'año' ? htmlspecialchars($filtro_fecha) : '' ?>"
        style="width: 80px; display: <?= $filtro_tipo === 'año' ? 'inline-block' : 'none' ?>"
    >

    <button type="submit">Filtrar</button>
</form>

<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Libro (Cantidad)</th>
            <th>Empleado</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Método Pago</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($venta = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($venta['cliente_nombre']) ?></td>
            <td><?= htmlspecialchars($venta['cliente_telefono']) ?></td>
            <td><?= htmlspecialchars($venta['libro_titulo']) ?> (x<?= $venta['cantidad'] ?>)</td>
            <td><?= htmlspecialchars($venta['empleado_nombre'] ?? 'Desconocido') ?></td>
            <td><?= htmlspecialchars($venta['fecha_compra']) ?></td>
            <td>$<?= number_format($venta['total_pagado'], 2) ?></td>
            <td><?= htmlspecialchars($venta['metodo_pago']) ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align:center; padding: 20px; color:#555;">No hay ventas registradas para el filtro seleccionado.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<div style="text-align: center; margin-bottom: 20px;">
    <a href="registrar_compra.php" class="registrar-btn">Registrar nueva venta</a>
</div>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
    <?php if ($_SESSION['rol'] === 'Empleado'): ?>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
    <?php endif; ?>
</div>

<?php include 'pie.php'; ?>