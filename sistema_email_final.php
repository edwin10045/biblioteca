<?php
// Versión final y funcional del sistema de email
class EmailLibreria
{
    private $smtp_conn;
    private $debug = false;
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }
    
    public function enviarComprobante($from_email, $app_password, $to_email, $datos_venta)
    {
        try {
            // 1. Conectar a Gmail
            $this->debug_msg("🔌 Conectando a Gmail SMTP...");
            if (!$this->conectar()) {
                return ['success' => false, 'error' => 'No se pudo conectar a Gmail'];
            }
            
            // 2. Autenticar
            $this->debug_msg("🔐 Autenticando con Gmail...");
            if (!$this->autenticar($from_email, $app_password)) {
                return ['success' => false, 'error' => 'Error de autenticación'];
            }
            
            // 3. Enviar correo
            $this->debug_msg("📧 Enviando comprobante...");
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
        
        // Leer saludo
        $response = fgets($this->smtp_conn);
        $this->debug_msg("Servidor: " . trim($response));
        
        // EHLO inicial
        fputs($this->smtp_conn, "EHLO localhost\r\n");
        $this->leer_respuesta_completa();
        
        // STARTTLS
        fputs($this->smtp_conn, "STARTTLS\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("STARTTLS: " . trim($response));
        
        // Activar TLS
        if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $this->debug_msg("❌ Error activando TLS");
            return false;
        }
        
        $this->debug_msg("✅ TLS activado");
        
        // EHLO después de TLS
        fputs($this->smtp_conn, "EHLO localhost\r\n");
        $this->leer_respuesta_completa();
        
        return true;
    }
    
    private function autenticar($email, $password)
    {
        // AUTH LOGIN
        fputs($this->smtp_conn, "AUTH LOGIN\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("AUTH LOGIN: " . trim($response));
        
        // Usuario
        fputs($this->smtp_conn, base64_encode($email) . "\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("Usuario: " . trim($response));
        
        // Contraseña
        fputs($this->smtp_conn, base64_encode($password) . "\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("Contraseña: " . trim($response));
        
        // Verificar autenticación (235 = éxito)
        if (strpos($response, '235') === 0) {
            $this->debug_msg("✅ Autenticación exitosa");
            return true;
        } else {
            $this->debug_msg("❌ Autenticación falló");
            return false;
        }
    }
    
    private function enviarEmail($from, $to, $datos)
    {
        // MAIL FROM
        fputs($this->smtp_conn, "MAIL FROM:<$from>\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("MAIL FROM: " . trim($response));
        
        // RCPT TO
        fputs($this->smtp_conn, "RCPT TO:<$to>\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("RCPT TO: " . trim($response));
        
        // DATA
        fputs($this->smtp_conn, "DATA\r\n");
        $response = fgets($this->smtp_conn);
        $this->debug_msg("DATA: " . trim($response));
        
        // Construir mensaje
        $subject = "Comprobante de Compra - Biblioteca";
        $body = $this->construirHTML($datos);
        
        $email = "From: Biblioteca Universidad <$from>\r\n";
        $email .= "To: $to\r\n";
        $email .= "Subject: $subject\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email .= "\r\n";
        $email .= $body;
        $email .= "\r\n.\r\n";
        
        // Enviar mensaje
        fputs($this->smtp_conn, $email);
        $response = fgets($this->smtp_conn);
        $this->debug_msg("Envío: " . trim($response));
        
        return strpos($response, '250') === 0;
    }
    
    private function construirHTML($datos)
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #2c3e50; text-align: center;'>🧾 Comprobante de Compra</h2>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 15px 0;'>
                <h3 style='color: #34495e; margin-top: 0;'>📋 Información del Ticket</h3>
                <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
                <p><strong>Cliente:</strong> {$datos['cliente']}</p>
                <p><strong>Email:</strong> {$datos['email']}</p>
            </div>
            
            <div style='background: #fff; border: 1px solid #dee2e6; padding: 20px; border-radius: 8px; margin: 15px 0;'>
                <h3 style='color: #34495e; margin-top: 0;'>📚 Detalles de la Compra</h3>
                <p><strong>Libro:</strong> {$datos['libro']}</p>
                <p><strong>Autor:</strong> {$datos['autor']}</p>
                <p><strong>Cantidad:</strong> {$datos['cantidad']}</p>
                <p><strong>Precio:</strong> $" . number_format($datos['precio'], 2) . "</p>
                <p><strong>Total:</strong> $" . number_format($datos['total'], 2) . "</p>
            </div>
            
            <div style='background: #d4edda; padding: 20px; border-radius: 8px; text-align: center; margin: 15px 0;'>
                <h3 style='color: #155724; margin: 0;'>¡Gracias por tu compra!</h3>
                <p style='margin: 10px 0 0 0;'>Conserva este comprobante para futuras referencias.</p>
            </div>
            
            <div style='text-align: center; font-size: 12px; color: #6c757d; margin-top: 30px;'>
                <p>Biblioteca Universidad - Sistema Automatizado</p>
            </div>
        </div>";
    }
    
    private function leer_respuesta_completa()
    {
        $response = '';
        while (($line = fgets($this->smtp_conn)) !== false) {
            $response .= $line;
            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        $this->debug_msg("Respuesta: " . trim($response));
        return $response;
    }
    
    private function cerrar()
    {
        if ($this->smtp_conn) {
            fputs($this->smtp_conn, "QUIT\r\n");
            fclose($this->smtp_conn);
            $this->debug_msg("🔚 Conexión cerrada");
        }
    }
    
    private function debug_msg($mensaje)
    {
        if ($this->debug) {
            echo "<div style='background: #f8f9fa; border-left: 4px solid #007bff; padding: 8px; margin: 3px 0; font-family: monospace; font-size: 12px;'>";
            echo htmlspecialchars($mensaje);
            echo "</div>";
        }
    }
}

// Procesar envío si se presiona el botón
$resultado = null;
if ($_POST['enviar_comprobante'] ?? false) {
    $mailer = new EmailLibreria(true); // Con debug
    
    $datos_ejemplo = [
        'cliente' => $_POST['cliente'] ?? 'Cliente Ejemplo',
        'email' => $_POST['email'] ?? 'diaztecete@gmail.com',
        'libro' => $_POST['libro'] ?? 'Cien Años de Soledad',
        'autor' => $_POST['autor'] ?? 'Gabriel García Márquez',
        'cantidad' => $_POST['cantidad'] ?? 1,
        'precio' => $_POST['precio'] ?? 25.99,
        'total' => ($_POST['cantidad'] ?? 1) * ($_POST['precio'] ?? 25.99)
    ];
    
    $resultado = $mailer->enviarComprobante(
        'diaztecete@gmail.com',     // Email origen
        'hqicplranmnjaojr',         // App Password
        $datos_ejemplo['email'],    // Email destino
        $datos_ejemplo              // Datos del comprobante
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Envío de Comprobantes - Biblioteca</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .header {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
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
        .btn-enviar {
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
        .btn-enviar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .resultado-exitoso {
            background: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .resultado-error {
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Sistema de Comprobantes por Email</h1>
            <p>Envío automático de comprobantes de compra</p>
            <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;">
                <p style="margin: 0;"><strong>📮 El comprobante se enviará a:</strong> <code>diaztecete@gmail.com</code></p>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Revisa tu bandeja de entrada, promociones y spam</p>
            </div>
        </div>

        <form method="POST">
            <h3>👤 Datos del Cliente</h3>
            
            <div class="form-group">
                <label>Nombre del Cliente:</label>
                <input type="text" name="cliente" value="<?php echo $_POST['cliente'] ?? 'Juan Pérez'; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email del Cliente:</label>
                <input type="email" name="email" value="<?php echo $_POST['email'] ?? 'diaztecete@gmail.com'; ?>" required>
                <small style="color: #666;">El comprobante se enviará a esta dirección</small>
            </div>

            <h3>📚 Datos de la Compra</h3>
            
            <div class="form-group">
                <label>Libro:</label>
                <input type="text" name="libro" value="<?php echo $_POST['libro'] ?? 'Cien Años de Soledad'; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Autor:</label>
                <input type="text" name="autor" value="<?php echo $_POST['autor'] ?? 'Gabriel García Márquez'; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Cantidad:</label>
                <input type="number" name="cantidad" value="<?php echo $_POST['cantidad'] ?? 1; ?>" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Precio Unitario ($):</label>
                <input type="number" name="precio" step="0.01" value="<?php echo $_POST['precio'] ?? '25.99'; ?>" required>
            </div>

            <button type="submit" name="enviar_comprobante" value="1" class="btn-enviar">
                📧 Enviar Comprobante por Email
            </button>
            
            <button type="button" onclick="enviarRapido()" style="background: linear-gradient(45deg, #17a2b8, #138496); color: white; border: none; padding: 12px 25px; border-radius: 6px; font-size: 14px; cursor: pointer; width: 100%; margin-top: 10px;">
                🚀 Envío Rápido a diaztecete@gmail.com
            </button>
        </form>

        <script>
        function enviarRapido() {
            // Cambiar el email a tu correo y enviar
            document.querySelector('input[name="email"]').value = 'diaztecete@gmail.com';
            document.querySelector('input[name="cliente"]').value = 'Cliente Test';
            document.querySelector('form').submit();
        }
        </script>

        <?php if ($resultado): ?>
            <div class="<?php echo $resultado['success'] ? 'resultado-exitoso' : 'resultado-error'; ?>">
                <h4><?php echo $resultado['success'] ? '✅ ¡Éxito!' : '❌ Error'; ?></h4>
                <p><?php echo $resultado['success'] ? $resultado['message'] : $resultado['error']; ?></p>
                <?php if ($resultado['success']): ?>
                    <p><strong>📱 Revisa:</strong></p>
                    <ul>
                        <li>📥 Bandeja de entrada</li>
                        <li>🎯 Carpeta Promociones</li>
                        <li>🚫 Carpeta Spam</li>
                        <li>📱 App Gmail en tu teléfono</li>
                    </ul>
                <?php endif; ?>
            </div>
            
            <?php if (isset($mailer)): ?>
                <div class="debug-panel">
                    <h4>🔍 Log de Comunicación SMTP</h4>
                    <p><small>Información técnica del envío</small></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="container">
        <h3>ℹ️ Información del Sistema</h3>
        <p><strong>📧 Email origen:</strong> diaztecete@gmail.com</p>
        <p><strong>🔐 Autenticación:</strong> App Password configurada</p>
        <p><strong>🌐 Servidor:</strong> smtp.gmail.com:587 (TLS)</p>
        <p><strong>✅ Estado:</strong> Sistema listo para enviar</p>
    </div>
</body>
</html>
