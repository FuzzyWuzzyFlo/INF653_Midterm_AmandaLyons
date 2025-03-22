<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Quote.php'; 

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize the Quote model
$quote = new Quote($db);

$id = isset($_GET['id']) ? $_GET['id'] : null;
$author_id = isset($_GET['author_id']) ? $_GET['author_id'] : null;
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;

if ($id || $author_id || $category_id) {
    $result = $quote->readFiltered($id, $author_id, $category_id);
} else {
    $result = $quote->read();
}

$num = $result->rowCount();

if ($num > 0) {
    $quotes_arr = [];
    $quotes_arr['data'] = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $quote_item = [
            'id' => $row['id'],
            'quote' => $row['quote'],
            'author_id' => $row['author_id'],
            'category_id' => $row['category_id']
        ];

        $quotes_arr['data'][] = $quote_item;
    }

    echo json_encode($quotes_arr);
} else {
    echo json_encode(['message' => 'No Quotes Found']);
}

