<?php
//te explico el codigo para que lo puedas entender si lo quieres editar o algo por el estilo y no se te complique tanto
session_start();
include 'conexion.php';//la conexion
//para que funcione para admin y empleado, despues de todo es la misma pagina para ambos
if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}
// Verifica si se recibió una solicitud POST (formulario enviado)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recibe los datos del formulario
    $prestamo_id = $_POST['prestamo_id'];
    $fecha_devolucion = date("Y-m-d"); // Usa la fecha actual
    $estado_libro = $_POST['estado_libro']; // Estado del libro al devolverse
    $observaciones = $_POST['observaciones']; // Comentarios opcionales

    // 2. Obtiene la fecha límite y el ID del libro prestado desde la base de datos
    $consulta_prestamo = mysqli_query($enlace, "SELECT fecha_limite, libro_ID FROM prestamos WHERE id = $prestamo_id");
    $prestamo = mysqli_fetch_assoc($consulta_prestamo);
    $fecha_limite = $prestamo['fecha_limite'];
    $libro_id = $prestamo['libro_ID'];

    // 3. Calcula la multa: si hay retraso, cobra $5 por día
    $dias_retraso = (strtotime($fecha_devolucion) - strtotime($fecha_limite)) / 86400;
    $multa = $dias_retraso > 0 ? $dias_retraso * 5 : 0;

    // 4. Inserta el registro en la tabla libros_devueltos
    mysqli_query($enlace, "INSERT INTO libros_devueltos (
        prestamo_id, libro_id, fecha_devolucion, estado_libro, observaciones, multa_aplicada)
        VALUES ($prestamo_id, $libro_id, '$fecha_devolucion', '$estado_libro', '$observaciones', $multa)");

    // 5. Actualiza el préstamo como "Devuelto", con la fecha y la multa
    mysqli_query($enlace, "UPDATE prestamos 
        SET fecha_devolucion='$fecha_devolucion', multa=$multa, estado='Devuelto' 
        WHERE id=$prestamo_id");

    // 6. Muestra mensaje y redirecciona de vuelta a la página de devoluciones
    echo "<script>alert('Devolución registrada correctamente'); window.location.href='devoluciones.php';</script>";
    exit;
}

// Obtener todos los préstamos activos (aún no devueltos), agregue el where para que no muestre los cancelados
$prestamos = mysqli_query($enlace, "
    SELECT p.id, l.titulo, p.cliente_nombre
    FROM prestamos p
    LEFT JOIN libros l ON p.libro_ID = l.id
    WHERE p.fecha_devolucion IS NULL
      AND p.estado != 'Cancelado'
");


// Obtener el historial de devoluciones, con datos del libro y del cliente, agregue el where para que no muestre los cancelados
$historial = mysqli_query($enlace, "
    SELECT d.*, l.titulo, p.cliente_nombre
    FROM libros_devueltos d
    LEFT JOIN prestamos p ON d.prestamo_id = p.id
    LEFT JOIN libros l ON d.libro_id = l.id
    WHERE p.estado != 'Cancelado' 
    ORDER BY d.fecha_devolucion DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Devolución</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 30px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        form {
            width: 80%;
            margin: 0 auto 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        select, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #219150;
        }
        table {
            width: 80%;
            margin: 30px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #67aee8;
            color: #fff;
        }
        .btn-comprar {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-comprar:hover {
            background: #219150;
        }
       
    </style>
</head>
<body>

    <h1>Registrar Devolución</h1>

    <form method="POST">
        <label>Seleccionar préstamo:</label>
        <select name="prestamo_id" required>
            <option value="">-- Selecciona --</option>
            <?php 
// Recorre el resultado de la consulta de préstamos aún no devueltos
while ($p = mysqli_fetch_assoc($prestamos)) : ?>

    <!-- Crea una opción para cada préstamo -->
    <!-- value = ID del préstamo que se enviará al hacer submit -->
    <!-- El texto visible muestra: #ID - Título del libro (Nombre del cliente) -->
    <option value="<?= $p['id'] ?>">
        #<?= $p['id'] ?> - <?= $p['titulo'] ?> (<?= $p['cliente_nombre'] ?>)
    </option>

<?php endwhile; ?>
        </select>

        <label>Estado del libro al devolver:</label>
        <select name="estado_libro" required>
            <option value="Bueno">Bueno</option>
            <option value="Dañado">Dañado</option>
            <option value="Perdido">Perdido</option>
        </select>

        <label>Observaciones:</label>
        <textarea name="observaciones" rows="3" placeholder="Opcional..."></textarea>

        <input type="submit" value="Registrar Devolución">
    </form>

    <h2>Historial de Devoluciones</h2>
    <table>
        <thead>
            <tr>
                <th>Libro</th>
                <th>Cliente</th>
                <th>Fecha Devolución</th>
                <th>Estado del Libro</th>
                <th>Observaciones</th>
                <th>Multa</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($d = mysqli_fetch_assoc($historial)) :// ya sabes son los campos de la tabla que se mostrara en la de observacion ?>
                <tr> 
                    <td><?= htmlspecialchars($d['titulo']) ?></td>
                    <td><?= htmlspecialchars($d['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($d['fecha_devolucion']) ?></td>
                    <td><?= htmlspecialchars($d['estado_libro']) ?></td>
                    <td><?= htmlspecialchars($d['observaciones']) ?></td>
                    <td>$<?= number_format($d['multa_aplicada'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
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

    <br></br>
    <?php include 'pie.php'; ?>

</body>
</html>
            <!--Se usa cuando estás mezclando PHP con HTML, y quieres que sea más limpio o legible.
             Es equivalente a cerrar el while con una llave }.-->