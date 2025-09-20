<?php
if (!isset($_SESSION)){session_start();}

date_default_timezone_set('America/Mexico_City');
$intFechaReg = time();

echo $intFechaReg.'<br><br>';

echo('<pre>');
echo 'SESSION:<br>';
var_dump($_SESSION);
echo '<br><br>';
echo 'POST:<br>';
var_dump($_POST);
echo('</pre>');

var_dump([
    'host' => $_ENV['MAIL_HOST'],
    'port' => $_ENV['MAIL_PORT'],
    'user' => $_ENV['MAIL_USERNAME'],
    'encryption' => $_ENV['MAIL_ENCRYPTION']
]);
