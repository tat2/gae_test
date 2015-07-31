<?php
/**
 * Copyright 2014 Tom Walder
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace GDS;

/**
 * Google Datastore Gateway
 *
 * Persists and retrieves Entities to/from GDS
 *
 * @author Tom Walder <tom@docnet.nu>
 * @package GDS
 */
class Gateway
{

    /**
     * @var \Google_Service_Datastore_Datasets_Resource|null
     */
    private $obj_datasets = NULL;

    /**
     * The dataset ID
     *
     * @var string
     */
    private $str_dataset_id = NULL;

    /**
     * Optional namespace (for multi-tenant applications)
     *
     * @var string|null
     */
    private $str_namespace = NULL;

    /**
     * The last response - usually a Commit or Query response
     *
     * @var \Google_Model
     */
    private $obj_last_response = NULL;

    /**
     * The transaction ID to use on the next commit
     *
     * @var null|string
     */
    private $str_next_transaction = NULL;

    /**
     * Create a new GDS service
     *
     * Optional namespace (for multi-tenant applications)
     *
     * @param \Google_Client $obj_client
     * @param $str_dataset_id
     * @param null $str_namespace
     */
    public function __construct(\Google_Client $obj_client, $str_dataset_id, $str_namespace = NULL)
    {
        $obj_service = new \Google_Service_Datastore($obj_client);
        $this->obj_datasets = $obj_service->datasets;
        $this->str_dataset_id = $str_dataset_id;
        $this->str_namespace = $str_namespace;
    }

    /**
     * Create a configured Google Client ready for Datastore use
     *
     * @param $str_app_name
     * @param $str_service_account
     * @param $str_key_file
     * @return \Google_Client
     */
    public static function createGoogleClient($str_app_name, $str_service_account, $str_key_file)
    {
        $obj_client = new \Google_Client();
        $obj_client->setApplicationName($str_app_name);
        $str_key = file_get_contents($str_key_file);
        $obj_client->setAssertionCredentials(
            new \Google_Auth_AssertionCredentials(
                $str_service_account,
                [\Google_Service_Datastore::DATASTORE, \Google_Service_Datastore::USERINFO_EMAIL],
                $str_key
            )
        );
        // App Engine php55 runtime dev server problems...
        $obj_client->setClassConfig('Google_Http_Request', 'disable_gzip', TRUE);
        return $obj_client;
    }

    /**
     * Set the transaction ID to be used next (once)
     *
     * @param $str_transaction_id
     * @return $this
     */
    public function withTransaction($str_transaction_id)
    {
        $this->str_next_transaction = $str_transaction_id;
        return $this;
    }

    /**
     * Put a single Entity into the Datastore
     *
     * @param \Google_Service_Datastore_Entity $obj_google_entity
     * @return bool
     */
    public function put(\Google_Service_Datastore_Entity $obj_google_entity)
    {
        $this->putMulti([$obj_google_entity]);
    }

    /**
     * Put an array of Entities into the Datastore
     *
     * @param \Google_Service_Datastore_Entity[] $arr_google_entities
     */
    public function putMulti(array $arr_google_entities)
    {
        $obj_mutation = new \Google_Service_Datastore_Mutation();
        $arr_auto_id = [];
        $arr_has_key = [];
        foreach ($arr_google_entities as $obj_google_entity) {
            $obj_key = $this->applyNamespace($obj_google_entity->getKey());
            /** @var \Google_Service_Datastore_KeyPathElement $obj_path_end */
            $arr_path = $obj_key->getPath();
            $obj_path_end = end($arr_path);
            if ($obj_path_end->getId() || $obj_path_end->getName()) {
                $arr_has_key[] = $obj_google_entity;
            } else {
                $arr_auto_id[] = $obj_google_entity;
            }
        }
        if (!empty($arr_auto_id)) {
            $obj_mutation->setInsertAutoId($arr_auto_id);
        }
        if (!empty($arr_has_key)) {
            $obj_mutation->setUpsert($arr_has_key);
        }
        $this->commitMutation($obj_mutation);

        // Record the Auto-generated Key IDs against the Entities.
        // https://cloud.google.com/datastore/docs/apis/v1beta2/datasets/commit
        // "Keys for insertAutoId entities. One per entity from the request, in the same order."
        if (!empty($arr_auto_id)) {
            foreach ($this->obj_last_response['mutationResult']['insertAutoIdKeys'] as $int_index => $obj_auto_insert_key) {
                $arr_auto_id[$int_index]->setKey($obj_auto_insert_key);
            }
        }
    }

