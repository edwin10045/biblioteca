<?php
include 'conexion.php';

$consulta = "SELECT r.*, l.titulo FROM reservas r JOIN libros l ON r.libro_id = l.id ORDER BY r.fecha_solicitud DESC";
$resultado = mysqli_query($enlace, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Reservas</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body { background: #f0f8ff; font-family: 'Poppins', sans-serif; padding: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; background: #fff3e0; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #d35400; color: #fff; }
        tr:nth-child(even) { background: #f9e7d3; }
        h1 { color: #d35400; }
        .volver { margin-top: 20px; display: inline-block; background: #67aee8; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; }
        .volver:hover { background: #4c94d4; }
    </style>
</head>
<body>
    <h1>Catálogo de Reservas</h1>
    <table>
        <tr>
            <th>Libro</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Fecha Solicitud</th>
            <th>Fecha Disponibilidad</th>
            <th>Estado</th>
        </tr>
        <?php while ($reserva = mysqli_fetch_assoc($resultado)) { ?>
        <tr>
            <td><?= htmlspecialchars($reserva['titulo']) ?></td>
            <td><?= htmlspecialchars($reserva['cliente_nombre']) ?></td>
            <td><?= htmlspecialchars($reserva['cliente_telefono']) ?></td>
            <td><?= htmlspecialchars($reserva['fecha_solicitud']) ?></td>
            <td><?= htmlspecialchars($reserva['fecha_disponibilidad']) ?></td>
            <td><?= htmlspecialchars($reserva['estado']) ?></td>
        </tr>
        <?php } ?>
    </table>
    <div style="text-align:center; margin-top:20px;">
        <button onclick="window.history.back();" style="background:#67aee8; color:#fff; border:none; padding:10px 22px; border-radius:7px; font-size:16px; cursor:pointer;">&larr; Volver</button>
    </div>
</body>
</html>
