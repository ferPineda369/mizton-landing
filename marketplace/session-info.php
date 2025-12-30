<?php
session_start();
echo '<h2>MARKETPLACE - Información de Sesión</h2>';
echo '<pre>';
echo 'Session ID: ' . session_id() . "\n";
echo 'Session Name: ' . session_name() . "\n";
echo 'Session Save Path: ' . session_save_path() . "\n";
echo 'Cookie Params: ';
print_r(session_get_cookie_params());
echo "\nSession Data:\n";
print_r($_SESSION);
echo '</pre>';
?>
