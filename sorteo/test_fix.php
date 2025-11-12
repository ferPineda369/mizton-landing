<?php
header('Content-Type: application/json');

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => 'Correcciones aplicadas',
    'fixes' => [
        'FILTER_SANITIZE_STRING' => 'Cambiado a FILTER_SANITIZE_FULL_SPECIAL_CHARS',
        'participant_email_null' => 'Cambiado a email por defecto: no-email@sorteo.mizton.cat',
        'apis_affected' => [
            'api/register_number.php',
            'api/register_number_simple.php',
            'debug_register.php'
        ]
    ],
    'test_data' => [
        'filter_test' => filter_var('Test <script>alert("xss")</script>', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'email_default' => 'no-email@sorteo.mizton.cat'
    ]
], JSON_PRETTY_PRINT);
?>
