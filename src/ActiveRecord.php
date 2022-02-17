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
 * The following is an example model called `Movie`:
 *
 * ```php
 *
 * use zhuzixian520\meilisearch\ActiveRecord;
 *
 * class Movie extends ActiveRecord
 * {
 *     public static function index()
 *     {
 *          return 'movies';
 *     }
 *
 *     public function attributes()
 *     {
 *         return ['id', 'title', 'poster', 'overview', 'release_date', 'genres'];
 *     }
 * }
 * ```
 *
 *
 *
 * @property mixed|null $primaryKey
 *
 * @author Trevor <zhuzixian520@126.com>
 * @since 1.0.1
 */
class ActiveRecord extends BaseActiveRecord
{
    /**
     * This method defines the attribute that uniquely identifies a record.
     * The name of the primary key attribute is `id`, and can be changed.
     *
     * Meilisearch does not support composite primary keys in the traditional sense. However to match the signature
     * of the [[\yii\db\ActiveRecordInterface|ActiveRecordInterface]] this methods returns an array instead of a
     * single string.
     *
     * @inheritdoc
     * @return string[] array of primary key attributes. Only the first element of the array will be used.
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

        $response = static::getDb()->createCommand()
            ->index(static::index())
            ->addDocuments([$values], $this->getPrimaryKeyName());

        if ($response === false) {
            return false;
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            return false;
        }

        return $this->updateInternal($attributeNames);
    }

    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }

        $result = static::getDb()->createCommand()
            ->index(static::index())
            ->updateDocuments([$values], $this->getPrimaryKeyName());

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = $this->getOldAttribute($name);
            $this->setOldAttribute($name, $value);
        }
        $this->afterSave(false, $changedAttributes);

        if ($result === false) {
            return 0;
        } else {
            return 1;
        }
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

    public function getPrimaryKeyName()
    {
        if (isset(static::primaryKey()[0])) {
            $pk = static::primaryKey()[0];
        }else {
            throw new InvalidConfigException('The primaryKey() method of Meilisearch ActiveRecord implemented by child classes cant not be empty.');
        }

        return $pk;
    }
}