    /**
     * Apply the current namespace to an object or array of objects
     *
     * @param $mix_target
     * @return mixed
     */
    private function applyNamespace($mix_target)
    {
        if(NULL !== $this->str_namespace) {
            $obj_partition = new \Google_Service_Datastore_PartitionId();
            $obj_partition->setNamespace($this->str_namespace);
            if(is_array($mix_target)) {
                foreach($mix_target as $obj_target) {
                    $obj_target->setPartitionId($obj_partition);
                }
            } else {
                $mix_target->setPartitionId($obj_partition);
            }
        }
        return $mix_target;
    }

    /**
     * Apply a mutation to the Datastore (commit)
     *
     * @param \Google_Service_Datastore_Mutation $obj_mutation
     * @return \Google_Service_Datastore_CommitResponse
     */
    private function commitMutation(\Google_Service_Datastore_Mutation $obj_mutation)
    {
        $obj_request = new \Google_Service_Datastore_CommitRequest();
        if(NULL === $this->str_next_transaction) {
            $obj_request->setMode('NON_TRANSACTIONAL');
        } else {
            $obj_request->setMode('TRANSACTIONAL');
            $obj_request->setTransaction($this->str_next_transaction);
            $this->str_next_transaction = NULL;
        }
        $obj_request->setMutation($obj_mutation);
        $this->obj_last_response = $this->obj_datasets->commit($this->str_dataset_id, $obj_request);
        return $this->obj_last_response;
    }

    /**
     * Fetch one entity by Key ID
     *
     * @param $str_kind
     * @param $int_key_id
     * @return array
     */
    public function fetchById($str_kind, $int_key_id)
    {
        return $this->fetchByIds($str_kind, [$int_key_id]);
    }

    /**
     * Fetch many entities by their Key ID
     *
     * @param $str_kind
     * @param $arr_ids
     * @return mixed
     */
    public function fetchByIds($str_kind, array $arr_ids)
    {
        $arr_keys = [];
        foreach($arr_ids as $int_id) {
            $obj_key = new \Google_Service_Datastore_Key();
            $obj_element = new \Google_Service_Datastore_KeyPathElement();
            $obj_element->setKind($str_kind);
            $obj_element->setId($int_id);
            $obj_key->setPath([$obj_element]);
            $arr_keys[] = $obj_key;
        }
        return $this->fetchByKeys($arr_keys);
    }

    /**
     * Fetch entity data by Key Name
     *
     * @param $str_kind
     * @param $str_key_name
     * @return mixed
     */
    public function fetchByName($str_kind, $str_key_name)
    {
        return $this->fetchByNames($str_kind, [$str_key_name]);
    }

    /**
     * Fetch many entities by their Key Name
     *
     * @param $str_kind
     * @param $arr_key_names
     * @return mixed
     */
    public function fetchByNames($str_kind, array $arr_key_names)
    {
        $arr_keys = [];
        foreach($arr_key_names as $str_key_name) {
            $obj_key = new \Google_Service_Datastore_Key();
            $obj_element = new \Google_Service_Datastore_KeyPathElement();
            $obj_element->setKind($str_kind);
            $obj_element->setName($str_key_name);
            $obj_key->setPath([$obj_element]);
            $arr_keys[] = $obj_key;
        }
        return $this->fetchByKeys($arr_keys);
    }

    /**
     * Fetch entity data for an array of Google Datastore Keys
     *
     * @param \Google_Service_Datastore_Key[] $arr_keys
     * @return mixed
     */
    private function fetchByKeys(array $arr_keys)
    {
        $obj_request = $this->applyTransaction(new \Google_Service_Datastore_LookupRequest());
        $obj_request->setKeys($this->applyNamespace($arr_keys));
        $this->obj_last_response = $this->obj_datasets->lookup($this->str_dataset_id, $obj_request);
        return $this->obj_last_response->getFound();
    }

    /**
     * Fetch entity data based on GQL (and optional parameters)
     *
     * @param $str_gql
     * @param array $arr_params
     * @return Entity[]
     */
    public function gql($str_gql, $arr_params = NULL)
    {
        $obj_query = new \Google_Service_Datastore_GqlQuery();
        $obj_query->setAllowLiteral(TRUE);
        $obj_query->setQueryString($str_gql);
        if(NULL !== $arr_params) {
            $this->addParamsToQuery($obj_query, $arr_params);
        }
        return $this->executeQuery($obj_query);
    }

