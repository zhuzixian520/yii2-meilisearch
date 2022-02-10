<?php

namespace zhuzixian520\meilisearch;

/**
 * Exception represents an exception that is caused by Meilisearch-related operations.
 *
 * @author Trevor <zhuzixian520@126.com>
 */
class Exception extends \yii\db\Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Meilisearch Database Exception';
    }
}