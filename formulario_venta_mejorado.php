<?php
session_start();
require_once 'conexion.php';

// Verificar que existe una sesión de empleado
if (!isset($_SESSION['empleado_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir la clase MailerSimple
require_once 'test_mailer_simple.php';

$mensaje = "";
$mostrar_ticket = false;
$datosTicket = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $libro_id = $_POST['libro_id'];
    $cantidad = intval($_POST['cantidad']);
    $metodo_pago = $_POST['metodo_pago'];
    $cliente_nombre = $_POST['cliente_nombre'];
    $cliente_telefono = $_POST['cliente_telefono'];
    $cliente_email = $_POST['cliente_email'];
    $empleado_id = $_SESSION['empleado_id'];

    // Validar campos
    if (empty($libro_id) || $cantidad <= 0 || empty($metodo_pago) || empty($cliente_nombre) || empty($cliente_email)) {
        $mensaje = "❌ Por favor complete todos los campos requeridos.";
    } else {
        // Obtener información del libro
        $libro_query = "SELECT titulo, autor, editorial, precio_venta, stock FROM libros WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $libro_query);
        mysqli_stmt_bind_param($stmt, "i", $libro_id);
        mysqli_stmt_execute($stmt);
        $libro_result = mysqli_stmt_get_result($stmt);
        
        if ($libro = mysqli_fetch_assoc($libro_result)) {
            if ($libro['stock'] >= $cantidad) {
                $precio_unitario = floatval($libro['precio_venta']);
                $total = $precio_unitario * $cantidad;
                
                // Iniciar transacción
                mysqli_begin_transaction($conexion);
                
                try {
                    // Insertar venta
                    $venta_query = "INSERT INTO ventas (libro_id, empleado_id, cantidad, precio_venta, metodo_pago, cliente_nombre, cliente_telefono, cliente_email, total_pagado, fecha_venta) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $stmt = mysqli_prepare($conexion, $venta_query);
                    mysqli_stmt_bind_param($stmt, "iiidssssd", $libro_id, $empleado_id, $cantidad, $precio_unitario, $metodo_pago, $cliente_nombre, $cliente_telefono, $cliente_email, $total);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error al registrar la venta");
                    }
                    
                    $venta_id = mysqli_insert_id($conexion);
                    
                    // Actualizar stock
                    $stock_query = "UPDATE libros SET stock = stock - ? WHERE id = ?";
                    $stmt = mysqli_prepare($conexion, $stock_query);
                    mysqli_stmt_bind_param($stmt, "ii", $cantidad, $libro_id);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error al actualizar el stock");
                    }
                    
                    // Confirmar transacción
                    mysqli_commit($conexion);
                    
                    // Obtener datos completos para el ticket
                    $ticket_query = "SELECT v.*, l.titulo, l.autor, l.editorial, e.nombre as empleado_nombre 
                                    FROM ventas v 
                                    JOIN libros l ON v.libro_id = l.id 
                                    JOIN empleados e ON v.empleado_id = e.id 
                                    WHERE v.id = ?";
                    $stmt = mysqli_prepare($conexion, $ticket_query);
                    mysqli_stmt_bind_param($stmt, "i", $venta_id);
                    mysqli_stmt_execute($stmt);
                    $ticket_result = mysqli_stmt_get_result($stmt);
                    $datosTicket = mysqli_fetch_assoc($ticket_result);
                    
                    $mostrar_ticket = true;
                    
                    // ===== ENVIAR EMAIL CON MAILER SIMPLE =====
                    $email_subject = "Comprobante de compra - Biblioteca Universidad";
                    $email_body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #2c3e50; text-align: center;'>🧾 Comprobante de Compra</h2>
                        <div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>
                            <p><strong>Ticket #:</strong> " . str_pad($datosTicket['id'], 6, '0', STR_PAD_LEFT) . "</p>
                            <p><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($datosTicket['fecha_venta'])) . "</p>
                            <p><strong>Atendido por:</strong> " . htmlspecialchars($datosTicket['empleado_nombre']) . "</p>
                        </div>
                        
                        <h3 style='color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px;'>Datos del Cliente</h3>
                        <p><strong>Nombre:</strong> " . htmlspecialchars($datosTicket['cliente_nombre']) . "</p>
                        <p><strong>Teléfono:</strong> " . htmlspecialchars($datosTicket['cliente_telefono']) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($datosTicket['cliente_email']) . "</p>
                        
                        <h3 style='color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px;'>Detalles de la Compra</h3>
                        <div style='background: #fff; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>
                            <p><strong>Libro:</strong> " . htmlspecialchars($datosTicket['titulo']) . "</p>
                            <p><strong>Autor:</strong> " . htmlspecialchars($datosTicket['autor']) . "</p>
                            <p><strong>Editorial:</strong> " . htmlspecialchars($datosTicket['editorial']) . "</p>
                            <p><strong>Cantidad:</strong> " . $datosTicket['cantidad'] . "</p>
                            <p><strong>Precio unitario:</strong> $" . number_format($datosTicket['precio_venta'], 2) . "</p>
                            <p><strong>Método de pago:</strong> " . htmlspecialchars($datosTicket['metodo_pago']) . "</p>
                        </div>
                        
                        <div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;'>
                            <h3 style='color: #155724; margin: 0;'>TOTAL PAGADO: $" . number_format($datosTicket['total_pagado'], 2) . "</h3>
                        </div>
                        
                        <div style='text-align: center; color: #6c757d; font-size: 14px;'>
                            <p>¡Gracias por su compra!</p>
                            <p>Conserve este comprobante para cualquier aclaración.</p>
                            <hr>
                            <p><small>Biblioteca Universidad - Sistema automatizado</small></p>
                        </div>
                    </div>";
                    
                    // Enviar email usando MailerSimple
                    $mailer = new MailerSimple(false); // Sin debug para producción
                    $resultado_email = $mailer->enviarGmail(
                        'diaztecete@gmail.com',
                        'hqicplranmnjaojr',
                        $cliente_email,
                        $email_subject,
                        $email_body,
                        'Biblioteca Universidad'
                    );
                    
                    if ($resultado_email['success']) {
                        $mensaje = "✅ Venta registrada correctamente. El comprobante se envió al correo: " . $cliente_email;
                    } else {
                        $mensaje = "✅ Venta registrada correctamente. ⚠️ Error enviando email: " . $resultado_email['error'];
                    }
                    
                } catch (Exception $e) {
                    mysqli_rollback($conexion);
                    $mensaje = "❌ Error al procesar la venta: " . $e->getMessage();
                }
            } else {
                $mensaje = "❌ No hay suficiente stock. Disponible: " . $libro['stock'];
            }
        } else {
            $mensaje = "❌ Libro no encontrado.";
        }
    }
}

