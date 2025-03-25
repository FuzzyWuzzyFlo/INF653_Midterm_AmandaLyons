<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Author.php';

// Instantiate Database & connect
$database = new Database();
$db = $database->connect();

// Instantiate Author object
$author = new Author($db);

// Get ID from the URL parameter
$author->id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validate ID
if (empty($author->id)) {
    echo json_encode(['message' => 'Missing required ID']);
    exit;
}

// Fetch the single author
if ($author->readSingle()) {
    //  Return the single author as JSON
    echo json_encode([
        'id' => $author->id,
        'author' => $author->author
    ]);
} else {
    //  No author found
    echo json_encode(['message' => 'Author not found']);
}
?>