<?php

namespace zhuzixian520\meilisearch;

use Yii;
use yii\db\ActiveQueryInterface;
use yii\db\BaseActiveRecord;

class ActiveRecord extends BaseActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['_id'];
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
        // TODO: Implement insert() method.
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->get('meilisearch');
    }
}