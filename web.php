<?php

use yii\filters\AccessControl;
$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language'=>'uk',
    'charset'=>'utf-8',
    'timeZone' => 'Europe/Kiev',
    'components' => [
		'request' => [
            'cookieValidationKey' => 'tyu567slu89768bnm5nx3v',
			'baseUrl'=> '',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\UserIdentity',
            'authTimeout' => 600,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
		'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
			'useFileTransport' => false,
            'transport' => [
			 'class' => 'Swift_SmtpTransport',
                'encryption' => 'ssl',
				'class' => 'Swift_SmtpTransport',
				'host' => 'vodokanal-pvk.org',
				'username' => 'mail',
				'password' => '****',
				'port' => '587',
				
			],
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
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
			'class' => 'codemix\localeurls\UrlManager',
            'languages' => ['uk', 'ru'],
            'enableDefaultLanguageUrlCode' => true,
            'enableLanguagePersistence' => false,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
			'enableStrictParsing' => true,
			'rules' => [
				[
					'pattern' => '',
					'route' =>'site/index',
					'suffix' =>'',
			   ],
               [
					'pattern' => '<controller>/<action>/<page:\w*>',
					'route' =>'<controller>/<action>',
					'suffix' =>'',
			   ],
			    [
					'pattern' => '<controller>/<action>/<name:\w*>',
					'route' =>'<controller>/<action>',
					'suffix' =>'',
			   ],
			   [
					'pattern' => '<controller:\w+>/<id:\d+>',
					'route' =>'<controller>/view',
					'suffix' =>'',
			   ],
			   [
					'pattern' => '<controller>/<action>',
					'route' =>'<controller>/<action>',
					'suffix' =>'',
			   ],
			    [
					'pattern' => '<controller>/<action>/<id:\d>',
					'route' =>'<controller>/view',
					'suffix' =>'',
			   ],
			   [
					'pattern' => '<language:(ru|uk)>/<controller>/<action>',
					'route' =>'<language:(ru|uk)>/<controller>/<action>',
					'suffix' =>'',
			   ],
            ],
        ],
		'i18n' => [
            'translations' => [
                'common*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
		'allowedIPs' => ['127.0.0.1', '::1']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
