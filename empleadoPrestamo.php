<?php
session_start();

include 'conexion.php';

$consulta = "SELECT p.*, l.titulo FROM prestamos p LEFT JOIN libros l ON p.libro_id = l.id ORDER BY p.fecha_prestamo DESC";
$resultado = mysqli_query($enlace, $consulta);
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($enlace));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Préstamos - Empleado</title>
    <style>
        body { background: #f2f2f2; font-family: Arial, sans-serif; padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #6c7ae0; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        h1 { color: #6c7ae0; text-align: center; }
        .volver {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background: #6c7ae0;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .volver:hover { background: #4e5bbf; }
    </style>
</head>
<body>
    <h1>Préstamos</h1>
    <table>
        <thead>
            <tr>
                <th>Libro</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Límite</th>
                <th>Multa</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($prestamo = mysqli_fetch_assoc($resultado)) : ?>
                <tr>
                    <td><?= htmlspecialchars($prestamo['titulo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($prestamo['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($prestamo['cliente_telefono']) ?></td>
                    <td><?= htmlspecialchars($prestamo['fecha_prestamo']) ?></td>
                    <td><?= htmlspecialchars($prestamo['fecha_limite']) ?></td>
                    <td><?= htmlspecialchars($prestamo['multa']) ?></td>
                    <td><?= htmlspecialchars($prestamo['estado']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <form method="GET" style="margin-bottom: 15px;">
    <button type="submit" style="padding: 8px 16px; background:#6c7ae0; color:#fff; border:none; border-radius:5px; cursor:pointer;">
        Actualizar
    </button>
</form>

    </table>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
    <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
    </a>
</div>

</body>
</html>
