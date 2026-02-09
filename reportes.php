<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include "conexion.php";
include "encabezado_admin.php";

$tipo_reporte = $_GET['tipo_reporte'] ?? 'multas';

$tipos_validos = ['multas', 'libros_mas_prestados', 'libros_mas_vendidos', 'libros_devueltos'];
if (!in_array($tipo_reporte, $tipos_validos)) {
    $tipo_reporte = 'multas';
}

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

$campo_fecha = match($tipo_reporte) {
    'multas' => 'p.fecha_devolucion',
    'libros_mas_prestados' => 'p.fecha_prestamo',
    'libros_mas_vendidos' => 'v.fecha_compra',
    'libros_devueltos' => 'p.fecha_devolucion',
};

if ($filtro_tipo !== 'todos' && $filtro_fecha_raw !== '') {
    if ($filtro_tipo === 'dia' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtro_fecha_raw)) {
        $where = "AND DATE($campo_fecha) = '$filtro_fecha_raw'";
    } elseif ($filtro_tipo === 'mes' && preg_match('/^\d{4}-\d{2}$/', $filtro_fecha_raw)) {
        $where = "AND DATE_FORMAT($campo_fecha, '%Y-%m') = '$filtro_fecha_raw'";
    } elseif ($filtro_tipo === 'año' && preg_match('/^\d{4}$/', $filtro_fecha_raw)) {
        $where = "AND YEAR($campo_fecha) = '$filtro_fecha_raw'";
    } else {
        $consulta_valida = false;
    }
}

$result = false;

if ($consulta_valida) {
    switch ($tipo_reporte) {
                case 'multas':
            $sql = "
    SELECT 
        p.cliente_nombre, 
        l.titulo,
        p.multa AS monto, 
        'Préstamo' AS tipo,
        p.fecha_devolucion
    FROM prestamos p
    INNER JOIN libros l ON p.libro_id = l.id
    WHERE p.multa > 0
    $where

    UNION ALL

    SELECT 
        p.cliente_nombre, 
        l.titulo,
        d.multa_aplicada AS monto, 
        'Devolución con retraso' AS tipo,
        d.fecha_devolucion
    FROM libros_devueltos d
    INNER JOIN prestamos p ON d.prestamo_id = p.id
    INNER JOIN libros l ON p.libro_id = l.id
    WHERE d.multa_aplicada > 0
    $where

    ORDER BY fecha_devolucion DESC
";
    break;



        case 'libros_mas_prestados':
            $sql = "SELECT l.titulo, COUNT(*) AS total_prestamos
                    FROM prestamos p
                    INNER JOIN libros l ON p.libro_id = l.id
                    WHERE 1=1 $where
                    GROUP BY l.id
                    ORDER BY total_prestamos DESC
                    LIMIT 10";
            break;

        case 'libros_mas_vendidos':
            $sql = "SELECT l.titulo, COUNT(*) AS total_ventas
                    FROM ventas v
                    INNER JOIN libros l ON v.libro_id = l.id
                    WHERE 1=1 $where
                    GROUP BY l.id
                    ORDER BY total_ventas DESC
                    LIMIT 10";
            break;

        case 'libros_devueltos':
            $sql = "SELECT l.titulo, p.cliente_nombre, p.fecha_devolucion
                    FROM prestamos p
                    INNER JOIN libros l ON p.libro_id = l.id
                    WHERE p.fecha_devolucion IS NOT NULL $where
                    ORDER BY p.fecha_devolucion DESC";
            break;
    }

    $result = mysqli_query($enlace, $sql);
}
?>

