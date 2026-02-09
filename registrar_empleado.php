<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.html");
    exit();
}

require_once 'conexion.php';

$error = "";
$success = "";
$nombre_admin = $_SESSION['nombre'] ?? 'Administrador';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $fecha_contratacion = $_POST['fecha_contratacion'] ?? date('Y-m-d');
    $estado = trim($_POST['estado'] ?? 'Activo');
    $rol = $_POST['rol'] ?? '';

    if ($nombre_completo === '' || $usuario === '' || $contraseña === '' || $rol === '') {
        $error = "Por favor completa los campos obligatorios.";
    } else {
        $stmt = $enlace->prepare("SELECT id FROM Empleados WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "El usuario ya existe.";
        } else {
            $hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $stmt_insert = $enlace->prepare("INSERT INTO Empleados (nombre_completo, usuario, contraseña, telefono, fecha_contratacion, estado, rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssssss", $nombre_completo, $usuario, $hash, $telefono, $fecha_contratacion, $estado, $rol);
            if ($stmt_insert->execute()) {
                $success = "Empleado registrado correctamente.";
            } else {
                $error = "Error al registrar empleado: " . $enlace->error;
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
include 'encabezado_admin.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Empleado</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { width: 400px; background: white; margin: 40px auto; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.2);}
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
        <div class="header">
            <strong>Usuario activo:</strong> <?= htmlspecialchars($nombre_admin) ?>
            &nbsp;|&nbsp; <a href="sesionfinalizada.php">Cerrar sesión</a>
        </div>

        <h2>Registrar Empleado</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Nombre completo *</label>
            <input type="text" name="nombre_completo" required>

            <label>Usuario *</label>
            <input type="text" name="usuario" required>

            <label>Contraseña *</label>
            <input type="text" name="contraseña" required>

            <label>Teléfono</label>
            <input type="text" name="telefono">

            <label>Fecha en que se contrató</label>
            <input type="date" name="fecha_contratacion" value="<?= date('Y-m-d') ?>">

            <label>Estado</label>
            <select name="estado" required>
                <option value="Activo" selected>Activo</option>
                <option value="Bloqueado">Bloqueado</option>
            </select>

            <label>Rol *</label>
            <select name="rol" required>
                <option value="Empleado">Empleado</option>
                <option value="Administrador">Administrador</option>
            </select>

            <input type="submit" value="Registrar">
        </form>

        <!-- Botones de navegación -->
        <div style="text-align: center; margin: 30px 0; padding: 20px; border-top: 2px solid #dee2e6;">
            <button onclick="history.back()" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; border: none; padding: 12px 25px; border-radius: 8px; margin: 5px; font-weight: bold; cursor: pointer;" title="Volver a la página anterior">
                ← Volver Atrás
            </button>
            <a href="gestion_empleados.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
                👥 Ver Empleados
            </a>
                <?php if ($_SESSION['rol'] === 'Empleado'): ?>

            <a href="comienzo.php" style="background: linear-gradient(135deg, #6c757d, #545b62); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
                🏠 Menú Principal
            </a>
                <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php include 'pie.php'; ?>
