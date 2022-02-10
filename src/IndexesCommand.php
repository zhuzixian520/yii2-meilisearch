<?php

namespace zhuzixian520\meilisearch;

use yii\base\BaseObject;
use yii\base\InvalidArgumentException;

/**
 * The Index-related command class implements the API for accessing the Meilisearch REST API.
 *
 * @property-read bool|string|mixed $searchableAttrs The searchable attributes of an index
 * @property-read bool|string|mixed $tasks All tasks for a given index
 * @property-read bool|string|mixed $filterableAttrs An index's filterableAttributes
 * @property-read bool|string|mixed $sortableAttrs An index's sortableAttributes
 * @property-read bool|string|mixed $displayedAttrs The displayed attributes of an index
 * @property-read bool|string|mixed $synonyms The list of synonyms of an index
 * @property-read bool|string|mixed $rankingRules The ranking rules of an index
 * @property-read bool|string|mixed $stopWords The stop-words list of an index
 * @property-read bool|string|mixed $distinctAttr The distinct attribute field of an index
 * @property-read bool|string|mixed $settings The settings of an index
 * @property-read bool|string|mixed $stats Stats of an index
 * @property-read bool|string|mixed $info Information about an index
 *
 * @author Trevor <zhuzixian520@126.com>
 */
class IndexesCommand extends BaseObject
{
    const ROUTE_DOCUMENTS = 'documents';
    const ROUTE_SEARCH = 'search';
    const ROUTE_TASKS = 'tasks';
    const ROUTE_SETTINGS = 'settings';
    const ROUTE_STATS = 'stats';

    /**
     * @var Connection
     */
    public $db;
    /**
     * @var string Index unique id
     */
    public $index_uid;

    /**
     * @var string[] Url array
     */
    private $_url = ['indexes'];

    public function init()
    {
        $this->_url[] = [$this->index_uid];
    }

    /**
     * @param string[] $routes
     * @return void
     */
    private function _setRoute($routes)
    {
        array_push($this->_url, ...$routes);
    }

    private function _setRouteSetting($route=null)
    {
        $routes = [self::ROUTE_SETTINGS];
        if ($routes) $routes[] = $route;

        array_push($this->_url, ...$routes);
    }

    /**
     * Get information about an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/indexes.html#get-one-index
     */
    public function getInfo()
    {
        return $this->db->get($this->_url);
    }

    /**
     * Update an index's primary key
     *
     * @param $primaryKey
     * @return bool|mixed|string
     * @throws Exception
     */
    public function update($primaryKey=null)
    {
        $body = $primaryKey ? ['primaryKey' => $primaryKey] : null;
        return $this->db->put($this->_url, [], json_encode($body));
    }

    /**
     * @return bool|mixed|string
     * @throws Exception
     */
    public function delete()
    {
        return $this->db->delete($this->_url);
    }

    /**
     * Get one document using its unique id
     *
     * @param string|int $document_id
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/documents.html#get-one-document
     */
    public function getDocument($document_id)
    {
        $this->_setRoute([self::ROUTE_DOCUMENTS, $document_id]);
        return $this->db->get($this->_url);
    }

    /**
     * Get documents by batch
     *
     * @param array $params Query parameters
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/documents.html#get-documents
     */
    public function getDocuments($params=[])
    {
        if (empty($document_id)) throw new InvalidArgumentException('Argument $document_id is empty');

        $this->_setRoute([self::ROUTE_DOCUMENTS]);

        return $this->db->get($this->_url, $params);
    }

    /**
     * Add or replace documents
     *
     * @param array $documents a JSON array of documents.
     * @param string|null $primaryKey The primary key of the index (optional)
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/documents.html#add-or-replace-documents
     */
    public function addDocuments($documents, $primaryKey=null)
    {
        if (empty($documents)) throw new InvalidArgumentException('Argument $documents is empty');

        $this->_setRoute([self::ROUTE_DOCUMENTS]);
        $options = $primaryKey ? ['primaryKey' => $primaryKey] : [];

        return $this->db->post($this->_url, $options, json_encode($documents));
    }

    /**
     * Add or update documents
     * @param array $documents a JSON array of documents.
     * @param string|null $primaryKey The primary key of the index (optional)
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/documents.html#add-or-update-documents
     */
    public function updateDocuments($documents, $primaryKey=null)
    {
        if (empty($documents)) throw new InvalidArgumentException('Argument $documents is empty');

        $this->_setRoute([self::ROUTE_DOCUMENTS]);
        $options = $primaryKey ? ['primaryKey' => $primaryKey] : [];

        return $this->db->put($this->_url, $options, json_encode($documents));
    }

