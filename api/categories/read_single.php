<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Category.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate object
$category = new Category($db);

// Get ID
$category->id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(['message' => 'Missing ID']));

// Get category
$category->read_single();

if ($category->category) {
    // Create array
    $category_arr = array(
        'id' => $category->id,
        'category' => $category->category,
    );

    // Make JSON
    echo json_encode($category_arr);
} else {
    // Set response code - 404 Not Found
    http_response_code(404);
    echo json_encode(['message' => 'Category_Id not found']);
}