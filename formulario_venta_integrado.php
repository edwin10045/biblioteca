<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['rol'] !== 'Empleado' && $_SESSION['rol'] !== 'Administrador')) {
    header("Location: index.html");
    exit();
}

if ($_SESSION['rol'] === 'Administrador') {
    include 'encabezado_admin.php';
} elseif ($_SESSION['rol'] === 'Empleado') {
    include 'encabezado_empleado.php';
}
include 'conexion.php';

// Clase EmailLibreria integrada - VERSIÓN QUE FUNCIONA
class EmailLibreria
{
    private $smtp_conn;
    private $debug = false;
    
    public function __construct($debug = false)
    {
        $this->debug = false; // Siempre debug desactivado
    }
    
    public function enviarComprobante($from_email, $app_password, $to_email, $datos_venta)
    {
        try {
            // 1. Conectar a Gmail
            if (!$this->conectar()) {
                return ['success' => false, 'error' => 'No se pudo conectar a Gmail'];
            }
            
            // 2. Autenticar
            if (!$this->autenticar($from_email, $app_password)) {
                return ['success' => false, 'error' => 'Error de autenticación'];
            }
            
            // 3. Enviar correo
            if (!$this->enviarEmail($from_email, $to_email, $datos_venta)) {
                return ['success' => false, 'error' => 'Error enviando correo'];
            }
            
            // 4. Cerrar conexión
            $this->cerrar();
            
            return ['success' => true, 'message' => 'Comprobante enviado exitosamente'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function conectar()
    {
        // Conectar a Gmail SMTP
        $this->smtp_conn = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
        
        if (!$this->smtp_conn) {
            $this->debug_msg("❌ Error conexión: $errstr");
            return false;
        }
        
        // Leer saludo del servidor
        $greeting = fgets($this->smtp_conn);
        $this->debug_msg("Gmail dice: " . trim($greeting));
        
        // EHLO inicial
        fputs($this->smtp_conn, "EHLO localhost\r\n");
        $this->leer_respuesta_completa();
        
        // STARTTLS para activar TLS
        fputs($this->smtp_conn, "STARTTLS\r\n");
        $tls_response = fgets($this->smtp_conn);
        $this->debug_msg("STARTTLS: " . trim($tls_response));
        
        // Activar cifrado TLS
        if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $this->debug_msg("❌ Error activando TLS");
            return false;
        }
        
        // EHLO después de TLS
        fputs($this->smtp_conn, "EHLO localhost\r\n");
        $this->leer_respuesta_completa();
        
yyyy        $this->debug_msg("✅ Conectado a Gmail con TLS");
        return true;
    }
    
    private function autenticar($email, $password)
    {
        // Iniciar autenticación LOGIN
        fputs($this->smtp_conn, "AUTH LOGIN\r\n");
        $auth_response = fgets($this->smtp_conn);
        $this->debug_msg("AUTH LOGIN: " . trim($auth_response));
        
        // Enviar usuario (base64)
        fputs($this->smtp_conn, base64_encode($email) . "\r\n");
        $user_response = fgets($this->smtp_conn);
        $this->debug_msg("Usuario: " . trim($user_response));
        
        // Enviar contraseña (base64)
        fputs($this->smtp_conn, base64_encode($password) . "\r\n");
        $pass_response = fgets($this->smtp_conn);
        $this->debug_msg("Password: " . trim($pass_response));
        
        // Verificar si autenticación fue exitosa (235 = Authentication successful)
        if (strpos($pass_response, '235') === 0) {
            $this->debug_msg("✅ Autenticación exitosa");
            return true;
        } else {
            $this->debug_msg("❌ Error autenticación");
            return false;
        }
    }
    
    private function enviarEmail($from, $to, $datos)
    {
        // MAIL FROM
        fputs($this->smtp_conn, "MAIL FROM:<$from>\r\n");
        $mail_response = fgets($this->smtp_conn);
        $this->debug_msg("MAIL FROM: " . trim($mail_response));
        
        // RCPT TO
        fputs($this->smtp_conn, "RCPT TO:<$to>\r\n");
        $rcpt_response = fgets($this->smtp_conn);
        $this->debug_msg("RCPT TO: " . trim($rcpt_response));
        
        // DATA
        fputs($this->smtp_conn, "DATA\r\n");
        $data_response = fgets($this->smtp_conn);
        $this->debug_msg("DATA: " . trim($data_response));
        
        // Construir el mensaje
        $subject = "Comprobante de Compra - Biblioteca Universidad";
        $body = $this->construirHTML($datos);
        
        $email = "From: Biblioteca Universidad <$from>\r\n";
        $email .= "To: $to\r\n";
        $email .= "Subject: $subject\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email .= "\r\n";
        $email .= $body;
        $email .= "\r\n.\r\n";
        
        // Enviar mensaje completo
        fputs($this->smtp_conn, $email);
        $send_response = fgets($this->smtp_conn);
        $this->debug_msg("SEND: " . trim($send_response));
        
        // 250 = Message accepted
        if (strpos($send_response, '250') === 0) {
            $this->debug_msg("✅ Email enviado exitosamente");
            return true;
        } else {
            $this->debug_msg("❌ Error enviando email");
            return false;
        }
    }
    
    private function construirHTML($datos)
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;'>
            <div style='background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <h2 style='color: #2c3e50; text-align: center; margin-bottom: 30px;'>🧾 Comprobante de Compra</h2>
                
                <div style='background: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;'>
                    <h3 style='color: #0056b3; margin-top: 0;'>📋 Información del Ticket</h3>
                    <p><strong>Ticket #:</strong> " . str_pad($datos['ticket_id'], 6, '0', STR_PAD_LEFT) . "</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s', strtotime($datos['fecha'])) . "</p>
                    <p><strong>Atendido por:</strong> {$datos['empleado']}</p>
                </div>
                
                <div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <h3 style='color: #856404; margin-top: 0;'>👤 Datos del Cliente</h3>
                    <p><strong>Nombre:</strong> {$datos['cliente']}</p>
                    <p><strong>Teléfono:</strong> {$datos['telefono']}</p>
                    <p><strong>Email:</strong> {$datos['email']}</p>
                </div>
                
                <div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                    <h3 style='color: #0c5460; margin-top: 0;'>📚 Detalles de la Compra</h3>
                    <p><strong>Libro:</strong> {$datos['libro']}</p>
                    <p><strong>Autor:</strong> {$datos['autor']}</p>
                    <p><strong>Editorial:</strong> {$datos['editorial']}</p>
                    <p><strong>Cantidad:</strong> {$datos['cantidad']}</p>
                    <p><strong>Precio unitario:</strong> $" . number_format($datos['precio'], 2) . "</p>
                    <p><strong>Método de pago:</strong> {$datos['metodo_pago']}</p>
                </div>
                
                <div style='background: #d4edda; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0; border: 2px solid #28a745;'>
                    <h3 style='color: #155724; margin: 0; font-size: 24px;'>TOTAL PAGADO: $" . number_format($datos['total'], 2) . "</h3>
                </div>
                
                <div style='text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;'>
                    <h4 style='color: #28a745; margin-top: 0;'>¡Gracias por tu compra!</h4>
                    <p style='margin: 10px 0; color: #666;'>Conserva este comprobante para futuras referencias.</p>
                    <hr style='border: 1px solid #dee2e6; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #999; margin: 0;'>Biblioteca Universidad - Sistema Automatizado</p>
                </div>
            </div>
        </div>";
    }
    
    private function leer_respuesta_completa()
    {
        $response = '';
        while (($line = fgets($this->smtp_conn)) !== false) {
            $response .= $line;
            // Las respuestas SMTP terminan con un código de 3 dígitos seguido de espacio
            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        $this->debug_msg("Respuesta: " . trim($response));
        return $response;
    }
    
    private function debug_msg($msg)
    {
        // Debug desactivado para producción - no muestra mensajes
        return;
    }
    
    private function cerrar()
    {
        if ($this->smtp_conn) {
            fputs($this->smtp_conn, "QUIT\r\n");
            fclose($this->smtp_conn);
            $this->debug_msg("✅ Conexión cerrada");
        }
    }
}

$mensaje = "";
$mostrarFormulario = true;
$mostrarTicket = false;
$datosTicket = null;
$email_resultado = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $libro_id = intval($_POST['libro_id']);
    $cliente_nombre = mysqli_real_escape_string($enlace, $_POST['cliente_nombre']);
    $cliente_telefono = mysqli_real_escape_string($enlace, $_POST['cliente_telefono']);
    $correo = mysqli_real_escape_string($enlace, $_POST['cliente_correo']);
    $metodo_pago = mysqli_real_escape_string($enlace, $_POST['metodo_pago']);
    $cantidad = intval($_POST['cantidad']);
    $empleado_id = $_SESSION['id'];
    $fecha_compra = date("Y-m-d");

    $queryLibro = "SELECT precio_venta, stock_disponible FROM Libros WHERE id = $libro_id";
    $resultLibro = mysqli_query($enlace, $queryLibro);
    $libro = mysqli_fetch_assoc($resultLibro);

    if (!$libro) {
        $mensaje = "Libro no encontrado.";
    } elseif ($libro['stock_disponible'] < $cantidad || $cantidad < 1) {
        $mensaje = "No hay suficiente stock disponible para la cantidad solicitada.";
    } else {
        $total_pagado = $libro['precio_venta'] * $cantidad;

        $insertVenta = "INSERT INTO Ventas (cliente_nombre, cliente_telefono, correo, empleado_id, fecha_compra, total_pagado, metodo_pago, libro_id, cantidad)
                        VALUES ('$cliente_nombre', '$cliente_telefono', '$correo', $empleado_id, '$fecha_compra', $total_pagado, '$metodo_pago', $libro_id, $cantidad)";

        if (mysqli_query($enlace, $insertVenta)) {
            $venta_id = mysqli_insert_id($enlace);
            $updateStock = "UPDATE Libros SET stock_disponible = stock_disponible - $cantidad WHERE id = $libro_id";
            mysqli_query($enlace, $updateStock);

            // Obtener información completa para el ticket
            $queryTicket = "SELECT v.*, l.titulo, l.precio_venta, a.nombre_completo as autor, e.nombre as editorial
                           FROM Ventas v 
                           JOIN Libros l ON v.libro_id = l.id
                           LEFT JOIN Autores a ON l.autor_id = a.id
                           LEFT JOIN Editoriales e ON l.editorial_id = e.id
                           WHERE v.id = $venta_id";
            $resultTicket = mysqli_query($enlace, $queryTicket);
            $datosTicket = mysqli_fetch_assoc($resultTicket);

            // Obtener nombre del empleado
            $empleado_nombre = "Desconocido";
            $queryEmpleado = "SELECT nombre_completo FROM Empleados WHERE id = $empleado_id LIMIT 1";
            $resultEmpleado = mysqli_query($enlace, $queryEmpleado);
            if ($resultEmpleado && $rowEmpleado = mysqli_fetch_assoc($resultEmpleado)) {
                $empleado_nombre = $rowEmpleado['nombre_completo'];
            }
            $datosTicket['empleado_nombre'] = $empleado_nombre;

            // ===== ENVIAR EMAIL CON SISTEMA INTEGRADO =====
            $mailer = new EmailLibreria(false); // Sin debug por pantalla
            
            $datos_email = [
                'ticket_id' => $venta_id,
                'fecha' => $datosTicket['fecha_compra'],
                'empleado' => $empleado_nombre,
                'cliente' => $datosTicket['cliente_nombre'],
                'telefono' => $datosTicket['cliente_telefono'],
                'email' => $datosTicket['correo'],
                'libro' => $datosTicket['titulo'],
                'autor' => $datosTicket['autor'] ?? 'N/A',
                'editorial' => $datosTicket['editorial'] ?? 'N/A',
                'cantidad' => $datosTicket['cantidad'],
                'precio' => $datosTicket['precio_venta'],
                'metodo_pago' => $datosTicket['metodo_pago'],
                'total' => $datosTicket['total_pagado']
            ];
            
            $email_resultado = $mailer->enviarComprobante(
                'diaztecete@gmail.com',     // Email origen
                'hqicplranmnjaojr',         // App Password
                $datosTicket['correo'],     // Email destino
                $datos_email                // Datos del comprobante
            );

            if ($email_resultado['success']) {
                $mensaje = "✅ Venta registrada correctamente. El comprobante se envió al correo: " . $datosTicket['correo'];
            } else {
                $mensaje = "✅ Venta registrada correctamente. ⚠️ Error enviando email: " . $email_resultado['error'];
            }

            $mostrarFormulario = false;
            $mostrarTicket = true;
        } else {
            $mensaje = "Error al registrar la venta: " . mysqli_error($enlace);
        }
    }
}

if ($mostrarFormulario) {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if (!isset($_GET['id_libro'])) {
            die("Libro no especificado.");
        }
        $libro_id = intval($_GET['id_libro']);
    }
    $query = "SELECT titulo, precio_venta FROM Libros WHERE id = $libro_id";
    $result = mysqli_query($enlace, $query);
    $libro = mysqli_fetch_assoc($result);