// Obtener lista de libros para el formulario
$libros_query = "SELECT id, titulo, autor, precio_venta, stock FROM libros WHERE stock > 0 ORDER BY titulo";
$libros_result = mysqli_query($conexion, $libros_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Venta - Biblioteca</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success { 
            background: #d4edda; 
            border: 1px solid #c3e6cb; 
            color: #155724; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .error { 
            background: #f8d7da; 
            border: 1px solid #f5c6cb; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #007bff;
            color: white;
            cursor: pointer;
            font-size: 16px;
            padding: 12px;
        }
        button:hover {
            background: #0056b3;
        }
        .ticket {
            background: #f8f9fa;
            border: 2px solid #28a745;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .ticket h3 {
            color: #28a745;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Formulario de Venta</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo strpos($mensaje, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Libro:</label>
                <select name="libro_id" required>
                    <option value="">Seleccionar libro</option>
                    <?php while ($libro = mysqli_fetch_assoc($libros_result)): ?>
                        <option value="<?php echo $libro['id']; ?>">
                            <?php echo htmlspecialchars($libro['titulo']); ?> - <?php echo htmlspecialchars($libro['autor']); ?> 
                            (Stock: <?php echo $libro['stock']; ?>, Precio: $<?php echo number_format($libro['precio_venta'], 2); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Cantidad:</label>
                <input type="number" name="cantidad" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Método de pago:</label>
                <select name="metodo_pago" required>
                    <option value="">Seleccionar método</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>
            
            <h3>Datos del Cliente</h3>
            
            <div class="form-group">
                <label>Nombre completo:</label>
                <input type="text" name="cliente_nombre" required>
            </div>
            
            <div class="form-group">
                <label>Teléfono:</label>
                <input type="tel" name="cliente_telefono" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="cliente_email" required>
            </div>
            
            <button type="submit">🛒 Registrar Venta y Enviar Comprobante</button>
        </form>
        
        <?php if ($mostrar_ticket && !empty($datosTicket)): ?>
            <div class="ticket">
                <h3>🧾 Ticket Generado</h3>
                <p><strong>Ticket #:</strong> <?php echo str_pad($datosTicket['id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($datosTicket['fecha_venta'])); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($datosTicket['cliente_nombre']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($datosTicket['cliente_email']); ?></p>
                <p><strong>Libro:</strong> <?php echo htmlspecialchars($datosTicket['titulo']); ?></p>
                <p><strong>Cantidad:</strong> <?php echo $datosTicket['cantidad']; ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($datosTicket['total_pagado'], 2); ?></p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="test_mailer_simple.php" style="background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
                🧪 Probar Email
            </a>
        </div>
    </div>
</body>
</html>
