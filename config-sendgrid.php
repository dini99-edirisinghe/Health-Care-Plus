<?php
/**
 * Email API Configuration
 * 
 * Supports Brevo / SendGrid API keys.
 * Add BREVO_API_KEY or SENDGRID_API_KEY to .env or sendgrid.env at the project root,
 * or set the environment variable in your webserver.
 * Keep your key secret and do not commit it to a public repository.
 */

$projectRoot = __DIR__;
$dotenvFiles = [
    $projectRoot . '/.env',
    $projectRoot . '/sendgrid.env'
];

function loadEnvFile($filePath) {
    if (!is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $name = trim($parts[0]);
        $value = trim($parts[1]);

        if ($name === '' || $value === '') {
            continue;
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

foreach ($dotenvFiles as $dotenvFile) {
    if (file_exists($dotenvFile)) {
        loadEnvFile($dotenvFile);
    }
}

$key = getenv('BREVO_API_KEY');
if (!$key) {
    $key = getenv('SENDGRID_API_KEY');
}
if (!$key && defined('BREVO_API_KEY')) {
    $key = BREVO_API_KEY;
}
if (!$key && defined('SENDGRID_API_KEY')) {
    $key = SENDGRID_API_KEY;
}

if (!$key) {
    define('SENDGRID_API_KEY', 'YOUR_NEW_SENDGRID_API_KEY');
    define('BREVO_API_KEY', 'YOUR_NEW_BREVO_API_KEY');
    error_log("WARNING: Email API key not configured. Please add BREVO_API_KEY or SENDGRID_API_KEY to .env or sendgrid.env, or set the environment variable.");
} else {
    if (stripos($key, 'xkeysib-') === 0) {
        define('BREVO_API_KEY', $key);
    } else {
        define('SENDGRID_API_KEY', $key);
    }
}
?>
