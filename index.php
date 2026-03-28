<?php
/**
 * GAE Entry Point / Router
 *
 * Google App Engine's `serve index.php` starts PHP's built-in web server with
 * this file as the router script. Without a router every request would be
 * handled by the same file; with a router we can dispatch each request to the
 * correct PHP page.
 *
 * How PHP's built-in server router works:
 *   - If the router returns false  → the server handles the request normally
 *     (serves a static file from disk).
 *   - If the router outputs content or calls exit → that response is sent.
 *
 * Why chdir() is needed:
 *   Pages in /pages/ use relative include paths such as
 *   `require_once '../config/database.php'`. PHP resolves relative paths from
 *   the current working directory (CWD), not the included file's directory.
 *   Without chdir() the CWD would be /workspace (the project root), making
 *   `../config/` resolve to the parent of the project — not found.
 *   chdir(dirname($file)) sets the CWD to the page's own directory so all
 *   relative paths inside that page resolve correctly.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Dispatch to an existing PHP page (e.g. /pages/attendees.php).
// Skip index.php itself to avoid infinite recursion.
if ($path !== '/' && $path !== '/index.php' && is_file($file) && substr($file, -4) === '.php') {
    // Change CWD to the page's directory so relative includes resolve correctly.
    chdir(dirname($file));
    require $file;
    exit;
}

// For any other existing file (CSS, images, JS, etc.) that was not caught by
// the app.yaml static_dir handlers, let PHP's built-in server serve it directly.
if ($path !== '/' && is_file($file)) {
    return false;
}

// Default route — serve the conference home page.
require __DIR__ . '/conference.php';
