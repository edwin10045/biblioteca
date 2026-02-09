<?php
echo "<h2>🧪 Test Gmail a Gmail</h2>";

// Configuración
$from_email = "diaztecete@gmail.com";
$from_password = "hqicplranmnjaojr"; // Tu App Password
$to_email = "diaztecete@gmail.com"; // Cambia por otro Gmail si quieres

echo "<p><strong>Desde:</strong> $from_email</p>";
echo "<p><strong>Para:</strong> $to_email</p>";
echo "<hr>";

// Test 1: Función mail() nativa
echo "<h3>🔧 Test 1: Función mail() nativa de PHP</h3>";

$subject = "Test Gmail - " . date('Y-m-d H:i:s');
$message = "
<html>
<head><title>Test Gmail</title></head>
<body>
  <h3>✅ Test exitoso de Gmail a Gmail</h3>
  <p>Este correo se envió usando la función mail() de PHP.</p>
  <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
  <p>Sistema: Biblioteca Edition</p>
</body>
</html>";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: Biblioteca <$from_email>\r\n";
$headers .= "Reply-To: $from_email\r\n";

if (mail($to_email, $subject, $message, $headers)) {
    echo "<p style='color: green;'>✅ Correo enviado exitosamente con mail()</p>";
} else {
    echo "<p style='color: red;'>❌ Error con mail() - Revisa configuración PHP</p>";
}

echo "<hr>";

// Test 2: PHPMailer con Gmail SMTP
echo "<h3>📧 Test 2: PHPMailer con Gmail SMTP</h3>";

if (file_exists('PHPMailer/PHPMailer.php')) {
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    try {
        $mail = new PHPMailer(true);
        
        // Configuración Gmail SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $from_email;
        $mail->Password = $from_password;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Debug detallado
        $mail->SMTPDebug = 2; // Nivel 2 para ver la comunicación
        $mail->Debugoutput = function($str, $level) {
            echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px; border-left: 3px solid #007cba;'>$str</div>";
        };
        
        // Configurar remitente y destinatario
        $mail->setFrom($from_email, 'Biblioteca Test');
        $mail->addAddress($to_email, 'Usuario Test');
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Test PHPMailer Gmail - ' . date('Y-m-d H:i:s');
        $mail->Body = "
        <h3>✅ Test exitoso de PHPMailer</h3>
        <p>Este correo se envió usando <strong>PHPMailer</strong> con Gmail SMTP.</p>
        <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p><strong>Desde:</strong> $from_email</p>
        <p><strong>Para:</strong> $to_email</p>
        <p><strong>Puerto:</strong> 587 (TLS)</p>
        <p>🎉 ¡El sistema de correo está funcionando!</p>
        ";
        
        echo "<div style='border: 1px solid #ddd; padding: 10px; background: #f9f9f9;'>";
        echo "<p><strong>Intentando enviar correo...</strong></p>";
        
        if ($mail->send()) {
            echo "<p style='color: green; font-weight: bold;'>✅ ¡CORREO ENVIADO EXITOSAMENTE!</p>";
            echo "<p>Revisa tu bandeja de entrada en: <strong>$to_email</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ Error: " . $mail->ErrorInfo . "</p>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Excepción PHPMailer: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ PHPMailer no encontrado</p>";
}

echo "<hr>";

// Información adicional
echo "<h3>ℹ️ Información importante</h3>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px;'>";
echo "<h4>Para que Gmail funcione correctamente:</h4>";
echo "<ul>";
echo "<li><strong>✅ App Password:</strong> Debes usar una contraseña de aplicación, no tu contraseña normal</li>";
echo "<li><strong>✅ 2FA:</strong> Debe estar activada la autenticación de 2 factores</li>";
echo "<li><strong>✅ Puerto 587:</strong> Con STARTTLS (recomendado)</li>";
echo "<li><strong>✅ Alternativa:</strong> Puerto 465 con SSL</li>";
echo "</ul>";

echo "<h4>Pasos para crear App Password:</h4>";
echo "<ol>";
echo "<li>Ve a <a href='https://myaccount.google.com' target='_blank'>Google Account</a></li>";
echo "<li>Seguridad → Autenticación en dos pasos</li>";
echo "<li>Contraseñas de aplicaciones</li>";
echo "<li>Crear nueva contraseña para 'Mail'</li>";
echo "<li>Usa esa contraseña en lugar de tu contraseña normal</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><small>Test ejecutado el: " . date('Y-m-d H:i:s') . "</small></p>";
?>
