<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Author.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate Author object
$author = new Author($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Check if ID is provided
if (!isset($data->id) || intval($data->id) <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['message' => 'Missing or invalid ID']);
    exit;
}

// Set ID for deletion
$author->id = intval($data->id);

// Call the delete function
$response = $author->delete();

if ($response && is_array($response)) {
    http_response_code($response['status']);
    echo json_encode($response);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Unexpected error while deleting author']);
}