    if (!$libro) {
        die("Libro no encontrado.");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Venta - Biblioteca</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            background: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 8px;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus, select:focus {
            border-color: #3498db;
            outline: none;
        }
        .btn-submit {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .mensaje-exito {
            background: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .mensaje-error {
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .ticket {
            background: #f8f9fa;
            border: 2px solid #28a745;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .ticket h3 {
            color: #28a745;
            margin-top: 0;
            text-align: center;
        }
        .ticket-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .ticket-total {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #d4edda;
            border-radius: 8px;
        }
        .email-status {
            background: #e8f4fd;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($mostrarFormulario): ?>
            <h2>📝 Formulario de Venta</h2>
            <form method="POST">
                <input type="hidden" name="libro_id" value="<?php echo $libro_id; ?>">
                
                <div class="form-group">
                    <label>📚 Libro:</label>
                    <input type="text" value="<?php echo htmlspecialchars($libro['titulo']); ?>" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label>💰 Precio:</label>
                    <input type="text" value="$<?php echo number_format($libro['precio_venta'], 2); ?>" readonly style="background: #f8f9fa;">
                </div>

                <h3 style="color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px;">👤 Datos del Cliente</h3>
                
                <div class="form-group">
                    <label>Nombre completo:</label>
                    <input type="text" name="cliente_nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="tel" name="cliente_telefono" required>
                </div>
                
                <div class="form-group">
                    <label>📧 Correo electrónico:</label>
                    <input type="email" name="cliente_correo" required placeholder="correo@ejemplo.com">
                    <small style="color: #666;">El comprobante se enviará a esta dirección</small>
                </div>

                <h3 style="color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px;">🛒 Detalles de la Compra</h3>
                
                <div class="form-group">
                    <label>Cantidad:</label>
                    <input type="number" name="cantidad" min="1" value="1" required>
                </div>
                
                <div class="form-group">
                    <label>Método de pago:</label>
                    <select name="metodo_pago" required>
                        <option value="">Seleccionar método</option>
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Tarjeta">💳 Tarjeta</option>
                        <option value="Transferencia">🏦 Transferencia</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    🛒 Registrar Venta y Enviar Comprobante
                </button>
            </form>

        <?php else: ?>
            <h2>✅ Venta Completada</h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="<?php echo strpos($mensaje, '✅') !== false ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <?php if ($email_resultado): ?>
                <div class="email-status">
                    <h4>📧 Estado del Email</h4>
                    <?php if ($email_resultado['success']): ?>
                        <p style="color: #28a745;">✅ Comprobante enviado exitosamente</p>
                        <p><strong>Enviado a:</strong> <?php echo htmlspecialchars($datosTicket['correo']); ?></p>
                        <p><small>Revisa tu bandeja de entrada, promociones y spam</small></p>
                    <?php else: ?>
                        <p style="color: #dc3545;">❌ Error enviando email</p>
                        <p><small><?php echo htmlspecialchars($email_resultado['error']); ?></small></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($mostrarTicket && $datosTicket): ?>
                <div class="ticket">
                    <h3>🧾 Ticket de Compra</h3>
                    
                    <div class="ticket-row">
                        <span><strong>Ticket #:</strong></span>
                        <span><?php echo str_pad($datosTicket['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    
                    <div class="ticket-row">
                        <span><strong>Fecha:</strong></span>
                        <span><?php echo date('d/m/Y H:i', strtotime($datosTicket['fecha_compra'])); ?></span>
                    </div>
                    
                    <div class="ticket-row">
                        <span><strong>Cliente:</strong></span>
                        <span><?php echo htmlspecialchars($datosTicket['cliente_nombre']); ?></span>
                    </div>
                    
                    <div class="ticket-row">
                        <span><strong>Libro:</strong></span>
                        <span><?php echo htmlspecialchars($datosTicket['titulo']); ?></span>
                    </div>
                    
                    <div class="ticket-row">
                        <span><strong>Cantidad:</strong></span>
                        <span><?php echo $datosTicket['cantidad']; ?></span>
                    </div>
                    
                    <div class="ticket-row">
                        <span><strong>Método de pago:</strong></span>
                        <span><?php echo htmlspecialchars($datosTicket['metodo_pago']); ?></span>
                    </div>
                    
                    <div class="ticket-total">
                        TOTAL PAGADO: $<?php echo number_format($datosTicket['total_pagado'], 2); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 30px;">
                <!-- Sección especial para gestión de comprobantes -->
                <?php if ($mostrarTicket && $datosTicket): ?>
                    <div style="background: linear-gradient(135deg, #e8f5e8, #d4edda); padding: 25px; border-radius: 15px; margin: 20px 0; border-left: 6px solid #28a745; box-shadow: 0 6px 12px rgba(40, 167, 69, 0.15);">
                        <h4 style="color: #155724; margin-top: 0; margin-bottom: 15px;">📧 Gestión de Comprobantes</h4>
                        <p style="color: #666; margin-bottom: 20px;">¿Necesitas reenviar el comprobante o enviarlo a otro email?</p>
                        
                        <a href="enviar_comprobantes.php?venta_id=<?php echo $datosTicket['id']; ?>" 
                           style="background: linear-gradient(135deg, #28a745, #1e7e34); 
                                  color: white; 
                                  padding: 18px 35px; 
                                  text-decoration: none; 
                                  border-radius: 12px; 
                                  font-weight: bold; 
                                  font-size: 16px;
                                  display: inline-block;
                                  box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3); 
                                  transition: all 0.3s ease;
                                  margin: 10px;"
                           onmouseover="this.style.transform='translateY(-3px) scale(1.02)'; this.style.boxShadow='0 8px 16px rgba(40, 167, 69, 0.4)';"
                           onmouseout="this.style.transform='translateY(0px) scale(1)'; this.style.boxShadow='0 6px 12px rgba(40, 167, 69, 0.3)';">
                            � Enviar/Reenviar Comprobante
                        </a>
                        
                        <div style="font-size: 12px; color: #666; margin-top: 10px;">
                            <strong>Ticket #{<?php echo str_pad($datosTicket['id'], 6, '0', STR_PAD_LEFT); ?>}</strong> - Listo para gestionar
                        </div>
                    </div>
                <?php endif; ?>
                

                <!-- Botones de navegación regulares -->
                <div style="border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 20px;">
                    <a href="catalogo.php" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 5px; display: inline-block; font-weight: bold;">
                        📚 Volver al Catálogo
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
