<?php
require __DIR__ . '/includes/session.php';
app_session_start();
header('Content-Type: text/plain; charset=utf-8');
echo "Session ID: " . session_id() . "\n";
echo "Session save path (PHP): " . ini_get('session.save_path') . "\n";
echo "Cookie header sent by browser (from server view): " . ($_SERVER['HTTP_COOKIE'] ?? '') . "\n";
echo "Set-Cookie header planned: ";
// simulate setcookie behavior to show params
$cookieParams = session_get_cookie_params();
print_r($cookieParams);

// show files in session.save_path (server side)
$path = ini_get('session.save_path');
if ($path && is_dir($path)) {
    $files = scandir($path);
    echo "Files in session.save_path:\n";
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        echo " - $f\n";
    }
} else {
    echo "session.save_path not a dir or inaccessible\n";
}

?>