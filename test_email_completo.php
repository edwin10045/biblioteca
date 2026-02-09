<?php
session_start();
require_once 'conexion.php';

// Verificar que existe una sesión de empleado
if (!isset($_SESSION['empleado_id'])) {
    header("Location: login.php");
    exit();
}

if (file_exists('PHPMailer/PHPMailer.php')) {
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
}

$mensaje = "";
$email_debug = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    $metodo = $_POST['metodo'];
    
    $subject = "Test desde Biblioteca - " . date('Y-m-d H:i:s');
    $body = "<h2>🧾 Test de Correo Electrónico</h2>";
    $body .= "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
    $body .= "<p><strong>Método:</strong> $metodo</p>";
    $body .= "<p><strong>Enviado a:</strong> $test_email</p>";
    $body .= "<p>Si recibes este correo, el sistema está funcionando correctamente.</p>";
    $body .= "<p>🎉 ¡Listo para enviar comprobantes de compra!</p>";
    
    if ($metodo == 'phpmailer') {
        // PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'diaztecete@gmail.com';
            $mail->Password = 'hqicplranmnjaojr';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Capturar debug en variable
            ob_start();
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';
            
            $mail->setFrom('diaztecete@gmail.com', 'Biblioteca Universidad');
            $mail->addAddress($test_email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($mail->send()) {
                $mensaje = "✅ Correo enviado exitosamente con PHPMailer a: $test_email";
            } else {
                $mensaje = "❌ Error PHPMailer: " . $mail->ErrorInfo;
            }
            $email_debug = ob_get_clean();
            
        } catch (Exception $e) {
            $mensaje = "❌ Excepción PHPMailer: " . $e->getMessage();
            $email_debug = ob_get_clean();
        }
        
    } elseif ($metodo == 'mail') {
        // Función mail() nativa
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Biblioteca Universidad <diaztecete@gmail.com>\r\n";
        
        if (mail($test_email, $subject, $body, $headers)) {
            $mensaje = "✅ Correo enviado exitosamente con mail() a: $test_email";
        } else {
            $mensaje = "❌ Error con mail() - Verifica configuración PHP";
        }
    }
}

// Obtener lista de libros para el formulario normal
$libros_query = "SELECT id, titulo, autor, precio_venta, stock FROM libros WHERE stock > 0 ORDER BY titulo";
$libros_result = mysqli_query($conexion, $libros_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Email - Biblioteca</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        .test-section {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .debug-output {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, button {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #3498db;
            color: white;
            cursor: pointer;
            padding: 10px 20px;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Sistema de Email</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo strpos($mensaje, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="test-section">
            <h3>📧 Probar Envío de Email</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Email de prueba:</label>
                    <input type="email" name="test_email" value="diaztecete@gmail.com" required>
                    <small>Cambia por otro Gmail para probar</small>
                </div>
                
                <div class="form-group">
                    <label>Método de envío:</label>
                    <select name="metodo" required>
                        <option value="phpmailer">PHPMailer (Recomendado)</option>
                        <option value="mail">Función mail() de PHP</option>
                    </select>
                </div>
                
                <button type="submit" name="test_email" value="1">🚀 Enviar Email de Prueba</button>
            </form>
        </div>
        
        <?php if (!empty($email_debug)): ?>
            <div class="container">
                <h3>🔍 Debug del Email</h3>
                <div class="debug-output"><?php echo $email_debug; ?></div>
            </div>
        <?php endif; ?>
        
        <div class="container">
            <h3>ℹ️ Información del Sistema</h3>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>PHPMailer:</strong> <?php echo file_exists('PHPMailer/PHPMailer.php') ? '✅ Disponible' : '❌ No encontrado'; ?></p>
            <p><strong>Configuración mail():</strong></p>
            <ul>
                <li><strong>SMTP:</strong> <?php echo ini_get('SMTP') ?: 'No configurado'; ?></li>
                <li><strong>Puerto:</strong> <?php echo ini_get('smtp_port') ?: 'No configurado'; ?></li>
                <li><strong>sendmail_from:</strong> <?php echo ini_get('sendmail_from') ?: 'No configurado'; ?></li>
            </ul>
        </div>
        
        <div class="container">
            <h3>📋 Respuestas a tu Pregunta</h3>
            <div style="background: #d4edda; padding: 15px; border-radius: 5px;">
                <h4>✅ ¿Se puede enviar desde Gmail a otro Gmail?</h4>
                <p><strong>SÍ</strong>, es completamente posible. Gmail permite:</p>
                <ul>
                    <li>📧 Enviar desde <code>diaztecete@gmail.com</code> a cualquier otro Gmail</li>
                    <li>🔐 Usar App Password para autenticación segura</li>
                    <li>🌐 Enviar a cualquier proveedor de email (Gmail, Yahoo, Hotmail, etc.)</li>
                    <li>📨 Usar tanto PHPMailer como mail() (con configuración)</li>
                </ul>
                
                <h4>🔧 Configuración Requerida:</h4>
                <ol>
                    <li><strong>App Password:</strong> Tu contraseña actual <code>hqicplranmnjaojr</code> debe ser válida</li>
                    <li><strong>Puerto 587:</strong> Con STARTTLS (lo que estás usando)</li>
                    <li><strong>SMTP Gmail:</strong> <code>smtp.gmail.com</code></li>
                </ol>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="formulario_venta.php" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                📝 Volver al Formulario de Ventas
            </a>
        </div>
    </div>
</body>
</html>
