<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

include 'conexion.php';

$error = "";
$success = "";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: gestion_empleados.php");
    exit();
}

$stmt = $enlace->prepare("SELECT nombre_completo, usuario, telefono, fecha_contratacion, estado, rol FROM Empleados WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$empleado = $result->fetch_assoc();
$stmt->close();

if (!$empleado) {
    header("Location: gestion_empleados.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $fecha_contratacion = $_POST['fecha_contratacion'] ?? date('Y-m-d');
    $estado = trim($_POST['estado'] ?? 'Activo');
    $rol = $_POST['rol'] ?? '';

    if ($nombre_completo === '' || $usuario === '' || $rol === '') {
        $error = "Por favor completa los campos obligatorios.";
    } else {
        $stmt_check = $enlace->prepare("SELECT id FROM Empleados WHERE usuario = ? AND id != ?");
        $stmt_check->bind_param("si", $usuario, $id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "El usuario ya existe para otro empleado.";
        } else {
            if ($contraseña !== '') {
                $hash = password_hash($contraseña, PASSWORD_DEFAULT);
                $stmt_update = $enlace->prepare("UPDATE Empleados SET nombre_completo=?, usuario=?, contraseña=?, telefono=?, fecha_contratacion=?, estado=?, rol=? WHERE id=?");
                $stmt_update->bind_param("sssssssi", $nombre_completo, $usuario, $hash, $telefono, $fecha_contratacion, $estado, $rol, $id);
            } else {
                $stmt_update = $enlace->prepare("UPDATE Empleados SET nombre_completo=?, usuario=?, telefono=?, fecha_contratacion=?, estado=?, rol=? WHERE id=?");
                $stmt_update->bind_param("ssssssi", $nombre_completo, $usuario, $telefono, $fecha_contratacion, $estado, $rol, $id);
            }

            if ($stmt_update->execute()) {
                $success = "Empleado actualizado correctamente.";
                $stmt_update->close();
                $stmt = $enlace->prepare("SELECT nombre_completo, usuario, telefono, fecha_contratacion, estado, rol FROM Empleados WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $empleado = $result->fetch_assoc();
                $stmt->close();
            } else {
                $error = "Error al actualizar empleado: " . $enlace->error;
            }
        }
        $stmt_check->close();
    }
}

include 'encabezado_admin.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Empleado</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { width: 400px; background: white; margin: 40px auto; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        input, select { width: 100%; padding: 8px; margin: 8px 0; }
        .error { color: red; }
        .success { color: green; }
        input[type=submit] { background: #007BFF; color: white; border: none; cursor: pointer; }
        input[type=submit]:hover { background: #0056b3; }
        .header { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        

        <h2>Editar Empleado</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Nombre completo *</label>
            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($empleado['nombre_completo']) ?>" required>

            <label>Usuario *</label>
            <input type="text" name="usuario" value="<?= htmlspecialchars($empleado['usuario']) ?>" required>

            <label>Contraseña (dejar vacío para no cambiar)</label>
            <input type="text" name="contraseña">

            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($empleado['telefono']) ?>">

            <label>Fecha en que se contrató</label>
            <input type="date" name="fecha_contratacion" value="<?= htmlspecialchars($empleado['fecha_contratacion']) ?>">

            <label>Estado</label>
            <select name="estado" required>
                <option value="Activo" <?= $empleado['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                <option value="Bloqueado" <?= $empleado['estado'] === 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
            </select>

            <label>Rol *</label>
            <select name="rol" required>
                <option value="Empleado" <?= $empleado['rol'] === 'Empleado' ? 'selected' : '' ?>>Empleado</option>
                <option value="Administrador" <?= $empleado['rol'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <input type="submit" value="Actualizar">
        </form>

        <!-- Botones de navegación -->
        <div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
            <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
                ← Volver Atrás
            </button>
            <a href="gestion_empleados.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
                 Ver Empleados
            </a>
           
        </div>
    </div>
</body>
</html>

<?php include 'pie.php'; ?>
