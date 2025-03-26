<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
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

// Validate input
if (empty($data->id) || empty($data->category)) {
    echo json_encode(['message' => 'Missing Required Parameters']);
    exit;
}

// Set data
$category->id = intval($data->id);
$category->category = $data->category;

// Update category
if ($category->update()) {
    echo json_encode([
        'id' => $category->id,
        'category' => $category->category
    ]);
} else {
    echo json_encode(['message' => 'Category not updated']);
}
