<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include 'conexion.php';
include 'encabezado_admin.php';

$query = "SELECT id, nombre_completo, usuario, telefono, fecha_contratacion, estado, rol FROM Empleados WHERE rol = 'Empleado'";
$result = $enlace->query($query);
?>

<style>
    h2 {
        text-align: center;
        color: #2c3e50;
        margin: 20px 0;
    }

    a.registrar-btn {
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
    a.registrar-btn:hover {
        background-color: #1e8449;
    }

    table {
        width: 90%;
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
</style>

<h2>Gestión de Empleados</h2>
<div style="text-align: center; margin-bottom: 20px;">
    <a href="registrar_empleado.php" class="registrar-btn">Registrar nuevo empleado</a>
</div>

<table>
    <thead>
        <tr>
            <th>Nombre Completo</th>
            <th>Usuario</th>
            <th>Teléfono</th>
            <th>Fecha Contratación</th>
            <th>Estado</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($empleado = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($empleado['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($empleado['usuario']) ?></td>
            <td><?= htmlspecialchars($empleado['telefono']) ?></td>
            <td><?= htmlspecialchars($empleado['fecha_contratacion']) ?></td>
            <td><?= htmlspecialchars($empleado['estado']) ?></td>
            <td><?= htmlspecialchars($empleado['rol']) ?></td>
            <td>
                <a href="editar_empleado.php?id=<?= $empleado['id'] ?>">✏️ Editar</a>
                <a href="eliminar_empleado.php?id=<?= $empleado['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este empleado?');">🗑️ Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align:center; padding: 20px; color:#555;">No hay empleados registrados.</td>
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
