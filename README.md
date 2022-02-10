<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
        <img src="logo_meilisearch.png" height="100px">
    </a>
    <h1 align="center">MeiliSearch Extension For Yii2 Framework</h1>
    <br>
</p>

![Build](https://api.travis-ci.com/zhuzixian520/yii2-meilisearch.svg?branch=master&status=unknown)

This extension provides the MeiliSearch integration for the Yii framework 2.0. 

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist zhuzixian520/yii2-meilisearch "*"
```

or add

```
"zhuzixian520/yii2-meilisearch": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
return [
    'components' => [
        'meilisearch' => [
            'class' => zhuzixian520\meilisearch\Connection::class,
            'hostname' => 'localhost',
            'port' => 7700,
            //'apiKey' => 'your_master_key',
            //'useSSL' => false,
        ],
    ],
];
```

### Work with this component

```php
$meilisearch = \Yii::$app->meilisearch;

// Set API keys From your master Key
$meilisearch->apiKey = 'xxxx';

//The health check endpoint enables you to periodically test the health of your Meilisearch instance
$meilisearch->createCommand()->health;
```

### Search

```php
$res = $meilisearch->createCommand()->index('movies')->search(['q' => 'saw', 'limit' => 2,]);
var_dump($res);
```

### Contact Us

>Email：zhuzixian520@126.com


## Sponsorship and donation:

<p align="center">
    <img src="wepay.jpg" width="25%">
    <img src="alipay.jpg" width="25%">
</p>