<style>
    body {
        background-color: #fff4e8;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    h2 {
        text-align: center;
        font-weight: 700;
        color: #222;
        margin-top: 20px;
        margin-bottom: 15px;
    }
    .nav-tabs {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    .nav-tabs a {
        text-decoration: none;
        font-weight: 600;
        color: #2980b9;
        padding: 8px 20px;
        border-radius: 30px;
        border: 2px solid transparent;
        transition: 0.3s;
    }
    .nav-tabs a.active,
    .nav-tabs a:hover {
        background-color: #2980b9;
        color: white;
        border-color: #2980b9;
    }
    table {
        width: 80%;
        margin: 0 auto 30px auto;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #2980b9;
        color: white;
        font-weight: 700;
    }
    tr:hover {
        background-color: #f1faff;
    }
</style>

<h2>Reportes de Biblioteca</h2>

<div class="nav-tabs">
    <a href="?tipo_reporte=multas" class="<?= $tipo_reporte === 'multas' ? 'active' : '' ?>">Multas</a>
    <a href="?tipo_reporte=libros_mas_prestados" class="<?= $tipo_reporte === 'libros_mas_prestados' ? 'active' : '' ?>">Libros más prestados</a>
    <a href="?tipo_reporte=libros_mas_vendidos" class="<?= $tipo_reporte === 'libros_mas_vendidos' ? 'active' : '' ?>">Libros más vendidos</a>
    <a href="?tipo_reporte=libros_devueltos" class="<?= $tipo_reporte === 'libros_devueltos' ? 'active' : '' ?>">Libros devueltos</a>
</div>
<form method="GET" style="text-align: center; margin-bottom: 20px;">
    <input type="hidden" name="tipo_reporte" value="<?= htmlspecialchars($tipo_reporte) ?>">

    <label for="filtro_tipo">Filtrar por:</label>
    <select name="filtro_tipo" id="filtro_tipo" onchange="mostrarCampoFecha()" style="margin: 0 10px;">
        <option value="todos" <?= $filtro_tipo === 'todos' ? 'selected' : '' ?>>Todos</option>
        <option value="dia" <?= $filtro_tipo === 'dia' ? 'selected' : '' ?>>Día</option>
        <option value="mes" <?= $filtro_tipo === 'mes' ? 'selected' : '' ?>>Mes</option>
        <option value="año" <?= $filtro_tipo === 'año' ? 'selected' : '' ?>>Año</option>
    </select>

    <input
        type="date"
        name="fecha_dia"
        id="campo_dia"
        style="display: none;"
        value="<?= $filtro_tipo === 'dia' ? htmlspecialchars($filtro_fecha) : '' ?>"
    >

    <input
        type="month"
        name="fecha_mes"
        id="campo_mes"
        style="display: none;"
        value="<?= $filtro_tipo === 'mes' ? htmlspecialchars($filtro_fecha) : '' ?>"
    >

    <input
        type="number"
        name="fecha_anio"
        id="campo_anio"
        style="display: none; width: 80px;"
        min="1900"
        max="<?= date('Y') ?>"
        value="<?= $filtro_tipo === 'año' ? htmlspecialchars($filtro_fecha) : '' ?>"
    >

    <button type="submit" style="margin-left: 10px; padding: 5px 15px;">Aplicar</button>
</form>

<script>
    function mostrarCampoFecha() {
        const tipo = document.getElementById("filtro_tipo").value;
        document.getElementById("campo_dia").style.display = tipo === "dia" ? "inline-block" : "none";
        document.getElementById("campo_mes").style.display = tipo === "mes" ? "inline-block" : "none";
        document.getElementById("campo_anio").style.display = tipo === "año" ? "inline-block" : "none";
    }
    // Ejecutar al cargar la página
    window.onload = mostrarCampoFecha;
</script>


<table>
    <thead>
        <tr>
            <?php
            switch ($tipo_reporte) {
                case 'multas':
echo "<th>CLIENTE</th><th>TÍTULO</th><th>MONTO</th><th>TIPO</th><th>FECHA DEVOLUCIÓN</th>";
                    break;
                case 'libros_mas_prestados':
                    echo "<th>TÍTULO</th><th>TOTAL PRÉSTAMOS</th>";
                    break;
                case 'libros_mas_vendidos':
                    echo "<th>TÍTULO</th><th>TOTAL VENTAS</th>";
                    break;
                case 'libros_devueltos':
                    echo "<th>TÍTULO</th><th>CLIENTE</th><th>FECHA DEVOLUCIÓN</th>";
                    break;
            }
            ?>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <?php
            switch ($tipo_reporte) {
                case 'multas':
            echo "<td>" . htmlspecialchars($row['cliente_nombre']) . "</td>";
echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
echo "<td>$" . number_format($row['monto'], 2) . "</td>";
echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
echo "<td>" . htmlspecialchars($row['fecha_devolucion'] ?? 'Pendiente') . "</td>";

                    break;
                case 'libros_mas_prestados':
                    echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['total_prestamos']) . "</td>";
                    break;
                case 'libros_mas_vendidos':
                    echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['total_ventas']) . "</td>";
                    break;
                case 'libros_devueltos':
                    echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cliente_nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha_devolucion']) . "</td>";
                    break;
            }
            ?>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" style="text-align:center; padding: 20px; color:#555;">No hay registros para este reporte y filtro.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

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
