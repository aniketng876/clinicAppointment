<?php
return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'your-backend-secret-key',
        ],
    ],
    'modules' => [
    ],
    'params' => require(__DIR__ . '/params.php'),
];
