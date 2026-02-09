<?php
include 'conexion.php';

$result = mysqli_query($enlace, "
    SELECT V.id, V.cliente_nombre, V.fecha_compra, V.total_pagado, V.metodo_pago, L.titulo 
    FROM Ventas V
    JOIN Libros L ON V.libro_id = L.id
    ORDER BY V.fecha_compra DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas Registradas</title>
</head>
<body>
    <h2>Ventas Realizadas</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Libro</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Método de pago</th>
        </tr>
        <?php while ($venta = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= $venta['id'] ?></td>
            <td><?= htmlspecialchars($venta['cliente_nombre']) ?></td>
            <td><?= htmlspecialchars($venta['titulo']) ?></td>
            <td><?= $venta['fecha_compra'] ?></td>
            <td>$<?= number_format($venta['total_pagado'], 2) ?></td>
            <td><?= htmlspecialchars($venta['metodo_pago']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
