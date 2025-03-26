<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Quote.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate Quote object
$quote = new Quote($db);

// Get raw input data
$data = json_decode(file_get_contents("php://input"));

// Validate ID
if (!isset($data->id) || intval($data->id) <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['message' => 'Missing or invalid ID']);
    exit;
}

// Assign ID to the object
$quote->id = intval($data->id);

// Delete quote and handle structured response
$response = $quote->delete();

if ($response && is_array($response)) {
    http_response_code($response['status']);
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Unexpected error during deletion']);
}
