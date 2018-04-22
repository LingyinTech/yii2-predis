# yii2-predis

在yii2 框架中使用 predis , 支持cluster,sentinel

## 安装

```
composer require lingyin/predis
```

## 配置

```
return [
    'components' =>
        'cache' => [
            'class' => \lingyin\predis\Cache::class,
            'keyPrefix' => 'lingyin-common:',
            'redis' => [
                'parameters' => [
                    [
                        'host' => '127.0.0.1',
                        'port' => 6380,
                    ],
                    [
                        'host' => '127.0.0.1',
                        'port' => 6381,
                    ],
                ],
                'options' => [
                    'replication' => 'sentinel',
                    'service' => 'sentinel-127.0.0.1-6380',
                    'parameters' => [
                        'password' => 'profileLogStash',
                        'database' => 4,
                    ]
                ],
            ]
        ]
    ]
```

## 使用

```
// set 
$result = Yii::$app->cache->set('test','test',600);  

// get
$value = Yii::$app->cache->get('test');

```