<?php

require_once __DIR__ . '/../mongodb.php';

function esc($s) {
    return htmlspecialchars(trim((string) $s));
}

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

// MongoDB collections used by the admin area.
// Create these collections in MongoDB Compass if they do not already exist:
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
