<?php
require_once __DIR__ . '/config.php';
if (!empty($_SESSION['api_token'])) {
    $logoutResponse = app_api_request('POST', 'auth/logout');
    if (!$logoutResponse['ok']) {
        error_log('Backend token logout failed: ' . app_api_error($logoutResponse));
    }
}
session_unset();
session_destroy();
header('Location: ' . app_url('login.php'));
exit;
?>
