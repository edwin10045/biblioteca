<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['rol'] !== 'Empleado' && $_SESSION['rol'] !== 'Administrador')) {
    header("Location: index.html");
    exit();
}


include 'conexion.php';
if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}

$sql = "SELECT L.id, L.titulo, A.nombre_completo AS autor, E.nombre AS editorial, C.nombre AS categoria, L.precio_venta, L.portada_url
        FROM Libros L
        LEFT JOIN Autores A ON L.autor_id = A.id
        LEFT JOIN Editoriales E ON L.editorial_id = E.id
        LEFT JOIN Categorias C ON L.categoria_id = C.id
        WHERE L.estado != 'No disponible' AND L.stock_disponible > 0";

$result = mysqli_query($enlace, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Venta</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #67aee8; color: #fff; }
        .btn-comprar { background: #27ae60; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; }
        .btn-comprar:hover { background: #219150; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Libros Disponibles para Compra</h2>
    <table>
        <tr>
            <th>Portada</th>
            <th>Título</th>
            <th>Autor</th>
            <th>Editorial</th>
            <th>Categoría</th>
            <th>Precio de Venta</th>
            <th>Acción</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td>
                <?php if (!empty($row['portada_url'])): ?>
                    <img src="<?= htmlspecialchars($row['portada_url']) ?>" alt="Portada" style="width:60px;height:auto;">
                <?php else: ?>
                    <span>Sin imagen</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['titulo']) ?></td>
            <td><?= htmlspecialchars($row['autor'] ?? 'Desconocido') ?></td>
            <td><?= htmlspecialchars($row['editorial'] ?? 'Desconocida') ?></td>
            <td><?= htmlspecialchars($row['categoria'] ?? 'Sin categoría') ?></td>
            <td>$<?= number_format($row['precio_venta'], 2) ?></td>
            <td>
                <form action="formulario_venta.php" method="GET" style="margin:0;">
                    <input type="hidden" name="id_libro" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn-comprar">Comprar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<!-- Botones de navegación -->
<div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
   
    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
    <a href="consultarventas.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        📊 Ver Ventas
    </a>
        <?php endif; ?>
    <?php if ($_SESSION['rol'] === 'Empleado'): ?>
    <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
        🏠 Menú Principal
        <?php endif; ?>
    </a>
</div>

</body>
<?php include 'pie.php'; ?>
