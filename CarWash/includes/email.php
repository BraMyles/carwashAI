<?php
require_once __DIR__ . '/../config/database.php';

function renderTemplate($body, array $params) {
    foreach ($params as $key => $value) {
        $body = str_replace('{' . $key . '}', (string)$value, $body);
    }
    return $body;
}

function sendSystemEmail($templateName, $toEmail, array $params, $subjectOverride = null) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT subject, body FROM email_templates WHERE template_name = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$templateName]);
    $tpl = $stmt->fetch();
    if (!$tpl) {
        return false;
    }
    $subject = $subjectOverride ?: renderTemplate($tpl['subject'], $params);
    $message = renderTemplate($tpl['body'], $params);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Car Wash <no-reply@localhost>\r\n";

    // Try to send email; on XAMPP without SMTP this may fail. Suppress warning and fallback to file log.
    $sent = @mail($toEmail, $subject, nl2br($message), $headers);

    if ($sent) {
        return true;
    }

    // Fallback: write email to local storage for debugging/record, so app flow continues without errors
    $dir = __DIR__ . '/../storage/emails';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $filename = $dir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9_\-]/i', '_', $templateName) . '.eml';
    $content = "To: {$toEmail}\r\nSubject: {$subject}\r\n{$headers}\r\n\r\n" . nl2br($message);
    @file_put_contents($filename, $content);

    return true; // Considered handled (logged) to avoid breaking UX
}
?>
