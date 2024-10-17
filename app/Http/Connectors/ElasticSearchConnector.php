<?php
namespace Connectors;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Config;
/**
 * Low-level client for Elasticsearch
 *
 * @author eman.mohamed
 */
class ElasticSearchConnector
{
    protected $searchEngineClient;

    /**
     * @param string $searchEngineUrl
     */
    public function __construct()
    {
        $searchEngineUrl = Config::get('elasticSearch.searchEngineUrl');
        $clientparams             = array($searchEngineUrl);
        $this->searchEngineClient = ClientBuilder::create()->setHosts($clientparams)->build();
    }

    /**
     *
     * Create new index
     *
     * @param string $indexName
     * @param array $settingsAndMapping
     *
     * @return Response
     */
    public function createIndex($indexName, $settingsAndMapping)
    {
        $indexParams          = array();
        $indexParams['index'] = $indexName;
        $indexParams['body']  = $settingsAndMapping;

        $result = $this->searchEngineClient->indices()->create($indexParams);
        return $result;
    }

    /**
     *
     * List all existing indices
     *
     * @return Response
     */
    public function listIndices()
    {
        $result = $this->searchEngineClient->indices()->getAliases();
        return $result;
    }

    /**
     *
     * Delete alias or list of aliases
     *
     * @return Response
     */
    public function deleteAlias($params)
    {
        $result = $this->searchEngineClient->indices()->deleteAlias($params);
        return $result;
    }

    /**
     *
     * Add alias to existing index
     *
     * @param array $params
     *
     * @return Response
     */
    public function addAlias($params)
    {
        $result = $this->searchEngineClient->indices()->putAlias($params);
        return $result;
    }

    /**
     *
     * Delete index
     *
     * @param array $params
     *
     * @return response
     */
    public function deleteIndex($params)
    {
        $response = $this->searchEngineClient->indices()->delete($params);
        return $response;
    }
/**
     *
     * Add one  record
     *
     * @param array $params
     *
     * @return response
     */
    public function addRecord($params)
    {
        $response = $this->searchEngineClient->index($params);
        return $response;
    }

    /**
     *
     * Add group of  records
     *
     * @param array $params
     *
     * @return boolean
     */
    public function addRecords($params)
    {
        $responses = $this->searchEngineClient->bulk($params);
        if (count($responses ["items"]) > 0) {
            unset($responses);
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * update  record
     *
     * @param array $params
     *
     * @return response
     */
    public function updateRecord($params)
    {
        $response = $this->searchEngineClient->update($params);
        return $response;
    }

    /**
     *
     * Search
     *
     * @param string $indexName
     * @param string $type
     * @param array $searchBody
     *
     * @return Response
     */
    public function search($indexName, $type, $searchBody)
    {
        $paramsSearch = [
            'index' => $indexName,
            'type' => $type,
            'body' => $searchBody
        ];


        $results      = $this->searchEngineClient->search($paramsSearch);
        return $results;
    }
       /**
     *
     * Delete record
     *
     * @param array $params
     *
     * @return response
     */
     public function deleteRecord($params){
        $response = $this->searchEngineClient->delete($params);
        return $response;
    }
}