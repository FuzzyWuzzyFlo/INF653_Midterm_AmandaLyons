<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Category.php';

// Instantiate Database & connect
$database = new Database();
$db = $database->connect();

// Instantiate Category object
$category = new Category($db);

// Get ID from the URL parameter
$category->id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validate ID
if (empty($category->id)) {
    echo json_encode(['message' => 'Missing required ID']);
    exit;
}

// Fetch the single category
if ($category->readSingle()) {
    // Return the single category as JSON
    echo json_encode([
        'id' => $category->id,
        'category' => $category->category
    ]);
} else {
    // No category found
    echo json_encode(['message' => 'Category not found']);
}
?>
