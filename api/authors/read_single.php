<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Author.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate blog post object
$author = new Author($db);

// Get ID
$author->id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(['message' => 'Missing ID']));

// Get post
$author->read_single();

if ($author->author) {
    // Create array
    $author_arr = array(
        'id' => $author->id,
        'author' => $author->author,
    );

    // Make JSON
    echo json_encode($author_arr);
} else {
    // Set response code - 404 Not Found
    http_response_code(404);
    echo json_encode(['message' => 'AuthorId not found']);
}
