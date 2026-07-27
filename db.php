<?php

// MongoDB connection wrapper for E-Land.
// Existing MySQL-based code still needs migration to use MongoDB queries.

require_once __DIR__ . '/mongodb.php';

// Legacy compatibility placeholders.
// This file no longer opens a MySQL connection.
$conn = null;
$mysqli = null;

function get_mongo_manager()
{
    global $mongoManager;
    return $mongoManager;
}

function get_mongo_database_name()
{
    global $mongoDb;
    return $mongoDb;
}

function mongo_collection(string $collection)
{
    return get_mongo_database_name() . '.' . $collection;
}

// Existing SQL table schema definitions are now represented as MongoDB collections.
// Collections to create in MongoDB Compass:
//   users
//   owners
//   land_records
//   ownership_history
//   land_cases
//   restrictions
//   survey_plans
//   land_images
//   verification_logs
//   administrators
//   lease_requests
//   lease_rejected_requests
//   purchase_requests
