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

// Get ID from the URL parameter
$quote->id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Validate ID
if (empty($quote->id)) {
    echo json_encode(['message' => 'Missing required ID']);
    exit;
}

// Fetch the single quote using readSingle()
if ($quote->readSingle()) {
    // Return the single quote as JSON
    echo json_encode([
        'id' => $quote->id,
        'quote' => $quote->quote,
        'author' => $quote->author, // Return author name instead of ID
        'category' => $quote->category // Return category name instead of ID
    ]);
} else {
    // No quote found
    echo json_encode(['message' => 'Quote not found']);
}