    /**
     * Delete all documents
     *
     * @return bool|mixed|string
     * @throws Exception
     */
    public function deleteAllDocuments()
    {
        $this->_setRoute([self::ROUTE_DOCUMENTS]);
        return $this->db->delete($this->_url);
    }

    /**
     * Delete one document based on its unique id
     *
     * @param string|int $document_id
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/documents.html#delete-one-document
     */
    public function deleteDocument($document_id)
    {
        if (empty($document_id)) throw new InvalidArgumentException('Argument $document_id is empty');

        $this->_setRoute([self::ROUTE_DOCUMENTS, $document_id]);

        return $this->db->delete($this->_url);
    }

    /**
     * Delete a selection of documents based on array of document id's
     *
     * @param string[]|int[] $document_ids a JSON Array with the unique id's of the documents
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/documents.html#delete-documents-by-batch
     */
    public function deleteDocuments($document_ids)
    {
        if (empty($document_ids)) throw new InvalidArgumentException('Argument $document_ids is empty');

        $this->_setRoute([self::ROUTE_DOCUMENTS, 'delete-batch']);

        return $this->db->post($this->_url, [], json_encode($document_ids));
    }

    /**
     * Search for documents matching a specific query in the given index
     *
     * @param array $body
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/search.html#search-in-an-index-with-post-route
     */
    public function search($body)
    {
        $this->_setRoute([self::ROUTE_SEARCH]);
        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * List all tasks for a given index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/tasks.html#get-all-tasks-by-index
     */
    public function getTasks()
    {
        $this->_setRoute([self::ROUTE_TASKS]);
        return $this->db->get($this->_url);
    }

    /**
     * Get a single task in a given index
     *
     * @param int $task_uid The task identifier
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/tasks.html#get-task-by-index
     */
    public function getTask($task_uid)
    {
        if (empty($task_uid)) throw new InvalidArgumentException('Argument $task_uid is empty');

        $this->_setRoute([self::ROUTE_TASKS, $task_uid]);

        return $this->db->get($this->_url);
    }

    /**
     * Get the settings of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/settings.html#get-settings
     */
    public function getSettings()
    {
        $this->_setRouteSetting();

        return $this->db->get($this->_url);
    }

    /**
     * Update the settings of an index
     *
     * @param $body
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/settings.html#update-settings
     */
    public function updateSettings($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting();

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset the settings of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/settings.html#reset-settings
     */
    public function resetSettings()
    {
        $this->_setRouteSetting();

        return $this->db->delete($this->_url);
    }

    /**
     * Get the displayed attributes of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/displayed_attributes.html#get-displayed-attributes
     */
    public function getDisplayedAttrs()
    {
        $this->_setRouteSetting('displayed-attributes');
        return $this->db->get($this->_url);
    }

    /**
     * Update the displayed attributes of an index
     *
     * @param $body
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/displayed_attributes.html#update-displayed-attributes
     */
    public function updateDisplayedAttrs($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('displayed-attributes');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset the displayed attributes of the index to the default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/displayed_attributes.html#reset-displayed-attributes
     */
    public function resetDisplayedAttrs()
    {
        $this->_setRouteSetting('displayed-attributes');

        return $this->db->delete($this->_url);
    }

    /**
     * Get the distinct attribute field of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/distinct_attribute.html#get-distinct-attribute
     */
    public function getDistinctAttr()
    {
        $this->_setRouteSetting('distinct-attribute');

        return $this->db->get($this->_url);
    }

    /**
     * Update the distinct attribute field of an index
     *
     * @param string $field_name The field name
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/distinct_attribute.html#update-distinct-attribute
     */
    public function updateDistinctAttr($field_name)
    {
        if (empty($field_name)) throw new InvalidArgumentException('Argument $field_name is empty');

        $this->_setRouteSetting('distinct-attribute');

        return $this->db->post($this->_url, [], $field_name);
    }

    /**
     * Reset the distinct attribute field of an index to its default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/distinct_attribute.html#reset-distinct-attribute
     */
    public function resetDistinctAttr()
    {
        $this->_setRouteSetting('distinct-attribute');

        return $this->db->delete($this->_url);
    }

    /**
     * Get an index's filterableAttributes
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/filterable_attributes.html#get-filterable-attributes
     */
    public function getFilterableAttrs()
    {
        $this->_setRouteSetting('filterable-attributes');

        return $this->db->get($this->_url);
    }

    /**
     * Update an index's filterable attributes list
     *
     * @param string[] $body An array of strings containing the attributes that can be used as filters at query time
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/filterable_attributes.html#update-filterable-attributes
     */
    public function updateFilterableAttrs($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('filterable-attributes');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset an index's filterable attributes list back to its default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/filterable_attributes.html#reset-filterable-attributes
     */
    public function resetFilterableAttrs()
    {
        $this->_setRouteSetting('filterable-attributes');

        return $this->db->delete($this->_url);
    }

    /**
     * Get the ranking rules of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/ranking_rules.html#get-ranking-rules
     */
    public function getRankingRules()
    {
        $this->_setRouteSetting('ranking-rules');

        return $this->db->get($this->_url);
    }

    /**
     * Update the ranking rules of an index
     *
     * @param string[] $body An array that contain ranking rules sorted by order of importance.
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/ranking_rules.html#update-ranking-rules
     */
    public function updateRankingRules($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('ranking-rules');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset the ranking rules of an index to their default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/ranking_rules.html#reset-ranking-rules
     */
    public function resetRankingRules()
    {
        $this->_setRouteSetting('ranking-rules');

        return $this->db->delete($this->_url);
    }

    /**
     * Get the searchable attributes of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/searchable_attributes.html#get-searchable-attributes
     */
    public function getSearchableAttrs()
    {
        $this->_setRouteSetting('searchable-attributes');

        return $this->db->get($this->_url);
    }

    /**
     * Update the searchable attributes of an index
     *
     * @param string[] $body An array of strings that contains searchable attributes sorted by order of importance (arranged from the most important attribute to the least important attribute)
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/searchable_attributes.html#update-searchable-attributes
     */
    public function updateSearchableAttrs($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('searchable-attributes');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset the searchable attributes of the index to the default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/searchable_attributes.html#reset-searchable-attributes
     */
    public function resetSearchableAttrs()
    {
        $this->_setRouteSetting('searchable-attributes');

        return $this->db->delete($this->_url);
    }

    /**
     * Get an index's sortableAttributes
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/sortable_attributes.html#get-sortable-attributes
     */
    public function getSortableAttrs()
    {
        $this->_setRouteSetting('sortable-attributes');

        return $this->db->get($this->_url);
    }

    /**
     * Update an index's sortable attributes list
     *
     * @param string[] $body An array of strings containing the attributes that can be used to sort search results at query time
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/sortable_attributes.html#update-sortable-attributes
     */
    public function updateSortableAttrs($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('sortable-attributes');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset an index's sortable attributes list back to its default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/sortable_attributes.html#reset-sortable-attributes
     */
    public function resetSortableAttrs()
    {
        $this->_setRouteSetting('sortable-attributes');

        return $this->db->delete($this->_url);
    }

    /**
     * Get the stop-words list of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/stop_words.html#get-stop-words
     */
    public function getStopWords()
    {
        $this->_setRouteSetting('stop-words');

        return $this->db->get($this->_url);
    }

    /**
     * Update the list of stop-words of an index
     *
     * @param string[] $body An array of strings that contains the stop-words
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/stop_words.html#update-stop-words
     */
    public function updateStopWords($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('stop-words');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset the list of stop-words of an index to its default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/stop_words.html#reset-stop-words
     */
    public function resetStopWords()
    {
        $this->_setRouteSetting('stop-words');

        return $this->db->delete($this->_url);
    }

    /**
     * Get the list of synonyms of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/synonyms.html#get-synonyms
     */
    public function getSynonyms()
    {
        $this->_setRouteSetting('synonyms');

        return $this->db->get($this->_url);
    }

    /**
     * Update the list of synonyms of an index
     *
     * @param array $body An object that contains all synonyms and their associated words
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/synonyms.html#update-synonyms
     */
    public function updateSynonyms($body)
    {
        if (empty($body)) throw new InvalidArgumentException('Argument $body is empty');

        $this->_setRouteSetting('synonyms');

        return $this->db->post($this->_url, [], json_encode($body));
    }

    /**
     * Reset the list of synonyms of an index to its default value
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/synonyms.html#reset-synonyms
     */
    public function resetSynonyms()
    {
        $this->_setRouteSetting('synonyms');

        return $this->db->delete($this->_url);
    }

    /**
     * Get stats of an index
     *
     * @return bool|mixed|string
     * @throws Exception
     * @see https://docs.meilisearch.com/reference/api/stats.html#get-stats-of-an-index
     */
    public function getStats()
    {
        $this->_setRoute([self::ROUTE_STATS]);

        return $this->db->get($this->_url);
    }
}