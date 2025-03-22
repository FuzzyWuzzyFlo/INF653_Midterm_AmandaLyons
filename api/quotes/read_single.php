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
$quote->id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validate ID
if (empty($quote->id)) {
    echo json_encode(['message' => 'Missing required ID']);
    exit;
}

// Fetch the single quote
$result = $quote->readFiltered($quote->id);
$quote_data = $result->fetch(PDO::FETCH_ASSOC);

if ($quote_data) {
    // Return the single quote as JSON
    echo json_encode([
        'id' => $quote_data['id'],
        'quote' => $quote_data['quote'],
        'author_id' => $quote_data['author_id'],
        'category_id' => $quote_data['category_id']
    ]);
} else {
    // No quote found
    echo json_encode(['message' => 'Quote not found']);
}
