<?php
/**
 * Simple MongoDB-like Database Interface
 * Uses JSON files to simulate MongoDB collections
 */

class SimpleMongoDB {
    private $data_dir;
    
    public function __construct($data_dir = 'data') {
        $this->data_dir = $data_dir;
        if (!is_dir($this->data_dir)) {
            mkdir($this->data_dir, 0755, true);
        }
    }
    
    public function getCollection($collection_name) {
        return new SimpleMongoCollection($this->data_dir, $collection_name);
    }
}

class SimpleMongoCollection {
    private $file_path;
    
    public function __construct($data_dir, $collection_name) {
        $this->file_path = $data_dir . '/' . $collection_name . '.json';
        if (!file_exists($this->file_path)) {
            file_put_contents($this->file_path, json_encode([], JSON_PRETTY_PRINT));
        }
    }
    
    public function find($filter = [], $options = []) {
        $data = $this->loadData();
        
        if (empty($filter)) {
            return $data;
        }
        
        $filtered = [];
        foreach ($data as $document) {
            if ($this->matchesFilter($document, $filter)) {
                $filtered[] = $document;
            }
        }
        
        return $filtered;
    }
    
    public function findOne($filter = []) {
        $results = $this->find($filter);
        return !empty($results) ? $results[0] : null;
    }
    
    public function insertOne($document) {
        $data = $this->loadData();
        
        if (!isset($document['_id'])) {
            $document['_id'] = $this->generateId();
        }
        
        $data[] = $document;
        $this->saveData($data);
        
        return ['insertedId' => $document['_id']];
    }
    
    public function updateOne($filter, $update) {
        $data = $this->loadData();
        $updated = false;
        
        foreach ($data as $key => $document) {
            if ($this->matchesFilter($document, $filter)) {
                if (isset($update['$set'])) {
                    $data[$key] = array_merge($document, $update['$set']);
                } else {
                    $data[$key] = array_merge($document, $update);
                }
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $this->saveData($data);
        }
        
        return ['modifiedCount' => $updated ? 1 : 0];
    }
    
    public function deleteOne($filter) {
        $data = $this->loadData();
        $deleted = false;
        
        foreach ($data as $key => $document) {
            if ($this->matchesFilter($document, $filter)) {
                unset($data[$key]);
                $deleted = true;
                break;
            }
        }
        
        if ($deleted) {
            $this->saveData(array_values($data));
        }
        
        return ['deletedCount' => $deleted ? 1 : 0];
    }
    
    public function countDocuments($filter = []) {
        $results = $this->find($filter);
        return count($results);
    }
    
    private function loadData() {
        if (!file_exists($this->file_path)) {
            return [];
        }
        $content = file_get_contents($this->file_path);
        return json_decode($content, true) ?: [];
    }
    
    private function saveData($data) {
        file_put_contents($this->file_path, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    private function matchesFilter($document, $filter) {
        foreach ($filter as $key => $value) {
            if (!isset($document[$key]) || $document[$key] !== $value) {
                return false;
            }
        }
        return true;
    }
    
    private function generateId() {
        return uniqid() . '_' . time();
    }
}

// MongoDB-like functions for compatibility
function getMongoConnection() {
    return new SimpleMongoDB();
}

function executeMongoQuery($collection_name, $operation, $params = []) {
    $mongo = new SimpleMongoDB();
    $collection = $mongo->getCollection($collection_name);
    
    switch ($operation) {
        case 'find':
            // Handle both old format (direct filter) and new format (params['filter'])
            $filter = is_array($params) && isset($params['filter']) ? $params['filter'] : $params;
            $options = is_array($params) && isset($params['options']) ? $params['options'] : [];
            return $collection->find($filter, $options);
        case 'findOne':
            $filter = is_array($params) && isset($params['filter']) ? $params['filter'] : $params;
            return $collection->findOne($filter);
        case 'insertOne':
            $document = is_array($params) && isset($params['document']) ? $params['document'] : $params;
            return $collection->insertOne($document);
        case 'updateOne':
            $filter = is_array($params) && isset($params['filter']) ? $params['filter'] : [];
            $update = is_array($params) && isset($params['update']) ? $params['update'] : [];
            return $collection->updateOne($filter, $update);
        case 'deleteOne':
            $filter = is_array($params) && isset($params['filter']) ? $params['filter'] : $params;
            return $collection->deleteOne($filter);
        case 'countDocuments':
            $filter = is_array($params) && isset($params['filter']) ? $params['filter'] : $params;
            return $collection->countDocuments($filter);
        default:
            throw new Exception("Unknown operation: $operation");
    }
}

function executeMongoInsert($collection_name, $document) {
    return executeMongoQuery($collection_name, 'insertOne', ['document' => $document]);
}

function executeMongoUpdate($collection_name, $filter, $update) {
    return executeMongoQuery($collection_name, 'updateOne', ['filter' => $filter, 'update' => $update]);
}

function executeMongoDelete($collection_name, $filter) {
    return executeMongoQuery($collection_name, 'deleteOne', ['filter' => $filter]);
}

function mongoCursorToArray($cursor) {
    return is_array($cursor) ? $cursor : [];
}

function getMongoDocument($collection_name, $filter) {
    return executeMongoQuery($collection_name, 'findOne', ['filter' => $filter]);
}

function countMongoDocuments($collection_name, $filter = []) {
    return executeMongoQuery($collection_name, 'countDocuments', ['filter' => $filter]);
}
?> 