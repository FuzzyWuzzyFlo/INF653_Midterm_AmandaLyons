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

// Attempt to delete author
if ($author->delete()) {
    http_response_code(200); // OK
    echo json_encode([
        'id' => $author->id,
        'message' => 'Author deleted'
    ]);
} else {
    http_response_code(404); // Not found
    echo json_encode([
        'message' => 'No author found with the specified ID'
    ]);
}