    /**
     * Add Parameters to a GQL Query object
     *
     * @param \Google_Service_Datastore_GqlQuery $obj_query
     * @param array $arr_params
     */
    private function addParamsToQuery(\Google_Service_Datastore_GqlQuery $obj_query, array $arr_params)
    {
        if(count($arr_params) > 0) {
            $arr_args = [];
            foreach ($arr_params as $str_name => $mix_value) {
                $obj_arg = new \Google_Service_Datastore_GqlQueryArg();
                $obj_arg->setName($str_name);
                if ('startCursor' == $str_name) {
                    $obj_arg->setCursor($mix_value);
                } else {
                    $obj_val = new \Google_Service_Datastore_Value();
                    if($mix_value instanceof \Google_Service_Datastore_Key) {
                        $obj_val->setKeyValue($mix_value);
                    } elseif($mix_value instanceof \DateTime) {
                        $obj_val->setDateTimeValue($mix_value->format(\DateTime::ATOM));
                    } elseif (is_int($mix_value)) {
                        $obj_val->setIntegerValue($mix_value);
                    } else {
                        $obj_val->setStringValue($mix_value);
                    }
                    $obj_arg->setValue($obj_val);
                }
                $arr_args[] = $obj_arg;
            }
            $obj_query->setNameArgs($arr_args);
        }
    }

    /**
     * Execute the given query and return the results.
     *
     * @param \Google_Collection $obj_query
     * @return array
     */
    private function executeQuery(\Google_Collection $obj_query)
    {
        $obj_request = $this->applyTransaction(
            $this->applyNamespace(
                new \Google_Service_Datastore_RunQueryRequest()
            )
        );
        if ($obj_query instanceof \Google_Service_Datastore_GqlQuery) {
            $obj_request->setGqlQuery($obj_query);
        } else {
            $obj_request->setQuery($obj_query);
        }
        $this->obj_last_response = $this->obj_datasets->runQuery($this->str_dataset_id, $obj_request);
        if (isset($this->obj_last_response['batch']['entityResults'])) {
            return $this->obj_last_response['batch']['entityResults'];
        }
        return [];
    }

    /**
     * If we are in a transaction, apply it to the request object
     *
     * @param $obj_request
     * @return mixed
     */
    private function applyTransaction($obj_request) {
        if(NULL !== $this->str_next_transaction) {
            $obj_read_options = new \Google_Service_Datastore_ReadOptions();
            $obj_read_options->setTransaction($this->str_next_transaction);
            $obj_request->setReadOptions($obj_read_options);
            $this->str_next_transaction = NULL;
        }
        return $obj_request;
    }

    /**
     * Delete an Entity
     *
     * @param \Google_Service_Datastore_Key $obj_key
     * @return bool
     */
    public function delete(\Google_Service_Datastore_Key $obj_key)
    {
        return $this->deleteMulti([$obj_key]);
    }

    /**
     * Delete one or more entities based on their Key
     *
     * @param array $arr_keys
     * @return bool
     */
    public function deleteMulti(array $arr_keys)
    {
        $obj_mutation = new \Google_Service_Datastore_Mutation();
        foreach($arr_keys as $obj_key) {
            $this->applyNamespace($obj_key);
        }
        $obj_mutation->setDelete($arr_keys);
        $this->obj_last_response = $this->commitMutation($obj_mutation);
        return TRUE;
    }

    /**
     * Begin a transaction and return it's reference id
     *
     * @return string
     */
    public function beginTransaction()
    {
        $obj_request = new \Google_Service_Datastore_BeginTransactionRequest();
        /** @var \Google_Service_Datastore_BeginTransactionResponse $obj_response */
        $obj_response = $this->obj_datasets->beginTransaction($this->str_dataset_id, $obj_request);
        return $obj_response->getTransaction();
    }

    /**
     * Retrieve the last response object
     *
     * @return \Google_Model
     */
    public function getLastResponse()
    {
        return $this->obj_last_response;
    }

    /**
     * Get the end Cursor for the last response
     *
     * @return mixed
     */
    public function getEndCursor()
    {
        return $this->obj_last_response['batch']['endCursor'];
    }

}