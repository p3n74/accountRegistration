<?php
// Dynamically determine the base URL for the application. This ensures that
// links generated in emails work both in local development (e.g., localhost)
// and in production (e.g., accounts.dcism.org) without hard-coding.

if (!defined('BASE_URL')) {
    // Detect protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

    // Hostname (e.g., localhost:8000 or accounts.dcism.org)
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // If the project is served from a sub-directory (e.g., /accounts in local dev),
    // append that path. dirname($_SERVER['SCRIPT_NAME']) gives the directory of the
    // currently executing script; we only need its first segment as the base.
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    // Normalize directory separators and trim leading/trailing slashes
    $scriptDir = trim(str_replace('\\', '/', $scriptDir), '/');

    // If the application is in a sub-folder, preserve it (e.g., "accounts/")
    $path = $scriptDir ? $scriptDir . '/' : '';

    // Always ensure BASE_URL ends with a single trailing slash
    define('BASE_URL', $protocol . $host . '/' . $path);
}

?>