<?php
// Test simple de envío de correo
echo "<h2>🧪 Test de Correo Electrónico</h2>";

$to = "diaztecete@gmail.com";
$subject = "Test desde biblioteca - " . date('Y-m-d H:i:s');
$message = "
<html>
<head>
  <title>Test de correo</title>
</head>
<body>
  <h3>Test de funcionalidad de correo</h3>
  <p>Este es un mensaje de prueba enviado desde la biblioteca.</p>
  <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
  <p>Si recibes este correo, la configuración está funcionando.</p>
</body>
</html>
";

// Headers para HTML
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Biblioteca <biblioteca@localhost>" . "\r\n";

echo "<p>Intentando enviar correo a: <strong>$to</strong></p>";
echo "<p>Asunto: <strong>$subject</strong></p>";

if (mail($to, $subject, $message, $headers)) {
    echo "<p style='color: green;'>✅ Correo enviado exitosamente usando mail()</p>";
} else {
    echo "<p style='color: red;'>❌ No se pudo enviar el correo con mail()</p>";
    
    // Información de debug
    echo "<h3>Información de configuración PHP:</h3>";
    echo "<p><strong>sendmail_path:</strong> " . ini_get('sendmail_path') . "</p>";
    echo "<p><strong>SMTP:</strong> " . ini_get('SMTP') . "</p>";
    echo "<p><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Configuración recomendada para XAMPP</h3>";
echo "<p>Para que funcione mail() en XAMPP, edita el archivo <code>php.ini</code>:</p>";
echo "<pre>
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = diaztecete@gmail.com
</pre>";
echo "<p>O instala un servidor SMTP local como <strong>Mercury Mail</strong> que viene con XAMPP.</p>";

// Test de PHPMailer simple
echo "<hr>";
echo "<h3>🧪 Test de PHPMailer</h3>";

if (file_exists('PHPMailer/PHPMailer.php')) {
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';
    require_once 'PHPMailer/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    try {
        $mail = new PHPMailer(true);
        
        // Solo verificar configuración, no enviar
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'diaztecete@gmail.com';
        $mail->Password = 'hqicplranmnjaojr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        echo "<p style='color: green;'>✅ PHPMailer se cargó correctamente</p>";
        echo "<p>Configuración SMTP: {$mail->Host}:{$mail->Port}</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en PHPMailer: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ PHPMailer no encontrado</p>";
}
?>
