<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Quote.php';

// Instantiate Database & connect
$database = new Database();
$db = $database->connect();

// Instantiate Quote object
$quote = new Quote($db);

// Get raw input data
$data = json_decode(file_get_contents("php://input"));

//  Validate ID
if (!isset($data->id) || intval($data->id) <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['message' => 'Missing or invalid ID']);
    exit;
}

// Assign ID to the object
$quote->id = intval($data->id);

// Attempt to delete the quote
if ($quote->delete()) {
    http_response_code(200); // OK
    echo json_encode([
        'id' => $quote->id,
        'message' => 'Quote deleted'
    ]);
} else {
    http_response_code(404); // Not found
    echo json_encode([
        'message' => 'No quote found with the specified ID'
    ]);
}
