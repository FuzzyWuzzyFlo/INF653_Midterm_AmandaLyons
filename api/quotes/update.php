<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
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

// Validate required fields
if (
    empty($data->id) || 
    empty($data->quote) || 
    empty($data->author_id) || 
    empty($data->category_id)
) {
    echo json_encode(['message' => 'Missing Required Parameters']);
    exit;
}

// Assign values
$quote->id = (int)$data->id;
$quote->quote = $data->quote;
$quote->author_id = (int)$data->author_id;
$quote->category_id = (int)$data->category_id;

// Check if author and category exist before updating
if (!$quote->isValidAuthor($quote->author_id)) {
    echo json_encode(['message' => 'author_id Not Found']);
    exit;
}

if (!$quote->isValidCategory($quote->category_id)) {
    echo json_encode(['message' => 'category_id Not Found']);
    exit;
}

// Attempt update
if ($quote->update()) {
    echo json_encode([
        'id' => $quote->id,
        'quote' => $quote->quote,
        'author_id' => $quote->author_id,
        'category_id' => $quote->category_id
    ]);
} else {
    echo json_encode(['message' => 'Quote not found or no changes made']);
}
