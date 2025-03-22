<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Quote.php'; 

// Instantiate Database & connect
$database = new Database();
$db = $database->connect();

// Instantiate Quote object
$quote = new Quote($db);

// Get raw input data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (
    empty($data->id) || 
    empty($data->quote) || 
    empty($data->author_id) || 
    empty($data->category_id)
) {
    echo json_encode(['message' => 'Missing required fields']);
    exit;
}

// Assign data to the object
$quote->id = (int)$data->id;
$quote->quote = htmlspecialchars(strip_tags($data->quote));
$quote->author_id = (int)$data->author_id;
$quote->category_id = (int)$data->category_id;

// Attempt to update the quote
if ($quote->update()) {
    echo json_encode(['message' => 'Quote updated']);
} else {
    echo json_encode(['message' => 'Quote not found or no changes made']);
}
