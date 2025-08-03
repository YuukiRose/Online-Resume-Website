<?php
/**
 * Simple SMTP Mailer Class
 * A lightweight SMTP client for sending emails
 */
class SMTPMailer {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    private $encryption;
    private $socket;

    public function __construct($config) {
        $this->smtp_host = $config['smtp_host'];
        $this->smtp_port = $config['smtp_port'];
        $this->smtp_username = $config['smtp_username'];
        $this->smtp_password = $config['smtp_password'];
        $this->from_email = $config['from_email'];
        $this->from_name = $config['from_name'];
        $this->encryption = $config['encryption'] ?? 'tls';
    }

    public function sendMail($to_email, $to_name, $subject, $html_body, $text_body = '') {
        try {
            // Connect to SMTP server
            $this->connect();
            
            // Authenticate
            $this->authenticate();
            
            // Send email
            $this->sendEmail($to_email, $to_name, $subject, $html_body, $text_body);
            
            // Close connection
            $this->disconnect();
            
            return true;
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    private function connect() {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        if ($this->encryption === 'ssl' || $this->smtp_port == 465) {
            $this->socket = stream_socket_client(
                "ssl://{$this->smtp_host}:{$this->smtp_port}",
                $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context
            );
        } else {
            $this->socket = stream_socket_client(
                "tcp://{$this->smtp_host}:{$this->smtp_port}",
                $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context
            );
        }

        if (!$this->socket) {
            throw new Exception("Failed to connect to SMTP server: $errstr ($errno)");
        }

        $this->getResponse(); // Read initial greeting

        // Send EHLO
        $this->sendCommand("EHLO " . gethostname());

        // Start TLS if needed
        if ($this->encryption === 'tls' && $this->smtp_port != 465) {
            $this->sendCommand("STARTTLS");
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand("EHLO " . gethostname()); // EHLO again after TLS
        }
    }

    private function authenticate() {
        $this->sendCommand("AUTH LOGIN");
        $this->sendCommand(base64_encode($this->smtp_username));
        $this->sendCommand(base64_encode($this->smtp_password));
    }

    private function sendEmail($to_email, $to_name, $subject, $html_body, $text_body) {
        // MAIL FROM
        $this->sendCommand("MAIL FROM:<{$this->from_email}>");
        
        // RCPT TO
        $this->sendCommand("RCPT TO:<{$to_email}>");
        
        // DATA
        $this->sendCommand("DATA");

        // Build simple HTML email (no multipart complexity)
        $headers = $this->buildHeaders($to_email, $to_name, $subject);
        $body = $html_body; // Just use HTML body directly
        
        $email_content = $headers . "\r\n\r\n" . $body . "\r\n.";
        $this->sendRaw($email_content);
        $this->getResponse();
    }

    private function buildHeaders($to_email, $to_name, $subject) {
        $headers = [];
        $headers[] = "From: {$this->from_name} <{$this->from_email}>";
        $headers[] = "To: {$to_name} <{$to_email}>";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: 8bit";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . uniqid() . "@{$this->smtp_host}>";
        
        return implode("\r\n", $headers);
    }

    private function sendCommand($command) {
        fwrite($this->socket, $command . "\r\n");
        return $this->getResponse();
    }

    private function sendRaw($data) {
        fwrite($this->socket, $data . "\r\n");
    }

    private function getResponse() {
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        
        $code = (int)substr($response, 0, 3);
        if ($code >= 400) {
            throw new Exception("SMTP Error: $response");
        }
        
        return $response;
    }

    private function disconnect() {
        if ($this->socket) {
            $this->sendCommand("QUIT");
            fclose($this->socket);
        }
    }
}
?>
