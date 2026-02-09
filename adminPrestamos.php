<?php
session_start();

if (!isset($_SESSION['id']) || ($_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado')) {
    header("Location: index.html");
    exit();
}

include 'conexion.php';

if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prestamo_id'])) {
    $prestamo_id = intval($_POST['prestamo_id']);
    $fecha_limite = mysqli_real_escape_string($enlace, $_POST['fecha_limite']);
    $multa = floatval($_POST['multa']);
    $estado = mysqli_real_escape_string($enlace, $_POST['estado']);
    $update = "UPDATE prestamos SET fecha_limite = '$fecha_limite', multa = $multa, estado = '$estado' WHERE id = $prestamo_id";
    mysqli_query($enlace, $update);

    if ($estado === 'Devuelto') {
        $q = mysqli_query($enlace, "SELECT libro_id FROM prestamos WHERE id = $prestamo_id");
        if ($row = mysqli_fetch_assoc($q)) {
            $libro_id = intval($row['libro_id']);
            mysqli_query($enlace, "UPDATE libros SET stock_disponible = stock_disponible + 1, estado = 'Disponible' WHERE id = $libro_id");
        }
    }
}

$consulta = "SELECT p.*, l.titulo FROM prestamos p LEFT JOIN libros l ON p.libro_id = l.id ORDER BY p.fecha_prestamo DESC";
$resultado = mysqli_query($enlace, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
   <center><title>Administrar Préstamos</title></center>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body { background: #f0f8ff; font-family: 'Poppins', sans-serif; padding: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff3e0; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #d35400; color: #fff; }
        tr:nth-child(even) { background: #f9e7d3; }
        h1 { color: #d35400; }
        
        select, button, input[type=date], input[type=number] { padding: 5px 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #67aee8; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #4c94d4; }
        .nuevo-btn {
            background: #2ecc71;
            padding: 10px 16px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .nuevo-btn:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
   <center><h1>Administrar Préstamos</h1></center>

    <div style="margin-bottom: 20px;">
        <center><a href="nuevoPrestamo.php" class="nuevo-btn"> Nuevo Préstamo</a></center>
    </div>

    <table>
        <tr>
            <th>Libro</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Fecha Préstamo</th>
            <th>Fecha Límite</th>
            <th>Multa</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
        <?php while ($prestamo = mysqli_fetch_assoc($resultado)) { ?>
     <tr>
    <form method="POST">
        <td><?= htmlspecialchars($prestamo['titulo'] ?? '') ?></td>
        <td><?= htmlspecialchars($prestamo['cliente_nombre']) ?></td>
        <td><?= htmlspecialchars($prestamo['cliente_telefono']) ?></td>
        <td><?= htmlspecialchars($prestamo['fecha_prestamo']) ?></td>
        <td>
            <input type="hidden" name="prestamo_id" value="<?= $prestamo['id'] ?>">
            <input type="date" name="fecha_limite" value="<?= htmlspecialchars($prestamo['fecha_limite']) ?>">
        </td>
        <td>
            <input type="number" name="multa" step="0.01" value="<?= htmlspecialchars($prestamo['multa'] ?? 0) ?>">
        </td>
        <td>
            <select name="estado" <?= $prestamo['estado'] === 'Devuelto' ? 'disabled' : '' ?>>
                <option value="Prestado" <?= $prestamo['estado']==='Prestado'?'selected':'' ?>>Prestado</option>
                <option value="Devuelto" <?= $prestamo['estado']==='Devuelto'?'selected':'' ?>>Devuelto</option>
                <option value="Retrasado" <?= $prestamo['estado']==='Retrasado'?'selected':'' ?>>Retrasado</option>
                <option value="Cancelado" <?= $prestamo['estado']==='Cancelado'?'selected':'' ?>>Cancelado</option>
            </select>
        </td>
        <td>
            <button type="submit">Actualizar</button>
        </td>
    </form>
</tr>

        <?php } ?>
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

 
</body>
</html>
<?php
include 'pie.php';
?>
