<?php
/**
 * MongoDB connection helper for E-Land.
 *
 * Collections to create in MongoDB Compass:
 *   - users
 *   - owners
 *   - land_records
 *   - ownership_history
 *   - land_cases
 *   - restrictions
 *   - survey_plans
 *   - land_images
 *   - verification_logs
 *   - administrators
 *   - lease_requests
 *   - lease_rejected_requests
 *   - purchase_requests
 */

$mongoUri = getenv('MONGO_URI') ?: 'mongodb://127.0.0.1:27017';
$mongoDb = getenv('MONGO_DB') ?: 'E-land';

try {
    $mongoManager = new MongoDB\Driver\Manager($mongoUri);
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    die('MongoDB connection failed: ' . $e->getMessage());
}

/**
 * Execute a query against a MongoDB collection.
 *
 * @param string $collection
 * @param array $filter
 * @param array $options
 * @return MongoDB\Driver\Cursor
 */
function mongo_find(string $collection, array $filter = [], array $options = [])
{
    global $mongoManager, $mongoDb;
    $namespace = "$mongoDb.$collection";
    $query = new MongoDB\Driver\Query($filter, $options);
    return $mongoManager->executeQuery($namespace, $query);
}

/**
 * Find a single document in a MongoDB collection.
 *
 * @param string $collection
 * @param array $filter
 * @param array $options
 * @return object|null
 */
function mongo_find_one(string $collection, array $filter = [], array $options = [])
{
    $options = array_merge(['limit' => 1], $options);
    $cursor = mongo_find($collection, $filter, $options);
    $documents = $cursor->toArray();
    return $documents[0] ?? null;
}

/**
 * Insert a document into a MongoDB collection.
 *
 * @param string $collection
 * @param array $document
 * @return MongoDB\Driver\WriteResult
 */
function mongo_insert_one(string $collection, array $document)
{
    global $mongoManager, $mongoDb;
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert($document);
    return $mongoManager->executeBulkWrite("$mongoDb.$collection", $bulk);
}

/**
 * Update a document in a MongoDB collection.
 *
 * @param string $collection
 * @param array $filter
 * @param array $update
 * @param array $options
 * @return MongoDB\Driver\WriteResult
 */
function mongo_update_one(string $collection, array $filter, array $update, array $options = [])
{
    global $mongoManager, $mongoDb;
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->update($filter, ['$set' => $update], array_merge(['multi' => false, 'upsert' => false], $options));
    return $mongoManager->executeBulkWrite("$mongoDb.$collection", $bulk);
}

/**
 * Delete a single document from a MongoDB collection.
 *
 * @param string $collection
 * @param array $filter
 * @return MongoDB\Driver\WriteResult
 */
function mongo_delete_one(string $collection, array $filter)
{
    global $mongoManager, $mongoDb;
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->delete($filter, ['limit' => 1]);
    return $mongoManager->executeBulkWrite("$mongoDb.$collection", $bulk);
}
