<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador' && $_SESSION['rol'] !== 'Empleado') {
    header("Location: index.html");
    exit();
}

include "conexion.php";
if ($_SESSION['rol'] === 'Administrador') {
    include "encabezado_admin.php";
} elseif ($_SESSION['rol'] === 'Empleado') {
    include "encabezado_empleado.php";
}

// Actualización de reservas (fecha o estado)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reserva_id'])) {
    $reserva_id = $_POST['reserva_id'];
    $fecha_disponibilidad = $_POST['fecha_disponibilidad'] ?? null;
    $estado = $_POST['estado'] ?? null;

    // Limpiar fecha para evitar cadena vacía
    if (empty($fecha_disponibilidad)) {
        $fecha_disponibilidad = null; // para enviar NULL a la BD
    }

    if ($fecha_disponibilidad === null) {
        // Preparar consulta con fecha_disponibilidad = NULL
        $sql_update = "UPDATE reservas SET fecha_disponibilidad = NULL, estado = ? WHERE id = ?";
        $stmt = mysqli_prepare($enlace, $sql_update);
        mysqli_stmt_bind_param($stmt, 'si', $estado, $reserva_id);
    } else {
        // Preparar consulta con fecha_disponibilidad con valor válido
        $sql_update = "UPDATE reservas SET fecha_disponibilidad = ?, estado = ? WHERE id = ?";
        $stmt = mysqli_prepare($enlace, $sql_update);
        mysqli_stmt_bind_param($stmt, 'ssi', $fecha_disponibilidad, $estado, $reserva_id);
    }
    mysqli_stmt_execute($stmt);
}

// Eliminar reserva
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = $_GET['eliminar_id'];
    $sql_delete = "DELETE FROM reservas WHERE id = $eliminar_id";
    mysqli_query($enlace, $sql_delete);
}

// Consultar todas las reservas
$sql = "SELECT r.*, l.titulo 
        FROM reservas r 
        LEFT JOIN libros l ON r.libro_id = l.id 
        ORDER BY r.fecha_solicitud DESC";
$result = mysqli_query($enlace, $sql);
?>

<style>
table {
    width: 90%;
    margin: 0 auto 40px auto;
    border-collapse: collapse;
    background-color: white;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

thead th {
    background-color: #4c94d4;
    color: white;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
}

tbody td {
    padding: 12px 15px;
    border-top: 1px solid #eee;
    color: #333;
}

tbody tr:hover {
    background-color: #f4faff;
}

form.inline-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

form.inline-form input[type="date"],
form.inline-form select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    width: 160px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

form.inline-form input[type="date"]:focus,
form.inline-form select:focus {
    border-color: #2980b9;
    outline: none;
}

.btn-guardar {
    background-color: #27ae60;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-guardar:hover {
    background-color: #1e8449;
}

.btn-eliminar {
    background-color: #2980b9;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-eliminar:hover {
    background-color: #1f6390;
}
h2 {
    text-align: center;
    margin-top: 20px;
    margin-bottom: 20px;
}

</style>

<h2>Gestión de Reservas</h2>
<div style="text-align: center; margin: 20px 0;">
    <a href="crearReserva.php" class="btn-guardar" style="text-decoration: none;">
        + Hacer Reserva
    </a>
</div>
<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Libro</th>
            <th>Fecha de Solicitud</th>
            <th>Fecha de Disponibilidad</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($reserva = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($reserva['cliente_nombre']) ?></td>
                <td><?= htmlspecialchars($reserva['cliente_telefono']) ?></td>
                <td><?= htmlspecialchars($reserva['titulo'] ?? 'Desconocido') ?></td>
                <td><?= htmlspecialchars($reserva['fecha_solicitud']) ?></td>

                <!-- Formulario unificado para fecha_disponibilidad, estado y botón guardar -->
                <form method="POST" class="inline-form">
                    <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                    <td>
                        <input type="date" name="fecha_disponibilidad" value="<?= htmlspecialchars($reserva['fecha_disponibilidad']) ?>">
                    </td>
                    <td>
                        <select name="estado">
                            <option value="Pendiente" <?= $reserva['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="Confirmada" <?= $reserva['estado'] === 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                            <option value="Cancelada" <?= $reserva['estado'] === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" class="btn-guardar">Guardar</button>
                </form>

                <!-- Formulario para eliminar -->
                <form method="GET" action="consultasReservas.php" onsubmit="return confirm('¿Deseas eliminar esta reserva?')" style="display:inline-block;">
                    <input type="hidden" name="eliminar_id" value="<?= $reserva['id'] ?>">
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align:center; padding: 20px; color:#555;">No hay reservas registradas.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
<center><button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
        ← Volver Atrás
    </button></center>
    
<?php include 'pie.php'; ?>
