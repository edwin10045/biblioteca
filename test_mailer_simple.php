<?php
// MailerSimple - Clase simplificada para Gmail SMTP
class MailerSimple
{
    private $smtp_conn;
    private $debug = false;
    private $last_reply = '';
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }
    
    public function enviarGmail($from_email, $from_password, $to_email, $subject, $body, $from_name = '')
    {
        try {
            // Conectar a Gmail SMTP
            if (!$this->conectarGmail()) {
                return ['success' => false, 'error' => 'No se pudo conectar a Gmail SMTP'];
            }
            
            // Autenticar
            if (!$this->autenticar($from_email, $from_password)) {
                return ['success' => false, 'error' => 'Falló la autenticación: ' . $this->last_reply];
            }
            
            // Enviar correo
            if (!$this->enviarCorreo($from_email, $to_email, $subject, $body, $from_name)) {
                return ['success' => false, 'error' => 'Falló el envío: ' . $this->last_reply];
            }
            
            // Cerrar conexión
            $this->cerrarConexion();
            
            return ['success' => true, 'message' => 'Correo enviado exitosamente'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Excepción: ' . $e->getMessage()];
        }
    }
    
    private function conectarGmail()
    {
        $this->debug_output("Conectando a smtp.gmail.com:587");
        
        // Conectar sin TLS primero
        $this->smtp_conn = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
        
        if (!$this->smtp_conn) {
            $this->debug_output("Error conexión: $errstr ($errno)");
            return false;
        }
        
        // Leer saludo del servidor
        $response = $this->leerRespuesta();
        $this->debug_output("Respuesta inicial: $response");
        
        if (!$this->verificarCodigo($response, 220)) {
            return false;
        }
        
        // Enviar EHLO
        if (!$this->enviarComando('EHLO localhost', 250)) {
            return false;
        }
        
        // Iniciar STARTTLS
        if (!$this->enviarComando('STARTTLS', 220)) {
            return false;
        }
        
        // Activar cifrado TLS
        if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $this->debug_output("Error activando TLS");
            return false;
        }
        
        $this->debug_output("TLS activado exitosamente");
        
        // EHLO nuevamente después de TLS
        return $this->enviarComando('EHLO localhost', 250);
    }
    
    private function autenticar($username, $password)
    {
        $this->debug_output("Iniciando autenticación");
        
        // AUTH LOGIN
        if (!$this->enviarComando('AUTH LOGIN', 334)) {
            return false;
        }
        
        // Enviar username en base64
        $username_b64 = base64_encode($username);
        if (!$this->enviarComando($username_b64, 334)) {
            return false;
        }
        
        // Enviar password en base64
        $password_b64 = base64_encode($password);
        fwrite($this->smtp_conn, $password_b64 . "\r\n");
        $response = $this->leerRespuesta();
        $this->debug_output("Comando: $password_b64");
        $this->debug_output("Respuesta: $response");
        
        // Código 235 significa autenticación exitosa
        if (!$this->verificarCodigo($response, 235)) {
            return false;
        }
        
        $this->debug_output("Autenticación exitosa");
        return true;
    }
    
    private function enviarCorreo($from, $to, $subject, $body, $from_name = '')
    {
        // MAIL FROM
        if (!$this->enviarComando("MAIL FROM:<$from>", 250)) {
            return false;
        }
        
        // RCPT TO
        if (!$this->enviarComando("RCPT TO:<$to>", 250)) {
            return false;
        }
        
        // DATA
        if (!$this->enviarComando('DATA', 354)) {
            return false;
        }
        
        // Construir mensaje
        $display_name = !empty($from_name) ? "$from_name <$from>" : $from;
        $mensaje = "From: $display_name\r\n";
        $mensaje .= "To: $to\r\n";
        $mensaje .= "Subject: $subject\r\n";
        $mensaje .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mensaje .= "MIME-Version: 1.0\r\n";
        $mensaje .= "\r\n";
        $mensaje .= $body;
        $mensaje .= "\r\n.\r\n";
        
        // Enviar mensaje
        fwrite($this->smtp_conn, $mensaje);
        $response = $this->leerRespuesta();
        $this->debug_output("Respuesta DATA: $response");
        
        return $this->verificarCodigo($response, 250);
    }
    
    private function enviarComando($comando, $codigo_esperado)
    {
        fwrite($this->smtp_conn, $comando . "\r\n");
        $response = $this->leerRespuesta();
        
        $this->debug_output("Comando: $comando");
        $this->debug_output("Respuesta: $response");
        
        return $this->verificarCodigo($response, $codigo_esperado);
    }
    
    private function leerRespuesta()
    {
        $response = '';
        while (($line = fgets($this->smtp_conn, 512)) !== false) {
            $response .= $line;
            // Si la línea termina con código + espacio, es la última línea
            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        $this->last_reply = trim($response);
        return $this->last_reply;
    }
    
    private function verificarCodigo($response, $codigo_esperado)
    {
        $codigo_actual = intval(substr($response, 0, 3));
        return $codigo_actual == $codigo_esperado;
    }
    
    private function cerrarConexion()
    {
        if ($this->smtp_conn) {
            fwrite($this->smtp_conn, "QUIT\r\n");
            fclose($this->smtp_conn);
            $this->debug_output("Conexión cerrada");
        }
    }
    
    private function debug_output($mensaje)
    {
        if ($this->debug) {
            echo "<div style='background: #f8f9fa; border-left: 4px solid #28a745; padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;'>";
            echo htmlspecialchars($mensaje);
            echo "</div>";
        }
    }
}

// Test del MailerSimple
echo "<h2>🧪 Test MailerSimple para Gmail</h2>";

$mailer = new MailerSimple(true); // Con debug activado

$resultado = $mailer->enviarGmail(
    'diaztecete@gmail.com',           // From email
    'hqicplranmnjaojr',               // App password
    'diaztecete@gmail.com',           // To email
    'Test MailerSimple - ' . date('H:i:s'),
    '<h3>✅ Test MailerSimple</h3><p>Este correo fue enviado usando la clase MailerSimple.</p><p>Fecha: ' . date('Y-m-d H:i:s') . '</p>',
    'Biblioteca Sistema'              // From name
);

if ($resultado['success']) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #155724; margin: 0;'>✅ " . $resultado['message'] . "</h4>";
    echo "<p style='margin: 5px 0 0 0;'>Revisa tu bandeja de entrada (y spam) en diaztecete@gmail.com</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #721c24; margin: 0;'>❌ Error</h4>";
    echo "<p style='margin: 5px 0 0 0;'>" . htmlspecialchars($resultado['error']) . "</p>";
    echo "</div>";
}
?>
