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

// ✅ Get raw input data
$data = json_decode(file_get_contents("php://input"));

// ✅ Debugging - Check raw data
error_log("Raw Input: " . file_get_contents("php://input"));
error_log("Decoded Data: " . print_r($data, true));

// ✅ Check if ID is provided and valid
if (!isset($data->id) || intval($data->id) <= 0) {
    echo json_encode(['message' => 'Missing or invalid ID']);
    exit;
}

// ✅ Assign ID to the object
$quote->id = intval($data->id);

// ✅ Attempt to delete the quote
if ($quote->delete()) {
    echo json_encode(['message' => 'Quote deleted']);
} else {
    echo json_encode(['message' => 'No quote found with the specified ID']);
}
