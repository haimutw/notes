<?php
$nameConnect = filter_input(INPUT_POST, 'nameConnect', FILTER_DEFAULT);
$psswdConnect = filter_input(INPUT_POST, 'psswdConnect', FILTER_DEFAULT);

if (preg_match('/^[a-zA-ZÀ-ÿ -]+$/', $nameConnect) === false) {
    throw new Exception('Connection failed');
    return;
}
if (strlen($nameConnect) < 4 || strlen($nameConnect) > 25 || strlen($psswdConnect) < 8 || strlen($psswdConnect) > 64) {
    throw new Exception('Account creation failed');
    return;
}

session_name('secureNotes');
session_start();

if (isset($_SESSION['name']) === true) {
    throw new Exception('Connection failed');
    return;
}
if (filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT) !== $_SESSION['csrf_token']) {
    throw new Exception('Connection timeout, please reload the page');
    return;
}

global $PDO;
require_once __DIR__ . '/config/config.php';

try {
    $query = $PDO->prepare("SELECT id,name,psswd FROM users WHERE name=:NameConnect LIMIT 1");
    $query->execute([':NameConnect' => $nameConnect]);
    $row = $query->fetch();
    if (!$row || $query->rowCount() !== 1) {
        throw new Exception('Connection failed');
        return;
    }
} catch (Exception $e) {
    throw new Exception('Connection failed');
    return;
}

$query->closeCursor();
$PDO = null;

if (!password_verify($psswdConnect, $row['psswd'])) {
    throw new Exception('Connection failed');
    return;
}

session_unset();
session_destroy();
session_name('secureNotes');
$cookieParams = [
    'path'     => '/',
    'lifetime' => 604800,
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict',
];
session_set_cookie_params($cookieParams);
session_start();
session_regenerate_id();
$_SESSION['name'] = $row['name'];
$_SESSION['userId'] = $row['id'];
