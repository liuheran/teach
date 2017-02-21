<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-document',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
		'bootstrap' => ['debug'],
		'modules' => [
				'debug' => 'yii\debug\Module',
		     'gii' => [
            'class' => 'yii\gii\Module',
            'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', 'm.teach.com'] // 按需调整这里
        ],
		],
    'controllerNamespace' => 'document\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-document',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-document', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-document',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules'=>array(
            ),
        ],
       
    ],
    'params' => $params,
];
