<?php

namespace zhuzixian520\meilisearch;

use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;
use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * This class implements the ActiveRecord pattern for the fulltext search and data storage
 * [Meilisearch](https://www.meilisearch.com).
 *
 * For defining a record a subclass should at least implement the [[attributes()]] method to define attributes.
 * A primary key can be defined via [[primaryKey()]] which defaults to `id` if not specified.
 *
 * You may override [[index()]] to define the index.
 *
 * @author Trevor <zhuzixian520@126.com>
 * @since 1.0.1
 */
class ActiveRecord extends BaseActiveRecord
{
    public $id;

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        if (!$this->beforeSave(true)) {
            return false;
        }

        $values = $this->getDirtyAttributes($attributes);
        var_dump($this->getPrimaryKey());exit;

        //$response = static::getDb()->createCommand()->index(static::index())->addDocuments([$values], $this->getPrimaryKey());

        return true;
    }

    /**
     * Returns the database connection used by this AR class.
     * By default, the "meilisearch" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     *
     * @inheritdoc
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('meilisearch');
    }

    /**
     * Returns the list of all attribute names of the model.
     *
     * This method must be overridden by child classes to define available attributes.
     * IMPORTANT: The primary key (the `_id` attribute) MUST NOT be included in [[attributes()]].
     *
     * Attributes are names of fields of the corresponding Meilisearch document.
     *
     * @return string[] list of attribute names.
     * @throws \yii\base\InvalidConfigException if not overridden in a child class.
     */
    public function attributes()
    {
        throw new InvalidConfigException('The attributes() method of Meilisearch ActiveRecord has to be implemented by child classes.');
    }

    /**
     * @return string the name of the index this record is stored in.
     */
    public static function index()
    {
        return Inflector::pluralize(Inflector::camel2id(StringHelper::basename(get_called_class()), '_'));
    }

    /**
     * @inheritdoc
     * @param $asArray
     * @return array|mixed|null
     */
    public function getPrimaryKey($asArray = false)
    {
        $pk = static::primaryKey()[0];
        if ($asArray) {
            return [$pk => $this->$pk];
        } else {
            return $this->$pk;
        }

    }
}