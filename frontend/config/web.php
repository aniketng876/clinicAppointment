<?php
return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'frontend\\controllers',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'your-frontend-secret-key',
        ],
    ],
    'modules' => [
    ],
    'params' => require(__DIR__ . '/params.php'),
];
