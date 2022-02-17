<div align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100" alt="yii2 logo">
    </a>
    <a href="https://github.com/meilisearch" target="_blank">
        <img src="https://raw.githubusercontent.com/meilisearch/integration-guides/main/assets/logos/logo.svg" height="100" alt="meilisearch logo">
    </a>
    <h1>MeiliSearch Extension For Yii2 Framework</h1>
    <br>
</div>

[![Latest Stable Version](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/v)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch) [![Total Downloads](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/downloads)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch) [![Latest Unstable Version](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/v/unstable)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch) [![License](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/license)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch) [![PHP Version Require](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/require/php)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch)
[![Dependents](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/dependents)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch)
[![Suggesters](http://poser.pugx.org/zhuzixian520/yii2-meilisearch/suggesters)](https://packagist.org/packages/zhuzixian520/yii2-meilisearch)

## English | [简体中文](./README.zh-CN.md) | [繁體中文](./README.zh-TW.md)

This extension provides the MeiliSearch integration for the Yii framework 2.0. 

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require zhuzixian520/yii2-meilisearch
```

or add

```
"zhuzixian520/yii2-meilisearch": "^1.0"
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
$ms = \Yii::$app->meilisearch;

// Set API keys From your master Key
$ms->apiKey = 'xxxx';

$msc = $ms->createCommand();

//The health check endpoint enables you to periodically test the health of your Meilisearch instance
$msc->health;
```

### Indexes

```php
//$res = $msc->allIndexes;
//$res = $msc->createIndex('movies', 'id');
//$res = $msc->updateIndex('movies', 'id');
//$res = $msc->deleteIndex('movies');
$msci = $msc->index('movies');
//$res = $msci->info;
//$res = $msci->update('id');
//$res = $msci->delete();
```

### Documents

```php
$res = $msci->getDocument(25684);
$res = $msci->getDocuments(['offset' => 0, 'limit' => 2, 'attributesToRetrieve' => '*']);
/*$file_name = Yii::getAlias('@app/runtime/') . 'movies.json';
$movies_json = file_get_contents($file_name);
$movies = json_decode($movies_json, true);
$res = $msci->addDocuments($movies,'id');*/
//$res = $msci->updateDocuments($movies,'id');
//$res = $msci->deleteAllDocuments();
//$res = $msci->deleteDocument(100);
//$res = $msci->deleteDocuments([10001, 100017]);
```

### Search

```php
$res = $meilisearch->createCommand()->index('movies')->search(['q' => 'saw', 'limit' => 2,]);
var_dump($res);
```

### Task
```php
$res = $msc->tasks;
$res = $msc->getTask(4);
$res = $msci->getTasks();
$res = $msci->getTask(8);
```

### Keys
```php
//$res = $msc->keys;
//$res = $msc->getKey('9bFPJSxkc0e7939d743e110b354fe3625876cfec14efb4301bf195f6aed4a57f1d9004fa');
/*$res = $msc->createKey([
'description' => 'Add documents: Products API key',
'actions' => ['documents.add'],
'indexes' => ['products'],
'expiresAt' => '2042-04-02T00:42:42Z',
]);*/
/*$res = $msc->updateKey('yKEcAaQX547e46c7d8eaa1224f6a5196c5c7a13f47932cbeb1ba06db962b379cb0ef19e4', [
'description' => 'Add documents: Products API key',
'actions' => ['documents.add'],
'indexes' => ['products'],
'expiresAt' => '2043-04-02T00:42:42Z',
]);*/
//$res = $msc->deleteKey('qAZlXnA191d328ea51fb26b822fecc556c38c0a3af1caa95962ede60a0155381a39a3a36');
```

### Settings
```php
$res = $msci->settings;
/*$res = $msci->updateSettings([
'displayedAttributes' => ['*'],
'distinctAttribute' => null,
'filterableAttributes' => [],
'rankingRules' => [
'words',
'typo',
'proximity',
'attribute',
'sort',
'exactness',
'release_date:desc',
'rank:desc'
],
'searchableAttributes' => ['*'],
'sortableAttributes' => [],
'stopWords' => [
'the',
'a',
'an'
],
'synonyms' => [
'wolverine' => ['xmen', 'logan'],
'logan' => ['wolverine']
]
]);*/
//$res = $msci->resetSettings();
//$res = $msci->displayedAttrs;
//$res = $msci->updateDisplayedAttrs(['*']);
//$res = $msci->resetDisplayedAttrs();
/*$res = $msci->distinctAttr;
$res = $msci->updateDistinctAttr('id');
$res = $msci->resetDistinctAttr();
$res = $msci->filterableAttrs;
$res = $msci->updateFilterableAttrs([]);
$res = $msci->resetFilterableAttrs();
$res = $msci->rankingRules;
$res = $msci->updateRankingRules([
'words',
'typo',
'proximity',
'attribute',
'sort',
'exactness',
'release_date:desc',
'rank:desc'
]);
$res = $msci->resetRankingRules();
$res = $msci->searchableAttrs;
$res = $msci->updateSearchableAttrs(['*']);
$res = $msci->resetSearchableAttrs();
$res = $msci->sortableAttrs;
$res = $msci->updateSortableAttrs([]);
$res = $msci->resetSortableAttrs();
$res = $msci->stopWords;
$res = $msci->updateStopWords([
'the',
'a',
'an'
]);
$res = $msci->resetStopWords();
$res = $msci->synonyms;
$res = $msci->updateSynonyms([
'wolverine' => ['xmen', 'logan'],
'logan' => ['wolverine']
]);
$res = $msci->resetSynonyms();*/
```

### Stats
```php
//$res = $msc->stats;
//$res = $msci->stats;
```
### Health
```php
$msc->health;
```
### Version
```php
$msc->version;
```
### Dumps
```php
$res = $msc->createDump();
//$res = $msc->getDumpStatus('20220211-145911299');
var_dump($res);
```

## Contact Us
>Email：zhuzixian520@126.com

## Sponsorship and donation:
<p align="center">
    <img src="/images/wepay.jpg" width="28%">
    <img src="/images/alipay.jpg" width="28%">
</p>