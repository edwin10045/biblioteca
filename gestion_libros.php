<?php
session_start();
include 'conexion.php';
include 'encabezado_admin.php';

$query = "SELECT Libros.*, Editoriales.nombre AS editorial, Autores.nombre_completo AS autor, Categorias.nombre AS categoria 
          FROM Libros 
          LEFT JOIN Editoriales ON Libros.editorial_id = Editoriales.id
          LEFT JOIN Autores ON Libros.autor_id = Autores.id
          LEFT JOIN Categorias ON Libros.categoria_id = Categorias.id";
$resultado = mysqli_query($enlace, $query);
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        margin: 20px 0;
        color: #2c3e50;
    }

    a {
        text-decoration: none;
        color: #2980b9;
        font-weight: bold;
        margin-left: 20px;
    }

    a:hover {
        color: #1c5980;
    }

    table {
        width: 95%;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
    }

    th {
        background-color: #2980b9;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 14px;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #e9f4ff;
    }

    td a {
        margin-right: 10px;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 13px;
    }

    td a[href*="editar_libro"] {
        background-color: #f1c40f;
        color: #2c3e50;
    }

    td a[href*="eliminar_libro"] {
        background-color: #e74c3c;
        color: white;
    }

    td a[href*="editar_libro"]:hover {
        background-color: #d4ac0d;
    }

    td a[href*="eliminar_libro"]:hover {
        background-color: #c0392b;
    }

    .btn-agregar {
        display: block;
        width: fit-content;
        margin: 0 auto 20px auto;
        background-color: #27ae60;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    .btn-agregar:hover {
        background-color: #1e8449;
    }
</style>

<h2>Gestión de Libros</h2>
<a class="btn-agregar" href="agregar_libro.php"> Agregar nuevo libro</a>

<table>
    <tr>
        <th>Título</th>
        <th>Autor</th>
        <th>Editorial</th>
        <th>Categoría</th>
        <th>Idioma</th>
        <th>Precio Venta</th>
            <th>Precio Alquiler</th> 
        <th>Stock Disponible</th>  
        <th>Stock Total</th>       
        <th>Acciones</th>
    </tr>
    <?php while ($libro = mysqli_fetch_assoc($resultado)) { ?>
        <tr>
    <td><?= htmlspecialchars($libro['titulo']) ?></td>
    <td><?= htmlspecialchars($libro['autor']) ?></td>
    <td><?= htmlspecialchars($libro['editorial']) ?></td>
    <td><?= htmlspecialchars($libro['categoria']) ?></td>
    <td><?= htmlspecialchars($libro['idioma']) ?></td>
    <td>$<?= number_format($libro['precio_venta'], 2) ?></td>
    <td>$<?= number_format($libro['precio_alquiler'], 2) ?></td> 
    <td><?= (int)$libro['stock_disponible'] ?></td> 
    <td><?= (int)$libro['stock_total'] ?></td>      
    <td>
        <a href="editar_libro.php?id=<?= $libro['id'] ?>">✏️ Editar</a>
        <a href="eliminar_libro.php?id=<?= $libro['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este libro?');">🗑️ Eliminar</a>
    </td>
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

<?php include 'pie.php'; ?>