<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Category.php';

// Instantiate DB & connect
$database = new Database();<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Category.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate Category object
$category = new Category($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Validate ID
if (!isset($data->id) || intval($data->id) <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['message' => 'Missing or invalid ID']);
    exit;
}

// Set ID for deletion
$category->id = intval($data->id);

// Attempt to delete category and handle structured response
$response = $category->delete();

if ($response && is_array($response)) {
    http_response_code($response['status']);
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Unexpected error during deletion']);
}

$db = $database->connect();

// Instantiate Category object
$category = new Category($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Validate ID
if (!isset($data->id) || intval($data->id) <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['message' => 'Missing or invalid ID']);
    exit;
}

// Set ID for deletion
$category->id = intval($data->id);

// Attempt to delete category
if ($category->delete()) {
    http_response_code(200); // OK
    echo json_encode([
        'id' => $category->id,
        'message' => 'Category deleted'
    ]);
} else {
    http_response_code(404); // Not found
    echo json_encode([
        'message' => 'No category found with the specified ID'
    ]);
}
