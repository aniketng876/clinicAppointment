<?php
return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'frontend\\controllers',
    'components' => [
        'user' => [
            'identityClass' => 'common\\models\\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/site/login'],
        ],
        // ...other components...
    ],
    // ...other config...
];
