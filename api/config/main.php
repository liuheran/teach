<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
		'v2' => [
			'class' => 'api\modules\v2\Module',
		],
    ],
    'components' => [
        'request' => [
        	'enableCsrfValidation' => false,
            'csrfParam' => '_csrf-api',
        ],
    	'response' => [
    		'format' => yii\web\Response::FORMAT_JSON,
    		'charset' => 'UTF-8',
    	],
        'user' => [
	        'identityClass' => 'api\modules\v1\models\User', // User must implement the IdentityInterface
	        'enableAutoLogin' => true,
		],
        'session' => [
            // this is the name of the session cookie used for login on the api
            'name' => 'api',
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
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];
