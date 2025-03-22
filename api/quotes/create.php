<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Quote.php'; 

// Instantiate Database & connect
$database = new Database();
$db = $database->connect();

// Instantiate Quote object
$quote = new Quote($db);

// Get raw input data
$data = json_decode(file_get_contents("php://input"));

// Check if JSON is valid
if (!$data) {
    echo json_encode(['message' => 'Invalid JSON format']);
    exit;
}

// Debugging - Check raw input
error_log("Raw Input: " . file_get_contents("php://input"));
error_log("Decoded Data: " . print_r($data, true));

// Validate required fields
if (
    !isset($data->quote) || 
    !isset($data->author_id) || 
    !isset($data->category_id) ||
    empty(trim($data->quote)) || 
    !is_numeric($data->author_id) || 
    !is_numeric($data->category_id)
) {
    echo json_encode(['message' => 'Missing or invalid required fields']);
    exit;
}

// Assign data to the object
$quote->quote = htmlspecialchars(strip_tags($data->quote));
$quote->author_id = intval($data->author_id);
$quote->category_id = intval($data->category_id);

// ✅ Attempt to create the quote
if ($quote->create()) {
    echo json_encode(['message' => 'Quote created']);
} else {
    echo json_encode(['message' => 'Failed to create quote']);
}